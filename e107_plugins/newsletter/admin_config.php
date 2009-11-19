<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsletter/admin_config.php,v $
 * $Revision: 1.12 $
 * $Date: 2009-11-19 20:24:21 $
 * $Author: e107steved $
 *
*/
require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'newsletter';
require_once(e_ADMIN.'auth.php');
// Include ren_help for display_help (while showing BBcodes)
require_once(e_HANDLER.'ren_help.php');

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
		$this->e107 = e107::getInstance();

		foreach($_POST as $key => $value)
		{
			$key = $this->e107->tp->toDB($key);
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

		if($this -> message)
		{
			$this->e107->ns->tablerender('', "<div style='text-align:center'><b>".$this -> message.'</b></div>');
		}
	}


	function showExistingNewsletters()
	{

		if(!$this->e107->sql->db_Select('newsletter', '*', "newsletter_parent='0'  ORDER BY newsletter_id DESC"))
		{
			$text = NLLAN_05;
		}
		else
		{
			$text = "<form action='".e_SELF."' id='newsletterform' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:5%; text-align: center;' class='forumheader'>".NLLAN_55."</td>
			<td style='width:65%' class='forumheader'>".NLLAN_06."</td>
			<td style='width:20%; text-align: center;' class='forumheader'>".NLLAN_07."</td>
			<td style='width:10%; text-align: center;' class='forumheader'>".NLLAN_08."</td>
			</tr>
			";

			$nlArray = $this->e107->sql->db_getList();
			foreach($nlArray as $data)
			{
				$text .= "<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>".$data['newsletter_id']."</td>
				<td style='width:65%' class='forumheader3'>".$data['newsletter_title']."</td>
				<td style='width:20%; text-align: center;' class='forumheader'>".((substr_count($data['newsletter_subscribers'], chr(1))!= 0)?"<a href='".e_SELF."?vs.".$data['newsletter_id']."'>".substr_count($data['newsletter_subscribers'], chr(1))."</a>":substr_count($data['newsletter_subscribers'], chr(1)))."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[newsletter_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$this->e107->tp->toJS(NLLAN_09." [ID: ".$data['newsletter_id']." ]")."') \"/>
				
				</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			</div>
			";
		}
		$this->e107->ns->tablerender(NLLAN_10, $text);

		if(!$this->e107->sql->db_Select('newsletter', '*', "newsletter_parent!='0' ORDER BY newsletter_id DESC"))
		{
			$text = NLLAN_11;
		}
		else
		{
			$text = "<form action='".e_SELF."' id='newsletterform2' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:5%; text-align: center;' class='forumheader'>ID</td>
			<td style='width:10%; text-align: center;' class='forumheader'>".NLLAN_12."</td>
			<td style='width:65%' class='forumheader'>".NLLAN_13."</td>
			<td style='width:10%; text-align: center;' class='forumheader'>".NLLAN_14."</td>
			<td style='width:10%; text-align: center;' class='forumheader'>".NLLAN_15."</td>
			</tr>
			";

			$nlArray = $this->e107->sql->db_getList();

			foreach($nlArray as $data)
			{
				$text .= "<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>".$data['newsletter_id']."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>".$data['newsletter_issue']."</td>
				<td style='width:65%' class='forumheader3'>[ ".$data['newsletter_parent']." ] ".$data['newsletter_title']."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>".($data['newsletter_flag'] ? NLLAN_16 : "<input class='button' type='submit' name='nlmailnow_".$data['newsletter_id']."' value='".NLLAN_17."' onclick=\"return jsconfirm('".$this->e107->tp->toJS(NLLAN_18)."') \" />")."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[issue_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$this->e107->tp->toJS(NLLAN_19." [ID: ".$data['newsletter_id']." ]")."') \"/>
				
				</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			</div>
			";
		}
		$this->e107->ns->tablerender(NLLAN_20, $text);
	}



	function defineNewsletter($edit=FALSE)
	{
		// We've been passed a value from DB, so should be reasonably sanitised.
		if($edit)
		{
			$newsletter_title	= $this->e107->tp->toFORM($edit['newsletter_title']);
			$newsletter_text	= $this->e107->tp->toFORM($edit['newsletter_text']);
			$newsletter_footer	= $this->e107->tp->toFORM($edit['newsletter_footer']);
			$newsletter_header	= $this->e107->tp->toFORM($edit['newsletter_header']);
		}

		$text .= "<div style='text-align:center; margin-left:auto; margin-right: auto;'>
		<form action='".e_SELF."' id='newsletterform' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_21."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_title' size='60' value='{$newsletter_title}' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_22."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' style='width:95%'>{$newsletter_text}</textarea></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_23."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_header' cols='80' rows='5' style='width:95%'>{$newsletter_header}</textarea></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_24."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_footer' cols='80' rows='5' style='width:95%'>{$newsletter_footer}</textarea></td>
		</tr>
		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>
		".
		($edit ? "<input class='button' type='submit' name='createNewsletter' value='".NLLAN_25."' />\n<input type='hidden' name='editid' value='{$edit['newsletter_id']}' />" : "<input class='button' type='submit' name='createNewsletter' value='".NLLAN_26."' />")."
		</td>
		</tr>

		</table>
		</form>
		</div>
		";

		$caption = ($edit ? NLLAN_25 : NLLAN_26);

		$this->e107->ns->tablerender($caption, $text);
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
		$letter['newsletter_title']		= $this->e107->tp->toDB($_POST['newsletter_title']);
		$letter['newsletter_text']		= $this->e107->tp->toDB($_POST['newsletter_text']);
		$letter['newsletter_header']	= $this->e107->tp->toDB($_POST['newsletter_header']);
		$letter['newsletter_footer']	= $this->e107->tp->toDB($_POST['newsletter_footer']);

		if(isset($_POST['editid']))
		{
			$this->e107->sql -> db_Update('newsletter', "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_header='{$letter['newsletter_header']}', newsletter_footer='{$letter['newsletter_footer']}' WHERE newsletter_id=".intval($_POST['editid']));
			$this -> message = NLLAN_27;
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$this->e107->sql->db_Insert('newsletter', $letter);
			$this -> message = NLLAN_28;
		}
	}



	function makeNewsletter($edit=FALSE)
	{
		// Passed data is from DB
		if($edit)
		{
			$newsletter_title = $this->e107->tp->toFORM($edit['newsletter_title']);
			$newsletter_text  = $this->e107->tp->toFORM($edit['newsletter_text']);
			$newsletter_issue = $this->e107->tp->toFORM($edit['newsletter_issue']);
		}

		if(!$this->e107->sql->db_Select('newsletter', '*', "newsletter_parent='0' "))
		{
			$this -> message = NLLAN_05;
			return;
		}

		$nlArray = $this->e107->sql -> db_getList();

		$text .= "<div style='text-align:center; margin-left:auto; margin-right: auto;'>
		<form action='".e_SELF."' id='newsletterform' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_30."</td>
		<td style='width:70%' class='forumheader3'>

		<select name='newsletter_parent' class='tbox'>
		";

		foreach($nlArray as $nl)
		{
			$text .= "<option value='".$nl['newsletter_id']."'>".$nl['newsletter_title']."</option>\n";
		}

		$text .= "</select>

		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_31."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_title' size='60' value='{$newsletter_title}' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_32."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_issue' size='10' value='{$newsletter_issue}' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_33."</td>
		<td style='width:70%' class='forumheader3'>
			<textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>{$edit['newsletter_text']}</textarea><br/>".display_help('helpa')."
		</td>
		</tr>
		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>
		".
		($edit ? "<input class='button' type='submit' name='createIssue' value='".NLLAN_34."' />\n<input type='hidden' name='editid' value='{$edit['newsletter_id']}' />" : "<input class='button' type='submit' name='createIssue' value='".NLLAN_35."' />")."
		</td>
		</tr>
		</table>
		</form>
		</div>
		";

		$caption = ($edit ? NLLAN_36 : NLLAN_37);

		$this->e107->ns->tablerender($caption, $text);
	}




	function createIssue()
	{
		$letter['newsletter_title'] =  $this->e107->tp->toDB($_POST['newsletter_title']);
		$letter['newsletter_text'] =   $this->e107->tp->toDB($_POST['newsletter_text']);
		$letter['newsletter_parent'] = intval($_POST['newsletter_parent']);
		$letter['newsletter_issue'] =  $this->e107->tp->toDB($_POST['newsletter_issue']);

		if (isset($_POST['editid']))
		{
			$this->e107->sql->db_Update('newsletter', "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_parent='".$letter['newsletter_parent']."', newsletter_issue='".$letter['newsletter_issue']."' WHERE newsletter_id=".intval($_POST['editid']));
			$this -> message = NLLAN_38;
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$this->e107->sql->db_Insert('newsletter', $letter);
			$this -> message = NLLAN_39;
		}
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
		global $pref;

		$issue = intval(str_replace('nlmailnow_', '', $issue));

		// Get details of current newsletter issue
		if(!$this->e107->sql->db_Select('newsletter', '*', 'newsletter_id='.$issue))
		{
			return FALSE;
		}
		$newsletterInfo = $this->e107->sql->db_Fetch(MYSQL_ASSOC);

		// Get parent details - has header/footer and subscriber list
		if(!$this->e107->sql -> db_Select('newsletter', '*', "newsletter_id='".$newsletterInfo['newsletter_parent']."' "))
		{
			return FALSE;
		}
		$newsletterParentInfo = $this->e107->sql->db_Fetch(MYSQL_ASSOC);
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
		$mail_style .= "<div class='fcaption'><b>{$mailout['mail_subject']}<br />[ ".NLLAN_12." ".$newsletterInfo['newsletter_issue']." ]</b></div><br /><br />";
		$mail_style .= "<div class='forumheader3'>";
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
				if($this->e107->sql->db_Select('user', 'user_name,user_email,user_loginname,user_lastvisit', 'user_id='.$memberID))
				{
					$row = $this->e107->sql->db_Fetch(MYSQL_ASSOC);
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
			$this->message = NLLAN_41;
		}
		else
		{
			$mailer->activateEmail($mailMainID, FALSE);					// Actually mark the email for sending
			$this->message = str_replace('--COUNT--', $counters['add'],NLLAN_40);
		}
		$this->e107->sql->db_Update('newsletter', "newsletter_flag='1' WHERE newsletter_id=".$issue);
	}



	/**
	 * Edit a newsletter
	 *
	 * @param $id int ID of newsletter to edit
	 * @return none
	 */
	function editNewsletter($id)
	{
		if($this->e107->sql->db_Select("newsletter", "*", "newsletter_id='{$id}'"))
		{
			$foo = $this->e107->sql->db_Fetch();
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
		$tmp = each($_POST['delete']);
		if(strpos($tmp['key'], 'newsletter') === 0)
		{
			$id = intval(str_replace('newsletter_', '', $tmp['key']));
			$this->e107->sql->db_Delete('newsletter', "newsletter_id='{$id}'");
			$this -> message = NLLAN_42;
		}
		else
		{
			$id = intval(str_replace('issue_', '', $tmp['key']));
			$this->e107->sql->db_Delete('newsletter', "newsletter_id='{$id}' ");
			$this -> message = NLLAN_43;
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
		$nl_sql = new db;
		$_nl_sanatized = '';
		if(!$nl_sql -> db_Select('newsletter', '*', 'newsletter_id='.$p_id))
		{	// Check if newsletter id is available
			$vs_text .= "<br /><br /><center>".NLLAN_56."<br /><br/>
					 <input class='button' type=button value='".NLLAN_57."' onClick=\"window.location='".e_SELF."'\"></center>";
			$ns -> tablerender(NLLAN_58, $vs_text);
			return;
		} 
		else 
		{
		  $vs_text .= "
				<form action='".e_SELF."' id='newsletterform' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
				<tr>
				<td style='width:5%; text-align: center;' class='forumheader'>".NLLAN_55."</td>
				<td style='width:35%' class='forumheader'>".NLLAN_59."</td>
				<td style='width:45%;' class='forumheader'>".NLLAN_60."</td>
				<td style='width:15%; text-align: center;' class='forumheader'>".NLLAN_61."</td>
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
						$nl_sql -> db_Select("user", "*", "user_id=".$val);
						if($nl_row = $nl_sql-> db_Fetch())
						{
							$vs_text .= "<tr>
								<td style='text-align: center;' class='forumheader3'>{$val}
								</td>
								<td class='forumheader3'><a href='".e_BASE."user.php?id.{$val}'>".$nl_row['user_name']."</a>
								</td>
								<td class='forumheader3'>".$nl_row['user_email']."
								</td>
								<td style='text-align: center;' class='forumheader3'><a href='".e_SELF."?remove.{$p_id}.{$val}'>".ADMIN_DELETE_ICON."</a>
							".(($nl_row['user_ban'] > 0) ? NLLAN_62 : "")."
							</td>
							</tr>";
							$_last_subscriber = $val;
						}
					}
					else 
					{	// Duplicate user id found in the subscribers_list array!
						newsletter::remove_subscribers($p_id, $val);	// removes all entries for this user id
						$newsletterArray[$p_id]['newsletter_subscribers'] = chr(1).$val;	// keep this single value in the list
						$nl_sql -> db_Update("newsletter", "newsletter_subscribers='".$newsletterArray[$p_id]['newsletter_subscribers']."' WHERE newsletter_id='".intval($p_id)."'");
						$subscribers_total_count --;
						$_nl_sanatized = 1;
					}
				}
			}
		}

		$vs_text .= "
		  <tr>
		  <td colspan='4' class='forumheader'>".NLLAN_63.": ".$subscribers_total_count."</td>
		  </tr>
		  <tr><td colspan='4' style='text-align:center;'><br /><input class='button' type='submit' value='".NLLAN_64."' /></td></tr>
		  </table></form>
		  ";
		if ($_nl_sanatized == 1)
		{
			$vs_text .= "<br /><div style='text-align:center;'>".NLLAN_66."</div>";
		}
		$this->e107->ns->tablerender(NLLAN_65.' '.$p_id, $vs_text);
	}
 


	function remove_subscribers($p_id, $p_key) 
	{
		$this->e107->sql -> db_Select('newsletter', '*', 'newsletter_id='.intval($p_id));
		if($nl_row = $this->e107->sql-> db_Fetch(MYSQL_ASSOC))
		{
			$subscribers_list = array_flip(explode(chr(1), $nl_row['newsletter_subscribers']));
			unset($subscribers_list[$p_key]);
			$new_subscriber_list = implode(chr(1), array_keys($subscribers_list));
			$this->e107->sql->db_Update('newsletter', "newsletter_subscribers='{$new_subscriber_list}' WHERE newsletter_id='".$p_id."'");
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