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
|     $Source: /cvs_backup/e107_0.7/signup.php,v $
|     $Revision: 1.34 $
|     $Date: 2005-05-28 02:47:53 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_usersettings.php");
@include_once(e_LANGUAGEDIR."English/lan_usersettings.php");
include_once(e_HANDLER."user_extended_class.php");
$usere = new e107_user_extended;
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);

$use_imagecode = ($pref['signcode'] && extension_loaded("gd"));

if(!$_POST){   // Notice Removal.
	$error = ""; 		$msn = "";
	$website = ""; 		$text = " ";
	$password1 = "";	$password2 = "";
	$email = "";		$name = "";
	$loginname = "";	$realname = "";
	$icq = "";			$aim = "";
	$birth_day = "";	$birth_month = "";
	$birth_year = "";	$user_timezone = "";
	$location = "";		$image = "";
	$avatar_upload = ""; $photo_upload = "";
	$_POST['ue'] = "";  $signature = "";
}




if ($pref['membersonly_enabled']) {
	$HEADER = "<div style='text-align:center; width:100%;margin-left:auto;margin-right:auto;text-align:center'><div style='width:70%;text-align:center;margin-left:auto;margin-right:auto'><br />";
	if (file_exists(THEME."images/login_logo.png")) {
		$HEADER .= "<img src='".THEME."images/login_logo.png' alt='' />\n";
	} else {
		$HEADER .= "<img src='".e_IMAGE."logo.png' alt='' />\n";
	}
	$HEADER .= "<br />";
	$FOOTER = "</div></div>";
}

if ($use_imagecode) {
	require_once(e_HANDLER."secure_img_handler.php");
	$sec_img = new secure_image;
}

if ($pref['user_reg'] == 0) {
	header("location:".e_BASE."index.php");
	exit;
}

if (USER) {
	header("location:".e_BASE."index.php");
	exit;
}





if (e_QUERY) {
	$qs = explode(".", e_QUERY);
	if ($qs[0] == "activate") {
		if ($sql->db_Select("user", "*", "user_sess='".$qs[2]."' ")) {
			if ($row = $sql->db_Fetch()) {
				$sql->db_Update("user", "user_ban='0', user_sess='' WHERE user_sess='".$qs[2]."' ");
				$e_event->trigger("userveri", $qs[2]);
				require_once(HEADERF);
				$text = LAN_401." <a href='index.php'>".LAN_SIGNUP_22."</a> ".LAN_SIGNUP_23."<br />".LAN_SIGNUP_24." ".SITENAME;
				$ns->tablerender(LAN_402, $text);
				require_once(FOOTERF);
				exit;
			}
		} else {
			header("location: ".e_BASE."index.php");
			exit;
		}
	}
}

$signupval = explode(".", $pref['signup_options']);
$signup_title = array(LAN_308, LAN_144, LAN_115, LAN_116, LAN_117, LAN_118, LAN_119, LAN_120, LAN_121, LAN_122, LAN_SIGNUP_28);
$signup_name = array("realname", "website", "icq", "aim", "msn", "birth_year", "location", "signature", "image", "timezone", "usrclass");


if (isset($_POST['register'])) {
	extract($_POST);
	require_once(e_HANDLER."message_handler.php");

	if ($use_imagecode) {
		if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify'])) {
			message_handler("P_ALERT", LAN_SIGNUP_3);
			$error = TRUE;
		}
	}

	if (strstr($_POST['name'], "#") || strstr($_POST['name'], "=") || strstr($_POST['name'], "\\")) {
		message_handler("P_ALERT", LAN_409);
		$error = TRUE;
	}

	$_POST['name'] = trim(chop(ereg_replace("&nbsp;|\#|\=", "", $_POST['name'])));
	if ($_POST['name'] == "Anonymous") {
		message_handler("P_ALERT", LAN_103);
		$error = TRUE;
		$name = "";
	}

	if(isset($pref['signup_disallow_text']))
	{
		$tmp = explode(",", $pref['signup_disallow_text']);
		foreach($tmp as $disallow)
		{
			if(strstr($_POST['name'], $disallow))
			{
				message_handler("P_ALERT", LAN_103);
				$error = TRUE;
				$name = "";
			}
		}
	}

	if (strlen($_POST['name']) > 30) {
		exit;
	}

	if ($sql->db_Select("user", "*", "user_name='".$_POST['name']."' ")) {
		message_handler("P_ALERT", LAN_104);
		$error = TRUE;
		$name = "";
	}

