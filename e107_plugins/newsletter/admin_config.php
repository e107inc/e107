<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once('../../class2.php');
if (!getperms("P")) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'newsletter';
require_once(e_ADMIN."auth.php");

if (e_QUERY) 
{
	list($action, $id, $key) = explode(".", e_QUERY);
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
		case 'vs' :	 // View subscribers of a newsletter
			$nl -> view_subscribers($id);
			break;
		case  'remove' :	// Remove subscriber
			$nl -> remove_subscribers($id,$key);
			$nl -> view_subscribers($id);
			break;
		default:
			$function = $action."Newsletter";
			if (method_exists($nl, $function))
			{
				$nl -> $function();
			}
			else
			{
				exit;
			}
	}
}



class newsletter
{
	var $message;


	function newsletter()
	{
		global $ns, $tp;

		foreach($_POST as $key => $value)
		{
			$key = $tp->toDB($key);
			if(strstr($key, "nlmailnow"))
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
			$ns->tablerender("", "<div style='text-align:center'><b>".$this -> message."</b></div>");
		}
	}


	function showExistingNewsletters()
	{
		global $sql, $ns, $tp;

		if(!$sql -> db_Select("newsletter", "*", "newsletter_parent='0'  ORDER BY newsletter_id DESC"))
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

			$nlArray = $sql -> db_getList();
			foreach($nlArray as $data)
			{
				$text .= "<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>".$data['newsletter_id']."</td>
				<td style='width:65%' class='forumheader3'>".$data['newsletter_title']."</td>
				<td style='width:20%; text-align: center;' class='forumheader'>".((substr_count($data['newsletter_subscribers'], chr(1))!= 0)?"<a href='".e_SELF."?vs.".$data['newsletter_id']."'>".substr_count($data['newsletter_subscribers'], chr(1))."</a>":substr_count($data['newsletter_subscribers'], chr(1)))."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[newsletter_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_09." [ID: ".$data['newsletter_id']." ]")."') \"/>
				
				</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			</div>
			";
		}
		$ns -> tablerender(NLLAN_10, $text);


		if(!$sql -> db_Select("newsletter", "*", "newsletter_parent!='0' ORDER BY newsletter_id DESC"))
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

			$nlArray = $sql -> db_getList();

			foreach($nlArray as $data)
			{
				$text .= "<tr>
				<td style='width:5%; text-align: center;' class='forumheader3'>".$data['newsletter_id']."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>".$data['newsletter_issue']."</td>
				<td style='width:65%' class='forumheader3'>[ ".$data['newsletter_parent']." ] ".$data['newsletter_title']."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>".($data['newsletter_flag'] ? NLLAN_16 : "<input class='button' type='submit' name='nlmailnow_".$data['newsletter_id']."' value='".NLLAN_17."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_18)."') \" />")."</td>
				<td style='width:10%; text-align: center;' class='forumheader3'>
				<a href='".e_SELF."?edit.".$data['newsletter_id']."'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[issue_".$data['newsletter_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_19." [ID: ".$data['newsletter_id']." ]")."') \"/>
				
				</td>
				</tr>
				";
			}

