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
|     $Source: /cvs_backup/e107_0.7/e107_admin/prefs.php,v $
|     $Revision: 1.40 $
|     $Date: 2005-03-15 12:08:01 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
include_once(e_HANDLER."userclass_class.php");

if (isset($_POST['newver'])) {
	header("location:http://e107.org/index.php");
	exit;
}

if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'prefs';
if (!$pref['timezone']) {
	$pref['timezone'] = "GMT";
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$signup_title = array(CUSTSIG_2, CUSTSIG_3, "ICQ", "Aim", "MSN", CUSTSIG_4, CUSTSIG_5, CUSTSIG_6, CUSTSIG_7, CUSTSIG_8, CUSTSIG_17);
$signup_name = array("real", "url", "icq", "aim", "msn", "dob", "loc", "sig", "avt", "zone", "usrclass");





if (isset($_POST['updateprefs'])) {
	unset($_POST['updateprefs']);

	foreach($_POST as $key => $value) {
		if(is_string($value)) {
			$pref[$key] = $tp->toDB($value, TRUE);
		} else {
			$pref[$key] = $value;
		}
	}

	if ($sql->db_Select("core", " e107_value", " e107_name='user_entended'")) {
		$row = $sql->db_Fetch();
		$user_entended = unserialize($row[0]);
		$c = 0;
		$user_pref = unserialize($user_prefs);
		while (list($key, $u_entended) = each($user_entended)) {
			if ($u_entended) {
				$signup_ext = "signup_ext".$key;
				$pref[$signup_ext] = $_POST[$signup_ext];
				$signup_ext_req = "signup_ext_req".$key;
				$pref[$signup_ext_req] = $_POST[$signup_ext_req];
			}
		}
	}

	$signup_options = "";
	for ($i = 0; $i < count($signup_title); $i++) {
		$valuesignup = $signup_name[$i];
		$signup_options .= $_POST[$valuesignup];
		$signup_options .= $i < (count($signup_title)-1)?".":
		"";
	}
	$pref['signup_options'] = $signup_options;

	$e107cache->clear();

	save_prefs();

	header("location:".e_ADMIN."prefs.php?u");
	echo "<script type='text/javascript'>document.location.href='prefs.php'</script>\n";
	exit;

}

$sql->db_Select("plugin", "*", "plugin_installflag='1' ");
while ($row = $sql->db_Fetch()) {
	extract($row);
	if (preg_match("/^auth_(.*)/", $plugin_path, $match)) {
		$authlist[] = $match[1];
	}
}
if ($authlist) {
	$authlist[] = "e107";
	$auth_dropdown = "\n<tr>
		<td style='width:50%' class='forumheader3'>".PRFLAN_56.": </td>
		<td style='width:50%; text-align:right;' class='forumheader3'>";
	$auth_dropdown .= "<select class='tbox' name='auth_method'>\n";
	foreach($authlist as $a) {
		$s = ($pref['auth_method'] == $a) ? " selected='selected'>" :
		 "";
		$auth_dropdown .= "<option {$s}>".$a."</option>\n";
	}
	$auth_dropdown .= "</select>\n";
	$auth_dropdown .= "</td></tr>";
} else {
	$auth_dropdown = "<input type='hidden' name='auth_method' value='' />";
	$pref['auth_method'] = "";
}


require_once("auth.php");

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(e_QUERY == "u") {
	$ns->tablerender("", "<div style='text-align:center'><b>".PRFLAN_106."</b></div>");
}

$handle = opendir(e_ADMIN.'includes/');
while ($file = readdir($handle)) {
	if ($file != "." && $file != "..") {
		$file = str_replace(".php", "", $file);
		$adminlist[] = $file;
	}
}
closedir($handle);

$text = "<script type=\"text/javascript\">
	<!--
	var hideid=\"main\";
	function showhideit(showid){
	if (hideid!=showid){
	show=document.getElementById(showid).style;
	hide=document.getElementById(hideid).style;
	show.display=\"\";
	hide.display=\"none\";
	hideid = showid;
	}
	}
	//-->
	</script>
	<div style='text-align:center'>
	<div style='text-align:center; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto'>
	<form method='post' action='".e_SELF."'>
	<div id='main' style='text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_1."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_2."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='sitename' size='50' value='".SITENAME."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_3."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteurl' size='50' value='".SITEURL."' maxlength='150' />
	</td>
	</tr>


	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_4."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='sitebutton' size='50' value='".SITEBUTTON."' maxlength='150' />
	</td>
	</tr>
	<tr>

	<td style='width:50%' class='forumheader3'>".PRFLAN_5."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitetag' cols='59' rows='3'>".SITETAG."</textarea>
	</td>
	</tr>
	<tr>

	<td style='width:50%' class='forumheader3'>".PRFLAN_6."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitedescription' cols='59' rows='6'>".SITEDESCRIPTION."</textarea>
	</td>
	</tr>
	<tr>

	<td style='width:50%' class='forumheader3'>".PRFLAN_7."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteadmin' size='50' value='".SITEADMIN."' maxlength='100' />
	</td>
	</tr>
	<tr>

	<td style='width:50%' class='forumheader3'>".PRFLAN_8."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteadminemail' size='50' value='".SITEADMINEMAIL."' maxlength='100' />
	</td>
	</tr>
	<tr>

	<td style='width:50%' class='forumheader3'>".PRFLAN_9."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitedisclaimer' cols='59' rows='6'>".SITEDISCLAIMER."</textarea>
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table>
	</div>

	<div id='display' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_13."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_14." </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='displaythemeinfo' value='1'".($pref['displaythemeinfo'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='displaythemeinfo' value='0'".(!$pref['displaythemeinfo'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_15." </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='displayrendertime' value='1'".($pref['displayrendertime'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='displayrendertime' value='0'".(!$pref['displayrendertime'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_16." </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='displaysql' value='1'".($pref['displaysql'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='displaysql' value='0'".(!$pref['displaysql'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table></div>";

// Admin Display Areas. .

$text .= "<div id='admindisp' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_77."</td>
	</tr>

	

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_95."<br /><span class='smalltext'>".PRFLAN_96."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='admin_alerts_ok' value='1'".($pref['admin_alerts_ok'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='admin_alerts_ok' value='0'".(!$pref['admin_alerts_ok'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_97."<br /><span class='smalltext'>".PRFLAN_98."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='admin_alerts_uniquemenu' value='1'".($pref['admin_alerts_uniquemenu'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='admin_alerts_uniquemenu' value='0'".(!$pref['admin_alerts_uniquemenu'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table></div>";

// Date options.
$text .= "<div id='date' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_21."</td>
	</tr>

	<tr>";

$ga = new convert;
$date1 = $ga->convert_date(time(), "short");
$date2 = $ga->convert_date(time(), "long");
$date3 = $ga->convert_date(time(), "forum");


$text .= "<td style='width:50%' class='forumheader3'>".PRFLAN_22.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='shortdate' size='40' value='".$pref['shortdate']."' maxlength='50' />
	<br />".PRFLAN_83.": $date1
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_23.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='longdate' size='40' value='".$pref['longdate']."' maxlength='50' />
	<br />".PRFLAN_83.": $date2
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_24."<br /><span class='smalltext'>".PRFLAN_25." <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>".PRFLAN_93."</a></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='forumdate' size='40' value='".$pref['forumdate']."' maxlength='50' />
	<br />".PRFLAN_83.": $date3
	</td>
	</tr>



	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_26."<br /><span class='smalltext'>".PRFLAN_27."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<select name='time_offset' class='tbox'>\n";
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13", "+14", "+15", "+16");
$counter = 0;
while (isset($toffset[$counter])) {
	if ($toffset[$counter] == $pref['time_offset']) {
		$text .= "<option selected='selected'>".$toffset[$counter]."</option>\n";
	} else {
		$text .= "<option>".$toffset[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
	</td></tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_56.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='timezone' size='20' value='".$pref['timezone']."' maxlength='50' />
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table></div>";

// =========== Registration Preferences. ==================

$text .= "<div id='registration' style='display:none; text-align:center'><table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_28."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_29."<br /><span class='smalltext'>".PRFLAN_30."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='user_reg' value='1'".($pref['user_reg'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='user_reg' value='0'".(!$pref['user_reg'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_31."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='user_reg_veri' value='1'".($pref['user_reg_veri'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='user_reg_veri' value='0'".(!$pref['user_reg_veri'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_32."<br /><span class='smalltext'>".PRFLAN_33."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='anon_post' value='1'".($pref['anon_post'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='anon_post' value='0'".(!$pref['anon_post'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_45."<br /><span class='smalltext'>".PRFLAN_46." <a href='http://www.cdt.org/legislation/105th/privacy/coppa.html'>".PRFLAN_94."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='use_coppa' value='1'".($pref['use_coppa'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='use_coppa' value='0'".(!$pref['use_coppa'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_58."<br /><span class='smalltext'>".PRFLAN_59."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='membersonly_enabled' value='1'".($pref['membersonly_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='membersonly_enabled' value='0'".(!$pref['membersonly_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".CUSTSIG_16."<br /><span class='smalltext'>".PRFLAN_78."</span></td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='text' class='tbox' size='3' name='signup_pass_len' value='".$pref['signup_pass_len']."' />
	</td>
	</tr>
	
	<tr>
	<td style='width:50%' class='forumheader3'>".CUSTSIG_18."<br /><span class='smalltext'>".CUSTSIG_19."</span></td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<textarea class='tbox' name='signup_disallow_text' cols='1' rows='3' style='width: 80%;'>".$pref['signup_disallow_text']."</textarea>
	</td>
	</tr>
	
	
	
	
	
	
	";

$text .= pref_submit();

$text .= "</table></div>";


// Signup options ===========================.

$text .= "<div id='signup' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' style='text-align:left;' colspan='2'>".PRFLAN_19."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_126."</td>
	<td style='width:50%' class='forumheader3'><textarea class='tbox' name='signup_text' cols='1' rows='5' style='width: 100%;'>".$pref['signup_text']."</textarea>
	</tr>


	<tr>
	<td class='forumheader'>".CUSTSIG_13."</td>
	<td class='forumheader'>".CUSTSIG_14."</td>
	</tr>";
$signupval = explode(".", $pref['signup_options']);
for ($i = 0; $i < count($signup_title); $i++) {
	$text .= "
		<tr>
		<td style='width:50%' class='forumheader3'>".$signup_title[$i]."</td>
		<td style='width:50%;text-align:center' class='forumheader3' >". ($signupval[$i] == "0" || $$signup_name[$i] == "" ? "<input type='radio' name='".$signup_name[$i]."' value='0' checked='checked' /> ".CUSTSIG_12 : "<input type='radio' name='".$signup_name[$i]."' value='0' /> ".CUSTSIG_12)."&nbsp;&nbsp;". ($signupval[$i] == "1" ? "<input type='radio' name='".$signup_name[$i]."' value='1' checked='checked' /> ".CUSTSIG_14 : "<input type='radio' name='".$signup_name[$i]."' value='1' /> ".CUSTSIG_14)."&nbsp;&nbsp;". ($signupval[$i] == "2" ? "<input type='radio' name='".$signup_name[$i]."' value='2' checked='checked' /> ".CUSTSIG_15 : "<input type='radio' name='".$signup_name[$i]."' value='2' /> ".CUSTSIG_15)."&nbsp;&nbsp;

		</td></tr>";
}

// Custom Fields.

if ($sql->db_Select("core", " e107_value", " e107_name='user_entended'")) {
	$row = $sql->db_Fetch();
	$user_entended = unserialize($row[0]);
	$c = 0;
	$user_pref = unserialize($user_prefs);
	while (list($key, $u_entended) = each($user_entended)) {
		if ($u_entended) {
			$ut = explode("|", $u_entended);
			$u_name = ($ut[0] != "") ? str_replace("_", " ", $ut[0]):
			 $u_entended;
			$u_type = $ut[1];
			$u_value = $ut[2];
			$signup_ext = "signup_ext";
			$text .= "
				<tr>
				<td style='width:50%' class='forumheader3'>".$u_name." <span class='smalltext'>(custom field)</span></td>
				<td style='width:50%;text-align:right' class='forumheader3' >". ($pref['signup_ext'.$key] == "0" || $pref['signup_ext'.$key] == "" ? "<input type='radio' name='signup_ext".$key."' value='0' checked='checked' /> ".CUSTSIG_12 : "<input type='radio' name='signup_ext".$key."' value='0' /> ".CUSTSIG_12)."&nbsp;&nbsp;". ($pref['signup_ext'.$key] == "1" ? "<input type='radio' name='signup_ext".$key."' value='1' checked='checked' /> ".CUSTSIG_14 : "<input type='radio' name='signup_ext".$key."' value='1' /> ".CUSTSIG_14)."&nbsp;&nbsp;". ($pref['signup_ext'.$key] == "2" ? "<input type='radio' name='signup_ext".$key."' value='2' checked='checked' /> ".CUSTSIG_15 : "<input type='radio' name='signup_ext".$key."' value='2' /> ".CUSTSIG_15)."&nbsp;&nbsp;". "</td>
				</tr>";
		}
	}
}


$text .= pref_submit();


$text .= "</table></div>";


/* text render options */


//$pref['link_text'] = str_replace("'", "#", $pref['link_text']);

if(!isset($pref['post_html']))
{
	$pref['post_html'] = '254';
	save_prefs();
}

$text .= "<div id='textpost' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_127.":  <div class='smalltext'>".PRFLAN_128."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='make_clickable' value='1'".($pref['make_clickable'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='make_clickable' value='0'".(!$pref['make_clickable'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_102."?:  <div class='smalltext'>".PRFLAN_103."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='link_replace' value='1'".($pref['link_replace'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='link_replace' value='0'".(!$pref['link_replace'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>


	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_104.": <div class='smalltext'>".PRFLAN_105."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input class='tbox' type='text' name='link_text' size='50' value='".$tp -> post_toForm($pref['link_text'])."' maxlength='200' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_107.": <div class='smalltext'>".PRFLAN_108."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input class='tbox' type='text' name='email_text' size='50' value='".$tp -> post_toForm($pref['email_text'])."' maxlength='200' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_109.":  <div class='smalltext'>".PRFLAN_110."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input class='tbox' type='text' name='main_wordwrap' size='5' value='".$pref['main_wordwrap']."' maxlength='3' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_111.":  <div class='smalltext'>".PRFLAN_110."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input class='tbox' type='text' name='menu_wordwrap' size='5' value='".$pref['menu_wordwrap']."' maxlength='3' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_116.":  <div class='smalltext'>".PRFLAN_117."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	".r_userclass('post_html',$pref['post_html'],'off','public, member, admin, classes')."
	</td>
	</tr>\n

    <tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_122.":  <div class='smalltext'>".PRFLAN_123."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='wysiwyg' value='1'".($pref['wysiwyg'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='wysiwyg' value='0'".(!$pref['wysiwyg'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>\n

    <tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_124.":  <div class='smalltext'>".PRFLAN_125."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='old_np' value='1'".($pref['old_np'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='old_np' value='0'".(!$pref['old_np'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

";

	if(file_exists(e_PLUGIN."geshi/geshi.php")) {
		$text .= "<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_118."?:  <div class='smalltext'>".PRFLAN_119."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='useGeshi' value='1'".($pref['useGeshi'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='useGeshi' value='0'".(!$pref['useGeshi'] ? " checked='checked'" : "")." /> ".PRFLAN_113."<br />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_120."?:  <div class='smalltext'>".PRFLAN_121."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input class='tbox' type='text' name='defaultLanGeshi' size='5' value='".($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : "php")."' maxlength='3' />
	</td>
	</tr>
	";
	}



$text .= pref_submit();

$text .= "</table></div>";









// Security Options. .

$text .= "<div id='security' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_47."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_60."<br /><span class='smalltext'>".PRFLAN_61."</span> </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='ssl_enabled' value='1'".($pref['ssl_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='ssl_enabled' value='0'".(!$pref['ssl_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_76.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='signcode' value='1'".($pref['signcode'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='signcode' value='0'".(!$pref['signcode'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_81.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='logcode' value='1'".($pref['logcode'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='logcode' value='0'".(!$pref['logcode'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_92.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='user_reg_secureveri' value='1'".($pref['user_reg_secureveri'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='user_reg_secureveri' value='0'".(!$pref['user_reg_secureveri'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>
	
	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_129.":<br /><span class='smalltext'>".PRFLAN_130."</span> </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='disallowMultiLogin' value='1'".($pref['disallowMultiLogin'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='disallowMultiLogin' value='0'".(!$pref['disallowMultiLogin'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_48.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>". ($pref['user_tracking'] == "cookie" ? "<input type='radio' name='user_tracking' value='cookie' checked='checked' /> ".PRFLAN_49 : "<input type='radio' name='user_tracking' value='cookie' /> ".PRFLAN_49). ($pref['user_tracking'] == "session" ? "<input type='radio' name='user_tracking' value='session' checked='checked' /> ".PRFLAN_50 : "<input type='radio' name='user_tracking' value='session' /> ".PRFLAN_50)."
	<br />
	".PRFLAN_55.": <input class='tbox' type='text' name='cookie_name' size='20' value='".$pref['cookie_name']."' maxlength='20' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_40."<br /><span class='smalltext'>".PRFLAN_41."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='profanity_filter' value='1'".($pref['profanity_filter'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='profanity_filter' value='0'".(!$pref['profanity_filter'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_42.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='profanity_replace' size='30' value='".$pref['profanity_replace']."' maxlength='20' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_43.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<textarea class='tbox' name='profanity_words' cols='59' rows='2'>".$pref['profanity_words']."</textarea>
	<br />".PRFLAN_44."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_82.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='search_restrict' value='1'".($pref['search_restrict'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='search_restrict' value='0'".(!$pref['search_restrict'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_35.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='antiflood1' value='1'".($pref['antiflood1'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='antiflood1' value='0'".(!$pref['antiflood1'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_36.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='antiflood_timeout' size='3' value='".$pref['antiflood_timeout']."' maxlength='3' />
	<br />
	<b class=\"smalltext\" >".PRFLAN_38."</b>
	</td>
	</tr>


	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_37."<br /><span class='smalltext'>".PRFLAN_91."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='autoban' value='1'".($pref['autoban'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='autoban' value='0'".(!$pref['autoban'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table></div>";

$text .= "<div id='comments' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='cursor:pointer; cursor:hand; text-align:left;' colspan='2'>".PRFLAN_87."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_89.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='comments_icon' value='1'".($pref['comments_icon'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='comments_icon' value='0'".(!$pref['comments_icon'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_88.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='nested_comments' value='1'".($pref['nested_comments'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='nested_comments' value='0'".(!$pref['nested_comments'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

$text .= pref_submit();

$text .= "</table></div>";

$text .= "</form></div></div>";

$ns->tablerender(PRFLAN_53, $text);

require_once("footer.php");

function pref_submit() {
	$text = "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>";

	// ML
	/* if(e_MLANG == 1){
	//$text .="<input class='fcaption' type='submit' name='updateprefs' value='".PRFLAN_52."' />
	$but_typ = array(""); // empty = submit
	$but_nam = array("updateprefs"); // empty = autobutX with X autoincrement
	$but_val = array("updateprefs"); // empty = Submit
	$but_class = array("caption"); // empty = button
	$butjs = array(""); // empty = ""
	$buttitle = array(""); // empty = ""
	$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
	}else{*/
	$text .= "<input class='button' type='submit' name='updateprefs' value='".PRFLAN_52."' />";
	// }
	$text .= "</td>
		</tr>";

	// END ML
	return $text;
}

function prefs_adminmenu() {
	$var['main']['text'] = PRFLAN_1;
	$var['display']['text'] = PRFLAN_13;
	$var['admindisp']['text'] = PRFLAN_77;
	$var['date']['text'] = PRFLAN_21;
	$var['registration']['text'] = PRFLAN_28;
	$var['signup']['text'] = PRFLAN_19;
	$var['textpost']['text'] = PRFLAN_101;
	$var['security']['text'] = PRFLAN_47;
	$var['comments']['text'] = PRFLAN_87;
	show_admin_menu(PRFLAN_99, $action, $var, TRUE);
}
?>