// check for multiple signups from the same IP address.
	if($ipcount = $sql->db_Select("user", "*", "user_ip='".$e107->getip()."' and user_ban !='2' ")){
		if($ipcount >= $pref['signup_maxip'] && trim($pref['signup_maxip']) != ""){
		message_handler("P_ALERT",LAN_202);
		$error = TRUE;
		}
	}


	if ($_POST['password1'] != $_POST['password2']) {
		message_handler("P_ALERT", LAN_105);
		$error = TRUE;
		$password1 = "";
		$password2 = "";
	}

	if (strlen($_POST['password1']) < $pref['signup_pass_len']) {
		message_handler("P_ALERT", LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5);
		$error = TRUE;
		$password1 = "";
		$password2 = "";
	}

	if ($_POST['name'] == "" || $_POST['password1'] == "" || $_POST['password2'] = "") {
		message_handler("P_ALERT", LAN_185);
		$error = TRUE;
	}

	// ========== Verify Custom Signup options if selected ========================

	for ($i = 0; $i < count($signup_title); $i++)
	{
		$postvalue = $signup_name[$i];
		if ($signupval[$i] == 2 && $_POST[$postvalue] == "") {
			message_handler("P_ALERT", LAN_SIGNUP_6.$signup_title[$i].LAN_SIGNUP_7);
			$error = TRUE;
		}
	}

	if ($sql->db_Select("user", "user_email", "user_email='".$_POST['email']."' ")) {
		message_handler("P_ALERT", LAN_408);
		$error = TRUE;
	}

	$extList = $usere->user_extended_get_fieldList();
	foreach($extList as $ext)
	{
		$ueRef["user_".$ext['user_extended_struct_name']] = $ext['user_extended_struct_id'];
	}
	foreach($_POST['ue'] as $key => $val)
	{
		if($extList[$ueRef[$key]]['user_extended_struct_signup'] == 2 && trim($val) == "")
		{
			echo "error on [$key] <br />";
			$error_ext = LAN_SIGNUP_6."[".$extList[$ueRef[$key]]['user_extended_struct_text']."]".LAN_SIGNUP_7;
			message_handler("P_ALERT", $error_ext);
			$error = TRUE;
		}
	}

	// ========== End of verification.. ====================================================

	if (!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]{1,50}@([-0-9A-Z]+\.){1,50}([0-9A-Z]){2,4}$/i', $_POST['email'])) {
		message_handler("P_ALERT", LAN_106);
		$error = TRUE;
	}
	if (preg_match('#^www\.#si', $_POST['website'])) {
		$_POST['website'] = "http://$homepage";
	}
	else if (!preg_match('#^[a-z0-9]+://#si', $_POST['website'])) {
		$_POST['website'] = "";
	}
	if (!$error)
	{
		$fp = new floodprotect;
		if ($fp->flood("user", "user_join") == FALSE) {
			header("location:".e_BASE."index.php");
			exit;
		}

		if ($sql->db_Select("user", "*", "user_email='".$_POST['email']."' AND user_ban='1' ")) {
			exit;
		}

		$wc = "*".substr($_POST['email'], strpos($_POST['email'], "@"));
		if ($sql->db_Select("banlist", "*", "banlist_ip='".$_POST['email']."' OR banlist_ip='$wc'")) {
			exit;
		}

		$username = strip_tags($_POST['name']);
		$loginname = strip_tags($_POST['loginname']);
		$time = time();
		$ip = $e107->getip();
		$birthday = $_POST['birth_year']."/".$_POST['birth_month']."/".$_POST['birth_day'];

		$ue_fields = "";
		foreach($_POST['ue'] as $key => $val)
		{
			$val = $tp->toDB($val);
			$ue_fields .= ($ue_fields) ? ", " : "";
			$ue_fields .= $key."='".$val."'";
		}

		if ($pref['user_reg_veri']){
			$u_key = md5(uniqid(rand(), 1));
			$nid = $sql->db_Insert("user", "0, '".$username."', '$loginname', '', '".md5($_POST['password1'])."', '$u_key', '".$_POST['email']."', '".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$birthday."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '2', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");

			// ==== Update Userclass =======

			if ($_POST['usrclass']) {
				unset($insert_class);
				sort($_POST['usrclass']);
				$insert_class = implode(",",$_POST['usrclass']);
				$sql->db_Update("user", "user_class='$insert_class' WHERE user_id='".$nid."' ");
			}

			// ========= save extended fields as serialized data. =====

			if($ue_fields){
				$sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$nid}')");
				$sql->db_Update("user_extended", $ue_fields." WHERE user_extended_id = '{$nid}'");
			}

			// ========== Send Email =====.                                                       // ==========================================================
			define("RETURNADDRESS", (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$nid.".".$u_key : SITEURL."/signup.php?activate.".$nid.".".$u_key));
			$pass_show = ($pref['user_reg_secureveri'])? "*******" : $_POST['password1'];
			$message = LAN_403." ".SITENAME."\n";
			$message .= LAN_SIGNUP_21."\n\n";
			$message .= RETURNADDRESS."\n\n";
			$message .= LAN_SIGNUP_18."\n\n";
			$message .= LAN_SIGNUP_19." ".$_POST['loginname']."\n".LAN_SIGNUP_20." ".$pass_show."\n\n";
			$message .= LAN_407." ".SITENAME."\n".SITEURL;

			require_once(e_HANDLER."mail.php");
			if (file_exists(THEME."emails.php")){
				require_once(THEME."emails.php");
				$message = ($SIGNUPEMAIL)? $SIGNUPEMAIL:
				$message;
			}
			sendemail($_POST['email'], LAN_404." ".SITENAME, $message);

			$edata_su = array("username" => $username, "email" => $_POST['email'], "website" => $_POST['website'], "icq" => $_POST['icq'], "aim" => $_POST['aim'], "msn" => $_POST['msn'], "location" => $_POST['location'], "birthday" => $birthday, "signature" => $_POST['signature'], "image" => $_POST['image'], "timezone" => $_POST['timezone'], "hideemail" => $_POST['hideemail'], "ip" => $ip, "realname" => $_POST['realname']);
			$e_event->trigger("usersup", $edata_su);

			require_once(HEADERF);
			$text = LAN_405;
			$ns->tablerender(LAN_406, $text);
			require_once(FOOTERF);
			exit;
		}
		else
		{
			require_once(HEADERF);
			$nid = $sql->db_Insert("user", "0, '$username', '$loginname', '', '".md5($_POST['password1'])."', '$u_key', '".$_POST['email']."', '".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$birthday."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");

			// ==== Update Userclass =======
			if ($_POST['usrclass']) {
				unset($insert_class);
				sort($_POST['usrclass']);
				$insert_class = implode(",",$_POST['usrclass']);
				$sql->db_Update("user", "user_class='$insert_class' WHERE user_id='".$nid."' ");
			}
			// ======== save extended fields as serialized data.

			if($ue_fields)
			{
				$sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$nid}')");
				$sql->db_Update("user_extended", $ue_fields." WHERE user_extended_id = '{$nid}'");
			}

			// ==========================================================

			$edata_su = array("username" => $username, "email" => $_POST['email'], "website" => $_POST['website'], "icq" => $_POST['icq'], "aim" => $_POST['aim'], "msn" => $_POST['msn'], "location" => $_POST['location'], "birthday" => $birthday, "signature" => $_POST['signature'], "image" => $_POST['image'], "timezone" => $_POST['timezone'], "hideemail" => $_POST['hideemail'], "ip" => $ip, "realname" => $_POST['realname']);
			$e_event->trigger("usersup", $edata_su);

			$ns->tablerender(LAN_SIGNUP_8, LAN_107."&nbsp;".SITENAME.", ".LAN_SIGNUP_12."<br /><br />".LAN_SIGNUP_13);
			require_once(FOOTERF);
			exit;
		}
	}

}
require_once(HEADERF);

