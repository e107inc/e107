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
|     $Revision: 1.17 $
|     $Date: 2005-01-30 03:43:12 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
$e_sub_cat = 'main';
require_once('auth.php');
require_once(e_HANDLER.'admin_handler.php');

// update users using old layout names to their new names
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
	@require_once(e_ADMIN.'update_routines.php');
	@update_check();
}
// end auto db update
	
if (e_QUERY == 'purge') {
	$sql->db_Delete("tmp", "tmp_ip='adminlog'");
}
	
$td = 1;
function render_links($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE) {
	global $td;
	if (getperms($perms)) {
		if ($mode == 'adminb') {
			$text = "<tr><td class='forumheader3'>
				<div class='td' style='text-align:left; vertical-align:top; width:100%'
				onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
				".$icon." <b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</div></td></tr>";
		} else {
			if ($td == 6) {
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
				$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."'>".$icon."</a><br />
					<a href='".$link."'><b>".$title."</b></a><br />".$description."<br /><br /></td>";
			}
			$td++;
		}
	}
	return $text;
}
	
function render_clean() {
	global $td;
	while ($td <= 5) {
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
		</tr></table>";
	 
	return $text;
}
	
function status_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade') {
		return TRUE;
	} else {
		return FALSE;
	}
}
	
function latest_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade') {
		return TRUE;
	} else {
		return FALSE;
	}
}
	
function log_request() {
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade') {
		return TRUE;
	} else {
		return FALSE;
	}
}
	
require_once("footer.php");
	
?>