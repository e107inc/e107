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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/log/admin_config.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-02-07 13:34:39 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

define("LOGPATH", e_PLUGIN."log/");
	
@include_once(LOGPATH."languages/admin/".e_LANGUAGE.".php");
@include_once(LOGPATH."languages/admin/English.php");

if (!getperms("P")) {
	header("location:../index.php");
	 exit;
}

if(!is_writable(LOGPATH."logs")) {
	$message = "<b>You must set the permissions of the e107_plugins/log/logs folder to 777 (chmod 777)</b>";
}


	
if (isset($_POST['updatesettings'])) {
	$pref['statActivate'] = $_POST['statActivate'];
	$pref['statUserclass'] = $_POST['statUserclass'];
	$pref['statBrowser'] = $_POST['statBrowser'];
	$pref['statOs'] = $_POST['statOs'];
	$pref['statScreen'] = $_POST['statScreen'];
	$pref['statDomain'] = $_POST['statDomain'];
	$pref['statRefer'] = $_POST['statRefer'];
	$pref['statQuery'] = $_POST['statQuery'];
	save_prefs();
	header("location:".e_SELF."?u");
	exit;
}
	
if (e_QUERY == "u") {
	$message = ADSTAT_L17;
}
	
if (isset($_POST['wipe'])) {
	if (isset($_POST['log_wipe_info'])) {
		$sql->db_Delete("stat_info", "");
		$sql->db_Delete("stat_last", "");
	}
	if (isset($_POST['log_wipe_counter'])) {
		$sql->db_Delete("stat_counter", "");
	}
	 
	$message = LOGLAN_2;
}
	
if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	 
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L4."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input type='radio' name='statActivate' value='1'".($pref['statActivate'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statActivate' value='0'".(!$pref['statActivate'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L18."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>".r_userclass("statUserclass", $pref['statUserclass'])."</td>
	</tr>
	 
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L5."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	".ADSTAT_L6."&nbsp;&nbsp;
	<input type='radio' name='statBrowser' value='1'".($pref['statBrowser'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statBrowser' value='0'".(!$pref['statBrowser'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L7."&nbsp;&nbsp;
	<input type='radio' name='statOs' value='1'".($pref['statOs'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statOs' value='0'".(!$pref['statOs'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L8."&nbsp;&nbsp;
	<input type='radio' name='statScreen' value='1'".($pref['statScreen'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statScreen' value='0'".(!$pref['statScreen'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L9."&nbsp;&nbsp;
	<input type='radio' name='statDomain' value='1'".($pref['statDomain'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statDomain' value='0'".(!$pref['statDomain'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L10."&nbsp;&nbsp;
	<input type='radio' name='statRefer' value='1'".($pref['statRefer'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statRefer' value='0'".(!$pref['statRefer'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L11."&nbsp;&nbsp;
	<input type='radio' name='statQuery' value='1'".($pref['statQuery'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statQuery' value='0'".(!$pref['statQuery'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	</td>
	</tr>
	
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L12."<br /><span class='smalltext'>".ADSTAT_L13."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	".ADSTAT_L14."<input type='checkbox' name='wipe[statWipePage]' value='1' /><br />
	".ADSTAT_L6."<input type='checkbox' name='wipe[statWipeBrowser]' value='1' /><br />
	".ADSTAT_L7." <input type='checkbox' name='wipe[statWipeOs]' value='1' /><br />
	".ADSTAT_L8." <input type='checkbox' name='wipe[statWipeScreen]' value='1' /><br />
	".ADSTAT_L9."<input type='checkbox' name='wipe[statWipeDomain]' value='1' /><br />
	".ADSTAT_L10."<input type='checkbox' name='wipe[statWipeRefer]' value='1' /><br />
	".ADSTAT_L11."<input type='checkbox' name='wipe[statWipeQuery]' value='1' /><br />



	<br /><input class='button' type='submit' name='wipe' value='".ADSTAT_L12."' />
	</td>
	</tr>


	 
	<tr>
	<td colspan='2'  style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".ADSTAT_L15."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender(ADSTAT_L16, $text);
require_once("footer.php");
	
?>