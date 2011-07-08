<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
$e_sub_cat = 'main';
require_once('auth.php');
require_once(e_HANDLER.'admin_handler.php');

// --- check for htmlarea.
if (is_dir(e_ADMIN.'htmlarea') || is_dir(e_HANDLER.'htmlarea'))
{
	$text = "<table class='admin-warning' style='margin-left:0px;border:0px'>
	<tr>
	<td style='width:40px;vertical-align:top'>".ADMIN_WARNING_ICON."</td>
	<td>".ADLAN_ERR_2."<br /><br /><ul><li>".
	$HANDLERS_DIRECTORY."htmlarea/</li><li>".$ADMIN_DIRECTORY."htmlarea/</li></ul>
	</td>
	</tr></table>";
	$ns ->tablerender(LAN_WARNING, $text);
}

// --- check for Malware/Trojans -----------

$mal_paths = array(e_FILE."thumbs.php",e_FILE.'ok.txt',e_FILE."css.php",e_PLUGIN."css.php");
$mal_list = array();
foreach($mal_paths as $pth)
{
	if(file_exists($pth))
	{
		$mal_list[] = str_replace("../","",$pth);		
	}
}

	$insp_srch = array('[',']');
	$insp_repl = array("<a href='".e_ADMIN."fileinspector.php'>","</a>");

if(count($mal_list))
{

	$text = "<table class='admin-warning' style='margin-left:0px;border:0px'>
	<tr>
	<td style='width:40px;vertical-align:top'>".ADMIN_WARNING_ICON."</td>
	<td>".$tp->toHtml(ADLAN_ERR_7,TRUE)."<br /><br /><ul><li>".implode("</li><li>",$mal_list)."</li></ul>
	<br />".str_replace($insp_srch,$insp_repl,ADLAN_ERR_8)."</td>
	</tr></table>";
	$ns -> tablerender(LAN_WARNING, $text);	
}




// check for old modules.
if(getperms('0') && isset($pref['modules']) && $pref['modules'] && $sql->db_Field("plugin",5) == "plugin_addons"){

	$mods=explode(",", $pref['modules']);
	$thef = "e_module.php";
	foreach ($mods as $mod)
	{
		if (is_readable(e_PLUGIN."{$mod}/module.php"))
		{
			$mod_found[] = e_PLUGIN."{$mod}/module.php";
		}
	}

	if($mod_found)
	{
    	$text = ADLAN_ERR_5." <b>".$thef."</b>:<br /><br /><ul>";
		foreach($mod_found as $val)
		{
			$text .= "<li>".str_replace("../","",$val)."</li>\n";
		}
		$text .="</ul><br />
		<form method='post' action='".e_ADMIN."db.php' id='upd'>
		<a href='#' onclick=\"document.getElementById('upd').submit()\">".ADLAN_ERR_6."</a>
		<input type='hidden' name='plugin_scan' value='1' />
		</form>";
		$ns -> tablerender(ADLAN_ERR_4,$text);
	}
}

// check for file-types;
if (is_readable(e_ADMIN.'filetypes.php')) {
	$a_types = strtolower(trim(file_get_contents(e_ADMIN.'filetypes.php')));
} else {
	$a_types = 'zip, gz, jpg, png, gif';
}

$a_types = explode(',', $a_types);
foreach ($a_types as $f_type) {
	$allowed_types[] = '.'.trim(str_replace('.', '', $f_type));
}

// avatar check.
$public = array(e_FILE.'public', e_FILE.'public/avatars');
foreach ($public as $dir) {
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (is_dir($dir."/".$file) == FALSE && $file != '.' && $file != '..' && $file != '/' && $file != 'CVS' && $file != 'avatars' && $file != 'Thumbs.db' && $file !=".htaccess" && $file !="php.ini") {
					$fext = substr(strrchr($file, "."), 0);
					if (!in_array(strtolower($fext), $allowed_types) ) {
						if ($file == 'index.html' || $file == "null.txt") {
							if (filesize($dir.'/'.$file)) {
								$potential[] = str_replace('../', '', $dir).'/'.$file;
							}
						} else {
							$potential[] = str_replace('../', '', $dir).'/'.$file;
						}
					}
				}
			}
		closedir($dh);
		}
	}
}

if (isset($potential))
{
	$text = "<table class='admin-warning' style='margin-left:0px;border:0px'>
	<tr>
	<td style='width:40px;vertical-align:top'>".ADMIN_WARNING_ICON."</td>
	<td>".$tp->toHtml(ADLAN_ERR_3,TRUE)."<br /><br /><ul><li>".implode("</li><li>",$potential)."</li></ul>
	<br />".str_replace($insp_srch,$insp_repl,ADLAN_ERR_8)."</td>
	</tr></table>";

	$ns -> tablerender(LAN_WARNING, $text);
}

unset($insp_srch,$insp_repl,$text);
// Moved to admin_template
//echo $tp->parseTemplate('{ADMIN_UPDATE}', true);


