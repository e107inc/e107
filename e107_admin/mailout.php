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

require_once("../class2.php");
$e_sub_cat = 'mail';
if (!isset($_POST['submit'])) {
	$e_wysiwyg = "email_body";
}
set_time_limit(180);
session_write_close();
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."ren_help.php");
if (!getperms("W")) {
	header("location:".e_BASE."index.php");
	 exit;
}
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");
require_once(e_HANDLER."userclass_class.php");

if (isset($_POST['testemail']) && getperms("0")) {
    if(trim($_POST['testaddress']) == ""){
		$message = MAILAN_19;
	}else{
		$mailheader_e107id = USERID;
		require_once(e_HANDLER."mail.php");
		$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : " (PHP)";
		$sendto = trim($_POST['testaddress']);
		if (!sendemail($sendto, PRFLAN_66." ".SITENAME.$add, PRFLAN_67,MAILAN_68)) 
		{
		  $message = ($pref['mailer'] == "smtp")  ? PRFLAN_75 : PRFLAN_68;
		} 
		else 
		{
		  $message = PRFLAN_69. "(".$sendto.")";
		}
	}
}

$sql->db_Delete("generic", "gen_type='sendmail' AND gen_datestamp < ".(time()-86400));

if (isset($_POST['save_email'])){
	$qry = "0,'massmail', '".time()."', '".USERID."', '".$tp->toDB($_POST['email_subject'])."',  '0', \"".$tp->toDB($_POST['email_body'])."\"  ";
	$message = $sql -> db_Insert("generic", $qry) ? LAN_SAVED : LAN_ERROR;
}

if (isset($_POST['update_email'])){
	$qry = "gen_user_id = '".USERID."', gen_datestamp = '".time()."', gen_ip = '".$tp->toDB($_POST['email_subject'])."', gen_chardata= \"".$tp->toDB($_POST['email_body'])."\" WHERE gen_id = '".$_POST['update_id']."' ";
	$message = $sql -> db_Update("generic", $qry) ? LAN_UPDATED : LAN_UPDATED_FAILED;
}

