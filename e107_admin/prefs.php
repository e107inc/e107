<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ?Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/prefs.php,v $
|     $Revision: 1.107 $
|     $Date: 2009-09-28 21:00:00 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
include_once(e_HANDLER."userclass_class.php");
include_once(e_HANDLER."user_extended_class.php");
$ue = new e107_user_extended;

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

if ($_POST['submit_resetdisplaynames'])
{
    $sql -> db_Update("user", "user_name=user_loginname");
	$message = PRFLAN_157;
}

if (isset($_POST['updateprefs']))
{
	unset($_POST['updateprefs'],$_POST['sitelanguage']);

	$_POST['cookie_name'] = str_replace(array(" ","."), "_", $_POST['cookie_name']);
	$_POST['cookie_name'] = preg_replace("#[^a-zA-Z0-9_]#", "", $_POST['cookie_name']);

	$_POST['siteurl']     = trim($_POST['siteurl']) ? trim($_POST['siteurl']) : SITEURL;
	$_POST['siteurl']     = substr($_POST['siteurl'], -1) == "/" ? $_POST['siteurl'] : $_POST['siteurl']."/";

	// If email verification, email address is required!
	if ($_POST['user_reg_veri'] == 1) $_POST['disable_emailcheck'] = 0;

// Table of range checking values - min and max for numerics. Only do the important ones
	$pref_limits = array(
	  'loginname_maxlength' => array('min' => 10, 'max' => 100, 'default' => 30),
	  'displayname_maxlength' => array('min' => 5, 'max' => 30, 'default' => 15),
	  'antiflood_timeout' => array('min' => 3, 'max' => 300, 'default' => 10)
	);

	$pref['post_html'] = intval($_POST['post_html']);			// This ensures the setting is reflected in set text

	foreach($_POST as $key => $value)
	{
	  if (isset($pref_limits[$key]))
	  {	// Its a numeric value to check
	    if (is_numeric($value))
		{
		  if ($value < $pref_limits[$key]['min']) $value = $pref_limits[$key]['min'];
		  if ($value > $pref_limits[$key]['max']) $value = $pref_limits[$key]['max'];
		}
		else
		{
		  $value = $pref_limits[$key]['default'];
		} 
	    $pref[$key] = $value;
	  }
	  else
	  {
	    $pref[$key] = $tp->toDB($value);
	  }
	}


	$e107cache->clear();
	save_prefs();
	$sql -> db_Select_gen("TRUNCATE ".MPREFIX."online");
	header("location:".e_ADMIN."prefs.php?u");
	exit;
}

if($sql->db_Select("plugin", "plugin_path", "plugin_installflag='1' AND plugin_path = 'alt_auth'"))
{
	$authlist[] = "e107";
	$handle=opendir(e_PLUGIN."alt_auth");
	while ($file = readdir($handle))
	{
		if(preg_match("/^(.*)_auth\.php/",$file,$match))
		{
			$authlist[] = $match[1];
		}
	}
}

