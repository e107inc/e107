<?php
/*
* e107 website system
*
* Copyright (C) 2008-2013 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

/**
 *	@package    e107
 *	@subpackage	e107_handlers
 *
 *	Handler for 'notify' events - sends email notifications to the appropriate user groups
 */

if (!defined('e107_INIT')) { exit; }

class notify
{
	public $notify_prefs;

	function __construct()
	{
		include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_notify.php');

		if(empty($this->notify_prefs))
		{
			$this->notify_prefs = e107::getConfig('notify')->getPref();
		}

	}


	/**
	 * Register core and plugin notification events.
	 */
	public function registerEvents()
	{
		$active = e107::getConfig()->get('notify');

		if(empty($active) && e_PAGE == 'notify.php')
		{
			e107::getMessage()->addDebug('Notify is disabled!');
			return false;
		}

		$e_event = e107::getEvent();

		if(varset($this->notify_prefs['event']))
		{
			foreach ($this->notify_prefs['event'] as $id => $status)
			{
				$include = null;

				if ($status['class'] != e_UC_NOBODY) // 255;
				{
					if(varset($status['include'])) // Plugin
					{
						$include 	= e_PLUGIN.$status['include']."/e_notify.php";

						if(varset($status['legacy']) != 1)
						{
							$class 		= $status['include']."_notify";
							$method 	= $id;
							$e_event->register($id, array($class, $method), $include);
						}
						else
						{
							$e_event->register($id, 'notify_'.$id, $include);
						}
					}
					else // core
					{
						if(method_exists($this, 'notify_'.$id)) // as found below.
						{
							$e_event->register($id, array('notify', 'notify_'.$id));
						}
						else
						{
							$e_event->register($id, array('notify', 'generic')); // use generic notification.
						}
					}


				}
			}
		}

	//	e107::getEvent()->debug();
	}


	/**
	 * Generic Notification method when none defined. 
	 */
	function generic($data,$id)
	{
		$message = print_a($data,true); 
		$this->send($id, 'Event Triggered: '.$id, $message);	
	}




