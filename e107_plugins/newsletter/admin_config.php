<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - Newsletter
 *
*/


require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}

e107::lan('newsletter',true);

$e_sub_cat = 'newsletter';
require_once(e_ADMIN.'auth.php');
// Include ren_help for display_help (while showing BBcodes)
require_once(e_HANDLER.'ren_help.php');

$mes = e107::getMessage();

if (e_QUERY) 
{
	list($action, $id, $key) = explode('.', e_QUERY);
	$key = intval($key);
	$id = intval($id);
}
else
{
	$action = FALSE;
	$id = FALSE;
}

$nl = new newsletter;


if(!e_QUERY)
{
	$nl -> showExistingNewsletters();
}
else
{
	switch ($action)
	{
		case 'vs' :	 		// View subscribers of a newsletter
			$nl -> view_subscribers($id);
			break;
		case  'remove' :	// Remove subscriber
			$nl -> remove_subscribers($id,$key);
			$nl -> view_subscribers($id);
			break;
		default:
			$function = $action.'Newsletter';
			if (method_exists($nl, $function))
			{
				$nl -> $function($id);
			}
			else
			{
				exit;
			}
	}
}


class newsletter
{
	protected $e107;
	var $message;

	public function __construct()
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$this->e107 = e107::getInstance(); // TODO remove?

		foreach($_POST as $key => $value)
		{
			$key = $tp->toDB($key);
			if(strpos($key, 'nlmailnow') === 0)
			{
				$this->releaseIssue($key);
				break;
			}
		}

		if(isset($_POST['delete']))
		{
			$this->deleteNewsletter();
		}

		if(isset($_POST['createNewsletter']))
		{
			$this->createNewsletter();
		}

		if(isset($_POST['createIssue']))
		{
			$this->createIssue();
		}

