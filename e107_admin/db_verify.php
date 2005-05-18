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
|     $Source: /cvs_backup/e107_0.7/e107_admin/db_verify.php,v $
|     $Revision: 1.14 $
|     $Date: 2005-05-18 03:08:38 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");

$filename = "sql/core_sql.php";
@$fd = fopen ($filename, "r");
$sql_data = @fread($fd, filesize($filename));
@fclose ($fd);

if (!$sql_data) {
	echo DBLAN_1."<br /><br />";
	exit;
}

$tables["core"] = $sql_data;

if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	exit;
}

//Get installed plugins
if($sql->db_Select("plugin","plugin_path","plugin_installflag = '1'")){
	while($row = $sql->db_Fetch()){
		$pluginList[] = $row['plugin_path'];
	}
}

//Get any plugin _sql.php files
$handle = opendir(e_PLUGIN);
$c = 1;
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file) && in_array($file, $pluginList))
	{
		$plugin_handle = opendir(e_PLUGIN.$file."/");
		while (false !== ($file2 = readdir($plugin_handle)))
		{
			if (preg_match("/(.*?)_sql.php/", $file2, $matches))
			{
				$filename = e_PLUGIN.$file."/".$file2;
				@$fd = fopen ($filename, "r");
				$sql_data = @fread($fd, filesize($filename));
				@fclose ($fd);
				$tables[$matches[1]] = $sql_data;
			}
		}
		closedir($plugin_handle);
	}
}
closedir($handle);


function read_tables($tab) {
	global $tablines;
	global $table_list;
	global $tables,$sql,$pref;

	$file = split("\n", $tables[$tab]);
	foreach($file as $line) {
		$line = ltrim(stripslashes($line));
		if (preg_match("/CREATE TABLE (.*) /", $line, $match)) {
			$table_list[$match[1]] = 1;
			$current_table = $match[1];
			$x = 0;
			$cnt = 0;
		}

		if (preg_match("/TYPE=/", $line, $match)) {
			$current_table = "";
		}

		if ($current_table && $x) {
			$tablines[$current_table][$cnt++] = $line;

		}

		$x = 1;
	}

// Get multi-language tables as well
	if($pref['multilanguage']){
		$langs = table_list();
		foreach($table_list as $name=>$stuff){
			if($langs[$name]){
				$ltab = $langs[$name];
				$table_list[$ltab] = 1;
				$tablines[$ltab] = $tablines[$name];
			}
		}
	}

}

function get_current($tab, $prefix = "") {
	if (!$prefix) {
		$prefix = MPREFIX;
	}
	$result = mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
	$qry = 'SHOW CREATE TABLE `'.$prefix.$tab."`";
	$z = mysql_query($qry);
	if ($z) {
		$row = mysql_fetch_row($z);
		return str_replace("`", "", stripslashes($row[1]));
	} else {

		return FALSE;
	}
}