	/**
	 * Send an email notification following an event.
	 *
	 * The email is sent via a common interface, which will send immediately for small numbers of recipients, and queue for larger.
	 * 
	 * @param string $id - identifies event actions
	 * @param string $subject - subject for email
	 * @param string $message - email message body
	 * @return void
	 *
	 *	@todo handle 'everyone except' clauses (email address filter done)
	 *	@todo set up pref to not notify originator of event which caused notify (see $blockOriginator)
	 */
	function send($id, $subject, $message, $media=array())
	{

		$tp = e107::getParser();
		$sql = e107::getDb();

		$subject = $tp->toEmail(SITENAME.': '.$subject);
		$message = $tp->replaceConstants($message, "full");
	//	$message = $tp->toEmail($message);
		$emailFilter = '';
		$notifyTarget = $this->notify_prefs['event'][$id]['class'];

		if ($notifyTarget == '-email')
		{
			$emailFilter = $this->notify_prefs['event'][$id]['email'];
		}

		$blockOriginator = FALSE;		// TODO: set this using a pref
		$recipients = array();

		if ($notifyTarget == 'email') // Single email address - that can always go immediately
		{
			if (!$blockOriginator || ($this->notify_prefs['event'][$id]['email'] != USEREMAIL))
			{
				$recipients[] = array(
								 'mail_recipient_email' => $this->notify_prefs['event'][$id]['email']
								 );	
			}
		}
		elseif (is_numeric($notifyTarget))
		{
			switch ($notifyTarget)
			{
				case e_UC_MAINADMIN :
					$qry = "`user_admin` = 1 AND `user_perms` = '0' AND `user_ban` = 0";
					break;
				case e_UC_ADMIN :
					$qry = "`user_admin` = 1 AND `user_ban` = 0";
					break;
				case e_UC_MEMBER :
					$qry = "`user_ban` = 0";
					break;
				default :
					$qry = "user_ban = 0 AND user_class REGEXP '(^|,)(".$notifyTarget.")(,|$)'";
					break;
			}

			$qry = 'SELECT user_id,user_name,user_email,user_join,user_lastvisit FROM `#user` WHERE '.$qry;

			if ($blockOriginator)
			{
				$qry .= ' AND `user_id` != '.USERID;
			}

			if (false !== ($count = $sql->gen($qry)))
			{
				// Now add email addresses to the list
				while ($row = $sql->fetch(MYSQL_ASSOC))
				{
					if ($row['user_email'] != $emailFilter)
					{

						$unsubscribe = array('date'=>$row['user_join'],'email'=>$row['user_email'],'id'=>$row['user_id'], 'plugin'=>'user', 'userclass'=>$notifyTarget);
						$urlQuery = http_build_query($unsubscribe,null,'&');
						$exclude  = array(e_UC_MEMBER,e_UC_ADMIN, e_UC_MAINADMIN); // no unsubscribe for these classes.
						$unsubUrl   = SITEURL."unsubscribe.php?id=".base64_encode($urlQuery);
						$unsubMessage =  "This message was sent to ".$row['user_email'].". If you don't want to receive these emails in the future, please <a href='".$unsubUrl."'>unsubscribe</a>.";


						$recipients[] = array(
							'mail_recipient_id'     => $row['user_id'],
							'mail_recipient_name'   => $row['user_name'],		// Should this use realname?
							'mail_recipient_email'  => $row['user_email'],
							'mail_target_info'		=> array(
								'USERID'		=> $row['user_id'],
								'DISPLAYNAME' 	=> $row['user_name'],
						//		'SIGNUP_LINK' 	=> '',
								'USERNAME' 		=> $row['user_name'],
								'USERLASTVISIT' => $row['user_lastvisit'],
								'UNSUBSCRIBE'	=> (!in_array($notifyTarget, $exclude)) ? $unsubUrl : '',
								'UNSUBSCRIBE_MESSAGE' => (!in_array($notifyTarget, $exclude)) ? $unsubMessage : ''
							)
						);
					}
				}
			}

		}

		if(E107_DEBUG_LEVEL > 0)
		{
			$data = array('id'=>$id, 'subject'=>$subject, 'recipients'=> $recipients, 'prefs'=>$this->notify_prefs['event'][$id], 'message'=>$message);
			e107::getMessage()->addDebug(print_a($data,true));
			e107::getLog()->add('Notify Debug', $data, E_LOG_INFORMATIVE, "NOTIFY_DBG");
			return;
		}



		if (count($recipients))
		{
			require_once(e_HANDLER.'mail_manager_class.php');
			$mailer = new e107MailManager;

			// Create the mail body
			$mailData = array(
				'mail_total_count'      => count($recipients),
				'mail_content_status' 	=> MAIL_STATUS_TEMP,
				'mail_create_app' 		=> 'notify',
				'mail_title' 			=> 'NOTIFY',
				'mail_subject' 			=> $subject,
				'mail_sender_email' 	=> e107::getPref('siteadminemail'),
				'mail_sender_name'		=> e107::getPref('siteadmin'),
				'mail_notify_complete' 	=> 0,			// NEVER notify when this email sent!!!!!
				'mail_body' 			=> $message,
				'template'				=> 'notify',
				'mail_send_style'       => 'notify'
			);

			if(!empty($media) && is_array($media))
			{
				foreach($media as $k=>$v)
				{
					$mailData['mail_media'][$k] = array('path'=>$v);
				}
			}
			
			$result = $mailer->sendEmails('notify', $mailData, $recipients);
			e107::getLog()->e_log_event(10,-1,'NOTIFY',$subject,$message,FALSE,LOG_TO_ROLLING);
		}
		else
		{
			$data = array('qry'=>$qry, 'error'=>'No recipients');
			e107::getLog()->add('Notify Debug', $data,  E_LOG_WARNING_, "NOTIFY_DBG");
		}
	}





	// ---------------------------------------



	function notify_usersup($data)
	{
		$message = "";
		foreach ($data as $key => $value)
		{
			if($key != "password1" && $key != "password2" && $key != "email_confirm" && $key != "register")
			{
				if(is_array($value))  // show user-extended values.
				{
					foreach($value as $k => $v)
					{
						$message .= str_replace("user_","",$k).': '.$v.'<br />';
					}
				}
				else
				{
					$message .=  $key.': '.$value.'<br />';
				}
			}
		}

		$this->send('usersup', NT_LAN_US_1, $message);
	}

