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
|     $Source: /cvs_backup/e107_0.7/e107_admin/mailout.php,v $
|     $Revision: 1.33 $
|     $Date: 2005-08-18 17:29:23 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
$e_sub_cat = 'mail';
$e_wysiwyg = "email_body";
set_time_limit(180);

require_once(e_ADMIN."auth.php");
if (!getperms("W")) {
	header("location:".e_BASE."index.php");
	 exit;
}
require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");
require_once(e_HANDLER."userclass_class.php");

// Get name of extended language field.
if($sql -> db_Select("user_extended_struct", "user_extended_struct_name", "user_extended_struct_type='8'")){
$row = $sql -> db_Fetch();
$language_field = $row['user_extended_struct_name'];
}

if (isset($_POST['testemail'])) {
    if(SITEADMINEMAIL == ""){
		$message = MAILAN_19;
	}else{
		require_once(e_HANDLER."mail.php");
		$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : " (PHP)";
		if (!sendemail(SITEADMINEMAIL, PRFLAN_66." ".SITENAME.$add, PRFLAN_67)) {
			$message = ($pref['mailer'] == "smtp")  ? PRFLAN_75 : PRFLAN_68;
		} else {
			$message = PRFLAN_69;
		}
	}
}



if (isset($_POST['submit'])) {

	if ($_POST['email_to'] == "all" || $_POST['email_to'] == "admin") {

		switch ($_POST['email_to']) {
			case "admin":
				$insert = "u.user_admin='1' ";
			break;

			case "all":
				$insert = "u.user_ban='0' ";
			break;
		}
		$insert2 = ($_POST['language']) ? " AND ue.user_{$language_field} = '".$_POST['language']."' " : "";
		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE $insert $insert2 ORDER BY u.user_name";

	} elseif($_POST['email_to'] == "unverified"){
        $qry = "SELECT u.* FROM #user AS u WHERE u.user_ban='2'";
	} elseif($_POST['email_to'] == "self"){
       $qry = "SELECT u.* FROM #user AS u WHERE u.user_id='".USERID."'";
	} else {
        $insert = "u.user_class IN (".$_POST['email_to'].")";
		$insert2 = ($_POST['language']) ? " AND ue.user_{$language_field} = '".$_POST['language']."' " : "";
		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE $insert $insert2 ORDER BY u.user_name";
	}

		$sql->db_Select_gen($qry);
		$c = 0;
			while ($row = $sql->db_Fetch()) {
				extract($row);
				$recipient_name[$c] = $user_name;
				$recipient[$c] = $user_email;
				$recipient_key[$c] = $user_sess;
				$recipient_id[$c] = $user_id;
				$c++;
			}



	// ===== phpmailer version.

	require(e_HANDLER."phpmailer/class.phpmailer.php");

	$mail = new PHPMailer();

	$mail->From = ($_POST['email_from_email'])? $_POST['email_from_email']:	$pref['siteadminemail'];
	$mail->FromName = ($_POST['email_from_name'])? $_POST['email_from_name']: $pref['siteadmin'];
	//  $mail->Host     = "smtp1.site.com;smtp2.site.com";
	if ($pref['mailer']== 'smtp' || $pref['smtp_enable']==1) {
		$mail->Mailer = "smtp";
		$mail->SMTPKeepAlive = TRUE;
		$mail->Host = $pref['smtp_server'];
		if($pref['smtp_username'] && $pref['smtp_password']){
			$mail->SMTPAuth = TRUE;
			$mail->Username = $pref['smtp_username'];
			$mail->Password = $pref['smtp_password'];
			$mail->$PluginDir = e_HANDLER."phpmailer/";
        }
    } elseif ($pref['mailer']== 'sendmail'){
		$mail->Mailer = "sendmail";
		$mail->Sendmail = ($pref['sendmail']) ? $pref['sendmail'] : "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'];
	} else {
        $mail->Mailer = "mail";
	}

	$mail->AddCC = ($_POST['email_cc']);
	$mail->WordWrap = 50;
	$mail->Charset = CHARSET;
	$mail->Subject = $_POST['email_subject'];
	$mail->IsHTML(true);

	$attach = chop($_POST['email_attachment']);

	$attach_link = e_DOWNLOAD.$attach;
//	echo $attach_link;

	if ($attach != "" && !$mail->AddAttachment($attach_link, $attach)){
		$mss = "There is a problem with the attachment<br />$attach_link";
		$ns->tablerender("Error", $mss);
		require_once(e_ADMIN."footer.php");
		exit;
	}
	// ============================  Render Results and Mailit =========

	$text = "<div style='overflow:auto;height:300px; ".ADMIN_WIDTH."'>";
	$text .= "<table class='fborder' style='width:100%'>";
	$text .= "<tr><td class='fcaption'>Username</td><td class='fcaption'>Email</td><td class='fcaption'>Status</td></tr>";
	$message_subject = stripslashes($tp -> toHTML($_POST['email_subject']));
	$message_body = stripslashes($tp -> toHTML($_POST['email_body']));
	$message_body = str_replace("&quot;", '"', $message_body);
	$message_body = eregi_replace('src="', 'src="'.SITEURL, $message_body);

	if (isset($_POST['use_theme'])) {
		$theme = $THEMES_DIRECTORY.$pref['sitetheme']."/";
		$mail_style = "<link rel=\"stylesheet\" href=\"".SITEURL.$theme."style.css\" type=\"text/css\" />";
		$mail_style .= "<div style='text-align:center; width:100%'>";
		$mail_style .= "<div style='width:90%;text-align:center;padding-top:10px'>";
		$mail_style .= "<div class='fcaption' style='text-align:center'><b>$message_subject</b></div>";
		$mail_style .= "<div class='forumheader3' style='text-align:left;'>";
		$message_body = $mail_style.$message_body."<br><br><br></div></div>";
	}


	$sent_no = 0;

	for ($i = 0; $i < count($recipient); $i++) {

		// --- start loop ----

		$text .= "<tr>";
		$text .= "<td class='forumheader3' style='width:40%'>".$recipient_name[$i]."</td>";
		$text .= "<td class='forumheader3' style='width:40%'>".$recipient[$i]."</td>";

		$mes_body = str_replace("{USERNAME}", $recipient_name[$i], $message_body);
		$mes_body = str_replace("{USERID}", $recipient_id[$i], $mes_body);

		$activator = (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$recipient_id[$i].".".$recipient_key[$i] : SITEURL."/signup.php?activate.".$recipient_id[$i].".".$recipient_key[$i]);
		if($recipient_key[$i]){
			$mes_body = str_replace("{SIGNUP_LINK}", "<a href='$activator'>$activator</a>", $mes_body);
		}else{
			$mes_body = str_replace("{SIGNUP_LINK}", "", $mes_body);
		}

		$mes_body = str_replace("\n", "<br>", $mes_body);

		$mail->Body = $tp->toHTML($mes_body);
		$mail->AltBody = strip_tags(str_replace("<br>", "\n", $mes_body));
		$mail->AddAddress($recipient[$i], $recipient_name[$i]);



        if ($mail->Send()) {
            $stat = "<span style='color:green'>Sent</span>";
            $sent_no ++;
        } else {
            $stat = "<span style='color:red'>Error</span>";
        }

		$text .= "<td class='forumheader3'>&nbsp;&nbsp; $stat </td></tr>";



		$mail->ClearAddresses();
		if ($pref['smtp_enable']) {
			$mail->SmtpClose();
		}


		// ---- end loop. ---

	};

	$mail->ClearAttachments();


	$text .= "</table></div>";
	$rec_text = $c > 1 ? "recipients":
	"recipient";

	if ($c == 0 ) {
		$text = "<div style='text-align:center'>No Recipients Found</div>";
	}

	$ns->tablerender("Emailing $c $rec_text", $text);
	require_once(e_ADMIN."footer.php");
	exit;
}
//. Update Preferences.