if (isset($_POST['delete'])){
	$d_idt = array_keys($_POST['delete']);
	$message = ($sql -> db_Delete("generic", "gen_id='".$d_idt[0]."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
}

if (isset($_POST['edit'])){
	$e_idt = array_keys($_POST['edit']);
	if($sql -> db_Select("generic", "*", "gen_id='".$e_idt[0]."' ")){
		$foo = $sql -> db_Fetch();

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

		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE $insert ";

	} elseif($_POST['email_to'] == "unverified"){
        $qry = "SELECT u.* FROM #user AS u WHERE u.user_ban='2'";

	} elseif($_POST['email_to'] == "self"){
       $qry = "SELECT u.* FROM #user AS u WHERE u.user_id='".USERID."'";

	} else {

    $insert = "u.user_class REGEXP concat('(^|,)',{$_POST['email_to']},'(,|$)') AND u.user_ban='0' ";
		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE $insert ";
	}

  $sql2 = new db;
	$c = 0;

	if($_POST['extended_1_name'] && $_POST['extended_1_value']){
		$qry .= " AND ".$_POST['extended_1_name']." = '".$_POST['extended_1_value']."' ";
	}

	if($_POST['extended_2_name'] && $_POST['extended_2_value']){
		$qry .= " AND ".$_POST['extended_2_name']." = '".$_POST['extended_2_value']."' ";
	}

	if($_POST['user_search_name'] && $_POST['user_search_value']){
		$qry .= " AND u.".$_POST['user_search_name']." LIKE '%".$_POST['user_search_value']."%' ";
	}

	$qry .= " ORDER BY u.user_name";

	$_POST['mail_id']  = time();

	$sql->db_Select_gen($qry);

	while ($row = $sql->db_Fetch()) {
		$qry = "0,'sendmail', '".$_POST['mail_id']."', '".$row['user_id']."', '', '0', \"".$tp->toDB($_POST['email_subject'])."\" ";
		if($sql2 -> db_Insert("generic", $qry)){
			$c++;
		}
	}

	$debug = (e_MENU == "debug") ? "?[debug]" : "";

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_HANDLER."phpmailer/mailout_process.php".$debug."' name='mailform' onsubmit=\"open('', 'popup','width=230,height=170,resizable=1,scrollbars=0');this.target = 'popup';return true;\" >
		<div>";

    foreach($_POST as $key=>$val)
	{
		$text .= "<input type='hidden' name='".$key."' value='".stripslashes($tp->post_toForm($val))."' />\n";
    }

	$text .= "</div>";

	$text .= "<div>$c ".MAILAN_24."</div>";

	$text .= "<div><br /><input class='button' type='submit' name='send_mails' value='".MAILAN_37."' />
	<input class='button' type='submit' name='cancel_emails' value='".MAILAN_38."' />
	</div>";
	$text .= "</form><br /><br /></div>";

// --------------- Preview Email -------------------------->

	if(is_numeric($_POST['email_to']))
	{
		$sql->db_Select("userclass_classes", "userclass_name", "userclass_id = '{$_POST['email_to']}'");
		$row = $sql->db_Fetch();
		$_to = MAILAN_23.$row['userclass_name'];
	}
	else
	{
		$_to = $_POST['email_to'];
	}
	$text .= "
	<div>
	<table class='fborder'>

		<tr>
			<td class='forumheader3' style='width:30%'>".MAILAN_03."</td>
			<td class='forumheader3'>".$_to."&nbsp;";
			if($_POST['email_to'] == "self"){
				$text .= "&lt;".USEREMAIL."&gt;";
			}
	$text .="</td>
		</tr>

		<tr>
			<td class='forumheader3' style='width:30%'>".MAILAN_01." / ".MAILAN_02."</td>
			<td class='forumheader3'>".$_POST['email_from_name']." &lt;".$_POST['email_from_email']."&gt;</td>
		</tr>

		<tr>
			<td class='forumheader3' style='width:20%'>".MAILAN_06."</td>
			<td class='forumheader3'>".$_POST['email_subject']."&nbsp;</td>
		</tr>";

		$text .= ($_POST['email_cc']) ? "
		<tr>
			<td class='forumheader3' style='width:30%'>".MAILAN_04."</td>
			<td class='forumheader3'>".$_POST['email_cc']."&nbsp;</td>
		</tr>": "";

		$text .= ($_POST['email_bcc']) ? "
		<tr>
			<td class='forumheader3' style='width:30%'>".MAILAN_05."</td>
			<td class='forumheader3'>".$_POST['email_bcc']."&nbsp;</td>
		</tr>": "";


		$text .= ($_POST['user_search_name'] && $_POST['user_search_value']) ? "
		<tr>
			<td class='forumheader3' style='width:30%'>".$_POST['user_search_name']."</td>
			<td class='forumheader3'>".$_POST['user_search_value']."&nbsp;</td>
		</tr>": "";



		$text .= ($_POST['extended_1_name'] && $_POST['extended_1_value']) ? "
		<tr>
			<td class='forumheader3' style='width:30%'>".$_POST['extended_1_name']."</td>
			<td class='forumheader3'>".$_POST['extended_1_value']."&nbsp;</td>
		</tr>": "";



		$text .= ($_POST['extended_2_name'] && $_POST['extended_2_value']) ? "
		<tr>
			<td class='forumheader3' style='width:30%'>".$_POST['extended_2_name']."</td>
			<td class='forumheader3'>".$_POST['extended_2_value']."&nbsp;</td>
		</tr>": "";



		$text .="
 		<tr>
			<td class='forumheader3' colspan='2'>".stripslashes($tp->toHTML($_POST['email_body'],TRUE))."</td>
		</tr>

	</table>
	</div>";


 	$ns->tablerender(MAILAN_39." ($c) ", $text);
	require_once(e_ADMIN."footer.php");
	exit;
}




//. Update Preferences.

if (isset($_POST['updateprefs']) && getperms("0")) {
	$pref['mailer'] = $_POST['mailer'];
	if (strpos($_POST['sendmail'],'sendmail') !== FALSE) $pref['sendmail'] = $_POST['sendmail'];
	$pref['smtp_server'] = $tp->toDB($_POST['smtp_server']);
	$pref['smtp_username'] = $tp->toDB($_POST['smtp_username']);
	$pref['smtp_password'] = $tp->toDB($_POST['smtp_password']);
	$pref['smtp_keepalive'] = $_POST['smtp_keepalive'];
	$pref['smtp_pop3auth'] = $_POST['smtp_pop3auth'];

	$pref['mail_pause'] = $_POST['mail_pause'];
	$pref['mail_pausetime'] = $_POST['mail_pausetime'];
	$pref['mail_bounce_email'] = $_POST['mail_bounce_email'];
	$pref['mail_bounce_pop3'] = $_POST['mail_bounce_pop3'];
	$pref['mail_bounce_user'] =	$_POST['mail_bounce_user'];
	$pref['mail_bounce_pass'] = $_POST['mail_bounce_pass'];
	$pref['mail_bounce_delete'] = $_POST['mail_bounce_delete'];

	save_prefs();
	$message = LAN_SETSAVED;
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



// ----------------- Actions ----------------------------------------------->

if((e_QUERY == "prefs") && getperms("0")){
	show_prefs();
}

if((!e_QUERY && !$_POST['delete']) || $_POST['edit']){
	show_mailform($foo);
}


if(e_QUERY == "list" || $_POST['delete'] ){
   showList();
}


require_once(e_ADMIN."footer.php");



// ------------------- Display Mailout Form.--------------------------------->

function show_mailform($foo=""){
	global $ns,$sql,$tp,$pref,$HANDLERS_DIRECTORY;

	$email_subject = $foo['gen_ip'];
	$email_body = $tp->toForm($foo['gen_chardata']);
	$email_id = $foo['gen_id'];
	$text = "";

	if(strpos($_SERVER['SERVER_SOFTWARE'],"mod_gzip") && !is_readable(e_HANDLER."phpmailer/.htaccess")){
		$warning = MAILAN_40." ".$HANDLERS_DIRECTORY."phpmailer/ ".MAILAN_41;
		$ns -> tablerender(MAILAN_42, $warning);
	}

	$debug = (e_MENU == "debug") ? "?[debug]" : "";
	$text .= "<div style='".ADMIN_WIDTH."; text-align:center'>
	<form method='post' action='".e_SELF.$debug."' id='mailout_form'>
	<table class='fborder' style='".ADMIN_WIDTH."'  cellpadding='0' cellspacing='0'>
	<tr>
	<td style='width:30%' class='forumheader3'>".MAILAN_01.": </td>
	<td style='width:70%' class='forumheader3'>
	<input type='text' name='email_from_name' class='tbox' style='width:80%' size='10' value=\"".$_POST['email_from_name']."\" />
	</td>
	</tr>";


	$text .="

	<tr>
	<td class='forumheader3'>".MAILAN_02.": </td>
	<td  class='forumheader3'>
	<input type='text' name='email_from_email' class='tbox' style='width:80%' value=\"".$_POST['email_from_email']."\" />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".MAILAN_03.": </td>
	<td class='forumheader3'>
	".userclasses("email_to", $_POST['email_to'])."</td>
	</tr>";


	// User Search Field.


		$u_array = array("user_name"=>MAILAN_43,"user_login"=>MAILAN_44,"user_email"=>MAILAN_45);

		$text .= "
		<tr>
			<td style='width:35%' class='forumheader3'>".MAILAN_46."
			<select name='user_search_name' class='tbox'>
			<option value=''>&nbsp;</option>";

			foreach($u_array as $key=>$val){
				$text .= "<option value='$key' >".$val."</option>\n";
			}

	$text .= "
		</select> ".MAILAN_47." </td>
		<td style='width:65%' class='forumheader3'>
		<input type='text' name='user_search_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		";

	// Extended Field #1.

		$text .= "
		<tr>
			<td class='forumheader3'>".MAILAN_46."
			<select name='extended_1_name' class='tbox'>
			<option value=''>&nbsp;</option>";
			$sql -> db_Select("user_extended_struct");
			while($row = $sql-> db_Fetch()){
				$text .= "<option value='ue.user_".$row['user_extended_struct_name']."' >".ucfirst($row['user_extended_struct_name'])."</option>\n";
			}

		$text .= "
		</select> ".MAILAN_48." </td>
		<td  class='forumheader3'>
		<input type='text' name='extended_1_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		";



	// Extended Field #2.

		$text .= "
		<tr>
			<td class='forumheader3'>".MAILAN_46."
			<select name='extended_2_name' class='tbox'>
			<option value=''>&nbsp;</option>";
			$sql -> db_Select("user_extended_struct");
			while($row = $sql-> db_Fetch()){
				$text .= "<option value='ue.user_".$row['user_extended_struct_name']."' >".ucfirst($row['user_extended_struct_name'])."</option>\n";
			}

		$text .= "
		</select> ".MAILAN_48." </td>
		<td  class='forumheader3'>
		<input type='text' name='extended_2_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		";




	$text .= "

	<tr>
	<td class='forumheader3'>".MAILAN_04.": </td>
	<td  class='forumheader3'>
	<input type='text' name='email_cc' class='tbox' style='width:80%' value=\"".$_POST['email_cc']."\" />

	</td>
	</tr>


	<tr>
	<td class='forumheader3'>".MAILAN_05.": </td>
	<td  class='forumheader3'>
	<input type='text' name='email_bcc' class='tbox' style='width:80%' value='$email_bcc' />

	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".MAILAN_06.": </td>
	<td class='forumheader3'>
	<input type='text' name='email_subject' class='tbox' style='width:80%' value='$email_subject' />

	</td>
	</tr>";




	// Attachment.

	$text .= "<tr>
	<td class='forumheader3'>".MAILAN_07.": </td>
	<td  class='forumheader3'>";
	$text .= "<select class='tbox' name='email_attachment' >
	<option value=''>&nbsp;</option>\n";
	$sql->db_Select("download", "download_url,download_name", "download_id !='' ORDER BY download_name");
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$selected = ($_POST['email_attachment'] == $download_url) ? "selected='selected'" :
		 "";
		$text .= "<option value=\"$download_url \" $selected>".htmlspecialchars($download_name)."</option>\n";
	}
	$text .= " </select>";

	$text .= "</td>
	</tr>";





	$text .= "
	<tr>
	<td class='forumheader3'>".MAILAN_09.": </td>
	<td  class='forumheader3'>
	<input type='checkbox' name='use_theme' value='1' />
	</td>
	</tr>

	<tr>
	<td colspan='2'  class='forumheader3'>
	<textarea rows='10' cols='20' id='email_body' name='email_body'  class='tbox' style='width:100%;height:200px' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$email_body."</textarea>
	</td>
	</tr>";

	$text .="
	<tr>
	<td style='width:100%' class='forumheader3' colspan='2'>
	<div style='width:100%;text-align:center;vertical-align: middle;' >";

    global $eplug_bb;

    $eplug_bb[] = array(
			"name"		=> 'shortcode',
			"onclick"	=> 'expandit',
			"onclick_var" => "sc_selector",
			"icon"		=> e_IMAGE."generic/bbcode/shortcode.png",
			"helptext"	=> MAILAN_11,
			"function"	=> "sc_Select",
			"function_var"	=> "sc_selector"
	);

	$text .= display_help("helpb",'mailout');

	if(e_WYSIWYG) {
		$text .="<span style='vertical-align: super;margin-left:5%;margin-bottom:auto;margin-top:auto'><input type='button' class='button' name='usrname' value=\"".MAILAN_16."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|USERNAME|')\" />
		<input type='button' class='button' name='usrlink' value=\"".MAILAN_17."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|SIGNUP_LINK|')\" />
		<input type='button' class='button' name='usrid' value=\"".MAILAN_18."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|USERID|')\" /></span>";
	}

 	$text .="
	</div></td>
	</tr>";


	$text .= "<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'>";
	if(isset($_POST['edit'])){
		$text .= "<input type='hidden' name='update_id' value='".$email_id."' />";
		$text .= "<input class='button' type='submit' name='update_email' value=\"".LAN_UPDATE."\" />";
	}else{
		$text .= "<input class='button' type='submit' name='save_email' value=\"".LAN_SAVE."\" />";
	}

	$text .="&nbsp;<input class='button' type='submit' name='submit' value=\"".MAILAN_08."\" />

	</td>
	</tr>
	</table>
	</form>
	</div>";

	$ns->tablerender(MAILAN_15, $text);

}

// ------------------ Preferences -------------------------------------------->

function show_prefs(){
	global $pref,$ns;
$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='mailsettingsform'>
	<div id='mail' style='text-align:center;'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:30%' class='forumheader3'>".PRFLAN_63."<br /></td>
	<td style='width:70%; text-align:right' class='forumheader3'><input class='button' type='submit' name='testemail' value=\"".PRFLAN_65."\" />
	<input name='testaddress' class='tbox' size='40' type='text' value=\"".SITEADMINEMAIL."\" />
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
	$text .= "<div id='smtp' style='{$smtpdisp} text-align:right'><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "	<tr>
	<td style='text-align:right' >".PRFLAN_72.":&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_server' size='40' value='".$pref['smtp_server']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".PRFLAN_73.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_username' size='40' value=\"".$pref['smtp_username']."\" maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".PRFLAN_74.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='password' name='smtp_password' size='40' value='".$pref['smtp_password']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td colspan='2' style='text-align:right' >".MAILAN_57.":&nbsp;
	";
	$checked = (isset($pref['smtp_keepalive']) && $pref['smtp_keepalive']==1) ? "checked='checked'" : "";
	$text .= "<input type='checkbox' name='smtp_keepalive' value='1' {$checked} />
	</td>
	</tr>

	<tr>
	<td colspan='2' style='text-align:right' >".MAILAN_67.":&nbsp;
	";
	$checked = (isset($pref['smtp_pop3auth']) && $pref['smtp_pop3auth']==1) ? "checked='checked'" : "";
	$text .= "<input type='checkbox' name='smtp_pop3auth' value='1' {$checked} />
	</td>
	</tr>



	</table></div>";

// Sendmail. -------------->


	$senddisp = ($pref['mailer'] != "sendmail") ? "display:none;" : "";
	$text .= "<div id='sendmail' style='{$senddisp} text-align:right'><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "

	<tr>
	<td >".MAILAN_20.":&nbsp;&nbsp;</td>
	<td style='text-align:right' >
	<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
	</td>
	</tr>

	</table></div>";
	$text .="</td>
	</tr>

	<tr>
		<td class='forumheader3'>".MAILAN_25."</td>
		<td class='forumheader3' style='text-align: right;'> ".MAILAN_26."
		<input class='tbox' size='3' type='text' name='mail_pause' value='".$pref['mail_pause']."' /> ".MAILAN_27.".
		</td>
	</tr>\n

	<tr>
		<td class='forumheader3'>".MAILAN_28."</td>
		<td class='forumheader3' style='text-align: right;'>
		<input class='tbox' size='3' type='text' name='mail_pausetime' value='".$pref['mail_pausetime']."' /> ".MAILAN_29.".<br />
		<span class='smalltext'>".MAILAN_30."</span>
		</td>
	</tr>\n

	<tr>
	<td style='vertical-align:top' class='forumheader3'>".MAILAN_31."</td>
	<td style=' text-align:right' class='forumheader3'>
		".MAILAN_32.": <input class='tbox' size='40' type='text' name='mail_bounce_email' value=\"".$pref['mail_bounce_email']."\" /><br />
		".MAILAN_33." (POP3):  <input class='tbox' size='40' type='text' name='mail_bounce_pop3' value=\"".$pref['mail_bounce_pop3']."\" /><br />
		".MAILAN_34.":  <input class='tbox' size='40' type='text' name='mail_bounce_user' value=\"".$pref['mail_bounce_user']."\" /><br />
		".MAILAN_35.":  <input class='tbox' size='40' type='text' name='mail_bounce_pass' value=\"".$pref['mail_bounce_pass']."\" /><br />";

	$check = ($pref['mail_bounce_delete']==1) ? "checked='checked'" : "";
	$text .= MAILAN_36.":  <input type='checkbox' name='mail_bounce_delete' value='1' {$check} />

	</td>
	</tr>


	<tr>
	<td style='text-align:center' colspan='2' class='forumheader'>
	<input class='button' type='submit' name='updateprefs' value=\"".PRFLAN_52."\" />
	</td>
	</tr>





	</table></div></form>";

	$text .= "";
	$caption = LAN_PREFS;
	$ns->tablerender($caption, $text);
}




function showList()
	{
		global $sql,$ns,$tp;
		$gen = new convert;
		$qry ="SELECT g.*,u.* FROM #generic AS g LEFT JOIN #user AS u ON g.gen_user_id = u.user_id WHERE g.gen_type = 'massmail' ORDER BY g.gen_datestamp DESC";
	 //	$count = $sql -> db_Select("generic", "*", "gen_type ='massmail' ORDER BY gen_datestamp DESC");
		$count = $sql -> db_Select_gen($qry);

		$text = "<div style='text-align:center'>";

		if (!$count)
		{
			$text = "
			<div class='forumheader2' style='text-align:center'>".MAILAN_22."</div>";
			$ns -> tablerender("<div style='text-align:center'>".MAILAN_21."</div>", $text);
			require_once(e_ADMIN."footer.php");
			exit;
		}
		else
		{

			$text .= "

			<form action='".e_SELF."' id='display' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>

			<tr>
			<td style='width:5%; text-align: center;' class='fcaption'>".MAILAN_49."</td>
			<td style='width:10%' class='fcaption'>".MAILAN_50."</td>
			<td style='width:40%' class='fcaption'>".MAILAN_51."</td>
			<td style='width:20%; text-align: center;' class='fcaption'>".MAILAN_52."</td>
			<td style='width:5%; text-align: center;' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>
			";

			$glArray = $sql -> db_getList();
			foreach($glArray as $row2)
			{

				$datestamp = $gen->convert_date($row2['gen_datestamp'], "short");

				$text .= "<tr>
				<td class='forumheader3' style='text-align: center;'>".$row2['gen_id'] ."</td>
				<td class='forumheader3'>".$row2['user_name']."</td>
				<td class='forumheader3'>".$row2['gen_ip']."</td>

				<td class='forumheader3' style='text-align: center;'>".$datestamp."</td>

				<td style='width:50px;white-space:nowrap' class='forumheader3'>
				<div>
				<input type='image' name='edit[{$row2['gen_id']}]' value='edit' src='".e_IMAGE."admin_images/edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' />
				<input type='image' name='delete[{$row2['gen_id']}]' value='del' onclick='return jsconfirm(\"".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gen_ip']."]")."\")'  src='".e_IMAGE."admin_images/delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' />
				</div>
				</td>
				</tr>
				";
			}
		}

		$text .= "</table>\n</form><br /><br /><br /></div>";
		$ns -> tablerender("<div style='text-align:center'>".MAILAN_21."</div>", $text);
	}