		echo $mes->render();
		/*if($mes)
		{
			$ns->tablerender($caption, $mes->render() . $text);
		}*/
	}


	function showExistingNewsletters()
	{
		$sql = e107::getDb();
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$tp = e107::getParser();

		if(!$sql->select('newsletter', '*', "newsletter_parent='0'  ORDER BY newsletter_id DESC"))
		{
			$mes->addInfo(NLLAN_05);
		}
		else
		{
			$text = "<form action='".e_SELF."' id='newsletterform' method='post'>
			<table class='table adminlist'>
			<colgroup>
				<col style='width: 5%; text-align: center;' />
				<col style='width: 65%;' />
				<col style='width: 20%; text-align: center;' />
				<col style='width: 10%; text-align: center;' />
			</colgroup>
			<tr>
				<td>".LAN_ID."</td>
				<td>".LAN_NAME."</td>
				<td>".NLLAN_07."</td>
				<td class='center options'>".LAN_OPTIONS."</td>
			</tr>
			";

			$nlArray = $sql->db_getList(); 
			foreach($nlArray as $data)
			{
				$text .= "
				<tr>
					<td>".$data['newsletter_id']."</td>
					<td>".$data['newsletter_title']."</td>
					<td>".((substr_count($data['newsletter_subscribers'], chr(1))!= 0)?"<a href='".e_SELF."?vs.".$data['newsletter_id']."'>".substr_count($data['newsletter_subscribers'], chr(1))."</a>":substr_count($data['newsletter_subscribers'], chr(1)))."</td>
					<td>
						<a class='btn btn-default btn-secondary btn-large' href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a>
						<input class='btn btn-default btn-secondary btn-large' type='image' title='".LAN_DELETE."' name='delete[newsletter_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [ID: ".$data['newsletter_id']." ]")."') \"/>

					</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			";
		}
		$ns->tablerender(NLLAN_10, $mes->render() . $text);

		unset($text);

		if(!$sql->select('newsletter', '*', "newsletter_parent!='0' ORDER BY newsletter_id DESC"))
		{
			$mes->addinfo(NLLAN_11);
		}
		else
		{
			$text = "<form action='".e_SELF."' id='newsletterform2' method='post'>
			<table class='table adminlist'>
			<colgroup>
				<col style='width: 5%;  text-align: center;' />
				<col style='width: 10%; text-align: center;' />
				<col style='width: 65%;' />
				<col style='width: 10%; text-align: center;' />
				<col style='width: 10%; text-align: center;' />
			</colgroup>
			<tr>
				<td>".LAN_ID."</td>
				<td>".NLLAN_12."</td>
				<td>".NLLAN_13."</td>
				<td>".NLLAN_14."</td>
				<td class='center options'>".LAN_OPTIONS."</td>
			</tr>
			";

			$nlArray = $sql->db_getList();

			foreach($nlArray as $data)
			{
				$text .= "
				<tr>
					<td>".$data['newsletter_id']."</td>
					<td>".$data['newsletter_issue']."</td>
					<td>[ ".$data['newsletter_parent']." ] ".$data['newsletter_title']."</td>
					<td>".($data['newsletter_flag'] ? LAN_YES : "<input class='btn btn-default btn-secondary button' type='submit' name='nlmailnow_".$data['newsletter_id']."' value='".NLLAN_17."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_18)."') \" />")."</td>
					<td><a class='btn btn-large' href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a><input type='image' title='".LAN_DELETE."' name='delete[issue_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_19." [ID: ".$data['newsletter_id']." ]")."') \"/>
				
				</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			";
		}
		$ns->tablerender(NLLAN_20, $mes->render() . $text);
	}



	function defineNewsletter($edit=FALSE)
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$frm = e107::getForm();

		// We've been passed a value from DB, so should be reasonably sanitised.
		if($edit)
		{
			$newsletter_title	= $tp->toFORM($edit['newsletter_title']);
			$newsletter_text	= $tp->toFORM($edit['newsletter_text']);
			$newsletter_footer	= $tp->toFORM($edit['newsletter_footer']);
			$newsletter_header	= $tp->toFORM($edit['newsletter_header']);
		}

		$text = "
		<form action='".e_SELF."' id='newsletterform' method='post'>
		<table class='table adminform'>
		<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
		<tr>
			<td>".LAN_TITLE."</td>
			<td>".$frm->text('newsletter_title', $newsletter_title, '200')."</td>
		</tr>
		<tr>
			<td>".LAN_DESCRIPTION."</td>
			<td><textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10'>{$newsletter_text}</textarea></td>
		</tr>
		<tr>
			<td>".NLLAN_23."</td>
			<td><textarea class='tbox' id='data' name='newsletter_header' cols='80' rows='5'>{$newsletter_header}</textarea></td>
		</tr>
		<tr>
			<td>".NLLAN_24."</td>
			<td><textarea class='tbox' id='data' name='newsletter_footer' cols='80' rows='5'>{$newsletter_footer}</textarea></td>
		</tr>	
		</table>
		
		<div class='buttons-bar center'>
		".($edit ? $frm->admin_button('createNewsletter', LAN_UPDATE, 'update')."\n<input type='hidden' name='editid' value='{$edit['newsletter_id']}' />" : $frm->admin_button('createNewsletter', LAN_CREATE, 'create'))."
		</div>
		</form>
		";

		$caption = ($edit ? LAN_PLUGIN_NEWSLETTER_NAME.' - '.LAN_UPDATE : LAN_PLUGIN_NEWSLETTER_NAME.' - '.LAN_CREATE);

		$ns->tablerender($caption, $text);
	}



	/**
	 * Save entry for new newsletter in DB, using $_POST values
	 *
	 * @param int $_POST['editid'] - ID of newsletter if existing - indicates edit to be saved
	 *
	 * @return none
	 */
	function createNewsletter()
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$letter['newsletter_title']		= $tp->toDB($_POST['newsletter_title']);
		$letter['newsletter_text']		= $tp->toDB($_POST['newsletter_text']);
		$letter['newsletter_header']	= $tp->toDB($_POST['newsletter_header']);
		$letter['newsletter_footer']	= $tp->toDB($_POST['newsletter_footer']);

		if(isset($_POST['editid']))
		{
			$sql ->update('newsletter', "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_header='{$letter['newsletter_header']}', newsletter_footer='{$letter['newsletter_footer']}' WHERE newsletter_id=".intval($_POST['editid']));
			$mes->addSuccess(LAN_UPDATED);
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$sql->insert('newsletter', $letter);
			$mes->addSuccess(LAN_CREATED);
		}
	}


	function makeNewsletter($edit=FALSE)
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$frm = e107::getForm();

		// Passed data is from DB
		if($edit)
		{
			$newsletter_title = $tp->toFORM($edit['newsletter_title']);
			$newsletter_text  = $tp->toFORM($edit['newsletter_text']);
			$newsletter_issue = $tp->toFORM($edit['newsletter_issue']);
		}

		if(!$sql->select('newsletter', '*', "newsletter_parent='0' "))
		{
			//$this -> message = NLLAN_05;
			$mes->addInfo(NLLAN_05);
		}
		else 
		{
		$nlArray = $sql -> db_getList();

		$text = "
		<form action='".e_SELF."' id='newsletterform' method='post'>
		<table class='table adminform'>
		<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
		<tr>
			<td>".NLLAN_30."</td>
			<td>

			<select name='newsletter_parent' class='tbox'>
			";

			foreach($nlArray as $nl)
			{
				$text .= "<option value='".$nl['newsletter_id']."'>".$nl['newsletter_title']."</option>\n";
			}

			$text .= "</select></td>

		<tr>
			<td>".NLLAN_31."</td>
			<td>".$frm->text('newsletter_title', $newsletter_title, '200')."</td>
		</tr>
		<tr>
			<td>".NLLAN_32."</td>
			<td>".$frm->number('newsletter_', $newsletter_issue, '200')."</td>
		</tr>
		<tr>
			<td>".NLLAN_33."</td>
			<td>
				<textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>{$edit['newsletter_text']}</textarea></td>
		</tr>
		</table>
		<div class='buttons-bar center'>
			".($edit ? $frm->admin_button('createIssue', LAN_UPDATE, 'update')."<input type='hidden' name='editid' value='{$edit['newsletter_id']}' />" : $frm->admin_button('createIssue', LAN_CREATE, 'submit'))."
		</div>
		</form>
		</div>
		";
		}
		$caption = ($edit ? NLLAN_36 : NLLAN_37);

		$ns->tablerender($caption, $mes->render() . $text);
	}


	function createIssue()
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$ns = e107::getRender();

		$letter['newsletter_title'] =  $tp->toDB($_POST['newsletter_title']);
		$letter['newsletter_text'] =   $tp->toDB($_POST['newsletter_text']);
		$letter['newsletter_parent'] = intval($_POST['newsletter_parent']);
		$letter['newsletter_issue'] =  $tp->toDB($_POST['newsletter_issue']);

		if (isset($_POST['editid']))
		{
			$sql->update('newsletter', "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_parent='".$letter['newsletter_parent']."', newsletter_issue='".$letter['newsletter_issue']."' WHERE newsletter_id=".intval($_POST['editid']));
			$mes->addSuccess(LAN_UPDATED);
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$sql->insert('newsletter', $letter);
			$mes->addSuccess(NLLAN_39);
		}

		echo $mes->render();
	//	$ns->tablerender($caption, $mes->render() . $text);
	}


	/**
	 * Actually release an issue of a newsletter
	 * Add the mailing to the mail queue
	 *
	 * @param int id of issue
	 *
	 * @return boolean FALSE on error
	 */
	function releaseIssue($issue)
	{
		$pref = e107::getPref();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$ns = e107::getRender();

		$issue = intval(str_replace('nlmailnow_', '', $issue));

		// Get details of current newsletter issue
		if(!$sql->select('newsletter', '*', 'newsletter_id='.$issue))
		{
			return FALSE;
		}
		$newsletterInfo = $sql->fetch();

		// Get parent details - has header/footer and subscriber list
		if(!$sql->select('newsletter', '*', "newsletter_id='".$newsletterInfo['newsletter_parent']."' "))
		{
			return FALSE;
		}
		$newsletterParentInfo = $sql->fetch();
		$memberArray = explode(chr(1), $newsletterParentInfo['newsletter_subscribers']);

		require(e_HANDLER.'mail_manager_class.php');
		$mailer = new e107MailManager;


		// Start by creating the mail body
		$mailData = array(
			'mail_content_status' => MAIL_STATUS_TEMP,
			'mail_create_app' => 'newsletter',
			'mail_title' => NLLAN_01.' '.$issue,
			'mail_subject' => $newsletterParentInfo['newsletter_title'] .': '.$newsletterInfo['newsletter_title'],
			'mail_sender_email' => $pref['siteadminemail'],
			'mail_sender_name'	=> $pref['siteadmin'],
			'mail_send_style'	=> 'themehtml',
			'mail_include_images' => TRUE
		);


		// Assemble body - we can leave a lot to to core mail sending routines
		$mail_style = "<div style='width:90%; padding-top:10px'>";
		$mail_style .= "<div><b>{$mailout['mail_subject']}<br />[ ".NLLAN_12." ".$newsletterInfo['newsletter_issue']." ]</b></div><br /><br />";
		$mail_style .= "<div>";
		$mailData['mail_body'] = $mail_style.$newsletterParentInfo['newsletter_header']."<hr />".$newsletterInfo['newsletter_text']."<br /><br /><hr />".$newsletterParentInfo['newsletter_footer']."<br /></div></div>";

		$result = $mailer->saveEmail($mailData, TRUE);
		if (is_numeric($result))
		{
			$mailMainID = $mailData['mail_source_id'] = $result;
		}
		else
		{
				// TODO: Handle error
		}
  

		$mailer->mailInitCounters($mailMainID);			// Initialise counters for emails added

		// Now add email addresses to the list

		foreach($memberArray as $memberID)
		{
			if ($memberID = intval($memberID))
			{
				if($sql->select('user', 'user_name,user_email,user_loginname,user_lastvisit', 'user_id='.$memberID))
				{
					$row = $sql->db_Fetch();
					$uTarget = array('mail_recipient_id' => $memberID,
									 'mail_recipient_name' => $row['user_name'],		// Should this use realname?
									 'mail_recipient_email' => $row['user_email'],
									 'mail_target_info' => array(
										'USERID' => $memberID,
										'DISPLAYNAME' => $row['user_name'],
										'USERNAME' => $row['user_loginname'],
										'USERLASTVISIT' => $row['user_lastvisit']
										)
									 );				// Probably overkill, but some user data in case we want to substitute
				}
				$result = $mailer->mailAddNoDup($mailMainID, $uTarget, MAIL_STATUS_TEMP);
				//echo '<b>'.NLLAN_54.'</b> '.$uTarget['mail_recipient_name'].' ( '.$uTarget['mail_recipient_email'].' ) <br />';
			}
		}
		$mailer->mailUpdateCounters($mailMainID);			// Update the counters
		$counters = $mailer->mailRetrieveCounters($mailMainID);		// Retrieve the counters
		if ($counters['add'] == 0)
		{
			$mailer->deleteEmail($mailMainID);			// No subscribers - delete email
			//$this->message = NLLAN_41;
			$mes->addError(NLLAN_41);
		}
		else
		{
			$mailer->activateEmail($mailMainID, FALSE);					// Actually mark the email for sending
			//$this->message = str_replace('[x]', $counters['add'],NLLAN_40);
			$mes->addSuccess(str_replace('[x]', $counters['add'], NLLAN_40));
		}
		$sql->update('newsletter', "newsletter_flag='1' WHERE newsletter_id=".$issue);

		$ns->tablerender($caption, $mes->render() . $text);
	}



	/**
	 * Edit a newsletter
	 *
	 * @param $id int ID of newsletter to edit
	 * @return none
	 */
	function editNewsletter($id)
	{
		$sql = e107::getDb();

		if($sql->select("newsletter", "*", "newsletter_id='{$id}'"))
		{
			$foo = $sql->fetch();
			if(!$foo['newsletter_parent'])
			{
				$this -> defineNewsletter($foo);
			}
			else
			{
				$this -> makeNewsletter($foo);
			}
		}
	}


	/**
	 * Delete a newsletter
	 *
	 * @return none
	 */
	function deleteNewsletter()
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$tmp = each($_POST['delete']);
		if(strpos($tmp['key'], 'newsletter') === 0)
		{
			$id = intval(str_replace('newsletter_', '', $tmp['key']));
			$sql->delete('newsletter', "newsletter_id='{$id}'");
			//$this -> message = NLLAN_42;
			$mes->addSuccess(LAN_DELETED);
		}
		else
		{
			$id = intval(str_replace('issue_', '', $tmp['key']));
			$sql->delete('newsletter', "newsletter_id='{$id}' ");
			//$this -> message = NLLAN_43;
			$mes->addSuccess(LAN_DELETED);
		}
	}



	/**
	 * Generate and display admin menu
	 *
	 * @return none
	 */
	function show_options($action)
	{
		if ($action == "")
		{
			$action = "main";
		}
		$var['main']['text'] = NLLAN_44;
		$var['main']['link'] = e_SELF;

		$var['define']['text'] = NLLAN_45;
		$var['define']['link'] = e_SELF."?define";

		$var['make']['text'] = NLLAN_46;
		$var['make']['link'] = e_SELF."?make";
	
		show_admin_menu(NLLAN_47, $action, $var);
	}



	function view_subscribers($p_id)
	{
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$frm = e107::getForm();

		$nl_sql = new db;
		$_nl_sanatized = '';
		$vs_text = '';


		if(!$nl_sql->db_Select('newsletter', '*', 'newsletter_id='.$p_id))// Check if newsletter id is available
		{	
			$mes->addError(NLLAN_56);
			$vs_text .= "<div class='buttons-bar center'>
							<input class='btn btn-default btn-secondary button' type=button value='".LAN_BACK."' onClick=\"window.location='".e_SELF."'\">
						</div>";
			$ns -> tablerender(NLLAN_65.' '.$p_id, $mes->render() . $vs_text);
			return;
		} 
		else 
		{
		  $vs_text .= "
			<form action='".e_SELF."' id='newsletterform' method='post'>
			<table class='table adminlist'>
			<colgroup>
				<col style='width: 5%;  text-align: center;' />
				<col style='width: 35%;' />
				<col style='width: 45%;' />
				<col style='width: 15%; text-align: center;' />
			</colgroup>

				<tr>
					<td>".LAN_ID."</td>
					<td>".LAN_NAME."</td>
					<td>".LAN_EMAIL."</td>
					<td>".LAN_OPTIONS."</td>
				</tr>";

			if($nl_row = $nl_sql-> db_Fetch())
			{
				$subscribers_list = explode(chr(1), trim($nl_row['newsletter_subscribers']));
				sort($subscriber_list);
				$subscribers_total_count = count($subscribers_list) - 1;	// Get a null entry as well
			}
			if ($subscribers_total_count<1) 
			{
				header("location:".e_SELF);
				exit;
			}
			// Loop through each user in the array subscribers_list & sanatize
			$_last_subscriber = '';
			foreach ($subscribers_list as $val)
			{
				$val=trim($val);
				if ($val)
				{
					if ($val != $_last_subscriber)
					{
						$nl_sql -> select("user", "*", "user_id=".$val);
						if($nl_row = $nl_sql-> fetch())
						{
							//<a href='".e_BASE."user.php?id.{$val}'>".$nl_row['user_name']."</a>
							$uparams = array('id' => $val, 'name' => $nl_row['user_name']);
							$link = e107::getUrl()->create('user/profile/view', $uparams);
							$userlink = "<a href='".$link."'>".$nl_row['user_name']."</a>";
							$vs_text .= "
							<tr>
								<td>".$val."</td>
								<td>".$userlink."</td>
								<td>".$nl_row['user_email']."</td>
								<td><a href='".e_SELF."?remove.{$p_id}.{$val}'>".ADMIN_DELETE_ICON."</a>".(($nl_row['user_ban'] > 0) ? NLLAN_62 : "")."</td>
							</tr>";
							$_last_subscriber = $val;
						}
					}
					else 
					{	// Duplicate user id found in the subscribers_list array!
						newsletter::remove_subscribers($p_id, $val);	// removes all entries for this user id
						$newsletterArray[$p_id]['newsletter_subscribers'] = chr(1).$val;	// keep this single value in the list
						$nl_sql -> update("newsletter", "newsletter_subscribers='".$newsletterArray[$p_id]['newsletter_subscribers']."' WHERE newsletter_id='".intval($p_id)."'");
						$subscribers_total_count --;
						$_nl_sanatized = 1;
					}
				}
			}
		}

		$vs_text .= "
		<tr>
			<td colspan='4'>".NLLAN_63.": ".$subscribers_total_count."</td>
		</tr>
		 </table>
			 <div class='buttons-bar center'>
			 	".$frm->admin_button('submit', LAN_BACK, 'submit')."
			 </div>			
		 </form>
		  ";
		if ($_nl_sanatized == 1)
		{
			$mes->addInfo(NLLAN_66);
		}
		
		$ns->tablerender(NLLAN_65.' '.$p_id, $mes->render() . $vs_text);
	}
 


	function remove_subscribers($p_id, $p_key) 
	{
		$sql = e107::getDb();
		$sql ->select('newsletter', '*', 'newsletter_id='.intval($p_id));
		if($nl_row = $sql->fetch())
		{
			$subscribers_list = array_flip(explode(chr(1), $nl_row['newsletter_subscribers']));
			unset($subscribers_list[$p_key]);
			$new_subscriber_list = implode(chr(1), array_keys($subscribers_list));
			$sql->update('newsletter', "newsletter_subscribers='{$new_subscriber_list}' WHERE newsletter_id='".$p_id."'");
		}
	}
}

require_once(e_ADMIN.'footer.php');

function admin_config_adminmenu()
{
	global $nl;
	global $action;
	$nl->show_options($action);
}
?>