if (isset($_POST['updateprefs'])) {
	$pref['mailer'] = $_POST['mailer'];
	$pref['sendmail'] = $_POST['sendmail'];
	$pref['smtp_enable'] = $_POST['smtp_enable'];
	$pref['smtp_server'] = $tp->toDB($_POST['smtp_server']);
	$pref['smtp_username'] = $tp->toDB($_POST['smtp_username']);
	$pref['smtp_password'] = $tp->toDB($_POST['smtp_password']);

	save_prefs();
	$message = LAN_SETSAVED;
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// Display Mailout Form.



$text = "";
$text .= ($pref['smtp_enable'] == 0)? "<div style='text-align:center'>".MAILAN_14."<br /><br /></div>":
"";
$text .= "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='linkform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_01.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_from_name' class='tbox' style='width:80%' value='$email_from_name' />
	</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_02.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_from_email' class='tbox' style='width:80%' value='$email_from_email' />
	</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_03.": </td>
	<td style='width:70%' class='forumheader3'>
	".userclasses("email_to", $email_to)."</td>
	</tr>";

// Language Option for those using it in their extended user area.
if(isset($language_field)){
$text .= "<tr>
	<td style='width:30%' class='forumheader3'>".ADLAN_132.": </td>
	<td style='width:70%' class='forumheader3'>";

    require_once(e_HANDLER."file_class.php");
	$fl = new e_file;
	$lanlist = $fl->get_dirs(e_LANGUAGEDIR);
	sort($lanlist);
	$text .= "<select class='tbox' name='language'>\n";
	$text .= "<option value=''></option>\n";  // ensures that the user chose it.
		foreach($lanlist as $choice){
			$choice = trim($choice);
			$sel = (e_LANGUAGE == $choice)? " selected='selected' " : "";
			$text .= "<option value='{$choice}' {$sel}>{$choice}</option>\n";
		}
	$text .= "</select>\n";
	$text .= "</td>
	</tr>";

}

$text .= "

	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_04.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_cc' class='tbox' style='width:80%' value='$email_cc' />

	</td>
	</tr>


	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_05.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_bcc' class='tbox' style='width:80%' value='$email_bcc' />

	</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_06.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_subject' class='tbox' style='width:80%' value='$email_subject' />

	</td>
	</tr>";


// Attachment.

$text .= "<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_07.": </td>
	<td style='width:70%' class='forumheader3'>";
$text .= "<select class='tbox' name='email_attachment' >
	<option></option>";
$sql->db_Select("download", "download_url,download_name", "download_id !='' ORDER BY download_name");
while ($row = $sql->db_Fetch()) {
	extract($row);
	$selected = ($_POST['email_attachment'] == $download_url) ? "selected='selected'" :
	 "";
	$text .= "<option value=\"$download_url \" $selected>$download_name</option>";
}
$text .= " </select>";

$text .= "</td>
	</tr>";


$text .= "
	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_09.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='checkbox' name='use_theme' value='1' />
	</td>
	</tr>



	<tr>
	<td colspan='2' style='width:30%' class='forumheader3'>

	<textarea rows='10' cols='20' id='email_body' name='email_body'  class='tbox' style='width:100%;height:200px' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>
	$email_body
	</textarea>
	</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_11.": </td>
	<td style='width:70%' class='forumheader3'>";

if($pref['wysiwyg']) {
	$text .="<input type='button' class='button' name='usrname' value='".MAILAN_16."' onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'{USERNAME}')\" />
	<input type='button' class='button' name='usrlink' value='".MAILAN_17."' onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'{SIGNUP_LINK}')\" />
	<input type='button' class='button' name='usrid' value='".MAILAN_18."' onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'{USERID}')\" />";
} else {
    $text .="<input type='button' class='button' name='usrname' value='".MAILAN_16."' onclick=\"addtext('{USERNAME}')\" />
	<input type='button' class='button' name='usrlink' value='".MAILAN_17."' onclick=\"addtext('{SIGNUP_LINK}')\" />
	<input type='button' class='button' name='usrid' value='".MAILAN_18."' onclick=\"addtext('{USERID}')\" />";
}


	$text .="</td>
	</tr>";



