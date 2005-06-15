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
|     $Source: /cvs_backup/e107_0.7/e107_admin/language.php,v $
|     $Revision: 1.20 $
|     $Date: 2005-06-15 17:47:26 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'language';
require_once("auth.php");
if (!getperms("ML"))
{
	header("location:".e_BASE."index.php");
	exit;
}
require_once(e_HANDLER."form_handler.php");
$rs = new form;

$tabs = table_list(); // array("news","content","links");
// list of languages.
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
$lanlist = $fl->get_dirs(e_LANGUAGEDIR);

if (isset($_POST['submit_prefs']) ) {

	$pref['multilanguage'] = $_POST['multilanguage'];
	$pref['sitelanguage'] = $_POST['sitelanguage'];

	save_prefs();
	$ns->tablerender("Saved", "<div style='text-align:center'>".LAN_SAVED."</div>");

}
// ----------------- delete tables ---------------------------------------------
if (isset($_POST['del_existing']) && $_POST['lang_choices']) {

	$lang = strtolower($_POST['lang_choices']);
	foreach ($tabs as $del_table) {
		if (db_Table_exists($lang."_".$del_table)) {
			$message .= (mysql_query("DROP TABLE ".$mySQLprefix."lan_".$lang."_".$del_table)) ? $_POST['lang_choices']." ".$del_table." deleted<br />" :
			 $_POST['lang_choices']." $del_table couldn't be deleted<br />";
		}
	}
	$ns->tablerender("Result", $message);
}

// ----------create tables -----------------------------------------------------

if (isset($_POST['create_tables']) && $_POST['language']) {

	$table_to_copy = array();
	$lang_to_create = array();
	$message = "";

	foreach ($tabs as $value) {
		if ($_POST[$value]) {
			$lang = strtolower($_POST['language']);
            $copdata = ($_POST['copydata_'.$value]) ? 1 : 0;

			if (copy_table($value, "lan_".$lang."_".$value, $_POST['drop'],$copdata)) {
				$message .= " ".$_POST['language']." ".$value." created<br />";
			} else {
				$message .= (!$_POST['drop'])? " ".$_POST['language']." ".$value." ".LANG_LAN_00."<br />" : $_POST['language']." ".$value." ".LANG_LAN_01."<br />";
			}
		} elseif(db_Table_exists($lang."_".$value)) {
			if ($_POST['remove']) {
				// Remove table.
				$message .= (mysql_query("DROP TABLE ".$mySQLprefix."lan_".$lang."_".$value)) ? $_POST['language']." ".$value." ".LAN_DELETED."<br />" :  $_POST['language']." $value ".LANG_LAN_02."<br />";
			} else {
				// leave table.
				$message = $_POST['language']." ".$value." was disabled but left intact.";
			}
		}
	}
	$ns->tablerender("Result", $message);
}

// ------------- render form ---------------------------------------------------



$caption = LANG_LAN_16;
$text = MLAD_LAN_4."<br /><br />";


// Choose Language to Edit:
$text = "<div style='text-align:center'>
	<div style='".ADMIN_WIDTH."; height:150px; overflow:auto; margin-left: auto; margin-right: auto;'>";

$text .= "<table class='fborder' style='width:99%; margin-top: 1px;'>\n";
$text .= "<tr><td class='fcaption'>".ADLAN_132."</td>";
$text .= "<td class='fcaption'>".LANG_LAN_03."</td><td class='fcaption'>".LAN_OPTIONS."</td>";
$text .= "</tr>\n\n";
sort($lanlist);
for($i = 0; $i < count($lanlist); $i++) {
	$installed = 0;

	$text .= "<tr><td class='forumheader3' style='width:30%'>".$lanlist[$i]."</td><td class='forumheader3'>\n";
	foreach ($tabs as $tab_name) {
		if (db_Table_exists(strtolower($lanlist[$i])."_".$tab_name)) {
			$text .= $tab_name.", ";
			$installed++;
		}
	}

	$text .= (!$installed)? "<div style='text-align:center'><i>".LANG_LAN_05."</i></div>" :
	 "";
	$text .= "</td><td class='forumheader3' style='width:10%;white-space:nowrap;text-align:right'>\n";
	$text .= $rs->form_open("post", e_SELF."?modify", "lang_form_".str_replace(" ", "_", $lanlist[$i]));
	$text .= "<div style='text-align: center'>\n";

	if ($installed) {
		$text .= " <input type='submit' class='button' name='edit_existing' value='".LAN_EDIT."' />\n";
		$text .= " <input type='submit' class='button' name='del_existing' value='".LAN_DELETE."' onclick=\"return jsconfirm('Delete all tables in ".$lanlist[$i]." ?')\" />\n";
	} else {
		$text .= "<input type='submit' class='button' name='edit_existing' value='".LAN_CREATE."' />\n";
	}
	$text .= "<input type='hidden' name='lang_choices' value='".$lanlist[$i]."' />";
	$text .= "</div>";
	$text .= $rs->form_close();
	$text .= "</td></tr>";

}

$text .= "</table></div></div>";

$ns->tablerender($caption, $text);

if (!$_POST['language'] && !$_POST['edit_existing']) {
	multilang_prefs();
}
unset($text);

