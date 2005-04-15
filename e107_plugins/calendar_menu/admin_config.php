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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/admin_config.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-04-15 16:38:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
}
	
$lan_file = e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php";
include(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");
	
	
if (isset($_POST['updatesettings'])) {
	$pref['eventpost_admin'] = $_POST['eventpost_admin'];
	$pref['eventpost_headercss'] = $_POST['eventpost_headercss'];
	$pref['eventpost_daycss'] = $_POST['eventpost_daycss'];
	$pref['eventpost_todaycss'] = $_POST['eventpost_todaycss'];
	$pref['eventpost_evtoday'] = $_POST['eventpost_evtoday'];	
	$pref['eventpost_addcat'] = $_POST['eventpost_addcat'];
	$pref['eventpost_forum'] = $_POST['eventpost_forum'];	
	$pref['eventpost_super'] = $_POST['eventpost_super'];
	$pref['eventpost_dateformat'] = $_POST['eventpost_dateformat'];	
	$pref['eventpost_weekstart'] = $_POST['eventpost_weekstart'];
	$pref['eventpost_lenday'] = $_POST['eventpost_lenday'];			
	save_prefs();
	$message = EC_LAN_75; // "Calendar settings updated.";
}
	
require_once(e_ADMIN."auth.php");
	
if ($message) {
	$ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:97%' class='fborder'>
	<tr>
	<td style='vertical-align:top;' colspan='2' class='fcaption'>".EC_LAN_78." </td>
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_76." </td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_admin", $pref['eventpost_admin'], "off", 'public, nobody, member, admin, classes')."
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_100." </td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_addcat", $pref['eventpost_addcat'], "off",  'public, nobody, member, admin, classes')."
	<br /><em>".EC_LAN_101."</em>
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_104." </td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>". r_userclass("eventpost_super", $pref['eventpost_super'], "off",  'public, nobody, member, admin, classes')."
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_84."<br /><span class='smalltext'><em>".EC_LAN_85."</em></span></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_headercss' size='20' value='".$pref['eventpost_headercss']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_86."<br /><span class='smalltext'><em>".EC_LAN_87."</em></span></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_daycss' size='20' value='".$pref['eventpost_daycss']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_88."<br /><span class='smalltext'><em>".EC_LAN_89."</em></span></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_todaycss' size='20' value='".$pref['eventpost_todaycss']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_122."<br /><span class='smalltext'><em>".EC_LAN_89."</em></span></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='eventpost_evtoday' size='20' value='".$pref['eventpost_evtoday']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_102."<br /><span class='smalltext'><em>".EC_LAN_103."</em></span></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='checkbox' name='eventpost_forum' value='1' ".
	($pref['eventpost_forum']==1?" checked='checked' ":"")." />
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_114."</td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>
	<select name='eventpost_weekstart' class='tbox'>
	<option value='sun' ".
	($pref['eventpost_weekstart']=='sun'?" selected='selected' ":"")." />".EC_LAN_115."</option>
	<option value='mon' ".
	($pref['eventpost_weekstart']=='mon'?" selected='selected' ":"")." />".EC_LAN_116."</option>
	</select>
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_117."<br /></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>
	<select name='eventpost_lenday' class='tbox'>
	<option value='1' ".
	($pref['eventpost_lenday']=='1'?" selected='selected' ":"")." /> 1 </option>
	<option value='2' ".
	($pref['eventpost_lenday']=='2'?" selected='selected' ":"")." /> 2 </option>
	<option value='3' ".
	($pref['eventpost_lenday']=='3'?" selected='selected' ":"")." /> 3 </option>
	</select>
	</td>
	</tr>
	<tr>
	<td style='width:40%;vertical-align:top;' class='forumheader3'>".EC_LAN_118."<br /></td>
	<td style='width:60%;vertical-align:top;' class='forumheader3'>
	<select name='eventpost_dateformat' class='tbox'>
	<option value='my' ".
	($pref['eventpost_dateformat']=='my'?" selected='selected' ":"")." />".EC_LAN_119."</option>
	<option value='ym' ".
	($pref['eventpost_dateformat']=='ym'?" selected='selected' ":"")." />".EC_LAN_120."</option>
	</select>
	</td>
	</tr>   
	<tr style='vertical-align:top'>
	<td colspan='2'  style='text-align:left' class='fcaption'>
	<input class='button' type='submit' name='updatesettings' value='".EC_LAN_77."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender("<div style='text-align:center'>".EC_LAN_78."</div>", $text);
require_once(e_ADMIN."footer.php");
	
?>