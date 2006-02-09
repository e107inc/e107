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
|     $Source: /cvs_backup/e107_0.7/e107_admin/admin.php,v $
|     $Revision: 1.29 $
|     $Date: 2006-02-09 09:30:35 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
// send the charset to the browser - overides spurious server settings with the lan pack settings.
header("Content-type: text/html; charset=".CHARSET, true);
$e_sub_cat = 'main';
require_once('auth.php');
require_once(e_HANDLER.'admin_handler.php');

if (is_dir(e_ADMIN.'htmlarea') || is_dir(e_HANDLER.'htmlarea')) {
	$text = "There are files on your server that are known to be
	exploitable. These must be removed <b>immediately</b>. The files are related to the WYSIWYG system used in the
	older 0.6xx branch of e107 - htmlArea. Please delete the following directories and all their contents:<br /><br />
	<div style='text-align:center'>".$HANDLERS_DIRECTORY."htmlarea/<br />".$ADMIN_DIRECTORY."htmlarea/</div>";
	$ns -> tablerender('Warning!', $text);
}

if (is_readable(e_ADMIN.'filetypes.php')) {
	$a_types = trim(file_get_contents(e_ADMIN.'filetypes.php'));
} else {
	$a_types = 'zip, gz, jpg, png, gif';
}
$a_types = explode(',', $a_types);
foreach ($a_types as $f_type) {
	$allowed_types[] = '.'.trim(str_replace('.', '', $f_type));
}
$public = array(e_FILE.'public', e_FILE.'public/avatars');
foreach ($public as $dir) {
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (is_dir($dir."/".$file) == FALSE && $file != '.' && $file != '..' && $file != '/' && $file != 'CVS' && $file != 'avatars' && $file != 'Thumbs.db') {
					$fext = substr(strrchr($file, "."), 0);
					if (!in_array($fext, $allowed_types)) {
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

if (isset($potential)) {
	$text = "There are one or more files in your public upload directories that are not in your allowed upload filetypes
	list. These may have been placed here by an attacker and if so should be removed <b>immediately</b>. You should
	<b>not</b> open these files as this may execute any malicious code the file might contain. ie. do not open them
	with your browser.<br /><br />

	If you recognise these files as being legitimate, it is likely that due to the recent allowed filetypes changes,
	the filetype you allowed is no longer in the allowed filetypes list and you will need to re-add it
	(see admin => uploads). You should not allow the upload of .html, .txt, etc as an attacker may upload a file of
	this type which includes malicious javascript. You should also, of course, not allow the upload of .php files or
	any other type of executable script.<br ><br />

	Below is the list of files that could potentially be malicious:<br /><br />";

	foreach ($potential as $p_file) {
		$text .= $p_file.'<br />';
	}

	$ns -> tablerender('Warning!', $text);
}

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
if ('0' == ADMINPERMS) {
	require_once(e_ADMIN.'update_routines.php');
	update_check();
}
// end auto db update

if (e_QUERY == 'purge') {
	$admin_log->purge_log_events(false);
}

$td = 1;
if(!defined("ADLINK_COLS")){
	define("ADLINK_COLS",5);
}
function render_links($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE) {
	global $td;
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
					onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">".$icon." ".$title."</td>";
			}
			else if ($mode == 'classis') {
				$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."' title='$description'>".$icon."</a><br />
					<a href='".$link."' title='$description'><b>".$title."</b></a><br /><br /></td>";
			}elseif ($mode == 'beginner'){
                $text .= "<td style='text-align:center; vertical-align:top; width:20%' ><a href='".$link."' >".$icon."</a>
					<div style='padding:5px'>
					<a href='".$link."' title='".$description."' style='text-decoration:none'><b>".$title."</b></a></div><br /><br /><br /></td>";
			}
			$td++;
		}
	}
	return $text;
}

function render_clean() {
	global $td;
	while ($td <= ADLINK_COLS) {
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
	$text = "<div style='text-align:center'>
		<table style='width: 100%; border-collapse:collapse; border-spacing:0px;'>
		<tr>
		<td style='width: 33%; vertical-align: top'>";

	$text .= $tp->parseTemplate('{ADMIN_STATUS}');

	$text .= "</td>
		<td style='width: 33%; vertical-align: top'>";

	$text .= $tp->parseTemplate('{ADMIN_LATEST}');

	$text .= "</td>
		<td style='width: 33%; vertical-align: top'>";

	$text .= $tp->parseTemplate('{ADMIN_LOG}');

	$text .= "</td>
		</tr></table></div>";

	return $text;
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

require_once("footer.php");

?>