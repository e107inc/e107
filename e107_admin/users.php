<?php

/*
* e107 website system
*
* Copyright (C) 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Administration Area - Users
*
* $URL$
* $Id$
*
*/
require_once ('../class2.php');

e107::coreLan('user');
e107::coreLan('users', true);
e107::coreLan('date');

// TODO List of permissions to be implemented
// "options" - getperms('4|U2')
// "create" - getperms('4|U1|U0')
// "ranks" - getperms('4|U3')
// "default" - getperms('4|U1|U0')

class users_admin extends e_admin_dispatcher
{
	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'users_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'users_admin_form_ui',
			'uipath' 		=> null,
			//'perm'			=> '0',
		)				
	);	


	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => '0'),
		'main/add' 	=> array('caption'=> LAN_USER_QUICKADD, 'perm' => '4|U0|U1'),
		'main/prefs' 	=> array('caption'=> LAN_OPTIONS, 'perm' => '4|U2'),
		'main/ranks'	=> array('caption'=> LAN_USER_RANKS, 'perm' => '4|U3')		
	);
	
	/*
		FIXME - move user prune to Schedule tasks (cron)
		$var ['prune']['text'] = LAN_USER_PRUNE;
		$var ['prune']['link'] = e_ADMIN.'users.php?action=prune';// Will be moved to "Schedule tasks"
		$var ['prune']['perm'] = '4';

*/
	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'main/admin'=> 'main/list',
		'main/userclass'=> 'main/list',					
		'main/test'=> 'main/list',					
	);	
	
	protected $menuTitle = 'users';
	
	/**
	 * Run observers/headers override
	 *
	 * @return users_admin
	 */
	public function runObservers($run_header = true)
	{
		// Catch useraction
		if (isset($_POST['useraction']))
		{
			if(is_array($_POST['useraction']))
			{
				foreach ($_POST['useraction'] as $key => $val)
				{
					if ($val)
					{
						$_POST['useraction'] = $val;
						$_POST['userip'] = $_POST['userip'][$key];
						$_POST['userid'] = (int) $key;
						break;
					}
				}
			}
			
			// FIXME IMPORTANT - permissions per action/trigger
			
			// map useraction to UI trigger
			switch ($_POST['useraction']) 
			{
				### etrigger_delete
				case 'deluser':
					if($_POST['userid'])
					{
						$id = $_POST['userid'];
						$_POST['etrigger_delete'] = array($id => $id);
						$user = e107::getDb()->retrieve('user', 'user_email, user_name', 'user_id='.$id);
						// TODO lan
						$this->getController()->deleteConfirmMessage = "You are about to delete {$user['user_name']} ({$user['user_email']}) with ID #{$id}. Are you sure?";//
					}
				break;
				
				// map to List{USERACTION}Trigger()
				case 'unban':
				case 'ban':
				case 'verify':
				case 'reqverify':
				case 'resend':
				case 'loginas':
				case 'unadmin':
					$_POST['etrigger_'.$_POST['useraction']] = $_POST['userid'];
				break;	
				
				// redirect to AdminObserver/AdminPage()
				case 'admin':
				case 'adminperms':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('admin')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to UserclassObserver/UserclassPage()
				case 'userclass':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('userclass')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to TestObserver/TestPage
				case 'test':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('test')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to TestObserver/TestPage
				case 'usersettings':
					header('location:'.e107::getUrl()->create('user/profile/edit', 'id='.(int) $_POST['userid'], 'full=1&encode=0'));
					exit;
				break;
			}
			
		}
		
		return parent::runObservers($run_header);
	}
}


class users_admin_ui extends e_admin_ui
{
		
	protected $pluginTitle = LAN_USER;
	protected $pluginName = 'core';
	protected $table = "user";
		
//	protected $listQry = "SELECT SQL_CALC_FOUND_ROWS * FROM #users"; // without any Order or Limit. 
	protected $listQry = "SELECT SQL_CALC_FOUND_ROWS u.*,ue.* from #user AS u left join #user_extended AS ue ON u.user_id = ue.user_extended_id  "; // without any Order or Limit.
			
	//protected $editQry = "SELECT * FROM #users WHERE comment_id = {ID}";
	
	protected $pid 			= "user_id";
	protected $perPage 		= 30;
	protected $batchDelete 	= true;
	protected $listOrder 	= 'user_id DESC'; 
	
	/**
	 * Show confirm screen before (batch/single) delete
	 * @var boolean
	 */
	public $deleteConfirmScreen = true;
	
	/**
	 * @var boolean
	 */
	protected $batchCopy = false;
	