// Grab Language configuration. ---
if ($_POST['edit_existing']) {


	$text = $rs->form_open("post", e_SELF);
	$text .= "<div style='text-align:center'>";
	$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>\n";

	foreach ($tabs as $table_name) {
		$installed = strtolower($_POST['lang_choices'])."_".$table_name;
		if (!eregi($installed, $_POST['lang_choices'])) {
			$text .= "<tr>
				<td style='width:30%' class='forumheader3'>".ucfirst(str_replace("_", " ", $table_name))."</td>\n
				<td style='width:70%' class='forumheader3'>\n";
			$selected = (db_Table_exists($installed)) ? "checked='checked'" : "";
			$text .= "<input type=\"checkbox\" id='$table_name' name=\"$table_name\" value=\"1\" $selected onclick=\"if(document.getElementById('$table_name').checked){document.getElementById('datacopy_$table_name').style.display = '';} \"  />";
			$text .= "<span id='datacopy_$table_name' style='display:none'>".LANG_LAN_15."<input type=\"checkbox\" name=\"copydata_$table_name\" value=\"1\" /> </span>";
			$text .= "</td></tr>\n";
		}

	}

	$text .= "<tr><td class='forumheader3' colspan='2'>&nbsp;";
	$text .= "<input type='hidden' name='language' value='".$_POST['lang_choices']."' />";
	$text .= "</td></tr>";

	// ===========================================================================

	// Drop tables ?
	$text .= "<tr><td class='forumheader3'><b>".LANG_LAN_07."</b></td>
		<td class='forumheader3'>".$rs->form_checkbox("drop", 1)."\n
		<span class=\"smalltext\" >".LANG_LAN_08."</span></td></tr>\n";

	$text .= "<tr><td class='forumheader3'><b>".LANG_LAN_10."</b></td>
		<td class='forumheader3'>".$rs->form_checkbox("remove", 1)."\n
		<span class=\"smalltext\" >".LANG_LAN_11."</span></td></tr>\n";

	$text .= "<tr>\n
		<td colspan='2' style='width:100%; text-align: center;' class='forumheader' >\n
		".$rs->form_button("submit", "create_tables", LANG_LAN_06, "", LANG_LAN_06, "disabled");
	//     $text .= " ".$rs->form_button("submit", "e107ml_delete", MLAD_LAN_34, "", MLAD_LAN_34);
	$text .= "</td>\n
		</tr>\n";
	$text .= "</table></div>\n";

	$text .= $rs->form_close();
	$ns->tablerender($_POST['lang_choices'], $text);
}

require_once(e_ADMIN."footer.php");

// ---------------------------------------------------------------------------
function multilang_prefs() {
	global $ns, $pref;

	$handle = opendir(e_LANGUAGEDIR);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && $file != "/") {
			$lanlist[] = $file;
		}
	}
	closedir($handle);


	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."' id='linkform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>";


	$text .= "<tr>

		<td style='width:40%' class='forumheader3'>".LANG_LAN_14.": </td>
		<td style='width:60%; text-align:center' class='forumheader3'>";

	// $text .= "<a href='".e_ADMIN."lancheck.php'>".PRFLAN_86."</a>";

	$text .= "
		<select name='sitelanguage' class='tbox'>\n";
	$counter = 0;
	$sellan = eregi_replace("lan_*.php", "", $pref['sitelanguage']);
	while (isset($lanlist[$counter])) {
		if ($lanlist[$counter] == $sellan) {
			$text .= "<option selected='selected'>".$lanlist[$counter]."</option>\n";
		} else {
			$text .= "<option>".$lanlist[$counter]."</option>\n";
		}
		$counter++;
	}
	$text .= "</select>
		</td>
		</tr>";

	$text .= "
		<tr>
		<td style='width:40%' class='forumheader3'>".LANG_LAN_12.": </td>
		<td style='width:60%;text-align:center' class='forumheader3'>";
	$checked = ($pref['multilanguage'] == 1) ? "checked='checked'" :
	 "";
	$text .= "<input type='checkbox' name='multilanguage'   value='1' $checked />
		</td>
		</tr>
		";


	$text .= "<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
	$text .= "<input class='button' type='submit' name='submit_prefs' value='".LANG_LAN_17."' />";
	$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";

	$caption = LANG_LAN_13; // "Multi-Language Preferences";
	$ns->tablerender($caption, $text);

}

// ----------------------------------------------------------------------------

function db_Table_exists($table)
{
	global $mySQLdefaultdb;
	$tables = mysql_list_tables($mySQLdefaultdb);
	while (list($temp) = mysql_fetch_array($tables))
	{
		if ($temp == strtolower(MPREFIX."lan_".$table))
		{
			return TRUE;
		}
	}
	return FALSE;
}
// ----------------------------------------------------------------------------

function copy_table($oldtable, $newtable, $drop = FALSE, $data = FALSE)
{
	global $sql;
	$old = MPREFIX.strtolower($oldtable);
	$new = MPREFIX.strtolower($newtable);
	if($drop)
	{
		$sql->db_Select_gen("DROP TABLE IF EXISTS {$new}");
	}

	//Get $old table structure
	$sql->db_Select_gen('SET SQL_QUOTE_SHOW_CREATE = 1');
	$qry = "SHOW CREATE TABLE {$old}";
	if($sql->db_Select_gen($qry))
	{
		$row = $sql->db_Fetch();
		$qry = $row[1];
		$qry = str_replace($old, $new, $qry);
	}
	$result = mysql_query($qry);
	if(!$result)
	{
		return FALSE;
	}
	if ($data)  //We need to copy the data too
	{
		$qry = "INSERT INTO {$new} SELECT * FROM {$old}";
		$sql->db_Select_gen($qry);
	}
	return TRUE;
}

// ----------------------------------------------------------------------------

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
	while (list($temp) = mysql_fetch_array($tables))
	{
		if(preg_match("#^".MPREFIX."#", $temp))
		{
			$e107tab = str_replace(MPREFIX, "", $temp);
			if (str_replace($exclude, "", $e107tab) && !eregi("lan_",$e107tab))
			{
				$tabs[] = $e107tab;
			}
		}
	}

	return $tabs;
}

// ----------------------------------------------------------------------------


?>