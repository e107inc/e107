<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$eplug_admin = TRUE;
require_once("../../class2.php");
e107::includeLan(e_PLUGIN."user_menu/languages/".e_LANGUAGE.".php");

require_once(e_HANDLER.'userclass_class.php');
global $e_userclass;
if (!is_object($e_userclass)) $e_userclass = new user_class;

if (!getperms("2")) 		// Same permissions as menu configuration
{
	e107::redirect('admin');
	exit ;
}

require_once(e_ADMIN."auth.php");

$frm = e107::getForm();
	
// Get the list of available themes
$handle = opendir(e_THEME);
while ($file = readdir($handle)) 
{
	if ($file != "." && $file != ".." && $file != "templates" && $file != "" && $file != "CVS") 
	{
		if (is_readable(e_THEME.$file."/theme.php") /*&& is_readable(e_THEME.$file."/style.css")*/) 
		{
			$themeOptions[] = $file;
			$themeCount[$file] = 0;
		}
	}
}
closedir($handle);


if (isset($_POST['update_theme'])) 
{
	$tmp = array();
	foreach($_POST as $key => $value) 
	{
		if (substr($key,0,6) == 'theme_')
		{
			$tmp[] = $value;
		}
	}
	$newThemes = implode(',',$tmp);
	$themeeditclass = intval($_POST['themeeditclass']);
	if (($newThemes != $pref['allowed_themes']) || ($themeeditclass != $pref['allow_theme_select']))
	{
		$pref['allowed_themes'] = $newThemes;
		$pref['allow_theme_select'] = $themeeditclass;
		save_prefs();
		$woffle = LAN_UMENU_THEME_8.$pref['allowed_themes'].'[!br!]'.LAN_UMENU_THEME_9.$pref['allow_theme_select'];
		e107::getLog()->add('UTHEME_01',$woffle,E_LOG_INFORMATIVE,'');
	}
}

if (isset($pref['allowed_themes']))
{
	$allThemes = FALSE;
	$themeList = explode(',',$pref['allowed_themes']);
}

$themeeditclass = varset($pref['allow_theme_select'],e_UC_NOBODY);

$text = "
	<form method='post' action='".e_SELF."' id='menu_conf_form'>
	<fieldset id='core-user_menu-usertheme'>
	<legend class='e-hideme'>".LAN_UMENU_THEME_6."</legend>
	<table class='table adminlist'>
		<colgroup span='2'>
		<col style='width: 50%' />
		<col style='width: 50%' />
	</colgroup>
    <thead>
	<tr>
		<th colspan='2'>".LAN_UMENU_THEME_4."</th>
	</tr>
	</thead>
		<tbody>";

		foreach ($themeOptions as $th) 
		{
			$ch = (in_array($th, $themeList) ? " checked='checked'" : '');
			$text .= "
				<tr>
					<td>{$th}</td>
					<td><input class='tbox' type='checkbox' name='theme_{$th}' value='{$th}' {$ch} /></td>
				</tr>";
		}
		$text .= "
				<tr>
					<td>".LAN_UMENU_THEME_7."</td>
					<td>".$e_userclass->uc_dropdown("themeeditclass", $themeeditclass, "main,member,admin,classes,matchclass,nobody")."</td>
				</tr>";

	$text .= "
    	</tbody>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('update_theme', LAN_UPDATE, 'update')."
	</div>
	</fieldset>
	</form>
	";
	$mes = e107::getMessage();
	
$ns->tablerender(LAN_UMENU_THEME_6,$mes->render().$text);
	
require_once(e_ADMIN."footer.php");

/*
function headerjs()
{
	return "<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>";


}
*/