	/**
	 * List (numerical array) of only disallowed for this controller actions
	 */
	protected $disallow = array('edit', 'create');

	
	//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
	protected $fields = array(
		'checkboxes'		=> array('title'=> '',				'type' => null, 'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
	
		'user_id' 			=> array('title' => 'Id',			'type' =>'integer',		'width' => '5%','forced' => true),
//		'user_status' 		=> array('title' => LAN_STATUS,		'type' => 'method',	'alias'=>'user_status', 'width' => 'auto','forced' => true, 'nosort'=>TRUE),
		'user_ban' 			=> array('title' => LAN_STATUS,	'type' => 'method', 'width' => 'auto', 'filter'=>true, 'batch'=>true,'thclass'=>'center', 'class'=>'center'),
	
		'user_name' 		=> array('title' => LAN_USER_01,	'type' => 'text',	'width' => 'auto','thclass' => 'left first'), // Display name
 		'user_loginname' 	=> array('title' => LAN_USER_02,	'type' => 'text',	'width' => 'auto'), // User name
 		'user_login' 		=> array('title' => LAN_USER_03,	'type' => 'text',	'width' => 'auto'), // Real name (no real vetting)
 		'user_customtitle' 	=> array('title' => LAN_USER_04,	'type' => 'text',	'width' => 'auto'), // No real vetting
 		'user_password' 	=> array('title' => LAN_USER_05,	'type' => 'password',	'width' => 'auto'), //TODO add md5 option to form handler? 
		'user_sess' 		=> array('title' => 'Session',		'type' => 'text',	'width' => 'auto'), // Photo
 		'user_image' 		=> array('title' => LAN_USER_07,	'type' => 'text',	'width' => 'auto'), // Avatar
 		'user_email' 		=> array('title' => LAN_USER_08,	'type' => 'text',	'width' => 'auto'),
		'user_hideemail' 	=> array('title' => LAN_USER_10,	'type' => 'boolean',	'width' => 'auto', 'thclass'=>'center', 'class'=>'center', 'filter'=>true, 'batch'=>true, 'readParms'=>'trueonly=1'),
		'user_xup' 			=> array('title' => 'Xup',			'type' => 'text',	'width' => 'auto'),
		'user_class' 		=> array('title' => LAN_USER_12,	'type' => 'userclasses' , 'writeParms' => 'classlist=classes', 'filter'=>true, 'batch'=>true),
		'user_join' 		=> array('title' => LAN_USER_14,	'type' => 'datestamp', 	'width' => 'auto', 'writeParms'=>'readonly=1'),
		'user_lastvisit' 	=> array('title' => LAN_USER_15,	'type' => 'datestamp', 	'width' => 'auto'),
		'user_currentvisit' => array('title' => LAN_USER_16,	'type' => 'datestamp', 	'width' => 'auto'),
		'user_comments' 	=> array('title' => LAN_USER_17,	'type' => 'int', 	'width' => 'auto','thclass'=>'right','class'=>'right'),
		'user_lastpost' 	=> array('title' => 'Last Post',	'type' => 'datestamp', 	'width' => 'auto'),
		'user_ip' 			=> array('title' => LAN_USER_18,	'type' => 'ip',		'width' => 'auto'),
		//	'user_prefs' 		=> array('title' => LAN_USER_20,	'type' => 'text', 	'width' => 'auto'),
		'user_visits' 		=> array('title' => LAN_USER_21,	'type' => 'int', 'width' => 'auto','thclass'=>'right','class'=>'right'),
		'user_admin' 		=> array('title' => LAN_USER_22,	'type' => 'boolean', 'width' => 'auto', 'thclass'=>'center', 'class'=>'center', 'filter'=>true, 'batch'=>true, 'readParms'=>'trueonly=1'),
		'user_perms' 		=> array('title' => LAN_USER_23,	'type' => 'method', 	'width' => 'auto'),
		'user_pwchange'		=> array('title' => LAN_USER_24,	'type'=>'datestamp' , 'width' => 'auto'),
		//'commatest'				=> array('title' => 'TEST',	'type'=>'comma' , 'writeParms' => 'data=test1,test2,test3&addAll&clearAll', 'width' => 'auto', 'filter'=>true, 'batch'=>true),
					
	);
	
	protected $fieldpref = array('user_ban','user_name','user_loginname','user_login','user_email','user_class','user_admin');
			
	protected $prefs = array(
	//	'anon_post'				=> array('title'=>PRFLAN_32, 	'type'=>'boolean'),
		'avatar_upload'				=> array('title' => USRLAN_44,  'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'photo_upload'				=> array('title' => USRLAN_53,  'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'im_width'					=> array('title' => USRLAN_47,  'type' => 'number',  'writeParms' => array('maxlength' => 4), 'help' => USRLAN_48, 'data' => 'int', ),
		'im_height'					=> array('title' => USRLAN_49,  'type' => 'number',  'writeParms' => array('maxlength' => 4), 'help' => USRLAN_50, 'data' => 'int', ),
		'profile_rate'				=> array('title' => USRLAN_126, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'profile_comments'			=> array('title' => USRLAN_127, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'force_userupdate'			=> array('title' => USRLAN_133, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'help' => USRLAN_134, 'data' => 'int',),
		'del_unv'					=> array('title' => USRLAN_93,  'type' => 'number',  'writeParms' => array('maxlength' => 5, 'post' => USRLAN_95), 'help' => USRLAN_94, 'data' => 'int',),
		'track_online'				=> array('title' => USRLAN_130, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'help' => USRLAN_131, 'data' => 'int',),
		'memberlist_access'			=> array('title' => USRLAN_146, 'type' => 'userclass', 'writeParms' => 'classlist=public,member,guest,admin,main,classes,nobody', 'data' => 'int',),
		'signature_access'			=> array('title' => USRLAN_194, 'type' => 'userclass', 'writeParms' => 'classlist=member,admin,main,classes,nobody', 'data' => 'int',),
		'user_new_period'			=> array('title' => USRLAN_190,  'type' => 'number',  'writeParms' => array('maxlength' => 3, 'post' => LANDT_04s), 'help' => USRLAN_191, 'data' => 'int',),
	);
	
	function init()
	{
	
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		// Extended fields - FIXME - better field types
		if($sql->db_Select('user_extended_struct', 'user_extended_struct_name,user_extended_struct_text', "user_extended_struct_type > 0 AND user_extended_struct_text != '_system_' ORDER BY user_extended_struct_parent ASC"))
		{
			// FIXME use the handler to build fields and field attributes
			// FIXME a way to load 3rd party language files for extended user fields
			e107::coreLan('user_extended'); 	
			while ($row = $sql->db_Fetch())
			{
				$field = "user_".$row['user_extended_struct_name'];
				$title = ucfirst(str_replace("user_","",$field));
				$label = $tp->toHtml($row['user_extended_struct_text'],false,'defs');
				$this->fields[$field] = array('title' => $label,'width' => 'auto','type'=>'text', 'noedit'=>true);
			}
		}
		$this->fields['user_signature']['writeParms']['data'] = e107::getUserClass()->uc_required_class_list("classes");
		
		$this->fields['user_signature'] = array('title' => LAN_USER_09,	'type' => 'bbarea',	'width' => 'auto');
		$this->fields['options'] = array('title'=> LAN_OPTIONS,	'type' => 'method',	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center');

				
		if(!getperms('4|U0')) // Quick Add User Access Only. 
		{
			unset($this->fields['checkboxes']);
			unset($this->fields['options']);			
		}	

		// if(isset ($_POST['adduser']))
		// {
			// addUser();		
		// }	
	}

	/**
	 * Unban user trigger
	 * @param int $userid
	 * @return void
	 */
	public function ListUnbanTrigger($userid)
	{
		$sql = e107::getDb();
		$sysuser = e107::getSystemUser($userid, false);
		
		if(!$sysuser->getId())
		{
			// TODO lan
			e107::getMessage()->addError('User not found.');
			return;
		}
		
		$sql->db_Update("user", "user_ban='0' WHERE user_id='".$userid."' ");
		$sql->db_Delete("banlist"," banlist_ip='{$row['user_ip']}' ");
		
		e107::getAdminLog()->log_event('USET_06', str_replace(array('--UID--', '--NAME--', '--EMAIL--'), array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_162), E_LOG_INFORMATIVE);
		e107::getMessage()->addSuccess("(".$sysuser->getId().".".$sysuser->getName()." - ".$sysuser->getValue('email').") ".USRLAN_9);
		
		// List data reload
		$this->getTreeModel()->load(true);
	}
	
	/**
	 * Ban user trigger
	 * @param int $userid
	 * @return void
	 */
	public function ListBanTrigger($userid)
	{
		$sql = e107::getDb();
		$message = e107::getMessage();
		$admin_log = e107::getAdminLog();
		$iph = e107::getIPHandler();
		
		$sysuser = e107::getSystemUser($userid, false);
		if(!$sysuser->getId())
		{
			// TODO lan
			e107::getMessage()->addError('User not found.');
			return;
		}
		$row = $sysuser->getData();
		
		if (($row['user_perms'] == "0") || ($row['user_perms'] == "0."))
		{
			$message->addWarning(USRLAN_7);
		}
		else
		{
			if ($sql->update("user","user_ban='1' WHERE user_id='".$userid."' "))
			{
				$admin_log->log_event('USET_05', str_replace(array('--UID--','--NAME--'), array($row['user_id'], $row['user_name']), USRLAN_161), E_LOG_INFORMATIVE);
				$message->addSuccess("(".$userid.".".$row['user_name']." - {$row['user_email']}) ".USRLAN_8);
			}
			if (trim($row['user_ip']) == "")
			{
				$message->addInfo(USRLAN_135);
			}
			else
			{
				if($sql->count('user', '(*)', "user_ip = '{$row['user_ip']}' AND user_ban=0 AND user_id <> {$userid}") > 0)
				{
					// Other unbanned users have same IP address
					$message->addWarning(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_136));
				}
				else
				{
					if ($iph->add_ban(6, USRLAN_149.$row['user_name'].'/'.$row['user_loginname'], $row['user_ip'], USERID))
					{
						// Successful IP ban
						$message->addSuccess(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_137));
					}
					else
					{
						// IP address on whitelist
						$message->addWarning(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_150));
					}
				}
			}
		}
		
		// List data reload
		$this->getTreeModel()->load(true);
	}

	/**
	 * Activate user trigger
	 */
	public function ListVerifyTrigger($userid)
	{
		$e_event = e107::getEvent();
		$admin_log = e107::getAdminLog();
		$sysuser = e107::getSystemUser($userid, false);
		$userMethods = e107::getUserSession();
		$emessage = e107::getMessage();
		
		$uid = intval($userid);
		if ($sysuser->getId())
		{
			$sysuser->set('user_ban', '0')
				->set('user_sess', '');
				
			$row = $sysuser->getData();
			if ($userMethods->userClassUpdate($row, 'userall'))
			{
				$sysuser->set('user_class', $row['user_class']);
			}
			$userMethods->addNonDefaulted($row);
			$sysuser->setData($row)->save();
			
			$admin_log->log_event('USET_10', str_replace(array('--UID--', '--NAME--', '--EMAIL--'), array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_166), E_LOG_INFORMATIVE);
			$e_event->trigger('userfull', $row);
			$emessage->addSuccess(USRLAN_86." (#".$sysuser->getId()." : ".$sysuser->getName().' - '.$sysuser->getValue('email').")");
			
			$this->getTreeModel()->load(true);

			if ((int) e107::pref('core', 'user_reg_veri') == 2)
			{
				$message = USRLAN_114." ".$row['user_name'].",\n\n".USRLAN_122." ".SITENAME.".\n\n".USRLAN_123."\n\n";
				$message .= str_replace("{SITEURL}", SITEURL, USRLAN_139);
				
				$options = array(
					'mail_subject' => USRLAN_113.' '.SITENAME,
					'mail_body' => nl2br($message),
				);
				if($sysuser->email('email', $options))
				{
					// TODO lan
					$emessage->addSuccess("Email sent to: ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
				}
				else 
				{
					$emessage->addError("Failed to send email to: ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
				}
			}
		}
		else
		{
			// TODO lan
			e107::getMessage()->addError('User not found.');
			return;
		}
	}

	/**
	 * Main admin login as system user trigger
	 */
	public function ListLoginasTrigger($userid)
	{
		if(e107::getUser()->getSessionDataAs())
		{
			e107::getMessage()->addWarning(USRLAN_AS_3);
		}
	  	elseif(e107::getUser()->loginAs($userid))
	  	{ 
	  		$sysuser = e107::getSystemUser($userid);
			$user = e107::getUser();
			
			// TODO - lan
			e107::getMessage()->addSuccess('Successfully logged in as '.$sysuser->getName().' <a href="'.e_ADMIN_ABS.'users.php?mode=main&amp;action=logoutas">[logout]</a>')
				->addSuccess('Please, <a href="'.SITEURL.'" rel="external">Leave Admin</a> to browse the system as this user. Use &quot;Logout&quot; option in Administration to end front-end session');
			
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			 // TODO - lan
			$lan = 'Administrator --ADMIN_EMAIL-- (#--ADMIN_UID--, --ADMIN_NAME--) has logged in as the user --EMAIL-- (#--UID--, --NAME--)';
			
			e107::getAdminLog()->log_event('USET_100', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			
			e107::getEvent()->trigger('loginas', array('user_id' => $sysuser->getId(), 'admin_id' => $user->getId()));
	  	}
	}

	/**
	 * Main admin logout as a system user trigger
	 */
	public function LogoutasObserver()
	{
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($user->getSessionDataAs(), false);

	  	if(e107::getUser()->logoutAs() && $sysuser && $sysuser->getId())
	  	{
	  		 // TODO - lan
			e107::getMessage()->addSuccess('Successfully logged out from '.$sysuser->getName().' ('.$sysuser->getValue('email').') account', 'default', true);
			
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			 // TODO - lan
			$lan = 'Administrator --ADMIN_EMAIL-- (#--ADMIN_UID--, --ADMIN_NAME--) has logged out as the user --EMAIL-- (#--UID--, --NAME--)';
			
			e107::getAdminLog()->log_event('USET_101', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			
			e107::getEvent()->trigger('logoutas', array('user_id' => $sysuser->getId(), 'admin_id' => $user->getId()));
			$this->redirect('list', 'main', true);
	  	}
		
  		 // TODO - lan
  		 if(!$sysuser->getId()) e107::getMessage()->addError('User not found.');
	}

	public function LogoutasPage()
	{
		// System Message only on non-successful logout as another user 
	}
	
	/**
	 * Remove admin status trigger
	 */
	public function ListUnadminTrigger($userid)
	{
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($userid, false);
		
		if(!$user->checkAdminPerms('3'))
		{
			// TODO lan
			e107::getMessage()->addError("You don't have enough permissions to do this.", 'default', true);
			
			// TODO lan
			$lan = 'Security violation (not enough permissions) - Administrator --ADMIN_UID-- (--ADMIN_NAME--, --ADMIN_EMAIL--) tried to remove admin status from --UID-- (--NAME--, --EMAIL--)';
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			e107::getAdminLog()->log_event('USET_08', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			$this->redirect('list', 'main', true);
		}

		if ($sysuser->isMainAdmin())
		{
			e107::getMessage()->addError(USRLAN_5);
		}
		else
		{
			if($sysuser->set('user_admin', '0')->set('user_perms', '')->save())
			{
				e107::getAdminLog()->log_event('USET_09',str_replace(array('--UID--', '--NAME--', '--EMAIL--'),array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_165),E_LOG_INFORMATIVE);
				e107::getMessage()->addSuccess($sysuser->getName()." (".$sysuser->getValue('email').") ".USRLAN_6);
				$this->getTreeModel()->load(true);
			}
			else
			{
				// TODO lan
				e107::getMessage()->addError('Unknown error. Action failed.');
			}
		}
	}

	/**
	 * Admin manage observer
	 * @return void
	 */
	public function AdminObserver()
	{
		if($this->getPosted('go_back'))
		{
			$this->redirect('list', 'main', true);
		}
		
		$userid = $this->getId();
		$sql = e107::getDb();
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($userid, false);
		$admin_log = e107::getAdminLog();
		
		if(!$user->checkAdminPerms('3'))
		{
			// TODO lan
			e107::getMessage()->addError("You don't have enough permissions to do this.", 'default', true);
			// TODO lan
			$lan = 'Security violation (not enough permissions) - Administrator --ADMIN_UID-- (--ADMIN_NAME--, --ADMIN_EMAIL--) tried to make --UID-- (--NAME--, --EMAIL--) system admin';
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			$admin_log->log_event('USET_08', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			
			$this->redirect('list', 'main', true);
		}
		
		if(!$sysuser->getId())
		{
			// TODO lan
			e107::getMessage()->addError("User not found.", 'default', true);
			$this->redirect('list', 'main', true);
		}
		
		if(!$sysuser->isAdmin())
		{
			$sysuser->set('user_admin', 1)->save(); //"user","user_admin='1' WHERE user_id={$userid}"
			$lan = str_replace(array('--UID--', '--NAME--', '--EMAIL--'), array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_164);
			$admin_log->log_event('USET_08', $lan, E_LOG_INFORMATIVE);
			e107::getMessage()->addSuccess($lan);
		}
		
		if($this->getPosted('update_admin')) e107::getUserPerms()->updatePerms($userid, $_POST['perms']);
	}
	
	/**
	 * Admin manage page
	 */
	public function AdminPage()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($request->getId(), false);
		//$sysuser->load($request->getId(), true);
		$prm = e107::getUserPerms();
		$frm = e107::getForm();
		
		$response->appendBody($frm->open('adminperms'))
			->appendBody($prm->renderPermTable('grouped', $sysuser->getValue('perms')))
			->appendBody($prm->renderCheckAllButtons())
			->appendBody($prm->renderSubmitButtons())
			->appendBody($frm->close());
		
		// TODO lan
		$this->addTitle(str_replace(array('{NAME}', '{EMAIL}'), array($sysuser->getName(), $sysuser->getValue('email')), 'Update administrator {NAME} ({EMAIL})'));
	}
	
	protected function checkAllowed($class_id) // check userclass change is permitted.
	{
		$e_userclass = e107::getUserClass();
		if (!isset ($e_userclass->class_tree[$class_id]))
		{
			return false;
		}
		if (!getperms("0") && !check_class($e_userclass->class_tree[$class_id]['userclass_editclass']))
		{
			return false;
		}
		return true;
	}
	
	protected function manageUserclass($userid, $uclass, $mode = false)
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($userid, false);
		
		$admin_log = e107::getAdminLog();
		$e_userclass = e107::getUserClass();
		$sql = e107::getDb();

		$remuser = true;
        $emessage = e107::getMessage();
		
		if(!$sysuser->getId())
		{
			// TODO lan
			$emessage->addError('User not found.');
			return false;
		}

		$curClass = array();
		if($mode !== 'update')
		{
			$curClass = $sysuser->getValue('class') ? explode(',', $sysuser->getValue('class')) : array();
        }

    	foreach ($uclass as $a)
		{
			$a = intval($a);
			if(!$this->checkAllowed($a)) 
			{
				// TODO lan
				$emessage->addError('Insufficient permissions, operation aborted.');
				return false;
			}
			
			if($a != 0) // if 0 - then do not add.
			{
				$curClass[] = $a;
			}
		}

		if($mode == "remove") // remove selected classes
		{
			$curClass = array_diff($curClass, $uclass);
		}
		elseif($mode == "clear") // clear all classes
		{
			$curClass = array();
		}

        $curClass = array_unique($curClass);

        $svar = is_array($curClass) ? implode(",", $curClass) : "";
		$check = $sysuser->set('user_class', $svar)->save();
		
		if($check)
		{
			$message = UCSLAN_9;
			if ($this->getPosted('notifyuser'))
			{
				$options = array();
				$message .= "<br />".UCSLAN_1.":</b> ".$sysuser->getName()."<br />";

				$messaccess = '';
				foreach ($curClass as $a)
				{
					if (!isset ($e_userclass->fixed_classes[$a]))
					{
						$messaccess .= $e_userclass->class_tree[$a]['userclass_name']." - ".$e_userclass->class_tree[$a]['userclass_description']."\n";
					}
				}
				if ($messaccess == '') $messaccess = UCSLAN_12."\n";
				
				$message = UCSLAN_3." ".$sysuser->getName().",\n\n".UCSLAN_4." ".SITENAME."\n( ".SITEURL." )\n\n".UCSLAN_5.": \n\n".$messaccess."\n".UCSLAN_10."\n".SITEADMIN;
				//    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User class change",str_replace("\n","<br />",$message),FALSE,LOG_TO_ROLLING);
				
				$options['mail_subject'] = UCSLAN_2;
				$options['mail_body'] = nl2br($message);
				
				$sysuser->email('email', $options);
				//sendemail($send_to,$subject,$message);
			}
			$admin_log->log_event('USET_14', str_replace(array('--UID--','--CLASSES--'), array($id, $svar), UCSLAN_11), E_LOG_INFORMATIVE);

            $emessage->add(nl2br($message), E_MESSAGE_SUCCESS);
		}
		else
		{
           	//	$emessage->add("Update Failed", E_MESSAGE_ERROR);
        	if($check === false)
			{
				$sysuser->setMessages(); // move messages to the default stack
			}
			else
			{
				$emessage->addInfo(LAN_NO_CHANGE);
			}
		}
	}

	/**
	 * Update user class trigger
	 */
	public function UserclassUpdateclassTrigger()
	{
		$this->manageUserclass($this->getId(), $this->getPosted('userclass'), 'update');
	}
	
	/**
	 * Back to user list trigger (userclass page)
	 */
	public function UserclassBackTrigger()
	{
		$this->redirect('list', 'main', true);
	}
	
	/**
	 * Manage userclasses page
	 */
	public function UserclassPage()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($request->getId(), false);
		$e_userclass = e107::getUserClass();
		$userid = $this->getId();
		$frm = e107::getForm();
		
		$caption = UCSLAN_6." <b>".$sysuser->getName().' - '.$sysuser->getValue('email')."</b> (".$sysuser->getClassList(true).")";
		$this->addTitle($caption);
		
		$text = "	<div>
					<form method='post' action='".e_REQUEST_URI."'>
					<fieldset id='core-user-userclass'>
					
                    <table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
					<tr>
						<td>";
		$text .= $e_userclass->vetted_tree('userclass', array($e_userclass,'checkbox_desc'), $sysuser->getValue('class'), 'classes');
		$text .= '
						</td>
					</tr>
					</tbody>
					</table>
		';

		$text .= "	<div class='buttons-bar center'>
	 					".$frm->hidden('userid', $userid)."
						".$frm->checkbox_label(UCSLAN_8.'&nbsp;&nbsp;', 'notifyuser', 1)."
						".$frm->admin_button('etrigger_updateclass', UCSLAN_7, 'update')."
						".$frm->admin_button('etrigger_back', 'Back', 'cancel')."
					</div>
					</fieldset>
					</form>
					</div>";

		$response->appendBody($text);
	}
	
	/**
	 * Resend user activation email trigger 
	 */
	public function ListResendTrigger($userid)
	{
		$this->resendActivation($userid);
	}
	
	/**
	 * Resend user activation email helper
	 * FIXME - better Subject/Content for the activation email when user is bounced/deactivated.
	 */
	protected function resendActivation($id, $lfile = '')
	{
		$admin_log = e107::getAdminLog();
		$sysuser = e107::getSystemUser($id, false);
		$key = $sysuser->getValue('sess');
		$emessage = e107::getMessage();
		
		if(!$sysuser->getId())
		{
			// TODO lan
			$emessage->addError('User not found.');
			return false;
		}

		if(!$key || !$sysuser->getValue('ban'))
		{
			// TODO lan
			$emessage->addError('Missing activation key.');
			return false;
		}
		
		// Check for a Language field, and if present, send the email in the user's language.
		// FIXME - make all system emails to be created from HTML email templates, this should fix the multi-lingual issue when sending multiple emails
		if ($lfile == "")
		{
			$lan = $sysuser->getValue('language');
			if ($lan)
			{
				$lfile = e_LANGUAGEDIR.$lan.'/lan_signup.php';
			}
		}
		if ($lfile && is_readable($lfile))
		{
			require_once($lfile);
		}
		else
		{
			//@FIXME use email templates by Language
			require_once (e_LANGUAGEDIR.e_LANGUAGE."/lan_signup.php");
		}
		if(!$lan) $lan = e_LANGUAGE;
		
		// FIXME switch to e107::getUrl()->create(), use email content templates
		//$return_address = (substr(SITEURL,- 1) == "/") ? SITEURL."signup.php?activate.".$sysuser->getId().".".$key : SITEURL."/signup.php?activate.".$sysuser->getId().".".$key;
		$return_address = SITEURL."signup.php?activate.".$sysuser->getId().".".$key;
		$message = LAN_EMAIL_01." ".$sysuser->getName()."\n\n".LAN_SIGNUP_24." ".SITENAME.".\n".LAN_SIGNUP_21."\n\n";
		$message .= "<a href='".$return_address."'>".$return_address."</a>";
		
		// custom header now auto-added in email() method 
		//$mailheader_e107id = $id;
		
		$check = $sysuser->email('email', array(
			'mail_subject' => LAN_SIGNUP_96." ".SITENAME,
			'mail_body' => nl2br($message),
		));
		
		if ($check)
		{
			$admin_log->log_event('USET_11', str_replace(array('--ID--','--NAME--','--EMAIL--'), array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_167), E_LOG_INFORMATIVE);
			$emessage->addSuccess(USRLAN_140.": <a href='mailto:".$sysuser->getValue('email')."?body=".$return_address."' title=\"".LAN_USER_08."\" >".$sysuser->getName()." (".$sysuser->getValue('email').")</a> ({$lan}) ");
		}
		else
		{
			$emessage->addError(USRLAN_141.": ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
		}
		return $check;
	}

	/**
	 * Test user email observer
	 */
	public function TestObserver()
	{
		$sysuser = e107::getSystemUser($this->getId(), false);
		$emessage = e107::getMessage();
		$email = $sysuser->getValue('email');
		
		if(!$sysuser->getId())
		{
			// TODO lan
			$emessage->addError('User not found.', 'default', true);
			$this->redirect('list', 'main', true);
		}
		
		$result = $this->testEmail($email);
		if($result)
		{
			// TODO lan
			$this->setParam('testSucces', $result);
			$emessage->addSuccess($email.' - Valid');
		}
		else
		{
			// TODO lan
			$emessage->addError($email.' - Invalid', 'default', true);
			$this->redirect('list', 'main', true);
		}

	}
	
	public function TestCancelTrigger()
	{
		$this->redirect('list', 'main', true);
	}
	
	/**
	 * Resend activation email page - only if tested email is valid
	 */
	public function TestPage()
	{
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($this->getId(), false);
		$userid = $this->getId();
		$email = $sysuser->getValue('email');
		$frm = e107::getForm();
		
		// TODO lan
		$caption = "Test ".$email;
		$this->addTitle($caption);
		
		$text = "<a href='".e_REQUEST_HTTP."?mode=main&amp;action=list'>".LAN_BACK."</a>";
		$text .= '<pre>'.htmlspecialchars($this->getParam('testSucces')).'</pre>';
		$text .= "	<div>
					<form method='post' action='".e_REQUEST_HTTP."?mode=main&amp;action=list'>
					<fieldset id='core-user-testemail'>
						<div class='buttons-bar center'>
		 					".$frm->hidden('useraction', 'resend')."
							".$frm->hidden('userid', $userid)."
							".$frm->hidden('userip', $sysuser->getValue('ip'))."
							".$frm->admin_button('resend', USRLAN_112, 'update')."
						</div>
					</fieldset>
					</form>
					</div>";

		$response->appendBody($text);
	}

	/**
	 * Test user email helper
	 */
	protected function testEmail($email)
	{
		list($adminuser,$adminhost) = explode('@',SITEADMINEMAIL, 2);
		
		$validator = new email_validation_class;
		$validator->localuser = $adminuser;
		$validator->localhost = $adminhost;
		$validator->timeout = 5;
		$validator->debug = 1;
		$validator->html_debug = 0;
		
		ob_start();
		$email_status = $validator->ValidateEmailBox($email);
		$text = ob_get_contents();
		ob_end_clean();
		
		if ($email_status == 1)
		{
			return $text;
		}
		
		return false;
	}
	
	/**
	 * Set user status to require verification - available for bounced users
	 */
	public function ListReqverifyTrigger($userid)
	{
		$sysuser = e107::getSystemUser($userid, false);
		
		if(!$sysuser->getId())
		{
			// TODO lan
			$emessage->addError('User not found.', 'default', true);
			return;
		}
		
		$sysuser->set('user_ban', 2)
			->set('user_sess', e_user_model::randomKey());
		
		if($sysuser->save())
		{
			// TODO lan
			e107::getMessage()->addSuccess('User now has to verify.');
			
			// TODO - auto-send email or not - discuss
			$this->resendActivation($userid);
			
			//FIXME admin log
			
			// Reload tree
			$this->getTreeModel()->load(true);
			return;
		}
		
		// TODO lan
		e107::getMessage()->addError('Action failed.');
	}

	/**
	 * Quick Add user submit trigger
	 */
	public function AddSubmitTrigger()
	{
		$e107cache 		= e107::getCache();
		$userMethods 	= e107::getUserSession();
		$mes 			= e107::getMessage();
		$sql 			= e107::getDb();
		$e_event 		= e107::getEvent();
		$admin_log		= e107::getAdminLog();
		
		if (!$_POST['ac'] == md5(ADMINPWCHANGE))
		{
			exit;
		}
		
		$e107cache->clear('online_menu_member_total');
		$e107cache->clear('online_menu_member_newest');
		$error = false;
		
		if (isset ($_POST['generateloginname']))
		{
			$_POST['loginname'] = $userMethods->generateUserLogin($pref['predefinedLoginName']);
		}
		
		$_POST['password2'] = $_POST['password1'] = $_POST['password'];
		
		// Now validate everything
		$allData = validatorClass::validateFields($_POST, $userMethods->userVettingInfo, true);
		
		// Fix Display and user name
		if (!check_class($pref['displayname_class'], $allData['data']['user_class']))
		{
			if ($allData['data']['user_name'] != $allData['data']['user_loginname'])
			{
				$allData['data']['user_name'] = $allData['data']['user_loginname'];
				// TODO lan
				$mes->addWarning(str_replace('--NAME--', $allData['data']['user_loginname'], 'User name and display name cannot be different (based on the site configuration). Display name set to <strong>--NAME--</strong>.'));
				//$allData['errors']['user_name'] = ERR_FIELDS_DIFFERENT;
			}
		}
		
		// Do basic validation
		validatorClass::checkMandatory('user_name, user_loginname', $allData);
		
		// Check for missing fields (email done in userValidation() )
		validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', 0);
		
		// Do basic DB-related checks
		$userMethods->userValidation($allData);
		
		// Do user-specific DB checks
		if (!isset($allData['errors']['user_password']))
		{
			// No errors in password - keep it outside the main data array
			$savePassword = $allData['data']['user_password'];
			// Delete the password value in the output array
			unset ($allData['data']['user_password']);
		}

		// Restrict the scope of this
		unset($_POST['password2'], $_POST['password1']);
		
		if (count($allData['errors']))
		{
			$temp = validatorClass::makeErrorList($allData, 'USER_ERR_','%n - %x - %t: %v', '<br />', $userMethods->userVettingInfo);
			$mes->addError($temp);
			$error = true;
		}
		
		// Always save some of the entered data - then we can redisplay on error
		$user_data = & $allData['data'];
		
		if($error)
		{
			$this->setParam('user_data', $user_data);
			return;
		}
		
		if(varset($_POST['perms']))
		{
			$allData['data']['user_admin'] = 1;
			$allData['data']['user_perms'] = implode('.',$_POST['perms']);
		}


		$user_data['user_password'] = $userMethods->HashPassword($savePassword, $user_data['user_login']);
		$user_data['user_join'] = time();
		
		if ($userMethods->needEmailPassword())
		{
			// Save separate password encryption for use with email address
			$user_data['user_prefs'] = serialize(array('email_password' => $userMethods->HashPassword($savePassword, $user_data['user_email'])));
		}
		
		$userMethods->userClassUpdate($allData['data'], 'userall');
		
		//FIXME - (SecretR) there is a better way to fix this (missing default value, sql error in strict mode - user_realm is to be deleted from DB later)
		$allData['data']['user_realm'] = '';
		
		// Set any initial classes
		$userMethods->addNonDefaulted($user_data);
		validatorClass::addFieldTypes($userMethods->userVettingInfo, $allData);
		
		if (($userid = $sql->db_Insert('user', $allData)))
		{
			$sysuser = e107::getSystemUser(false, false);
			$sysuser->setData($allData['data']);
			$sysuser->setId($userid);
			$user_data['user_id'] = $userid;
			
			// Add to admin log
			$admin_log->log_event('USET_02',"UName: {$user_data['user_name']}; Email: {$user_data['user_email']}", E_LOG_INFORMATIVE);
			
			// Add to user audit trail
			$admin_log->user_audit(USER_AUDIT_ADD_ADMIN, $user_data, 0, $user_data['user_loginname']);
			$e_event->trigger('userfull', $user_data);
			
			// send everything available for user data - bit sparse compared with user-generated signup
			if(isset($_POST['sendconfemail']))
			{
				$check = false;
				
				// Send confirmation email to user
				switch ((int) $_POST['sendconfemail']) 
				{
					case 0:
						// activate, don't notify
						$check = -1;
					break;
					
					case 1:
						// activate and send password
						$check = $sysuser->email('quickadd', array(
							'user_password' => $savePassword, 
							'mail_subject' => USRLAN_187.SITENAME,
							// TODO lan
							'activation_url' => 'Your current status is <strong>Active</strong>',
						));
					break;
					
					case 2:
						// require activation and send password and activation link
						$sysuser->set('user_ban', 2)
							->set('user_sess', e_user_model::randomKey())
							->save();
							
						$check = $sysuser->email('quickadd', array(
							'user_password' => $savePassword, 
							'mail_subject' => USRLAN_187.SITENAME,
							'activation_url' => SITEURL."signup.php?activate.".$sysuser->getId().".".$sysuser->getValue('sess'),
						));
					break;
				}
				
				
				if($check && $check !== -1)
				{
					$mes->addSuccess(USRLAN_188);
				}
				elseif(!$check)
				{
					$mes->addError(USRLAN_189);
				}
			}
			
			$message = str_replace('--NAME--', htmlspecialchars($user_data['user_name'], ENT_QUOTES, CHARSET), USRLAN_174);
			
			// Always show Login name and password
			//if (isset($_POST['generateloginname']))
			{
				$mes->addSuccess($message)->addSuccess(USRLAN_173.': <strong>'.htmlspecialchars($user_data['user_loginname'], ENT_QUOTES, CHARSET).'</strong>');	
			}
				
			//if (isset($_POST['generatepassword']))
			{
				$mes->addSuccess($message)->addSuccess(USRLAN_172.': <strong>'.htmlspecialchars($savePassword, ENT_QUOTES, CHARSET).'</strong>');	
			}
			return;
		}
	}
	
	/**
	 * Quick add user page
	 */
	function AddPage()
	{
		$prm = e107::getUserPerms();
		//$list = $prm->getPermList();
		$frm = e107::getForm();
		$e_userclass = e107::getUserClass();
		$pref = e107::getPref();
		$user_data = $this->getParam('user_data');
		
		$this->addTitle(LAN_USER_QUICKADD);
		
		$text = "<div>".$frm->open("core-user-adduser-form")."
		<fieldset id='core-user-adduser'>
        <table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		<tr>
			<td>".USRLAN_61."</td>
			<td>
			".$frm->text('username', varset($user_data['user_name']), varset($pref['displayname_maxlength'], 15))."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_128."</td>
			<td>
			".$frm->text('loginname', varset($user_data['user_loginname']), varset($pref['loginname_maxlength'], 30))."&nbsp;&nbsp;
			".$frm->checkbox_label(USRLAN_170, 'generateloginname', 1, varset($pref['predefinedLoginName'], false))."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_129."</td>
			<td>
			".$frm->text('realname', varset($user_data['user_login']), 30)."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_62."</td>
			<td>".$frm->password('password', '', 20, array('size' => 40, 'class' => 'tbox e-password', 'generate' => 1, 'strength' => 1))."
			</td>
		</tr>";
		


		$text .= "
			<tr>
				<td>".USRLAN_64."</td>
				<td>
				".$frm->text('email', varset($user_data['user_email']), 100)."
				</td>
			</tr>
	
			<tr>
				<td>Notification and user status</td>
				<td>
					".$frm->selectbox('sendconfemail', array('0' => "Activate, Don't Notify", '1' => 'Activate, Notify (password)', '2' => 'Require Activation, Notify (password and activation link)'), (int) varset($_POST['sendconfemail'], 0))."
					<div class='field-help'>".USRLAN_181."</div>
				</td>
			</tr>";
			// TODO lan above

		if (!isset ($user_data['user_class'])) $user_data['user_class'] = varset($pref['initial_user_classes']);
		$temp = $e_userclass->vetted_tree('class', array($e_userclass, 'checkbox_desc'), $user_data['user_class'], 'classes');

		if ($temp)
		{
			$text .= "<tr style='vertical-align:top'>
			<td>
				".USRLAN_120."
			</td>
			<td>
				<a href='#set_class' class='e-expandit'>".USRLAN_120."</a>
				<div class='e-hideme' id='set_class'>
				{$temp}
				</div>
			</td>
			</tr>\n";
		}

		// Make Admin.
		$text .= "
		<tr>
			<td>".USRLAN_35."</td>
			<td>
				<a href='#set_perms' class='e-expandit'>Set Permissions</a>
				<div class='e-hideme' id='set_perms'>
		";
			
		$text .= $prm->renderPermTable('grouped');

		$text .= "
				</div>
			</td>
		</tr>
		";


		$text .= "

		</table>
		<div class='buttons-bar center'>
			".$frm->admin_trigger('submit', USRLAN_60, 'create')."
			".$frm->token()."
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
		</div>
		</fieldset>
		</form>
		</div>
		";
		
		
		return $text;
		//$ns->tablerender(USRLAN_59,$mes->render().$text);
	}	

	public function RanksUpdateTrigger()
	{
		$fg = array();
		$ranks_calc = '';
		$ranks_flist = '';
		$config = e107::getConfig();
		foreach ($_POST['op'] as $f => $o)
		{
			$cfg[$f]['op'] = $o;
			$cfg[$f]['val'] = varset($_POST['val'][$f],'');
			if ($_POST['val'][$f])
			{
				$ranks_calc .= ($ranks_calc ? ' + ' : '').'({'.$f.'} '." $o {$_POST['val'][$f]}".' )';
				$ranks_flist .= ($ranks_flist ? ',' : '').$f;
			}
		}

		//Delete existing rank config
		e107::getDb()->delete('generic', "gen_type = 'user_rank_config'");
		
		$tmp = array();
		$tmp['data']['gen_type'] = 'user_rank_config';
		$tmp['data']['gen_chardata'] = serialize($cfg);
		$tmp['_FIELD_TYPES']['gen_type'] = 'string';
		$tmp['_FIELD_TYPES']['gen_chardata'] = 'escape';
		
		//Add the new rank config
		e107::getDb()->insert('generic', $tmp);
		
		// save prefs
		$config->set('ranks_calc', $ranks_calc);
		$config->set('ranks_flist', $ranks_flist);
		$config->save();

		//Delete existing rank data
		e107::getDb()->delete('generic',"gen_type = 'user_rank_data'");
		
		//Add main site admin info
		$tmp = array();
		$tmp['_FIELD_TYPES']['gen_datestamp'] = 'int';
		$tmp['_FIELD_TYPES']['gen_ip'] = 'todb';
		$tmp['_FIELD_TYPES']['gen_user_id'] = 'int';
		$tmp['_FIELD_TYPES']['gen_chardata'] = 'todb';
		$tmp['_FIELD_TYPES']['gen_intdata'] = 'int';
		$tmp['data']['gen_datestamp'] = 1;
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_ip'] = $_POST['calc_name']['main_admin'];
		$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['main_admin'],0);
		$tmp['data']['gen_chardata'] = $_POST['calc_img']['main_admin'];
		e107::getDb()->insert('generic',$tmp);
		
		//Add site admin info
		unset ($tmp['data']);
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_datestamp'] = 2;
		$tmp['data']['gen_ip'] = $_POST['calc_name']['admin'];
		$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['admin'],0);
		$tmp['data']['gen_chardata'] = $_POST['calc_img']['admin'];
		e107::getDb()->insert('generic', $tmp);
		
		//Add all current site defined ranks
		if (isset ($_POST['field_id']))
		{
			foreach ($_POST['field_id'] as $fid => $x)
			{
				unset ($tmp['data']);
				$tmp['data']['gen_type'] = 'user_rank_data';
				$tmp['data']['gen_ip'] = varset($_POST['calc_name'][$fid],'');
				$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx'][$fid],0);
				$tmp['data']['gen_chardata'] = varset($_POST['calc_img'][$fid],'');
				$tmp['data']['gen_intdata'] = varset($_POST['calc_lower'][$fid],'_NULL_');
				e107::getDb()->insert('generic', $tmp);
			}
		}
		
		//Add new rank, if posted
		if (varset($_POST['new_calc_lower']))
		{
			unset ($tmp['data']);
			$tmp['data']['gen_type'] = 'user_rank_data';
			$tmp['data']['gen_datestamp'] = 0;
			$tmp['data']['gen_ip'] = varset($_POST['new_calc_name']);
			$tmp['data']['gen_user_id'] = varset($_POST['new_calc_pfx'],0);
			$tmp['data']['gen_chardata'] = varset($_POST['new_calc_img']);
			$tmp['data']['gen_intdata'] = varset($_POST['new_calc_lower']);
		}
		
		e107::getCache()->clear_sys('nomd5_user_ranks');
		e107::getMessage()->add(USRLAN_217,E_MESSAGE_SUCCESS);
	}

	function RanksDeleteTrigger($posted)
	{
		$rankId = (int) key($posted);
		
		e107::getCache()->clear_sys('nomd5_user_ranks');
		if (e107::getDb()->delete('generic',"gen_id='{$rankId}'"))
		{
			e107::getMessage()->add(USRLAN_218,E_MESSAGE_SUCCESS);
		}
		else
		{
			e107::getMessage()->add(USRLAN_218,E_MESSAGE_FAIL);
		}
	}

	function RanksPage()
	{
		$frm = e107::getForm();
		$e107 = e107::getInstance();
		$pref = e107::getPref();
		$emessage = e107::getMessage();
		$ui = $this->getUI();

		$ranks = e107::getRank()->getRankData();
		$tmp = e107::getFile()->get_files(e_IMAGE.'ranks', '.*?\.(png|gif|jpg)');
		
		$this->addTitle(LAN_USER_RANKS);
		
		foreach($tmp as $k => $v)
		{
			$imageList[] = $v['fname'];
		}
		unset($tmp);
		natsort($imageList);
	
		$text = $frm->open('core-user-ranks-form');

		
		$fields = array(
			'type' => array('title' => USRLAN_207, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'rankName' => array('title' => USRLAN_208, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'lowThresh' => array('title' => USRLAN_209, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'langPrefix' => array('title' => USRLAN_210, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'rankImage' => array('title' => USRLAN_210, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		);
	
	
		$text .= "
		<table class='table adminlist'>".
		$frm->colGroup($fields, array_keys($fields)).
		$frm->thead($fields, array_keys($fields));
	
		$info = $ranks['special'][1];
		$val = $e107->tp->toForm($info['name']);
		$text .= "
		<tr>
			<td>".LAN_MAINADMIN."</td>
			<td>
				".$frm->text('calc_name[main_admin]', $val)."
			</td>
			<td>N/A</td>
			<td>".$frm->checkbox('calc_pfx[main_admin]', 1, $info['lan_pfx'] ? true : false)."</td>
			<td>".$ui->RankImageDropdown($imageList,'calc_img[main_admin]',$info['image'])."</td>
		</tr>
		";
		$info = $ranks['special'][2];
		$val = $e107->tp->toForm($info['name']);
		$text .= "
		<tr>
			<td>".LAN_ADMIN."</td>
			<td>
				".$frm->text('calc_name[admin]', $val)."
			</td>
			<td>N/A</td>
			<td>".$frm->checkbox('calc_pfx[admin]', 1, $info['lan_pfx'] ? true : false)."</td>
			<td>".$ui->RankImageDropdown($imageList, 'calc_img[admin]', $info['image'])."</td>
		</tr>
		<tr>
			<td colspan='5'>&nbsp;</td>
		</tr>
		";
		
		foreach ($ranks['data'] as $k => $r)
		{
			$pfx_checked = ($r['lan_pfx'] ? "checked='checked'" : '');
			$text .= "
			<tr>
				<td>".USRLAN_212."</td>
				<td>
					<input type='hidden' name='field_id[{$k}]' value='1' />
					<input class='tbox' type='text' name='calc_name[$k]' value='{$r['name']}' />
				</td>
				<td>".$frm->number("calc_lower[{$k}]", $r['thresh'])."</td>
				<td>".$frm->checkbox("calc_pfx[{$k}]", 1, $r['lan_pfx'] ? true : false)."</td>
				<td>".$ui->RankImageDropdown($imageList, "calc_img[$k]", $r['image'])."&nbsp;".
				$frm->submit_image("etrigger_delete[{$r['id']}]", LAN_DELETE, 'delete', LAN_CONFIRMDEL.": [{$r['name']}]?")."
				</td>
			</tr>
			";
		}
		
		$text .= "
	
		<tr>
			<td colspan='5'>&nbsp;</td>
		</tr>
		<tr>
			<td>".USRLAN_214."</td>
			<td>".$frm->text('new_calc_name', '')."</td>
			<td>".$frm->number('new_calc_lower', '')."</td>
			<td>".$frm->checkbox('new_calc_pfx', 1, false)."</td>
			<td>".$ui->RankImageDropdown($imageList, 'new_calc_img')."</td>
		</tr>";
		
		$text .= '</table>
		<div class="buttons-bar center">
		'.$frm->admin_trigger('update', 'no-value', 'update', USRLAN_215).'
		</div>
		</form>';
		
		return $text;		
	}
	
	// ================== OLD CODE backup =============>
	
	// It might be rewritten with user info option (ui trigger)
	// Old trigger code
	// if ((isset ($_POST['useraction']) && $_POST['useraction'] == "userinfo") || $_GET['userinfo'])
	// {
		// $ip = ($_POST['userip']) ? $_POST['userip'] : $_GET['userinfo'];
		// $user->user_info($ip);
	// }
	function user_info($ipd)
	{
		global $ns, $sql, $e107;

		if (isset($ipd))
		{
			$bullet = '';
			if(defined('BULLET'))
			{
				$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
			}
            // TODO - move to e_userinfo.php
			$obj = new convert;
			$sql->db_Select("chatbox", "*", "cb_ip='$ipd' LIMIT 0,20");
			$host = $e107->get_host_name($ipd);
			$text = USFLAN_3." <b>".$ipd."</b> [ ".USFLAN_4.": $host ]<br />
				<i><a href=\"banlist.php?".$ipd."\">".USFLAN_5."</a></i>

				<br /><br />";
			while (list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip ) = $sql->db_Fetch())
			{
				$datestamp = $obj->convert_date($cb_datestamp, "short");
				$post_author_id = substr($cb_nick, 0, strpos($cb_nick, "."));
				$post_author_name = substr($cb_nick, (strpos($cb_nick, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />
					". $cb_message."
					</div>
					<br />";
			}

			$text .= "<hr />";

			$sql->db_Select("comments", "*", "comment_ip='$ipd' LIMIT 0,20");
			while (list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql->db_Fetch())
			{
				$datestamp = $obj->convert_date($comment_datestamp, "short");
				$post_author_id = substr($comment_author, 0, strpos($comment_author, "."));
				$post_author_name = substr($comment_author, (strpos($comment_author, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />". $comment_comment."
					</div>
					<br />";
			}

		}

		$ns->tablerender(USFLAN_7, $text);
	}

	// It might be used in the future - batch options
	function resend_to_all()
	{
		global $sql,$pref,$sql3,$admin_log;
		$count = 0;
		$pause_count = 1;
		$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
		$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;
		if ($sql->db_Select_gen('SELECT user_language FROM `#user_extended` LIMIT 1'))
		{
			$query = "SELECT u.*, ue.* FROM `#user` AS u LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id WHERE u.user_ban = 2 ORDER BY u.user_id DESC";
		}
		else
		{
			$query = 'SELECT * FROM `#user` WHERE user_ban=2';
		}

		$sql3 = e107::getDb('sql3');

		$sql3->db_Select_gen($query);
		while ($row = $sql3->db_Fetch())
		{
			echo $row['user_id']." ".$row['user_sess']." ".$row['user_name']." ".$row['user_email']."<br />";
			$this->resend($row['user_id'],$row['user_sess'],$row['user_name'],$row['user_email'],$row['user_language']);
			if ($pause_count > $pause_amount)
			{
				sleep($pause_time);
				$pause_count = 1;
			}
			sleep(1);
			$pause_count++;
			$count++;
		}
		if ($count)
		{
			$admin_log->log_event('USET_12',str_replace('--COUNT--',$count,USRLAN_168),E_LOG_INFORMATIVE);
		}
	}

	// ---------------------------------------------------------------------
	//		Bounce handling - FIXME convert to cron job
	// ---------------------------------------------------------------------
	// $bounce_act has the task to perform:
	//	'first_check' - initial read of list of bounces
	//	'delnonbounce' - delete any emails that aren't bounces
	//  'clearemailbounce' - delete email address for any user whose emails bounced
	//	'delchecked' - delete the emails whose comma-separated IDs are in $bounce_arr
	//	'delall' - delete all bounced emails
	function check_bounces($bounce_act = 'first_check',$bounce_arr = '')
	{
		### old Trigger code for bounce check
		// $bounce_act = '';
		// if (isset ($_POST['check_bounces']))
			// $bounce_act = 'first_check';
		// if (isset ($_POST['delnonbouncesubmit']))
			// $bounce_act = 'delnonbounce';
		// if (isset ($_POST['clearemailbouncesubmit']))
			// $bounce_act = 'clearemailbounce';
		// if (isset ($_POST['delcheckedsubmit']))
			// $bounce_act = 'delchecked';
		// if (isset ($_POST['delallsubmit']))
			// $bounce_act = 'delall';
		// if ($bounce_act)
		// {
			// $user->check_bounces($bounce_act,implode(',',$_POST['delete_email']));
			// require_once ("footer.php");
			// exit;
		// }
		
		global $sql,$pref;
		include (e_HANDLER.'pop3_class.php');
		if (!trim($bounce_act))
		{
			$bounce_act = 'first_check';
		}
		//	  echo "Check bounces. Action: {$bounce_act}; Entries: {$bounce_arr}<br />";
		$obj = new receiveMail($pref['mail_bounce_user'],$pref['mail_bounce_pass'],$pref['mail_bounce_email'],$pref['mail_bounce_pop3'],varset($pref['mail_bounce_type'],'pop3'));
		$del_count = 0;
		if ($bounce_act != 'first_check')
		{
		// Must do some deleting
			$obj->connect();
			$tot = $obj->getTotalMails();
			$del_array = explode(',',$bounce_arr);
			for ($i = 1; $i <= $tot; $i++)
			{
			// Scan all emails; delete current one if meets the criteria
				$dodel = false;
				switch ($bounce_act)
				{
					case 'delnonbounce' :
						$head = $obj->getHeaders($i);
						$dodel = (!$head['bounce']);
						break;
					case 'clearemailbounce' :
						if (!in_array($i,$del_array))
							break;
						$head = $obj->getHeaders($i);
						if ($head['bounce'])
						{
							if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
							{
								$usr_email = trim($result[0]);
							}
							if ($sql->db_Select('user','user_id, user_name, user_email',"user_email='".$usr_email."' "))
							{
								$row = $sql->db_Fetch();
								if ($sql->db_Update('user',"`user_email`='' WHERE `user_id` = '".$row['user_id']."' ") !== false)
								{
								// echo "Deleting user email {$row['user_email']} for user {$row['user_name']}, id={$row['user_id']}<br />";
									$dodel = true;
								}
							}
						}
						break;
					case 'delall' :
						$dodel = true;
						break;
					case 'delchecked' :
						$dodel = in_array($i,$del_array);
						break;
				}
				if ($dodel)
				{
				//			  echo "Delete email ID {$i}<br />";
					$obj->deleteMails($i);
					$del_count++;
					// Keep track of number of emails deleted
				}
			}
			// End - Delete one email
			$obj->close_mailbox();
			// This actually deletes the emails
		}
		// End of email deletion
		// Now list the emails that are left
		$obj->connect();
		$tot = $obj->getTotalMails();
		$found = false;
		$DEL = ($pref['mail_bounce_delete']) ? true : false;
		$text = "<br /><div><form  method='post' action='".e_SELF.$qry."'><table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td class='fcaption' style='width:5%'>#</td><td class='fcaption'>e107-id</td><td class='fcaption'>email</td><td class='fcaption'>Subject</td><td class='fcaption'>Bounce</td></tr>\n";
		for ($i = 1; $i <= $tot; $i++)
		{
			$head = $obj->getHeaders($i);
			if ($head['bounce'])
			{
			// Its a 'bounce' email
				if (preg_match('/.*X-e107-id:(.*)MIME/',$obj->getBody($i),$result))
				{
					if ($result[1])
					{
						$id[$i] = intval($result[1]);
						// This should be a user ID - but not on special mailers!
						//	Try and pull out an email address from body - should be the one that failed
						if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
						{
							$emails[$i] = "'".$result[0]."'";
						}
						$found = true;
					}
				}
				elseif (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					if ($result[0] && $result[0] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[0]."'";
						$found = true;
					}
					elseif ($result[1] && $result[1] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[1]."'";
						$found = true;
					}
				}
				if ($DEL && $found)
				{
				// Auto-delete bounced emails once noticed (if option set)
					$obj->deleteMails($i);
					$del_count++;
				}
			}
			else
			{
			// Its a warning message or similar
			//			  $id[$i] = '';			// Don't worry about an ID for now
			//				Try and pull out an email address from body - should be the one that failed
				if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					$wmails[$i] = "'".$result[0]."'";
				}
			}
			$text .= "<tr><td class='forumheader3'>".$i."</td><td class='forumheader3'>".$id[$i]."</td><td class='forumheader3'>".(isset ($emails[$i]) ? $emails[$i] : $wmails[$i])."</td><td class='forumheader3'>".$head['subject']."</td><td class='forumheader3'>".($head['bounce'] ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON);
			$text .= "<input type='checkbox' name='delete_email[]' value='{$i}' /></td></tr>\n";
		}
		if ($del_count)
		{
			$admin_log->log_event('USET_13',str_replace('--COUNT--',$del_count,USRLAN_169),E_LOG_INFORMATIVE);
		}
		if ($tot)
		{
		// Option to delete emails - only if there are some in the list
			$text .= "</table><table style='".ADMIN_WIDTH."'><tr>
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delnonbouncesubmit' value='".USRLAN_183."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='clearemailbouncesubmit' value='".USRLAN_184."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delcheckedsubmit' value='".USRLAN_179."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delallsubmit' value='".USRLAN_180."' /></td>\n
			</td></tr>";
		}
		$text .= "</table></form></div>";
		array_unique($id);
		array_unique($emails);
		$all_ids = implode(',',$id);
		$all_emails = implode(',',$emails);
		$obj->close_mailbox();
		// This will actually delete emails
		// $tot has total number of emails in the mailbox
		$found = count($emails);
		// $found - Number of bounce emails found
		// $del_count has number of emails deleted
		// Update bounce status for users
		$ed = $sql->db_Update('user',"user_ban=3 WHERE (`user_id` IN (".$all_ids.") OR `user_email` IN (".$all_emails.")) AND user_sess !='' ");
		if (!$ed)
			$ed = '0';
		$this->show_message(str_replace(array('{TOTAL}','{DELCOUNT}','{DELUSER}','{FOUND}'),array($tot,$del_count,$ed,$found),USRLAN_155).$text);
	}
	
// ------- FIXME  Prune Users move to cron --------------
// if (isset ($_POST['prune']))
// {
	// $e107cache->clear('online_menu_member_total');
	// $e107cache->clear('online_menu_member_newest');
	// $text = USRLAN_56.' ';
	// $bantype = $_POST['prune_type'];
	// if ($bantype == 30)
		// // older than 30 days.
	// {
		// $bantype = 2;
		// $ins = " AND user_join < ".strtotime("-30 days");
	// }
	// if ($sql->db_Select("user","user_id, user_name","user_ban= {$bantype}".$ins))
	// {
		// $uList = $sql->db_getList();
		// foreach ($uList as $u)
		// {
			// $text .= $u['user_name']." ";
			// $sql->db_Delete("user","user_id='{$u['user_id']}' ");
			// $sql->db_Delete("user_extended","user_extended_id='{$u['user_id']}' ");
		// }
		// $admin_log->log_event('USET_04',str_replace(array('--COUNT--','--TYPE--'),array(count($uList),$bantype),USRLAN_160),E_LOG_INFORMATIVE);
	// }
	// $ns->tablerender(USRLAN_57,"<div style='text-align:center'><b>".$text."</b></div>");
	// unset ($text);
// }
}


class users_admin_form_ui extends e_admin_form_ui
{
						
	function user_perms($curval,$mode)
	{
		if($mode == 'read')
		{
			$uid = $this->getController()->getListModel()->get('user_id');	
			return e107::getUserPerms()->renderPerms($curval,$uid);		
		}
		if($mode == 'write')
		{
			$prm = e107::getUserPerms();
			$text = "<a class='e-expandit' href='#perms'>Admin Permissions</a>";
			$text .= "<div id='perms' style='display:none'>". $prm->renderPermTable('grouped',$curval).'</div>';				
			return $text;
		}
			
		
	}
	
	function user_ban($curval,$mode)
	{
		$bo = array('Active',LAN_BANNED,LAN_NOTVERIFIED,LAN_BOUNCED);
		
		if($mode == 'filter' || $mode == 'batch')
		{
			return 	$bo;
		}
		if($mode == 'write')
		{
			$frm = e107::getForm();
			return $frm->selectbox('user_ban',$bo,$curval);	
		}	
			
		return vartrue($bo[$curval],' '); // ($curval == 1) ? ADMIN_TRUE_ICON : '';	
	}	
	
	
	/*
	function user_class($curval,$mode)
		{
					
			$e_userclass 	= new user_class;
			$frm 			= e107::getForm();
			$list 			= $e_userclass->uc_required_class_list("classes");
							 if($mode == 'filter')
			{
				return $list;	
			}
			
			if($mode == 'write') //FIXME userclasses are NOT be saved since they are an array. 
			{		
				return $frm->selectbox('user_class', $list, $curval, 'description=1&multiple=1');
				// return $frm->uc_select('user_class[]', $curval, 'admin,classes', 'description=1&multiple=1');// doesn't work correctly. 	
			}
			
			
			//FIXME TODO - option to append userclass to existing value. 
			if($mode == 'batch')
			{
				//$list['#delete'] = "(clear userclass)"; // special 
				return $list;	
			}
						  $tmp = explode(",",$curval);
			$text = array();
			foreach($tmp as $v)
			{
				$text[] = $list[$v];	
			}
			return implode("<br />",$text); // $list[$curval];
					
		}*/
		
	
	
	function user_status($curval,$mode)
	{
	
		$row = $this->getController()->getListModel()->getData();
		$text = "";
			if ($row['user_perms'] == "0")
			{
				$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'>".LAN_MAINADMIN."</div>";
			}
			else
				if ($row['user_admin'])
				{
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;;text-align:center'><a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'>".LAN_ADMIN."</a></div>";
				}
				else
					if ($row['user_ban'] == 1)
					{
						$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'>".LAN_BANNED."</a></div>";
					}
					else
						if ($row['user_ban'] == 2)
						{
							$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_NOTVERIFIED."</div>";
						}
						else
							if ($row['user_ban'] == 3)
							{
								$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_BOUNCED."</div>";
							}
							else
							{
								$text .= "&nbsp;";
							}
		
		return $text;
	
		
	}
	
	
	function options($val, $mode) // old drop-down options. 
	{
		$controller = $this->getController();
		
		if($controller->getMode() != 'main' || $controller->getAction() != 'list') return;
		$row = $controller->getListModel()->getData();
		
		if(!getperms('4'))
		{
		//	return; 
		}
	
		
		extract($row);
		$text .= "<div>

				<input type='hidden' name='userid[{$user_id}]' value='{$user_id}' />
				<input type='hidden' name='userip[{$user_id}]' value='{$user_ip}' />
				<select name='useraction[{$user_id}]' onchange='this.form.submit()' class='tbox' style='width:75%'>
				<option selected='selected' value=''>&nbsp;</option>";
		if ($user_perms != "0")
		{
			// disabled user info <option value='userinfo'>".USRLAN_80."</option>
			$text .= "
					<option value='usersettings'>".LAN_EDIT."</option>
					";
			// login/logout As
			if(getperms('0') && !($row['user_admin'] && getperms('0', $row['user_perms'])))
			{
				if(e107::getUser()->getSessionDataAs() == $row['user_id']) $text .= "<option value='logoutas'>".sprintf(USRLAN_AS_2, $row['user_name'])."</option>";
				else $text .= "<option value='loginas'>".sprintf(USRLAN_AS_1, $row['user_name'])."</option>";
			}
			switch ($user_ban)
			{
				case 0 :
					$text .= "<option value='ban'>".USRLAN_30."</option>\n";
					break;
				case 1 :
					// Banned user
					$text .= "<option value='unban'>".USRLAN_33."</option>\n";
					break;
				case 2 :
					// Unverified
					$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='verify'>".USRLAN_32."</option>
						<option value='resend'>".USRLAN_112."</option>
						<option value='test'>".USRLAN_118."</option>";
					break;
				case 3 :
					// Bounced
					// FIXME wrong lan for 'reqverify' - USRLAN_181, wrong lan for 'verify' (USRLAN_182), changed to USRLAN_32
					$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='reqverify'>Make not verified</option>
						<option value='verify'>".USRLAN_32/*USRLAN_182*/."</option>
						<option value='test'>".USRLAN_118."</option>";
					break;
				default :
			}
			if (!$user_admin && !$user_ban && $user_ban != 2 && getperms('3'))
			{
				$text .= "<option value='admin'>".USRLAN_35."</option>\n";
			}
			else
				if ($user_admin && $user_perms != "0" && getperms('3'))
				{
					$text .= "<option value='adminperms'>".USRLAN_221."</option>\n";
					$text .= "<option value='unadmin'>".USRLAN_34."</option>\n";
				}
		}
		if ($user_perms == "0" && !getperms("0"))
		{
			$text .= "";
		}
		elseif ($user_id != USERID || getperms("0"))
		{
			$text .= "<option value='userclass'>".USRLAN_36."</option>\n";
		}
		if ($user_perms != "0")
		{
			$text .= "<option value='deluser'>".LAN_DELETE."</option>\n";
		}
		$text .= "</select></div>";
		return $text;	
	}

	
	function RankImageDropdown(&$imgList, $field, $curVal = '')
	{
		$ret = "
		<select class='tbox' name='{$field}'>
		<option value=''>".USRLAN_216."</option>
		";
		foreach ($imgList as $img)
		{
			$sel = ($img == $curVal ? "selected='selected'" : '');
			$ret .= "\n<option {$sel}>{$img}</option>";
		}
		$ret .= '</select>';
		return $ret;
	}
	
	
}

new users_admin();
require_once ('auth.php');

e107::getAdminUI()->runPage();
 
require_once ("footer.php");