	/**
	 * @param $data
	 */
	function notify_userveri($data)
	{
		$msgtext = NT_LAN_UV_2.$data['user_id']."\n";
		$msgtext .= NT_LAN_UV_3.$data['user_loginname']."\n";
		$msgtext .= NT_LAN_UV_4.e107::getIPHandler()->getIP(FALSE);

		$this->send('userveri', NT_LAN_UV_1, $msgtext);
	}


	function notify_login($data)
	{
		$message = "";
		foreach ($data as $key => $value)
		{
			$message .= $key.': '.$value.'<br />';
		}

		$this->send('login', NT_LAN_LI_1, $message);
	}

	function notify_logout()
	{
		$this->send('logout', NT_LAN_LO_1, USERID.'. '.USERNAME.' '.NT_LAN_LO_2);
	}

	function notify_flood($data)
	{
		$this->send('flood', NT_LAN_FL_1, NT_LAN_FL_2.': '.e107::getIPHandler()->ipDecode($data));
	}

	function notify_subnews($data)
	{
		$message = "";
		foreach ($data as $key => $value)
		{
			$message .= $key.': '.$value.'<br />';
		}

		$this->send('subnews', NT_LAN_SN_1, $message);
	}

	function notify_newspost($data)
	{
		$message = '<b>'.$data['news_title'].'</b>';
		if (vartrue($data['news_summary'])) $message .= '<br /><br />'.$data['news_summary'];
		if (vartrue($data['news_body'])) $message .= '<br /><br />'.$data['news_body'];
		if (vartrue($data['news_extended'])) $message.= '<br /><br />'.$data['news_extended'];
		$this->send('newspost', $data['news_title'], e107::getParser()->text_truncate(e107::getParser()->toDB($message), 400, '...'));
	}

	function notify_newsupd($data)
	{
		$message = '<b>'.$data['news_title'].'</b>';
		if (vartrue($data['news_summary'])) $message .= '<br /><br />'.$data['news_summary'];
		if (vartrue($data['news_body'])) $message .= '<br /><br />'.$data['news_body'];
		if (vartrue($data['news_extended'])) $message.= '<br /><br />'.$data['news_extended'];
		$this->send('newsupd', NT_LAN_NU_1.': '.$data['news_title'], e107::getParser()->text_truncate(e107::getParser()->toDB($message), 400, '...'));
	}

	function notify_newsdel($data)
	{
		$this->send('newsdel', NT_LAN_ND_1, NT_LAN_ND_2.': '.$data);
	}


	function notify_maildone($data)
	{
		$message = '<b>'.$data['mail_subject'].'</b><br /><br />'.$data['mail_body'];
		$this->send('maildone', NT_LAN_ML_1.': '.$data['mail_subject'], $message);
	}


	function notify_fileupload($data)
	{
		$message = '<b>'.$data['upload_name'].'</b><br /><br />'.$data['upload_description'].'<br /><br />'.$data['upload_size'].'<br /><br />'.$data['upload_user'];
		$this->send('fileupload', $data['upload_name'], $message);
	}



	function notify_admin_news_created($data)
	{
		$this->notify_newspost($data);
	}


	function notify_admin_news_subscribers($data)
	{
		$tp = e107::getParser();
		$url = e107::getUrl()->create('news/view/item', $data,'full=1');
		$message = "<b><a href='".$url."'>".$tp->toHtml($data['news_title'])."</a></b>";
		$img = explode(",",$data['news_thumbnail']);


		if (vartrue($data['news_summary'])){ $message .= '<br /><br />'.$tp->toEmail($data['news_summary']);    }

		$message .= "<a href='".$url."'>View now</a>";

		$this->send('admin_news_updated', $data['news_title'], $message, $img);

		print_a($message);
		return $message;

	}




}

	/*
	if (isset($nt->notify_prefs['plugins']) && e_PAGE != 'notify.php')
	{
	foreach ($nt->notify_prefs['plugins'] as $plugin_id => $plugin_settings)
	{
	if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
	{
	require_once(e_PLUGIN.$plugin_id.'/e_notify.php');
	}
		}
		}

	*/