$qs = ($error ? "stage" : e_QUERY);

if ($pref['use_coppa'] == 1 && !ereg("stage", $qs)) {
	$cert_text = LAN_109 . " <a href='http://www.cdt.org/legislation/105th/privacy/coppa.html'>".LAN_SIGNUP_14."</a>. ".LAN_SIGNUP_15." <a href='mailto:".SITEADMINEMAIL."'>".LAN_SIGNUP_14."</a> ".LAN_SIGNUP_16."<br /><br /><div style='text-align:center'><b>".LAN_SIGNUP_17."\n";
	if (eregi("stage", LAN_109)) {
		$text .= $cert_text."</b></div>\n";
	} else {
		$text .= $cert_text."</b>\n<form method='post' action='signup.php?stage1' >\n
			<div><br />
			<input type='radio' name='coppa' value='0' checked='checked' /> ".LAN_200."
			<input type='radio' name='coppa' value='1' /> ".LAN_201."<br />
			<br />
			<input class='button' type='submit' name='newver' value='".LAN_399."' />
			</div></form>
			</div>";
	}

	$ns->tablerender(LAN_110, $text);
	require_once(FOOTERF);
	exit;
}

if (!$website) {
	$website = "http://";
}

if (!eregi("stage", LAN_109)) {
	if (isset($_POST['newver'])) {
		if (!$_POST['coppa']) {
			$ns->tablerender(LAN_202, "<div style='text-align:center'>".LAN_SIGNUP_9."</div>");
			require_once(FOOTERF);
			exit;
		}
	}
}
$text .= "<div style='text-align:center;width:100%'>";