function check_tables($what) {
	global $tablines;
	global $table_list;
	global $ns;
	$cur=0;
	$table_list = "";
	read_tables($what);

	$text = "<form method='POST' action='".e_SELF."' id='checktab'>
		<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' style='text-align:center'>".DBLAN_4."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_5."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_6."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_7."</td>
		</tr>";
	foreach(array_keys($table_list) as $k) {
		$text .= "<tr>";
		$prefix = MPREFIX;
		$current_tab = get_current($k, $prefix);
		unset($fields);
		unset($xfields);
		if ($current_tab) {
			$lines = split("\n", $current_tab);
			$fieldnum = 0;
			foreach($tablines[$k] as $x) {
				$fieldnum++;
				$ffound = 0;
				list($fname, $fparams) = split(" ", $x, 2);
				if ($fname == "KEY") {
					list($key, $keyname, $keyparms) = split(" ", $x, 3);
					$fname = $key." ".$keyname;
					$fparams = $keyparms;
				}
				$fields[$fname] = 1;
				$fparams = ltrim(rtrim($fparams));
				$fparams = preg_replace("/\r?\n$|\r[^\n]$|,$/", "", $fparams);

			if(eregi("lan_",$k) && $cur != 1){
				$text .= "<tr><td colspan='6' class='fcaption'>".ADLAN_132."</td></tr>";
				$cur = 1;
			};



				$text .= "<tr><td class='forumheader3'>$k</td><td class='forumheader3'>$fname";
				if (preg_match("#KEY#", $fparams)) {
					$text .= " $fparams";
				}
				$text .= "</td>";
				$s = 0;
				$xfieldnum = -1;
				foreach($lines as $l) {
					$xfieldnum++;
					list($xl, $tmp) = split("\n", $l, 2);
					$xl = ltrim(rtrim(stripslashes($xl)));
					$xl = preg_replace("/\r?\n$|\r[^\n]$/", "", $xl);
					list($xfname, $xfparams) = split(" ", $xl, 2);

					if ($xfname == "KEY") {
						list($key, $keyname, $keyparms) = split(" ", $xl, 3);
						$xfname = $key." ".$keyname;
						$xfparams = $keyparms;
					}

					if ($xfname != "CREATE" && $xfname != ")") {
						$xfields[$xfname] = 1;
					}
					$xfparams = preg_replace("/,$/", "", $xfparams);
					$fparams = preg_replace("/,$/", "", $fparams);
					if ($xfname == $fname) {
						$ffound = 1;
						if (strcasecmp($fparams, $xfparams) != 0) {
							$text .= "<td class='forumheader' style='text-align:center'>".DBLAN_8."</td>";
							$text .= "<td class='forumheader3' style='text-align:center'>".DBLAN_9."<div class='indent'>".$xfparams."</div><b>".DBLAN_10."</b><div class='indent'>".$fparams." <br />".fix_form($k,$fname,$fparams,"alter")."</div></td>";
							$fix_active = TRUE;
						} elseif($fieldnum != $xfieldnum) {
							$text .= "<td class='fcaption' style='text-align:center'>".DBLAN_5." ".DBLAN_8."</td>
								<td class='forumheader3' style='text-align:center'>".DBLAN_9." #{$xfieldnum}<br />".DBLAN_10." #{$fieldnum}</td>";
						} else {
							$text .= "<td class='forumheader3' style='text-align:center;'>OK</td>
								<td class='forumheader3' style='text-align:center'>&nbsp;</td>";
						}
					}
				}

				if ($ffound == 0) {
					$text .= "<td class='forumheader' style='text-align:center'><strong><em>".DBLAN_11."</em></strong></td>
						<td class='forumheader3' style='text-align:center'><b>".DBLAN_10." [$fparams]</b><br />".fix_form($k,$fname,$fparams,"insert",$prev_fname)."<br /></td>";
					$fix_active = TRUE;
				}
				$prev_fname = $fname;
				$text .= "</tr>\n";
			}
			foreach(array_keys($xfields) as $tf) {
				if (!$fields[$tf] && $k != "user_extended") {
					$fix_active = TRUE;
					$text .= "<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>$tf</td><td class='forumheader3' style='text-align:center'><strong><em>".DBLAN_12."</em></strong></td><td class='forumheader3' style='text-align:center'>&nbsp;".fix_form($k,$tf,$fparams,"drop")."</td></tr>";
				}
			}
		} else {	// Table Missing.
			$text .= "<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>&nbsp;</td><td class='forumheader' style='text-align:center'>".DBLAN_13."<br /><td class='forumheader3' style='text-align:center'>&nbsp;".fix_form($k,$tf,$tablines[$k],"create")."</td></tr>";
			$fix_active = TRUE;
		}
	}
	$text .= "</table></div>";

	if($fix_active){
		$text .= "<div style='".ADMIN_WIDTH.";text-align:right'>
		<input class='button' type='submit' name='do_fix' value='".DBLAN_21."' /></div>\n";
	}

	foreach(array_keys($_POST) as $j) {
		if (preg_match("/table_(.*)/", $j, $mitch)) {
			$lx = $mitch[1];
			$text .= "<input type='hidden' name='table_{$lx}' value='1' />\n";
		}
	}
	$text .= "</form>";

	return $text;
}

global $table_list;

// -------------------- Table Fixing ------------------------------

