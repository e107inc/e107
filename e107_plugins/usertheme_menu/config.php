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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/usertheme_menu/config.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-08-15 11:54:32 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
include_lan(e_PLUGIN."usertheme_menu/languages/".e_LANGUAGE.".php");
require_once(e_HANDLER.'userclass_class.php');


if (!getperms("2")) 		// Same permissions as menu configuration
{
	header("location:".e_BASE."index.php");
	exit ;
}

require_once(e_ADMIN."auth.php");
	
// Get the list of available themes
$handle = opendir(e_THEME);
while ($file = readdir($handle)) 
{
	if ($file != "." && $file != ".." && $file != "templates" && $file != "" && $file != "CVS") 
	{
		if (is_readable(e_THEME.$file."/theme.php") && is_readable(e_THEME.$file."/style.css")) 
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
	}
}

if (isset($pref['allowed_themes']))
{
	$allThemes = FALSE;
	$themeList = explode(',',$pref['allowed_themes']);
}

$themeeditclass = varset($pref['allow_theme_select'],e_UC_NOBODY);

$text = "
	<form method='post' action='".e_SELF."' name='menu_conf_form'>
	<table style='".ADMIN_WIDTH."' class='fborder' >
	<colgroup>
	<col style='width: 50%' />
	<col style='width: 50%' />
	</colgroup>
	<tr>
	<td colspan='2' class='forumheader2'>".LAN_UMENU_THEME_4."</td>
	</tr>";

	foreach ($themeOptions as $th)
	{
		$ch = (in_array($th, $themeList) ? " checked='checked'" : '');
		$text .= "
			<tr>
			<td class='forumheader3'>{$th}</td>
			<td class='forumheader3'><input class='tbox' type='checkbox' name='theme_{$th}' value='{$th}' {$ch} /></td>
			</tr>";
	}
	$text .= "
			<tr>
			<td class='forumheader3'>".LAN_UMENU_THEME_7."</td>
			<td class='forumheader3'>".r_userclass("themeeditclass", $themeeditclass, "off", "main,member,admin,classes,matchclass,nobody")."</td>
			</tr>";


$text .= "
	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_theme' value='".LAN_UMENU_THEME_5."' /></td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(LAN_UMENU_THEME_6, $text);
	
require_once(e_ADMIN."footer.php");

?>