			$text .= "</table>
			</form>
			</div>
			";
		}
		$ns -> tablerender(NLLAN_20, $text);
	}




	function defineNewsletter($edit=FALSE)
	{
		global $ns, $tp;
		// We've been passed a value from DB, so should be reasonably sanitised.

		if($edit)
		{
			$newsletter_title = $tp -> toFORM($edit['newsletter_title']);
			$newsletter_text = $tp -> toFORM($edit['newsletter_text']);
			$newsletter_footer = $tp -> toFORM($edit['newsletter_footer']);
			$newsletter_header = $tp -> toFORM($edit['newsletter_header']);	// Looks as if this was missed
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

		$ns -> tablerender($caption, $text);
	}



	function createNewsletter()
	{
		global $sql, $tp;

		$letter['newsletter_title'] = $tp -> toDB($_POST['newsletter_title']);
		$letter['newsletter_text'] = $tp -> toDB($_POST['newsletter_text']);
		$letter['newsletter_header'] = $tp -> toDB($_POST['newsletter_header']);
		$letter['newsletter_footer'] = $tp -> toDB($_POST['newsletter_footer']);

		if(isset($_POST['editid']))
		{
			$sql -> db_Update("newsletter", "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_header='{$letter['newsletter_header']}', newsletter_footer='{$letter['newsletter_footer']}' WHERE newsletter_id=".intval($_POST['editid']));
			$this -> message = NLLAN_27;
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$sql -> db_Insert('newsletter', $letter);
			$this -> message = NLLAN_28;
		}
	}



	function makeNewsletter($edit=FALSE)
	{
		global $sql, $ns, $tp;

		// Passed data is from DB
		if($edit)
		{
			$newsletter_title = $tp -> toFORM($edit['newsletter_title']);
			$newsletter_text = $tp -> toFORM($edit['newsletter_text']);
			$newsletter_issue = $tp -> toFORM($edit['newsletter_issue']);
		}

		if(!$sql -> db_Select("newsletter", "*", "newsletter_parent='0' "))
		{
			$this -> message = NLLAN_29;
			return;
		}

		$nlArray = $sql -> db_getList();

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
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' style='width:95%'>{$edit['newsletter_text']}</textarea></td>
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

		$ns -> tablerender($caption, $text);
	}



	function createIssue()
	{
		global $sql, $tp;
		$letter['newsletter_title'] = $tp -> toDB($_POST['newsletter_title']);
		$letter['newsletter_text'] = $tp -> toDB($_POST['newsletter_text']);
		$letter['newsletter_parent'] = intval($_POST['newsletter_parent']);
		$letter['newsletter_issue'] = $tp->toDB($_POST['newsletter_issue']);

		if (isset($_POST['editid']))
		{
			$sql -> db_Update('newsletter', "newsletter_title='{$letter['newsletter_title']}', newsletter_text='{$letter['newsletter_text']}', newsletter_parent='".$letter['newsletter_parent']."', newsletter_issue='".$letter['newsletter_issue']."' WHERE newsletter_id=".intval($_POST['editid']));
			$this -> message = NLLAN_38;
		}
		else
		{
			$letter['newsletter_datestamp'] = time();
			$sql -> db_Insert('newsletter', $letter);
			$this -> message = NLLAN_39;
		}
	}



	function releaseIssue($issue)
	{
		global $pref, $sql, $ns, $tp, $THEMES_DIRECTORY;

		$issue = str_replace("nlmailnow_", "", $issue);

		if(!$sql -> db_Select("newsletter", "*", "newsletter_id='{$issue}' "))
		{
			return FALSE;
		}
		$newsletterInfo = $sql -> db_Fetch();

		if(!$sql -> db_Select("newsletter", "*", "newsletter_id='".$newsletterInfo['newsletter_parent']."' "))
		{
			return FALSE;
		}
		$newsletterParentInfo = $sql -> db_Fetch();
		$memberArray = explode(chr(1), $newsletterParentInfo['newsletter_subscribers']);

		require(e_HANDLER."phpmailer/class.phpmailer.php");

		$mail = new PHPMailer();

		$mail->From = $pref['siteadminemail'];
		$mail->FromName = $pref['siteadmin'];
		if ($pref['mailer'] == "smtp")
		{
			$mail->Mailer = "smtp";
			$mail->SMTPKeepAlive = (isset($pref['smtp_keepalive']) && $pref['smtp_keepalive']==1) ? TRUE : FALSE;
			$mail->SMTPAuth = TRUE;
			$mail->Username = $pref['smtp_username'];
			$mail->Password = $pref['smtp_password'];
			$mail->Host = $pref['smtp_server'];
		}
		else
		{
			$mail->Mailer = "mail";
		}

		$mail->WordWrap = 50;
		$mail->CharSet = CHARSET;
		$mail->Subject = $newsletterParentInfo['newsletter_title'] . ": ".$newsletterInfo['newsletter_title'];
		$mail->IsHTML(true);

		// ============================  Render Results and Mail it =========

		$message_subject = stripslashes($tp -> toHTML($mail->Subject));
		$message_body = stripslashes($tp -> toHTML($mail->Subject, TRUE));
		$message_body = str_replace("&quot;", '"', $tp -> toHTML($newsletterInfo['newsletter_text'], TRUE));
		$message_body = str_replace('src="', 'src="'.SITEURL, $message_body);

		$newsletter_header = $tp -> toHTML($newsletterParentInfo['newsletter_header'], TRUE);
		$newsletter_footer = $tp -> toHTML($newsletterParentInfo['newsletter_footer'], TRUE);


		$theme = $THEMES_DIRECTORY.$pref['sitetheme']."/";
		$mail_style = "<link rel=\"stylesheet\" href=\"".SITEURL.$theme."style.css\" type=\"text/css\" />";
		$mail_style .= "<div style='width:100%'>";
		$mail_style .= "<div style='width:90%; padding-top:10px'>";
		$mail_style .= "<div class='fcaption'><b>$message_subject<br />[ ".NLLAN_12." ".$newsletterInfo['newsletter_issue']." ]</b></div><br /><br />";
		$mail_style .= "<div class='forumheader3'>";
		$message_body = $mail_style.$newsletter_header."<hr />".$message_body."<br /><br /><hr />".$newsletter_footer."<br /></div></div>";

		$message_body = str_replace("\n", "<br />", $message_body);

		$mail->Body = $tp->toHTML($message_body, TRUE,'no_replace, emotes_off');
		$mail->AltBody = strip_tags(str_replace("<br />", "\n", $message_body));

		$sent_counter = 0;

		foreach($memberArray as $memberID)
		{
			if($memberID)
			{
				if($sql -> db_Select("user", "user_name, user_email", "user_id='$memberID' "))
				{
					$row = $sql -> db_Fetch();
					$mname = $row['user_name'];
					$memail = $row['user_email'];
				}

				$mail->AddAddress($memail, $mname);

				echo "<b>".NLLAN_54."</b> ".$mname." ( ".$memail." ) <br />";

				$mail->Send();
				$sent_counter ++;

				$mail->ClearAddresses();
				if ($pref['mailer'] == "smtp") {
					$mail->SmtpClose();
				}
			}
		}
		$sql -> db_Update("newsletter", "newsletter_flag='1' WHERE newsletter_id='$issue' ");
		$this -> message = NLLAN_40.$sent_counter.NLLAN_41;
	}



	function editNewsletter()
	{
		global $id, $sql;

		if($sql -> db_Select("newsletter", "*", "newsletter_id='{$id}' "))
		{
			$foo = $sql -> db_Fetch();
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



	function deleteNewsletter()
	{
		global $sql;
		$tmp = each($_POST['delete']);
		if(strstr($tmp['key'], "newsletter"))
		{
			$id = str_replace("newsletter_", "", $tmp['key']);
			$sql -> db_Delete("newsletter", "newsletter_id='{$id}' ");
			$this -> message = NLLAN_42;
		}
		else
		{
			$id = str_replace("issue_", "", $tmp['key']);
			$sql -> db_Delete("newsletter", "newsletter_id='{$id}' ");
			$this -> message = NLLAN_43;
		}
	}



	function show_options($action)
	{
		global $sql;
		if ($action == "")
		{
			$action = "main";
		}
		// ##### Display options ---------------------------------------------------------------------------------------------------------

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
	global $ns;

	$nl_sql = new db;
	if(!$nl_sql -> db_Select('newsletter', '*', 'newsletter_id='.$p_id))
	{
		// Check if newsletter id is available
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

//		$nl_sql -> db_Select("newsletter", "*", "newsletter_id=".$p_id);		Already done
		if($nl_row = $nl_sql-> db_Fetch())
		{
			$subscribers_list = explode(chr(1), trim($nl_row['newsletter_subscribers']));
			$subscribers_total_count = count($subscribers_list) - 1;		// Get a null entry as well
		}
		if ($subscribers_total_count<1) 
		{
			header("location:".e_SELF);
			exit;
		}
		// Loop through each user in the array subscribers_list
		foreach ($subscribers_list as $val)
		{
			$val=trim($val);
			if ($val) 
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
	$ns -> tablerender(NLLAN_65.' '.$p_id, $vs_text);
 }
 
 
	function remove_subscribers($p_id, $p_key) 
	{
		global $sql;
		$sql -> db_Select("newsletter", "*", "newsletter_id=".$p_id);
		if($nl_row = $sql-> db_Fetch())
		{
			$subscribers_list = array_flip(explode(chr(1), $nl_row['newsletter_subscribers']));
			unset($subscribers_list[$p_key]);
			$new_subscriber_list = implode(chr(1), array_keys($subscribers_list));
			$sql -> db_Update("newsletter", "newsletter_subscribers='{$new_subscriber_list}' WHERE newsletter_id=".$p_id);
		}
	}
}



require_once(e_ADMIN."footer.php");


function admin_config_adminmenu()
{
	global $nl;
	global $action;
	$nl->show_options($action);
}

?>