<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsletter/admin_config.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-08-23 03:54:05 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'newsletter';
require_once(e_ADMIN."auth.php");

if (e_QUERY) {
	list($action, $id) = explode(".", e_QUERY);
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
	$function = $action."Newsletter";
	$nl -> $function();
}

class newsletter
{

	var $message;


	function newsletter()
	{
		global $ns;

		foreach($_POST as $key => $value)
		{
			if(strstr($key, "nlmailnow"))
			{
				$this -> releaseIssue($key);
				break;
			}
		}

		if(isset($_POST['delete']))
		{
			$this -> deleteNewsletter();
		}

		if(isset($_POST['createNewsletter']))
		{
			$this -> createNewsletter();
		}

		if(isset($_POST['createIssue']))
		{
			$this -> createIssue();
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
			<td style='width:5%; text-align: center;' class='forumheader'>ID</td>
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
				<td style='width:20%; text-align: center;' class='forumheader'>".substr_count($data['newsletter_subscribers'], chr(1))."</td>
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
				<td style='width:10%; text-align: center;' class='forumheader3'>".($data['newsletter_flag'] ? NLLAN_16 : "<input class='button' type='submit' name='nlmailnow_".$data['newsletter_id']."' value='".NLLAN_17."' onclick=\"return jsconfirm('".$tp->toJS(".NLLAN_18.")."') \" />")."</td>
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

		if($edit)
		{
			extract($edit);
			$newsletter_title = $tp -> toFORM($newsletter_title);
			$newsletter_text = $tp -> toFORM($newsletter_text);
			$newsletter_footer = $tp -> toFORM($newsletter_footer);
		}

		$text .= "<div style='text-align:center; margin-left:auto; margin-right: auto;'>
		<form action='".e_SELF."' id='newsletterform' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_21."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_title' size='60' value='$newsletter_title' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_22."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' style='width:95%'>$newsletter_text</textarea></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_23."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_header' cols='80' rows='5' style='width:95%'>$newsletter_header</textarea></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_24."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_footer' cols='80' rows='5' style='width:95%'>$newsletter_footer</textarea></td>
		</tr>
		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>
		".
		($edit ? "<input class='button' type='submit' name='createNewsletter' value='".NLLAN_25."' />\n<input type='hidden' name='editid' value='$newsletter_id' />" : "<input class='button' type='submit' name='createNewsletter' value='".NLLAN_26."' />")."
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

		$newsletter_title = $tp -> toDB($_POST['newsletter_title']);
		$newsletter_text = $tp -> toDB($_POST['newsletter_text']);
		$newsletter_header = $tp -> toDB($_POST['newsletter_header']);
		$newsletter_footer = $tp -> toDB($_POST['newsletter_footer']);

		if(isset($_POST['editid']))
		{
			$sql -> db_Update("newsletter", "newsletter_title='$newsletter_title', newsletter_text='$newsletter_text', newsletter_header='$newsletter_header', newsletter_footer='$newsletter_footer' WHERE newsletter_id='".$_POST['editid']."' ");
			$this -> message = NLLAN_27;
		}
		else
		{
			$sql -> db_Insert("newsletter", "0, '".time()."', '$newsletter_title', '$newsletter_text', '$newsletter_header', '$newsletter_footer', '', '0', '0', '0' ");
			$this -> message = NLLAN_28;
		}
	}


	function makeNewsletter($edit=FALSE)
	{

		global $sql, $ns, $tp;

		if($edit)
		{
			extract($edit);
			$newsletter_title = $tp -> toFORM($newsletter_title);
			$newsletter_text = $tp -> toFORM($newsletter_text);
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
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_title' size='60' value='$newsletter_title' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_32."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='newsletter_issue' size='10' value='$newsletter_issue' maxlength='200' /></td>
		</tr>
		<tr>
		<td style='width:30%;' class='forumheader3'>".NLLAN_33."</td>
		<td style='width:70%' class='forumheader3'><textarea class='tbox' id='data' name='newsletter_text' cols='80' rows='10' style='width:95%'>$newsletter_text</textarea></td>
		</tr>
		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>
		".
		($edit ? "<input class='button' type='submit' name='createIssue' value='".NLLAN_34."' />\n<input type='hidden' name='editid' value='$newsletter_id' />" : "<input class='button' type='submit' name='createIssue' value='".NLLAN_35."' />")."
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
		$newsletter_title = $tp -> toDB($_POST['newsletter_title']);
		$newsletter_text = $tp -> toDB($_POST['newsletter_text']);

		if(isset($_POST['editid']))
		{
			$sql -> db_Update("newsletter", "newsletter_title='$newsletter_title', newsletter_text='$newsletter_text', newsletter_parent='".$_POST['newsletter_parent']."', newsletter_issue='".$_POST['newsletter_issue']."' WHERE newsletter_id='".$_POST['editid']."' ");
			$this -> message = NLLAN_38;
		}
		else
		{
			$sql -> db_Insert("newsletter", "0, '".time()."', '$newsletter_title', '$newsletter_text', '', '', '', '".$_POST['newsletter_parent']."', '0', '".$_POST['newsletter_issue']."' ");
			$this -> message = NLLAN_39;
		}
	}


	function releaseIssue($issue)
	{

		global $pref, $sql, $ns, $tp, $THEMES_DIRECTORY;

		$issue = str_replace("nlmailnow_", "", $issue);

		if(!$sql -> db_Select("newsletter", "*", "newsletter_id='$issue' "))
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
		if ($pref['smtp_enable'])
		{
			$mail->Mailer = "smtp";
			$mail->SMTPKeepAlive = TRUE;
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
		$mail->Charset = CHARSET;
		$mail->Subject = $newsletterParentInfo['newsletter_title'] . ": ".$newsletterInfo['newsletter_title'];
		$mail->IsHTML(true);

		// ============================  Render Results and Mailit =========

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
		$mail_style .= "<div class='fcaption'><b>$message_subject<br />[ issue ".$newsletterInfo['newsletter_issue']." ]</b></div><br /><br />";
		$mail_style .= "<div class='forumheader3'>";
		$message_body = $mail_style.$newsletter_header."<hr />".$message_body."<br><br><hr />".$newsletter_footer."<br></div></div>";

		$message_body = str_replace("\n", "<br>", $message_body);

		$mail->Body = $tp->toHTML($message_body, TRUE);
		$mail->AltBody = strip_tags(str_replace("<br>", "\n", $message_body));

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

				echo "<b>Sending</b> ".$mname." ( ".$memail." ) <br />";

				$mail->Send();
				$sent_counter ++;

				$mail->ClearAddresses();
				if ($pref['smtp_enable']) {
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

		if($sql -> db_Select("newsletter", "*", "newsletter_id='$id' "))
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
		if(strstr("newsletter", $tmp['key']))
		{
			$id = str_replace("newsletter_", "", $tmp['key']);
			$sql -> db_Delete("newsletter", "newsletter_id='$id' ");
			$this -> message = NLLAN_42;
		}
		else
		{
			$id = str_replace("issue_", "", $tmp['key']);
			$sql -> db_Delete("newsletter", "newsletter_id='$id' ");
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

}



require_once(e_ADMIN."footer.php");


function admin_config_adminmenu()
{
	global $nl;
	global $action;
	$nl->show_options($action);
}


?>