if($pref['signup_text']) {
	$text .= $tp->toHTML($pref['signup_text'], TRUE, 'parse_sc,defs')."<br />";
}elseif($pref['user_reg_veri']) {
	$text .= LAN_309."<b>".LAN_SIGNUP_29."</b><br />".LAN_SIGNUP_30."<br /><br />";
}

$text .= LAN_400;
require_once(e_HANDLER."form_handler.php");
$rs = new form;



$text .= $rs->form_open("post", e_SELF, "signupform")."
<table class='fborder' style='width:90%'>
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_7."<span style='font-size:15px; color:red'> *</span><br /><span class='smalltext'>".LAN_8."</span></td>
<td class='forumheader3' style='width:70%'>
".$rs->form_text("name", 30, $name, 30)."
</td>
</tr>

<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_9."<span style='font-size:15px; color:red'> *</span><br /><span class='smalltext'>".LAN_10."</span></td>
<td class='forumheader3' style='width:70%'>
".$rs->form_text("loginname", 30, $loginname, 30)."
</td>
</tr>
";





if ($signupval[0]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_308."".req($signupval[0])."</td>
		<td class='forumheader3' style='width:70%' >
		".$rs->form_text("realname", 30, $realname, 100)."
		</td>
		</tr>";
}

$text .= "
	<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_17."<span style='font-size:15px; color:red'> *</span></td>
	<td class='forumheader3' style='width:70%'>
	".$rs->form_password("password1", 30, $password1, 20)."
	";
if ($pref['signup_pass_len']) {
	$text .= "<span class='smalltext'> (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
}
$text .= "
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_111."<span style='font-size:15px; color:red'> *</span></td>
	<td class='forumheader3' style='width:70%'>
	".$rs->form_password("password2", 30, $password2, 20)."
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_112."<span style='font-size:15px; color:red'> *</span></td>
	<td class='forumheader3' style='width:70%'>
	".$rs->form_text("email", 30, $email, 100)."
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_113."</td>
	<td class='forumheader3' style='width:70%'>". $rs->form_radio("hideemail", 1)." ".LAN_SIGNUP_10."&nbsp;&nbsp;".$rs->form_radio("hideemail", 0, 1)." ".LAN_200."
	</td>
	</tr>";


