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
|     $Source: /cvs_backup/e107_0.7/e107_admin/db.php,v $
|     $Revision: 1.15 $
|     $Date: 2005-11-23 13:25:10 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
$e_sub_cat = 'database';

if (isset($_POST['db_update'])) {
	header("location: ".e_ADMIN."e107_update.php");
	exit;
}

if (isset($_POST['verify_sql'])) {
	header("location: ".e_ADMIN."db_verify.php");
	exit;
}

require_once("auth.php");
if (isset($_POST['optimize_sql'])) {
	optimizesql($mySQLdefaultdb);
	require_once("footer.php");
	exit;
}

if (isset($_POST['backup_core'])) {
	backup_core();
	message_handler("MESSAGE", DBLAN_1);
}



$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>\n
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_15."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='db_update' value='".DBLAN_16."' /></td>
	</tr>
	
	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_4."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='verify_sql' value='".DBLAN_5."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_6."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='optimize_sql' value='".DBLAN_7."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_8."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='backup_core' value='".DBLAN_9."' />
	<input type='hidden' name='sqltext' value='$sqltext' />
	</td></tr>
	</table>
	</form>
	</div>";

$ns->tablerender(DBLAN_10, $text);

function backup_core() {
	global $pref, $sql;
	$tmp = base64_encode((serialize($pref)));
	if (!$sql->db_Insert("core", "'pref_backup', '{$tmp}' ")) {
		$sql->db_Update("core", "e107_value='{$tmp}' WHERE e107_name='pref_backup'");
	}
}

function optimizesql($mySQLdefaultdb) {

	$result = mysql_list_tables($mySQLdefaultdb);
	while ($row = mysql_fetch_row($result)) {
		mysql_query("OPTIMIZE TABLE ".$row[0]);
	}

	$str = "
		<div style='text-align:center'>
		<b>".DBLAN_11." $mySQLdefaultdb ".DBLAN_12.".</b>

		<br /><br />

		<form method='POST' action='".e_SELF."'>
		<input class='button' type='submit' name='back' value='".DBLAN_13."' />
		</form>
		</div>
		<br />";
	$ns = new e107table;
	$ns->tablerender(DBLAN_14, $str);

}

require_once("footer.php");

?>