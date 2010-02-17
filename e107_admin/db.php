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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms('0')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'database';

if (isset($_POST['db_update'])) 
{
	header("location: ".e_ADMIN."e107_update.php");
	exit;
}

if (isset($_POST['check_user'])) 
{
	header("location: ".e_ADMIN."check_user.php");
	exit;
}

if (isset($_POST['verify_sql'])) 
{
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


if (isset($_POST['optimize_sql'])) 
{
	optimizesql($mySQLdefaultdb);
	require_once("footer.php");
	exit;
}


if (isset($_POST['backup_core'])) 
{
	backup_core();
	message_handler("MESSAGE", DBLAN_1);
}

if(isset($_POST['delplug']))
{
	delete_plugin_entry();

}

if (isset($_POST['plugin_scan']) || e_QUERY == "plugin" || $_POST['delplug']) 
{
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
	<td style='width:70%' class='forumheader3'>".DBLAN_28."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='plugin_scan' value=\"".DBLAN_29."\" /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_19."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='pref_editor' value='".DBLAN_20."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_35."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='check_user' value='".DBLAN_36."' /></td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".DBLAN_8."</td>
	<td class='forumheader3' style='width:30%;text-align:center'><input class='button' style='width: 100%' type='submit' name='backup_core' value='".DBLAN_9."' />
	<input type='hidden' name='sqltext' value='{$sqltext}' />
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
  $error_messages = array(0 => DBLAN_31, 1 =>DBLAN_32, 2 =>DBLAN_33, 3 => DBLAN_34);
  $error_image = array("integrity_pass.png","integrity_fail.png","warning.png","blank.png");


		global $sql, $pref, $ns, $tp;
		require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table(); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs();  // generate global e_xxx_list prefs from plugin table.

		$ns -> tablerender(DBLAN_22, "<div style='text-align:center'>".DBLAN_23."<br /><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>");

		$text = "<form method='post' action='".e_ADMIN."db.php' id='plug_edit'>
				<div style='text-align:center'>  <table class='fborder' style='".ADMIN_WIDTH."'>
				<tr><td class='fcaption'>".DBLAN_24."</td>
				<td class='fcaption'>".DBLAN_25."</td>
				<td class='fcaption'>".DBLAN_26."<br />".DBLAN_30."</td>
				<td class='fcaption'>".DBLAN_27."</td>";

        $sql -> db_Select("plugin", "*", "plugin_id !='' order by plugin_path ASC"); // Must order by path to pick up duplicates. (plugin names may change).
		while($row = $sql-> db_Fetch())
		{
			$text .= "<tr>
				<td class='forumheader3'>".$tp->toHtml($row['plugin_name'],FALSE,"defs,emotes_off")."</td>
                <td class='forumheader3'>".$row['plugin_path']."</td>
				<td class='forumheader3'>";
				
			if (trim($row['plugin_addons']))
			{
			  $nl_code = '';
			  foreach(explode(',',$row['plugin_addons']) as $this_addon)
			  {
			    $ret_code = 3;		// Default to 'not checked
			    if (strpos($this_addon,'e_') === 0)
			    {
//			      echo "Checking: ".$row['plugin_path'].":".$this_addon."<br />";
			      $ret_code = $ep->checkAddon($row['plugin_path'],$this_addon);		// See whether spaces before opening tag or after closing tag
			    }
				$text .= "<div style='border-bottom:1px dotted #cccccc'>";
				$text .= "<img src='".e_IMAGE."fileinspector/".$error_image[$ret_code]."' alt=\"".$error_messages[$ret_code]."\" title=\"".$error_messages[$ret_code]."\" style='vertical-align:middle;height:16px;width:16px' />\n";
			    $text .= trim($this_addon);	// $ret_code - 0=OK, 1=content error, 2=access error
			    $text .= "</div>";
			  }
			}
			
			$text .= "</td>
				<td class='forumheader3' style='text-align:center'>";
            if($previous == $row['plugin_path'])
			{
				$delid 	= $row['plugin_id'];
				$delname = $row['plugin_name'];
				$text .= "<input class='button' type='submit' title='".LAN_DELETE."' value='Delete Duplicate' name='delplug[$delid]' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." ID:$delid [$delname]')\" />\n";
			}
			else
			{
            	$text .= ($row['plugin_installflag'] == 1) ? DBLAN_27 : " "; // "Installed and not installed";
			}
			$text .= "</td>
			</tr>";
			$previous = $row['plugin_path'];
		}
//		$text .= "<tr><td colspan='4' class='forumheader3'>".DBLAN_30."</td></tr>";
        $text .= "</table></div></form>";
        $ns -> tablerender(ADLAN_CL_7, $text);

}


function pref_editor()
{
		global $pref,$ns,$tp;
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
			$ptext = (is_array($val)) ? "<pre>".print_r($val,TRUE)."</pre>" : htmlspecialchars($val);
            $ptext = $tp -> textclean($ptext, 80);

			$text .= "
				<tr>
				<td class='forumheader3' style='width:40px;text-align:center'><input type='checkbox' name='delpref2[$key]' value='1' /></td>
				<td class='forumheader3'>".$key."</td>
                <td class='forumheader3' style='width:50%'>".$ptext."</td>
				<td class='forumheader3' style='width:20px;text-align:center'>
					<input type='image' title='".LAN_DELETE."' src='".ADMIN_DELETE_ICON_PATH."' name='delpref[$key]' onclick=\"return jsconfirm('".LAN_CONFIRMDEL." [$key]')\" />
       			</td>
			</tr>";
		}
        $text .= "<tr><td class='forumheader' colspan='4' style='text-align:center'>
			<input class='button' type='submit' title='".LAN_DELETE."' value=\"".DBLAN_21."\" name='delpref_checked' onclick=\"return jsconfirm('".LAN_CONFIRMDEL."')\" />
			</tr>
		</table></div></form>";
        $text .= "<div style='text-align:center'><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>\n";
        $ns -> tablerender(DBLAN_20, $text);

		return $text;

}



function del_pref_val(){
	global $pref,$ns,$e107cache;
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

	$message = "<div><br /><ul>".$deleted_list."</ul></div>
	<div style='text-align:center'><br /><a href='".e_SELF."'>".DBLAN_13."</a></div>";
 	save_prefs();
	$e107cache->clear();
    $ns -> tablerender(LAN_DELETED,$message);

}

function delete_plugin_entry()
{
	global $sql,$ns;
	$del = array_keys($_POST['delplug']);
	$message = ($sql -> db_Delete("plugin", "plugin_id='".intval($del[0])."' LIMIT 1")) ? LAN_DELETED : LAN_DELETED_FAILED;
    $caption = ($message == LAN_DELETED) ? LAN_DELETED : LAN_ERROR;
    $ns -> tablerender($caption,$message);
}

require_once("footer.php");

?>
