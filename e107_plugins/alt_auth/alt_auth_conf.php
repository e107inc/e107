<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/alt_auth_conf.php,v $
 * $Revision: 1.7 $
 * $Date: 2009-11-17 11:28:41 $
 * $Author: marj_nl_fr $
 */

$eplug_admin = true;
require_once('../../class2.php');
if(!getperms("P") || !plugInstalled('alt_auth'))
{
	header('location:'.e_BASE.'index.php'); 
	exit(); 
}
require_once(e_HANDLER.'form_handler.php');
require_once(e_ADMIN.'auth.php');
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'main');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');
require_once(e_HANDLER.'user_extended_class.php');
$euf = new e107_user_extended;


if(isset($_POST['updateprefs']))
{
	unset($temp);
	$temp['auth_method'] = $tp->toDB($_POST['auth_method']);
	$temp['auth_noconn'] = intval($_POST['auth_noconn']);
	$temp['auth_method2'] = $tp->toDB($_POST['auth_method2']);
	if ($admin_log->logArrayDiffs($temp, $pref, 'AUTH_01'))
	{
		save_prefs();		// Only save if changes
		header("location:".e_SELF);
		exit;
	}
}


if(isset($_POST['updateeufs']))
{
	$authExtended = array();
	foreach ($_POST['auth_euf_include'] as $au)
	{
		$authExtended[] = trim($tp->toDB($au));
	}
	$au = implode(',',$authExtended);
	if ($au != $pref['auth_extended'])
	{
		$pref['auth_extended'] = $au;
		save_prefs();
		$admin_log->log_event('AUTH_02',$au,'');
	}
}

// Avoid need for lots of checks later
if (!isset($pref['auth_badpassword'])) $pref['auth_badpassword'] = 0;
if (!isset($pref['auth_noconn'])) $pref['auth_noconn'] = 0;

// Convert prefs
if (isset($pref['auth_nouser']))
{
	$pref['auth_method2'] = 'none';		// Default to no fallback
	if ($pref['auth_nouser'])
	{
		$pref['auth_method2'] = 'e107';
	}
	unset($pref['auth_nouser']);
	if (!isset($pref['auth_badpassword'])) $pref['auth_badpassword'] = 0;
	save_prefs();
}


$authlist = alt_auth_get_authlist();
if (isset($pref['auth_extended']))
{
	$authExtended = explode(',',$pref['auth_extended']);
}
else
{
	$pref['auth_extended'] = '';
	$authExtended = array();
}


if(isset($message))
{
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "
<div>
<form method='post' action='".e_SELF."'>
<table cellpadding='0' cellspacing='0' class='adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
<tr>
<td>".LAN_ALT_1.": </td>
<td>".
alt_auth_get_dropdown('auth_method', $pref['auth_method'], 'e107')."
</td>
</tr>

<tr>
<td>".LAN_ALT_78.":<br /></td>
<td>
<select class='tbox' name='auth_noconn'>";
$sel = (!$pref['auth_badpassword'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = ($pref['auth_badpassword'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select><div class='smalltext field-help'>".LAN_ALT_79."</div>
</td>
</tr>

<tr>
<td>".LAN_ALT_6.":<br /></td>
<td>
<select class='tbox' name='auth_noconn'>";
$sel = (!$pref['auth_noconn'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = ($pref['auth_noconn'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select><div class='smalltext field-help'>".LAN_ALT_7."</div>
</td>
</tr>

<tr>
<td>".LAN_ALT_8.":<br />

</td>
<td>".alt_auth_get_dropdown('auth_method2', $pref['auth_method2'], 'none')."
<div class='smalltext field-help'>".LAN_ALT_9."</div>
</td>
</tr>
</table>

<div class='buttons-bar center'>
<input class='button' type='submit' name='updateprefs' value='".LAN_ALT_2."' />
</div>
</form>
</div>";

$ns -> tablerender(LAN_ALT_3, $text);


if ($euf->userCount)
{
	include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user_extended.php');
	$fl = &$euf->fieldDefinitions;
	$text = "<div>
		<form method='post' action='".e_SELF."'>
		<table class='adminlist' cellspacing='1' cellpadding='0'>
		<colgroup>
		<col style='width:10%' />
		<col style='width:30%' />
		<col style='width:40%' />
		<col style='width:20%' />
		</colgroup>\n";

		$text .= "<thead><tr>
			<th class='center'>".LAN_ALT_61."</th>
			<th>".LAN_ALT_62."</th>
			<th>".LAN_ALT_63."</th>
			<th>".LAN_ALT_64."</th>
			</tr>
			</thead>
			<tbody>";
		foreach ($fl as $f)
		{
			$checked = (in_array($f['user_extended_struct_name'], $authExtended) ? " checked='checked'" : '');
			$text .= "<tr>
			<td class='center'><input type='checkbox' name='auth_euf_include[]' value='{$f['user_extended_struct_name']}'{$checked} /></td>
			<td>{$f['user_extended_struct_name']}</td>
			<td>".$tp->toHTML($f['user_extended_struct_text'],FALSE,'TITLE')."</td>
			<td>{$euf->user_extended_types[$f['user_extended_struct_type']]}</td></tr>\n";
		}
	$text .= "</tbody>
</table><div class='buttons-bar center'>
<input class='button' type='submit' name='updateeufs' value='".LAN_ALT_2."' />
</div>

</form>
</div>";
$ns -> tablerender(LAN_ALT_60, $text);


}


require_once(e_ADMIN."footer.php");

function alt_auth_conf_adminmenu()
{
	alt_auth_adminmenu();
}


?>