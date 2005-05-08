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
|     $Revision: 1.10 $
|     $Date: 2005-05-08 17:13:45 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:../index.php");
	 exit;
}

if (isset($_POST['updateStats']))
{
	header("location: ".e_PLUGIN."log/admin_updateroutine.php");
	exit;
}

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

define("LOGPATH", e_PLUGIN."log/");
	
@include_once(LOGPATH."languages/admin/".e_LANGUAGE.".php");
@include_once(LOGPATH."languages/admin/English.php");



if(IsSet($_POST['wipeSubmit']))
{
	foreach($_POST['wipe'] as $key => $wipe)
	{
		switch($key)
		{
			case "statWipePage":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='pageTotal' ");
			break;
			case "statWipeBrowser":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statBrowser' ");
			break;
			case "statWipeOs":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statOs' ");
			break;
			case "statWipeScreen":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statScreen' ");
			break;
			case "statWipeDomain":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statDomain' ");
			break;
			case "statWipeRefer":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statReferer' ");
			break;
			case "statWipeQuery":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statQuery' ");
			break;
		}
	}
	$message = ADSTAT_L25;
}



if(!is_writable(LOGPATH."logs")) {
	$message = "<b>You must set the permissions of the e107_plugins/log/logs folder to 777 (chmod 777)</b>";
}


	
if (isset($_POST['updatesettings'])) {
	$pref['statActivate'] = $_POST['statActivate'];
	$pref['statCountAdmin'] = $_POST['statCountAdmin'];
	$pref['statUserclass'] = $_POST['statUserclass'];
	$pref['statBrowser'] = $_POST['statBrowser'];
	$pref['statOs'] = $_POST['statOs'];
	$pref['statScreen'] = $_POST['statScreen'];
	$pref['statDomain'] = $_POST['statDomain'];
	$pref['statRefer'] = $_POST['statRefer'];
	$pref['statQuery'] = $_POST['statQuery'];
	$pref['statRecent'] = $_POST['statRecent'];
	$pref['statDisplayNumber'] = $_POST['statDisplayNumber'];
	
	save_prefs();
	header("location:".e_SELF."?u");
	exit;
}
	
if (e_QUERY == "u") {
	$message = ADSTAT_L17;
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
	<td style='width:50%; text-align: right;' class='forumheader3'>".r_userclass("statUserclass", $pref['statUserclass'],'off','public, member, admin, classes')."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L20."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input type='radio' name='statCountAdmin' value='1'".($pref['statCountAdmin'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statCountAdmin' value='0'".(!$pref['statCountAdmin'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."
	</td>
	</tr>
	 
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L21."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input class='tbox' type='text' name='statDisplayNumber' size='8' value='".$pref['statDisplayNumber']."' maxlength='3' />
	</td>
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

	".ADSTAT_L19."&nbsp;&nbsp;
	<input type='radio' name='statRecent' value='1'".($pref['statRecent'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statRecent' value='0'".(!$pref['statRecent'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

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
	<br /><input class='button' type='submit' name='wipeSubmit' value='".ADSTAT_L12."' />
	</td>
	</tr>
	
	";

	if($sql -> db_Select("stat_counter "))
	{

		$text .= "<tr>
		<td style='width:50%' class='forumheader3'>".ADSTAT_L22."<br /><span class='smalltext'>".ADSTAT_L23."</td>
		<td style='width:50%; text-align: right;' class='forumheader3'><input class='button' type='submit' name='updateStats' value='".ADSTAT_L24."' />
		</td>
		</tr>
		";
	}

	$text .= "
	

	 
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