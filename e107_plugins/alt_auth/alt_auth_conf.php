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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/alt_auth_conf.php,v $
|     $Revision: 1.5 $
|     $Date: 2009-07-21 19:49:36 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
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
	$temp['auth_nouser'] = intval($_POST['auth_nouser']);
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

$auth_dropdown = "<select class='tbox' name='auth_method'>\n";
foreach($authlist as $a)
{
	$s = ($pref['auth_method'] == $a) ? "selected='selected'" : "";
	$auth_dropdown .= "<option value='{$a}' {$s}>".$a."</option>\n";
}
$auth_dropdown .= "</select>\n";

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
$auth_dropdown."
</td>
</tr>

<tr>
<td>".LAN_ALT_6.":<br />

</td>
<td>
<select class='tbox' name='auth_noconn'>";
$sel = (isset($pref['auth_noconn']) && $pref['auth_noconn'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = (isset($pref['auth_noconn']) && $pref['auth_noconn'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select><div class='smalltext field-help'>".LAN_ALT_7."</div>
</td>
</tr>

<tr>
<td>".LAN_ALT_8.":<br />

</td>
<td>
<select class='tbox' name='auth_nouser'>";
$sel = (isset($pref['auth_nouser']) && $pref['auth_nouser'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = (isset($pref['auth_nouser']) && $pref['auth_nouser'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select><div class='smalltext field-help'>".LAN_ALT_9."</div>
</td>
</tr>
</table>

<div class='buttons-bar center'>
<input class='button' type='submit' name='updateprefs' value='".LAN_ALT_2."' />
</div>
</form>
</div>";

$ns -> tablerender(LAN_ALT_3, $text);


//$extendedFields = $euf->user_extended_get_fields();
//$extendedFields = &$euf->fieldDefinitions;
//print_a($extendedFields);
if (count($euf->fieldDefinitions))
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