$text .= "<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'>";
$text .= "<input class='button' type='submit' name='submit' value='".MAILAN_08."' />";
$text .= "</td>
	</tr>
	</table>
	</form>
	</div>";

$ns->tablerender(MAILAN_15, $text);




$text = "
	<form method='post' action='".e_SELF."' id='mailsettingsform'>
	<div id='mail' style='text-align:center;'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:40%' class='forumheader3'><span title='".PRFLAN_64."' style='cursor:help'>".PRFLAN_63."<span><br /></td>
	<td style='width:60%; text-align:right' class='forumheader3'><input class='button' type='submit' name='testemail' value='".PRFLAN_65." ".SITEADMINEMAIL."' />
	</td>
	</tr>

	<tr>
	<td style='vertical-align:top' class='forumheader3'>".PRFLAN_70."<br /><span class='smalltext'>".PRFLAN_71."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<select class='tbox' name='mailer' onchange='disp(this.value)'>\n";
	$mailers = array("php","smtp","sendmail");
    foreach($mailers as $opt){
		$sel = ($pref['mailer'] == $opt) ? "selected='selected'" : "";
    	$text .= "<option value='$opt' $sel>$opt</option>\n";
	}
	$text .="</select><br />";
  // SMTP. -------------->
	$smtpdisp = ($pref['mailer'] != "smtp") ? "display:none;" : "";
	$text .= "<div id='smtp' style='$smtpdisp text-align:right'><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "	<tr>
	<td style='text-align:right' >".PRFLAN_72.":&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_server' size='40' value='".$pref['smtp_server']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".PRFLAN_73.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_username' size='40' value='".$pref['smtp_username']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".PRFLAN_74.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='password' name='smtp_password' size='40' value='".$pref['smtp_password']."' maxlength='50' />
	</td>
	</tr>

	</table></div>";

  // Sendmail. -------------->
    $senddisp = ($pref['mailer'] != "sendmail") ? "display:none;" : "";
	$text .= "<div id='sendmail' style='$senddisp text-align:right'><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "

	<tr>
	<td >".MAILAN_20.":&nbsp;&nbsp;</td>
	<td text-align:right' >
	<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
	</td>
	</tr>

	</table></div>";
	$text .="</td>
	</tr>




	<tr>
	<td style='text-align:center' colspan='2' class='forumheader'>
	<input class='button' type='submit' name='updateprefs' value='".PRFLAN_52."' />
	</td>
	</tr>





	</table></div></form>";