// update users using old layout names to their new names
$update_prefs = FALSE;
if (!$pref['adminstyle'] || $pref['adminstyle'] == 'default') {
	$pref['adminstyle'] = 'compact';
	$update_prefs = true;
}
if ($pref['adminstyle'] == 'adminb') {
	$pref['adminstyle'] = 'cascade';
	$update_prefs = true;
}
if ($pref['adminstyle'] == 'admin_etalkers') {
	$pref['adminstyle'] = 'categories';
	$update_prefs = true;
}
if ($pref['adminstyle'] == 'admin_combo') {
	$pref['adminstyle'] = 'combo';
	$update_prefs = true;
}
if ($pref['adminstyle'] == 'admin_classis') {
	$pref['adminstyle'] = 'classis';
	$update_prefs = true;
}

// temporary code to switch users using admin_jayya to jayya

if ($pref['admintheme'] == 'admin_jayya') {
	$pref['admintheme'] = 'jayya';
	$update_prefs = true;
}

if ($pref['sitetheme'] == 'admin_jayya') {
	$pref['sitetheme'] = 'jayya';
	$update_prefs = true;
}

// ---------------------------------------------------------


if ($update_prefs == true) {
	save_prefs();
}

// auto db update
if ('0' == ADMINPERMS) 
{
	$sql->db_Mark_Time("Start: Db Update Check");
	$dont_check_update = TRUE;		// This reduces frequency of checks
	require_once(e_ADMIN.'update_routines.php');
	update_check();
}
// end auto db update
$sql->db_Mark_Time("Start: Render Admin Panel");

if (e_QUERY == 'purge' && getperms('0')) {
	$admin_log->purge_log_events(false);
}

$td = 1;
if(!defined("ADLINK_COLS")){
	define("ADLINK_COLS",5);
}
function render_links($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE) {
	global $td,$tp;
	$text = '';
	if (getperms($perms)) {
		if ($mode == 'adminb') {
			$text = "<tr><td class='forumheader3'>
				<div class='td' style='text-align:left; vertical-align:top; width:100%'
				onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
				".$icon." <b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</div></td></tr>";
		} else {
			if ($td == (ADLINK_COLS+1)) {
				$text .= '</tr>';
				$td = 1;
			}
			if ($td == 1) {
				$text .= '<tr>';
			}
			if ($mode == 'default') {
				$text .= "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap'
					onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">".$icon." ".$tp->toHTML($title,FALSE,"defs,emotes_off")."</td>";
			}
			else if ($mode == 'classis') {
				$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."' title=\"".$description."\">".$icon."</a><br />
					<a href='".$link."' title=\"".$description."\"><b>".$tp->toHTML($title,FALSE,"defs,emotes_off")."</b></a><br /><br /></td>";
			}elseif ($mode == 'beginner'){
                $text .= "<td style='text-align:center; vertical-align:top; width:20%' ><a href='".$link."' >".$icon."</a>
					<div style='padding:5px'>
					<a href='".$link."' title=\"".$description."\" style='text-decoration:none'><b>".$tp->toHTML($title,FALSE,"defs,emotes_off")."</b></a></div><br /><br /><br /></td>";
			}
			$td++;
		}
	}
	return $text;
}

function render_clean() 
{
	global $td;
	$text = '';
	while ($td <= ADLINK_COLS) 
	{
		$text .= "<td class='td' style='width:20%;'></td>";
		$td++;
	}
	$text .= "</tr>";
	$td = 1;
	return $text;
}

$newarray = asortbyindex($array_functions, 1);

require_once(e_ADMIN.'includes/'.$pref['adminstyle'].'.php');

function admin_info() {
	global $tp;

	$width = (getperms('0')) ? "33%" : "50%";

	$ADMIN_INFO_TEMPLATE = "
	<div style='text-align:center'>
		<table style='width: 100%; border-collapse:collapse; border-spacing:0px;'>
		<tr>
			<td style='width: ".$width."; vertical-align: top'>
			{ADMIN_STATUS}
			</td>
			<td style='width:".$width."; vertical-align: top'>
			{ADMIN_LATEST}
			</td>";

    	if(getperms('0'))
		{
			$ADMIN_INFO_TEMPLATE .= "
			<td style='width:".$width."; vertical-align: top'>{ADMIN_LOG}</td>";
    	}

   	$ADMIN_INFO_TEMPLATE .= "
		</tr></table></div>";

	return $tp->parseTemplate($ADMIN_INFO_TEMPLATE);
}

function status_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade' || $pref['adminstyle'] == 'beginner') {
		return TRUE;
	} else {
		return FALSE;
	}
}

function latest_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade' || $pref['adminstyle'] == 'beginner') {
		return TRUE;
	} else {
		return FALSE;
	}
}

function log_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade'|| $pref['adminstyle'] == 'beginner') {
		return TRUE;
	} else {
		return FALSE;
	}
}
$sql->db_Mark_Time("Start: Render Admin Footer");
require_once("footer.php");

?>