// ----------  User subscription to userclasses.
if ($signupval[10] && ($sql->db_Select("userclass_classes", "*", "userclass_editclass =0 order by userclass_name"))) {
	$text .= "<tr>
		<td class='forumheader3' style='width:30%;vertical-align:top'>".LAN_USET_5." ".req($signupval[10])."
		<br /><span class='smalltext'>".LAN_USET_6."</span></td>
		<td class='forumheader3' style='width:70%'>";
	$text .= "<table style='width:100%'>";
	while ($row3 = $sql->db_Fetch()) {
		//  $frm_checked = check_class($userclass_id,$user_class) ? "checked='checked'" : "";
		$text .= "<tr><td class='defaulttext' style='width:10%;vertical-align:top'>";
		$text .= "<input type='checkbox' name='usrclass[]' value='".$row3['userclass_id']."'  />\n";
		$text .= "</td><td class='defaulttext' style='text-align:left;margin-left:0px;width:90%padding-top:3px;vertical-align:top'>".$tp->toHTML($row3['userclass_name'],"","defs")."<br />";
		$text .= "<span class='smalltext'>".$tp->toHTML($row3['userclass_description'],"","defs")."</span></td>";
		$text .= "</tr>\n";
	}
	$text .= "</table>\n";

	$text .= " </td>
		</tr>";
}
// --------------------------



if ($signupval[1]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_144.req($signupval[1])."</td>
		<td class='forumheader3' style='width:70%' >
		<input class='tbox' style='width:99%' type='text' name='website' size='60' value='$website' maxlength='60' />
		</td>
		</tr>";
}


if ($signupval[2]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_115.req($signupval[2])."</td>
		<td class='forumheader3' style='width:70%' >
		<input class='tbox' type='text' name='icq' size='30' value='$icq' maxlength='100' />
		</td>
		</tr>";
}

if ($signupval[3]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_116.req($signupval[3])."</td>
		<td class='forumheader3' style='width:70%; ' >
		<input class='tbox' type='text' name='aim' size='30' value='$aim' maxlength='100' />
		</td>
		</tr>";
}


if ($signupval[4]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_117.req($signupval[4])."</td>
		<td class='forumheader3' style='width:70%;'>
		<input class='tbox' type='text' name='msn' size='30' value='$msn' maxlength='100' />
		</td>
		</tr>";
}


if ($signupval[5]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_118.req($signupval[5])."</td>
		<td class='forumheader3' style='width:70%'>".
	$rs->form_select_open("birth_day"). $rs->form_option("", 0);
	$today = getdate();
	$year = $today['year'];
	for($a = 1; $a <= 31; $a++) {
		$text .= ($birth_day == $a ? $rs->form_option($a, 1) : $rs->form_option($a, 0));
	}
	$text .= $rs->form_select_close(). $rs->form_select_open("birth_month"). $rs->form_option("", 0);
	for($a = 1; $a <= 12; $a++) {
		$text .= ($birth_month == $a ? $rs->form_option($a, 1, $a) : $rs->form_option($a, 0, $a));
	}
	$text .= $rs->form_select_close(). $rs->form_select_open("birth_year"). $rs->form_option("", 0);
	for($a = 1900; $a <= $year; $a++) {
		$text .= ($birth_year == $a ? $rs->form_option($a, 1) : $rs->form_option($a, 0));
	}
	$text .= $rs->form_select_close();
	$text .= "</td></tr>";
}



if ($signupval[6]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_119." ".req($signupval[6])."</td>
		<td class='forumheader3' style='width:70%' >
		<input class='tbox' style='width:99%' type='text' name='location' size='60' value='$location' maxlength='200' />
		</td>
		</tr>";
}


$extList = $usere->user_extended_get_fieldList();

foreach($extList as $ext) {
	if($ext['user_extended_struct_signup'] > 0) {
		$text .= "
		<tr>
			<td style='width:40%' class='forumheader3'>".$tp->toHTML($ext['user_extended_struct_text'],"","emotes_off defs")." ".req($ext['user_extended_struct_signup'])."</td>
			<td style='width:60%' class='forumheader3'>".$usere->user_extended_edit($ext, $_POST['ue']['user_'.$ext['user_extended_struct_name']])."
		</td>
		</tr>
		";
	}
}

