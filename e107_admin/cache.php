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
|     $Source: /cvs_backup/e107_0.8/e107_admin/cache.php,v $
|     $Revision: 1.4 $
|     $Date: 2008-11-02 11:04:29 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("C")) 
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'cache';
require_once("auth.php");
require_once(e_HANDLER."cache_handler.php");
$ec = new ecache;
if ($pref['cachestatus'] == '2') 
{
	$pref['cachestatus'] = '1';
	save_prefs();
}

if(!is_writable(e_CACHE))
{
	$ns->tablerender(CACLAN_3, CACLAN_10."<br />(".$CACHE_DIRECTORY.")");
	require_once("footer.php");
	exit;
}

if (isset($_POST['submit_cache']))
{
	if ($pref['cachestatus'] != $_POST['cachestatus'] || $pref['syscachestatus'] != $_POST['syscachestatus'])
	{
		$pref['cachestatus'] = $_POST['cachestatus'];
		$pref['syscachestatus'] = $_POST['syscachestatus'];
		save_prefs();
		$admin_log->log_event('CACHE_01',$pref['syscachestatus'].', '.$pref['cachestatus'],E_LOG_INFORMATIVE,'');
		$ec->clear();
		$ec->clear_sys();
		$update = true;
		admin_update($update, 'update', CACLAN_4);
	}
}

if (isset($_POST['empty_syscache'])) 
{
	$ec->clear_sys();
	$admin_log->log_event('CACHE_02',$pref['syscachestatus'].', '.$pref['cachestatus'],E_LOG_INFORMATIVE,'');
	$ns->tablerender(LAN_UPDATE, "<div style='text-align:center'><b>".CACLAN_15."</b></div>");
}

if (isset($_POST['empty_cache'])) 
{
	$ec->clear();
	$admin_log->log_event('CACHE_03',$pref['syscachestatus'].', '.$pref['cachestatus'],E_LOG_INFORMATIVE,'');
	$ns->tablerender(LAN_UPDATE, "<div style='text-align:center'><b>".CACLAN_6."</b></div>");
}


	
$syscache_files = glob($e107->file_path.$FILES_DIRECTORY."cache/S_*.*");
$cache_files = glob($e107->file_path.$FILES_DIRECTORY."cache/C_*.*");

$syscache_files_num = count($syscache_files);
$cache_files_num = count($cache_files);

$sys_count = CACLAN_17." ".$syscache_files_num." ".($syscache_files_num != 1 ? CACLAN_19 : CACLAN_18);
$nonsys_count = CACLAN_17." ".$cache_files_num." ".($cache_files_num != 1 ? CACLAN_19 : CACLAN_18);

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td colspan='3' class='fcaption'>".CACLAN_1."</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:60%;'>".CACLAN_11.":  <div class='smalltext'>".CACLAN_13."</div><br />{$nonsys_count}</td>
	<td class='forumheader3' style='width:20%'>
	<input type='radio' name='cachestatus' value='1'".($pref['cachestatus'] ? " checked='checked'" : "")." /> ".LAN_ENABLED."&nbsp;&nbsp;
	<input type='radio' name='cachestatus' value='0'".(!$pref['cachestatus'] ? " checked='checked'" : "")." /> ".LAN_DISABLED."&nbsp;&nbsp;
    </td>
	<td class='forumheader3' style='width:20%'> <input class='button' type='submit' name='empty_cache' value=\"".CACLAN_5."\" />
	</td>
	</tr>

	<tr>
	<td class='forumheader3' style='width:60%;'>".CACLAN_12.":  <div class='smalltext'>".CACLAN_14."</div><br />{$sys_count}</td>
	<td class='forumheader3' style='width:20%'>
	<input type='radio' name='syscachestatus' value='1'".($pref['syscachestatus'] ? " checked='checked'" : "")." /> ".LAN_ENABLED."&nbsp;&nbsp;
	<input type='radio' name='syscachestatus' value='0'".(!$pref['syscachestatus'] ? " checked='checked'" : "")." /> ".LAN_DISABLED."&nbsp;&nbsp;
	</td>
	<td class='forumheader3' style='width:20%'>
	<input class='button' type='submit' name='empty_syscache' value=\"".CACLAN_16."\" />
	</td>
	</tr>

 
	<tr style='vertical-align:top'>
	<td colspan='3' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='submit_cache' value=\"".CACLAN_2."\" />
	</td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender(CACLAN_3, $text);
	
require_once("footer.php");
?>