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
|     $Revision: 1.16 $
|     $Date: 2006-07-08 21:52:42 $
|     $Author: e107coders $
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



if(isset($_POST['delpref']) || (isset($_POST['delpref_checked']) && isset($_POST['delpref2']))  )
{
	del_pref_val();
}


if(isset($_POST['pref_editor']) || isset($_POST['delpref']) || isset($_POST['delpref_checked']))
{
	pref_editor();
	require_once("footer.php");
	exit;
}


if (isset($_POST['optimize_sql'])) {
	optimizesql($mySQLdefaultdb);
	require_once("footer.php");
	exit;
}


if (isset($_POST['backup_core'])) {
	backup_core();
	message_handler("MESSAGE", DBLAN_1);
}


if (isset($_POST['plugin_scan'])) {
	plugin_viewscan();
	require_once("footer.php");
	exit;
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
	<td style='width:70%' class='forumheader3'>Click button to scan plugin directories for changes (experimental)</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='plugin_scan' value='Scan plugin directories' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_19."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='pref_editor' value='".DBLAN_20."' /></td>
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

function plugin_viewscan()
{
		// experimental - don't expect LANs yet.

		global $sql, $pref, $ns;
		require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table(); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs();  // generate global e_xxx_list prefs from plugin table.

		$ns -> tablerender("Plugin View and Scan", "<div style='text-align:center'>Scan Completed<br /><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>");

		$text = "<div style='text-align:center'>  <table class='fborder' style='".ADMIN_WIDTH."'>
				<tr><td class='fcaption'>Name</td>
				<td class='fcaption'>Path</td>
				<td class='fcaption'>Installed plugin addons</td>";

        $sql -> db_Select("plugin", "*", "plugin_installflag='1' order by plugin_name ASC");
		while($row = $sql-> db_Fetch()){
			$text .= "<tr>
				<td class='forumheader3'>".$row['plugin_name']."</td>
                <td class='forumheader3'>".$row['plugin_path']."</td>
				<td class='forumheader3'>".str_replace(",","<br />",$row['plugin_addons'])."</td>
			</tr>";
		}
        $text .= "</table></div>";
        $ns -> tablerender(ADLAN_CL_7, $text);

}


function pref_editor()
{
		global $pref,$ns;
		ksort($pref);

		$text = "<form method='post' action='".e_ADMIN."db.php' id='pref_edit'>
				<div style='text-align:center'>
				<table class='fborder' style='".ADMIN_WIDTH."'>
                <tr>
					<td class='fcaption'>".LAN_DELETE."</td>
					<td class='fcaption'>".DBLAN_17."</td>
					<td class='fcaption'>".DBLAN_18."</td>
					<td class='fcaption'>".LAN_OPTIONS."</td>
				</tr>";

         foreach($pref as $key=>$val)
		{
			$text .= "
			<tr>
				<td class='forumheader3' style='width:40px;text-align:center'><input type='checkbox' name='delpref2[$key]' value='1' /></td>
				<td class='forumheader3'>".$key."</td>
                <td class='forumheader3' style='width:50%'>".show_pref_val($val)."</td>
				<td class='forumheader3' style='width:20px;text-align:center'>
					<input type='image' title='".LAN_DELETE."' src='".ADMIN_DELETE_ICON_PATH."' name='delpref[$key]' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." [$key]')\" />
       			</td>
			</tr>";
		}
        $text .= "<tr><td class='forumheader' colspan='4' style='text-align:center'>
			<input class='button' type='submit' title='".LAN_DELETE."' value=\"".DBLAN_21."\" name='delpref_checked' onclick=\"return jsconfirm('".LAN_CONFIRMDEL."')\" />
			</tr>
		</table></div></form>";

        $ns -> tablerender(DBLAN_20, $text);

		return $text;

}

function show_pref_val($val){
global $tp;
	if(is_array($val))
	{
		foreach($val as $k=>$v)
		{
          	$ptext .= $k ." => ".show_pref_val($v)."<br />";
		}
	}
	else
	{
    	$ptext .= htmlspecialchars($val);
	}

	return $tp -> textclean($ptext, 80);

}

function del_pref_val(){
	global $pref,$ns;
	$del = array_keys($_POST['delpref']);
	$delpref = $del[0];

	if($delpref)
	{
   		unset($pref[$delpref]);
    	$deleted_list .= "<li>".$delpref."</li>";
	}
	if($_POST['delpref2']){

    	foreach($_POST['delpref2'] as $k=>$v)
		{
            $deleted_list .= "<li>".$k."</li>";
			unset($pref[$k]);
		}
	}

	$message = "<div>".LAN_DELETED." : <br /><br /><ul>".$deleted_list."</ul></div>";
 	save_prefs();
    $ns -> tablerender(LAN_DELETED,$message);

}

require_once("footer.php");

?>