if ($signupval[7]) {
	require_once(e_HANDLER."ren_help.php");
	$text .= "<tr>
		<td class='forumheader3' style='width:30%;white-space:nowrap;vertical-align:top' >".LAN_120." ".req($signupval[7])."</td>
		<td class='forumheader3' style='width:70%' >
		<textarea class='tbox' style='width:99%' name='signature' cols='10' rows='4' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$signature</textarea><br />
		<div style='width:99%'>".ren_help(2)."</div>
		</td></tr>";
}

if ($signupval[8]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%; vertical-align:top;white-space:nowrap' >".LAN_121.req($signupval[8])."<br /><span class='smalltext'>(".LAN_402.")</span></td>
		<td class='forumheader3' style='width:70%;vertical-align:top' >
		<input class='tbox' style='width:80%' id='avatar' type='text' name='image' size='40' value='$image' maxlength='100' />

		<input class='button' type ='button' style='cursor:hand' size='30' value='".LAN_SIGNUP_27."' onclick='expandit(this)' />
		<div style='display:none' >";
	$avatarlist[0] = "";
	$handle = opendir(e_IMAGE."avatars/");
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && $file != "CVS" && $file != "index.html") {
			$avatarlist[] = $file;
		}
	}
	closedir($handle);

	for($c = 1; $c <= (count($avatarlist)-1); $c++) {
		$text .= "<a href='javascript:insertext(\"$avatarlist[$c]\", \"avatar\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
	}

	$text .= "<br />
		</div>";

	if ($pref['avatar_upload'] && FILE_UPLOADS) {
		$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_25."</span> <input class='tbox' name='file_userfile[]' type='file' size='40'>
			<br /><div class='smalltext'>".LAN_404."</div>";
	}

	if ($pref['photo_upload'] && FILE_UPLOADS) {
		$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_26."</span> <input class='tbox' name='file_userfile[]' type='file' size='40'>
			<br /><div class='smalltext'>".LAN_404."</div>";
	}


	$text .= "</td>
		</tr>";
}

if ($signupval[9]) {
	$text .= "
		<tr>
		<td class='forumheader3' style='width:30%' >".LAN_122.req($signupval[9])."</td>
		<td class='forumheader3' style='width:70%;white-space:nowrap'>
		<select style='width:99%' name='timezone' class='tbox'>\n";

	timezone();
	$count = 0;
	while ($timezone[$count]) {
		if ($timezone[$count] == $user_timezone) {
			$text .= "<option value='".$timezone[$count]."' selected>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
		} else {
			$text .= "<option value='".$timezone[$count]."'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
		}
		$count++;
	}

	$text .= "</select>
		</td>
		</tr>";
}

if ($use_imagecode) {
	$text .= " <tr>
		<td class='forumheader3' style='width:30%'>".LAN_410.req(2)."</td>
		<td class='forumheader3' style='width:70%'>". $rs->form_hidden("rand_num", $sec_img->random_number). $sec_img->r_image()."<br />".$rs->form_text("code_verify", 20, "", 20)."
		</td>
		</tr>";
}

$text .= "
	<tr style='vertical-align:top'>
	<td class='forumheader' colspan='2'  style='text-align:center'>
	<input class=\"button\" type=\"submit\" name=\"register\" value=\"".LAN_123."\" />
	<br />
	</td>
	</tr>
	</table>
	</form>
	</div>
	";

$ns->tablerender(LAN_123, $text);

require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone() {
	/*
	# Render style table
	# - parameters                none
	# - return                    timezone arrays
	# - scope                     public
	*/
	global $timezone, $timearea;
	$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+5.30", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
	$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Mumbai, Delhi, Calcutta", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function req($field) {
	global $pref;
	if ($field == 2) {
		$ret = "<span style='text-align:right;font-size:15px; color:red'> *</span>";
	} else {
		$ret = "";
	}
	return $ret;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function headerjs() {
	$script_txt = "
	<script type=\"text/javascript\">
	function addtext3(sc){
	document.getElementById('signupform').image.value = sc;
	}

	function addsig(sc){
	document.getElementById('signupform').signature.value += sc;
	}
	function help(help){
	document.getElementById('signupform').helpb.value = help;
	}
	</script>\n";

	global $cal;
	$script_txt .= $cal->load_files();
	return $script_txt;
}

?>