$text .= "";
$caption = LAN_PREFS;
$ns->tablerender($caption, $text);



require_once(e_ADMIN."footer.php");






function userclasses($name) {
	global $sql;
	$text .= "<select style='width:80%' class='tbox' name='$name' >
		<option value='all'>".MAILAN_12."</option>
		<option value='unverified'>".MAILAN_13."</option>
		<option value='admin'>Admins</option>
		<option value='self'>Self</option>";
	$sql->db_Select("userclass_classes");
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$public = ($userclass_editclass == 0)? "(".MAILAN_10.")" :
		 "";
		$text .= "<option value=\"$userclass_id\" $selected>Userclass - $userclass_name  $public</option>";
	}
	$text .= " </select>";

	return $text;
}


function show_options($action) {
	// ##### Display options ---------------------------------------------------------------------------------------------------------
	/*   if($action==""){$action="main";}
	// ##### Display options ---------------------------------------------------------------------------------------------------------
	$var['main']['text']= "Mails Prefs";//USRLAN_71;
	$var['main']['link']= "mailout.php";

	$var['create']['text']="Mail-Out";USRLAN_72;
	$var['create']['link']="mailout.php?mailout";

	$var['prune']['text']=USRLAN_73;
	$var['prune']['link']="users.php?prune";

	$var['extended']['text']=USRLAN_74;
	$var['extended']['link']="users.php?extended";

	$var['options']['text']=USRLAN_75;
	$var['options']['link']="users.php?options";

	$var['mailing']['text']= USRLAN_121;
	$var['mailing']['link']="mailout.php";*/
	//   show_admin_menu(USRLAN_76,$action,$var);
}

/*function mailout_adminmenu(){
global $user;
global $action;
$action = "mailing";
show_options($action);
}*/
function headerjs()
{
	$text = "
	<script type='text/javascript'>
	function disp(type) {


		if(type == 'smtp'){
			document.getElementById('smtp').style.display = '';
			document.getElementById('sendmail').style.display = 'none';
			return;
		}

		if(type =='sendmail'){
            document.getElementById('smtp').style.display = 'none';
			document.getElementById('sendmail').style.display = '';
			return;
		}

		document.getElementById('smtp').style.display = 'none';
		document.getElementById('sendmail').style.display = 'none';

	}
	</script>";
	return $text;
}
?>