if ($authlist) {
	$auth_dropdown .= "<select class='tbox' name='auth_method'>\n";
	foreach($authlist as $a) {
		$s = ($pref['auth_method'] == $a ? " selected='selected' " : "");
		$auth_dropdown .= "<option {$s}>".$a."</option>\n";
	}
	$auth_dropdown .= "</select>\n";
} else {
	$auth_dropdown = "<input type='hidden' name='auth_method' value='' />".PRFLAN_151;
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
	<colgroup>
	  <col style='width:50%' />
	  <col style='width:50%' />
	</colgroup>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_1."</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_2."</td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='sitename' size='50' value='".$pref['sitename']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_3."</td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteurl' size='50' value='".$pref['siteurl']."' maxlength='150' /><br />
	".($pref['siteurl'] == SITEURL ? "" : "<br />( ".PRFLAN_159.": <b>".SITEURL."</b> )")."
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_134."<br /><span class='smalltext'>".PRFLAN_135."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<input type='radio' name='redirectsiteurl' value='1'".($pref['redirectsiteurl'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='redirectsiteurl' value='0'".(!$pref['redirectsiteurl'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_4."</td>
	<td style='text-align:right' class='forumheader3'>";

    $param = "sitebutton,".e_IMAGE.",".SITEBUTTON.",81px,30px,";
    $text .= $tp->parseTemplate("{IMAGESELECTOR={$param}}");

	$text .= "
		</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_5."</td>
	<td style='text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitetag' cols='59' rows='3'>".$pref['sitetag']."</textarea>
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_6."</td>
	<td style='text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitedescription' cols='59' rows='6'>".$pref['sitedescription']."</textarea>
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_7."</td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteadmin' size='50' value='".SITEADMIN."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_8."</td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='siteadminemail' size='50' value='".SITEADMINEMAIL."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_174."<br /><span class='smalltext'>".PRFLAN_175."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='replyto_name' size='50' value='".$pref['replyto_name']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_176."<br /><span class='smalltext'>".PRFLAN_177."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<input class='tbox' type='text' name='replyto_email' size='50' value='".$pref['replyto_email']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_162."<br /><span class='smalltext'>".PRFLAN_163."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitecontactinfo' cols='59' rows='6'>".$pref['sitecontactinfo']."</textarea>
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_168."<br /><span class='smalltext'>".PRFLAN_169."</span></td>
	<td style='text-align:right' class='forumheader3'>".r_userclass("sitecontacts",$pref['sitecontacts'],"off","nobody,main,admin,userclasses")."
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_164."<br /><span class='smalltext'>".PRFLAN_165."</span></td>
	<td style='text-align:right' class='forumheader3'>
	<input type='radio' name='contact_emailcopy' value='1'".($pref['contact_emailcopy'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='contact_emailcopy' value='0'".(!$pref['contact_emailcopy'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td class='forumheader3'>".PRFLAN_9."</td>
	<td style='text-align:right' class='forumheader3'>
	<textarea class='tbox' name='sitedisclaimer' cols='59' rows='6'>".$pref['sitedisclaimer']."</textarea>
	</td>
	</tr>

	";

$text .= pref_submit();

$text .= "</table>
	</div>

	<div id='display' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_13."</td>
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
	</tr>
	";
if(function_exists("memory_get_usage"))
{
	$text .= "
	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_137." </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='display_memory_usage' value='1'".($pref['display_memory_usage'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='display_memory_usage' value='0'".(!$pref['display_memory_usage'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>
	";
}
$text .= pref_submit();

$text .= "</table></div>";

// Admin Display Areas. .

$text .= "<div id='admindisp' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='; text-align:left;' colspan='2'>".PRFLAN_77."</td>
	</tr>



	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_95."<br /><span class='smalltext'>".PRFLAN_96."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='admin_alerts_ok' value='1'".($pref['admin_alerts_ok'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='admin_alerts_ok' value='0'".(!$pref['admin_alerts_ok'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_97."<br /><span class='smalltext'>".PRFLAN_98."</span></td>
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
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_21."</td>
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
	<td style='width:50%' class='forumheader3'>".PRFLAN_24."<br /><span class='smalltext'>".PRFLAN_25." <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>".PRFLAN_93."</a></span></td>
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
if(!isset($pref['time_offset']))
{
	$pref['time_offset'] = "0";
}
foreach($toffset as $o)
{
	if (!isset($pref['time_offset']) || $o == $pref['time_offset']) {
		$text .= "<option selected='selected'>".$o."</option>\n";
	} else {
		$text .= "<option>".$o."</option>\n";
	}
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
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_28."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_29."<br /><span class='smalltext'>".PRFLAN_30."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='user_reg' value='1'".($pref['user_reg'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='user_reg' value='0'".(!$pref['user_reg'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_141."<br /></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='xup_enabled' value='1'".($pref['xup_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='xup_enabled' value='0'".(!$pref['xup_enabled'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_154."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>

	<select name='user_reg_veri' class='tbox'>";
	$veri_list[0] = PRFLAN_152;
	$veri_list[1] = PRFLAN_31;
	$veri_list[2] = PRFLAN_153;

	foreach($veri_list as $v => $v_title){
		$sel = ($pref['user_reg_veri'] == $v) ? "selected='selected'" : "";
    	$text .= "<option value='$v' $sel>".$v_title."</option>\n";
	}

	$text .="</select></td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_160."<br /></td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='radio' name='signup_remote_emailcheck' value='1'".($pref['signup_remote_emailcheck'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='signup_remote_emailcheck' value='0'".(!$pref['signup_remote_emailcheck'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_167."<br /></td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='radio' name='disable_emailcheck' value='1'".($pref['disable_emailcheck'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='disable_emailcheck' value='0'".(!$pref['disable_emailcheck'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
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
	<td style='width:50%' class='forumheader3'>".PRFLAN_45."<br /><span class='smalltext'>".PRFLAN_46." <a href='http://www.cdt.org/legislation/105th/privacy/coppa.html'>".PRFLAN_94."</a></span></td>
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
	<td style='width:50%' class='forumheader3'>".PRFLAN_136."</td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='text' class='tbox' size='3' name='signup_maxip' value='".$pref['signup_maxip']."' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".CUSTSIG_18."<br /><span class='smalltext'>".CUSTSIG_19."</span></td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<textarea class='tbox' name='signup_disallow_text' cols='1' rows='3' style='width: 80%;'>".$pref['signup_disallow_text']."</textarea>
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_155.":</td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	".r_userclass('displayname_class',$pref['displayname_class'],'off','nobody,public,member,admin,classes')."
	<input class='button' type='submit' name='submit_resetdisplaynames' value='".PRFLAN_156."' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_158.":</td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='text' class='tbox' size='3' name='displayname_maxlength' value='".varset($pref['displayname_maxlength'],15)."' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_172.":</td>
	<td class='forumheader3' style='width:50%;text-align:right' >
	<input type='text' class='tbox' size='3' name='loginname_maxlength' value='".varset($pref['loginname_maxlength'],30)."' />
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
	<td style='width:50%' class='forumheader3'><textarea class='tbox' name='signup_text' cols='1' rows='3' style='width: 100%;'>".$pref['signup_text']."</textarea>
	</td>
	</tr>

    <tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_140."</td>
	<td style='width:50%' class='forumheader3'><textarea class='tbox' name='signup_text_after' cols='1' rows='3' style='width: 100%;'>".$pref['signup_text_after']."</textarea>
	</td>
	</tr>


	<tr>
	<td class='forumheader'>".CUSTSIG_13."</td>
	<td class='forumheader'>".CUSTSIG_14."</td>
	</tr>";

    $signup_option_title = array(CUSTSIG_2, CUSTSIG_6, CUSTSIG_7, CUSTSIG_8, CUSTSIG_17);
    $signup_option_names = array("signup_option_realname", "signup_option_signature", "signup_option_image", "signup_option_timezone", "signup_option_class");

	foreach($signup_option_names as $key => $value)
	{
		$text .= "
		<tr>
			<td style='width:50%' class='forumheader3'>".$signup_option_title[$key]."</td>
			<td style='width:50%' class='forumheader3'>".
			(!$pref[$value]        ? "<input type='radio' name='{$value}' value='0' checked='checked' /> ".CUSTSIG_12 : "<input type='radio' name='{$value}' value='0' /> ".CUSTSIG_12)."&nbsp;&nbsp;".
			( $pref[$value] == "1" ? "<input type='radio' name='{$value}' value='1' checked='checked' /> ".CUSTSIG_14 : "<input type='radio' name='{$value}' value='1' /> ".CUSTSIG_14)."&nbsp;&nbsp;".
			( $pref[$value] == "2" ? "<input type='radio' name='{$value}' value='2' checked='checked' /> ".CUSTSIG_15 : "<input type='radio' name='{$value}' value='2' /> ".CUSTSIG_15)."&nbsp;&nbsp;
			</td>
		</tr>";
	}

// Custom Fields.

$text .= pref_submit();
$text .= "</table></div>";

/* text render options */

if(!isset($pref['post_html']))
{
	$pref['post_html'] = '250';
	save_prefs();
}

$text .= "<div id='textpost' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' style='text-align:left;' colspan='2'>".PRFLAN_101."</td>
	</tr>

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
	<td class='forumheader3' style='width:50%;'>".PRFLAN_145."?:  <div class='smalltext'>".PRFLAN_146."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	<input type='radio' name='links_new_window' value='1'".($pref['links_new_window'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='links_new_window' value='0'".(!$pref['links_new_window'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
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
	".r_userclass('post_html',$pref['post_html'],'off','nobody,public,member,admin,main,classes')."
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

	<tr>
	<td class='forumheader3' style='width:50%;'>".PRFLAN_131.":  <div class='smalltext'>".PRFLAN_132."</div></td>
	<td class='forumheader3' style='width:50%; text-align: right;'>
	".r_userclass('php_bbcode',$pref['php_bbcode'],'off','nobody,admin,main,classes')."
	</td>
	</tr>\n

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
	<input class='tbox' type='text' name='defaultLanGeshi' size='10' value='".($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : "php")."' maxlength='20' />
	</td>
	</tr>
	";
	}



$text .= pref_submit();
$text .= "</table></div>";

// Security Options. .
$hasGD = extension_loaded("gd");

$text .= "<div id='security' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_47."</td>
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
	";
	if($hasGD)
	{
	$text .= "
	<input type='radio' name='signcode' value='1'".($pref['signcode'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='signcode' value='0'".(!$pref['signcode'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	";
	}
	else
	{
		$text .= PRFLAN_133;
	}
	$text .= "
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_81.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	";
	if($hasGD)
	{
	$text .= "
	<input type='radio' name='logcode' value='1'".($pref['logcode'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='logcode' value='0'".(!$pref['logcode'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	";
	}
	else
	{
		$text .= PRFLAN_133;
	}
	$text .= "
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_138.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	";
	if($hasGD)
	{
	$text .= "
	<input type='radio' name='fpwcode' value='1'".($pref['fpwcode'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='fpwcode' value='0'".(!$pref['fpwcode'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	";
	}
	else
	{
		$text .= PRFLAN_133;
	}
	$text .= "
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
	<td style='width:50%' class='forumheader3'>".PRFLAN_40."<br /><span class='smalltext'>".PRFLAN_41."</span></td>
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
	<textarea class='tbox' name='profanity_words' cols='59' rows='2' style='width:100%'>".$pref['profanity_words']."</textarea>
	<br />".PRFLAN_44."
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
	<td style='width:50%' class='forumheader3'>".PRFLAN_37."<br /><span class='smalltext'>".PRFLAN_91."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<select name='autoban' class='tbox'>";
    $autoban_list[0] = PRFLAN_113;
    $autoban_list[1] = PRFLAN_144;
    $autoban_list[2] = PRFLAN_142;
    $autoban_list[3] = PRFLAN_143;

	foreach($autoban_list as $ab=>$ab_title){
		$sel = ($pref['autoban'] == $ab) ? "selected='selected'" : "";
    	$text .= "<option value='$ab' $sel>".$ab_title."</option>\n";
	}

	$text .="</select></td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_170."<br /><span class='smalltext'>".PRFLAN_171."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='enable_rdns' value='1'".(varsettrue($pref['enable_rdns']) ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='enable_rdns' value='0'".(!varsettrue($pref['enable_rdns']) ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_139.":</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='adminpwordchange' value='1'".($pref['adminpwordchange'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='adminpwordchange' value='0'".(!$pref['adminpwordchange'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>
	";

$text .= pref_submit();

$text .= "</table></div>";

$text .= "<div id='comments' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_87."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_89.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='comments_icon' value='1'".($pref['comments_icon'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='comments_icon' value='0'".(!$pref['comments_icon'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_88.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='nested_comments' value='1'".($pref['nested_comments'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='nested_comments' value='0'".(!$pref['nested_comments'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_90.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='allowCommentEdit' value='1'".($pref['allowCommentEdit'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='allowCommentEdit' value='0'".(!$pref['allowCommentEdit'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_161.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='comments_disabled' value='1'".($pref['comments_disabled'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='comments_disabled' value='0'".(!$pref['comments_disabled'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_166.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='comments_emoticons' value='1'".($pref['comments_emoticons'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='comments_emoticons' value='0'".(!$pref['comments_emoticons'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td class='fcaption' style='text-align:left;' colspan='2'>".PRFLAN_209."</td>
	</tr>
	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_208.": </td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	".r_userclass('email_item_class',varset($pref['email_item_class'],e_UC_MEMBER),'off','nobody,admin,main,public,member,classes')."
	</td>
	</tr>
	";

$text .= pref_submit();

$text .= "</table></div>";


//Advanced Features
$text .= "<div id='advanced' style='display:none; text-align:center'>
	<table style='width:100%' class='fborder'>
	<tr>
	<td class='fcaption' title='".PRFLAN_80."' style='text-align:left;' colspan='2'>".PRFLAN_149."</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_147.":<br /><span class='smalltext'>".PRFLAN_148."</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='developer' value='1'".($pref['developer'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='developer' value='0'".(!$pref['developer'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_17."<br /><span class='smalltext'>&nbsp;</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='compress_output' value='1'".($pref['compress_output'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='compress_output' value='0'".(!$pref['compress_output'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_150."<br /><span class='smalltext'>&nbsp;</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>{$auth_dropdown}</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".PRFLAN_173."<br /><span class='smalltext'>&nbsp;</span></td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='check_updates' value='1'".($pref['check_updates'] ? " checked='checked'" : "")." /> ".PRFLAN_112."&nbsp;&nbsp;
	<input type='radio' name='check_updates' value='0'".(!$pref['check_updates'] ? " checked='checked'" : "")." /> ".PRFLAN_113."
	</td>
	</tr>

	";

$text .= pref_submit();

$text .= "</table></div>";
// END Advanced Features



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
	$var['comments']['text'] = PRFLAN_210;
	$var['advanced']['text'] = PRFLAN_149;
	show_admin_menu(LAN_OPTIONS, $action, $var, TRUE);
}
?>