if(isset($_POST['do_fix'])){
	$text = "<div><table class='fborder' style='width:100%'>";
	foreach( $_POST['fix_active'] as $key=>$val){
		$table= $_POST['fix_table'][$key][0];
		$field= $key;
		$newval= $_POST['fix_newval'][$key][0];
		$mode = $_POST['fix_mode'][$key][0];
		$after = $_POST['fix_after'][$key][0];

		if($mode == "alter"){
			$query = "ALTER TABLE `".MPREFIX.$table."` CHANGE `$field` `$field` $newval";
		}

		if($mode == "insert"){
			$query = "ALTER TABLE `".MPREFIX.$table."` ADD `$field` $newval AFTER $after";
		}

		if($mode == "drop"){
			$query = "ALTER TABLE `".MPREFIX.$table."` DROP `$field` ";
		}

		if($mode == "index"){
			$query = "ALTER TABLE `".MPREFIX.$table."` ADD INDEX (`$field`) ";
		}

		if($mode == "create"){
			$query = "CREATE TABLE ".MPREFIX.$table." ($newval) TYPE=MyISAM;";
		}


		$text .= "<tr><td class='forumheader3' style='vertical-align:top;width:70%'>".$query."</td><td class='forumheader3' style='vertical-align:top;width:30%'>";
		$text .= (mysql_query($query)) ? " - <b>".LAN_UPDATED."</b>" : " - <b>".LAN_UPDATED_FAILED."</b>";
		$text .= "</td></tr>";


	}
		$text .= "</table></div>";
		$text .="<div style='text-align:center'><br />
				<form method='POST' action='db.php'>
				<input class='button' type='submit' name='back' value='".DBLAN_17."' />
				</form>
				</div>";

	$ns -> tablerender(DBLAN_20, $text);
}



// ---------------------- Main Form and Submit. ------------------------
if (!$_POST) {
	$text = "
		<form method='POST' action='".e_SELF."'>
		<table border=0 align='center'>
		<tr><td>".DBLAN_14."<br /><br />";
	foreach(array_keys($tables) as $x) {
		$text .= "<input type='checkbox' name='table_".$x."'>".$x."<br />";
	}
	$text .= "
		<br /><input class='button' type='submit' value='".DBLAN_15."'>
		</td></tr></table></form>";
	$ns->tablerender(DBLAN_16, $text);
} else {
	foreach(array_keys($_POST) as $k) {
		if (preg_match("/table_(.*)/", $k, $match)) {
			$xx = $match[1];
			$str = "<br />
				<div style='text-align:center'>
				<form method='POST' action='db.php'>
				<input class='button' type='submit' name='back' value='".DBLAN_17."' />
				</form>
				</div>";
			$ns->tablerender(DBLAN_16." - $xx ".DBLAN_18, check_tables($xx).$str);
		}
	}
}



// --------------------------------------------------------------
function fix_form($table,$field, $newvalue,$mode,$after =''){

	if(eregi("KEY ",$field)){
		$field = chop(str_replace("KEY ","",$field));
		$mode = "index";
		$search = array("(",")");
		$newvalue = str_replace($search,"",$newvalue);
	}

	if($mode == "create"){
		$newvalue = implode("\n",$newvalue);
	}

	$text .= "<input type='checkbox'  name=\"fix_active[$field][]\" value='1' /> ".DBLAN_19."\n"; // 'attempt to fix'
	$text .= "<input type='hidden' name=\"fix_newval[$field][]\" value=\"$newvalue\" />\n";
	$text .= "<input type='hidden'  name=\"fix_table[$field][]\" value=\"$table\" / >\n";
	$text .= "<input type='hidden'  name=\"fix_mode[$field][]\" value=\"$mode\" / >\n";
	$text .= ($after) ? "<input type='hidden'  name=\"fix_after[$field][]\" value=\"$after\" / >\n" : "";

	return $text;
}

function table_list() {
	// grab default language lists.
	global $mySQLdefaultdb;

	$exclude[] = "banlist";		$exclude[] = "banner";
	$exclude[] = "cache";		$exclude[] = "core";
	$exclude[] = "online";		$exclude[] = "parser";
	$exclude[] = "plugin";		$exclude[] = "user";
	$exclude[] = "upload";		$exclude[] = "userclass_classes";
	$exclude[] = "rbinary";		$exclude[] = "session";
	$exclude[] = "tmp";	 		$exclude[] = "flood";
	$exclude[] = "stat_info";	$exclude[] = "stat_last";
	$exclude[] = "submit_news";	$exclude[] = "rate";
	$exclude[] = "stat_counter";$exclude[] = "user_extended";
	$exclude[] = "user_extended_struc";
	$exclude[] = "pm_messages";
	$exclude[] = "pm_blocks";

	//   print_r($search);

	$tables = mysql_list_tables($mySQLdefaultdb);
	while (list($temp) = mysql_fetch_array($tables)){
		$prefix = MPREFIX."lan_";

		if(preg_match("/^".$prefix."(.*)/", $temp, $match)){
			$e107tab = str_replace(MPREFIX, "", $temp);
			$pos = strrpos($match[1],"_")+1;
			$core = substr(str_replace("lan_","",$e107tab),$pos);
			if (str_replace($exclude, "", $e107tab)){
				$tabs[$core] = $e107tab;
			}
		}
	}

	return $tabs;
}




require_once(e_ADMIN."footer.php");
?>