function userclasses($name) {
	global $sql;
	$text .= "<select style='width:80%' class='tbox' name='$name' >
		<option value='all'>".MAILAN_12."</option>
		<option value='unverified'>".MAILAN_13."</option>
		<option value='admin'>".MAILAN_53."</option>
		<option value='self'>".MAILAN_54."</option>";
	$query = "SELECT uc.*, count(u.user_id) AS members
			FROM #userclass_classes AS uc
			LEFT JOIN #user AS u ON u.user_class REGEXP concat('(^|,)',uc.userclass_id,'(,|$)')
			GROUP BY uc.userclass_id
					";

	$sql->db_Select_gen($query);
	while ($row = $sql->db_Fetch()) {
		$public = ($row['userclass_editclass'] == 0)? "(".MAILAN_10.")" : "";
		$text .= "<option value='{$row['userclass_id']}' >".MAILAN_55." - {$row['userclass_name']}  $public [{$row['members']}]</option>";
	}
	$text .= " </select>";

	return $text;
}


function mailout_adminmenu() {
	$action = (e_QUERY) ? e_QUERY : "post";
	if($action == "edit"){
    	$action = "post";
	}
    $var['post']['text'] = MAILAN_56;
	$var['post']['link'] = e_SELF;
	$var['post']['perm'] = "W";
    $var['list']['text'] = LAN_SAVED;
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "W";
	if(getperms("0")){
		$var['prefs']['text'] = LAN_OPTIONS;
		$var['prefs']['link'] = e_SELF."?prefs";
   		$var['prefs']['perm'] = "0";
    }
	show_admin_menu(MAILAN_15, $action, $var);
}


function sc_Select($container='sc_selector') {
	$text ="
<!-- Start of Shortcode selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$container}'>
	<div style='position:absolute; bottom:30px; right:125px'>
	<table class='fborder' style='background-color: #fff'>
	<tr><td class='forumheader3'>
	<select class='tbox' name='sc_sel' onchange=\"addtext(this.value); this.selectedIndex= 0; expandit('{$container}')\">
	<option value=''> -- </option>\n";

	$sc = array(
		"|USERNAME|" => MAILAN_16,
        "|SIGNUP_LINK|" => MAILAN_17,
        "|USERID|" => MAILAN_18
	);

	foreach($sc as $key=>$val){
		$text .= "<option value='".$key."'>".$val."</option>\n";
	}
	$text .="
	</select></td></tr>	\n </table></div>
	</div>
\n<!-- End of SC selector -->

";

	return $text;
}















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
