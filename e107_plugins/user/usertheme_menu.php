<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
global $pref, $eArrayStorage;

if ((USER == TRUE) && check_class(varset($pref['allow_theme_select'],FALSE)))
{

	$allThemes = TRUE;
	if (isset($pref['allowed_themes']))
	{
		$allThemes = FALSE;
		$themeList = explode(',',$pref['allowed_themes']);
	}
	$handle = opendir(e_THEME);
	while ($file = readdir($handle)) 
	{
		if ($file != "." && $file != ".." && $file != "templates" && $file != "" && $file != "CVS") 
		{
			if (is_readable(e_THEME.$file."/theme.php") /*&& is_readable(e_THEME.$file."/style.css")*/ && ($allThemes || in_Array($file, $themeList))) 
			{
				$themelist[] = $file;
				$themecount[$file] = 0;
			}
		}
	}
	closedir($handle);

	if (count($themelist))
	{
		$defaulttheme = $pref['sitetheme'];
		$count = 0;

		$totalct = $sql->select("user", "user_prefs", "user_prefs REGEXP('sitetheme') ");
 
		while ($row = $sql->fetch()) 
		{
            $up = (substr($row['user_prefs'],0,5) == "array") ? e107::unserialize($row['user_prefs']) : unserialize($row['user_prefs']);

			if (isset($themecount[$up['sitetheme']])) { $themecount[$up['sitetheme']]++; }
		}
 
		$defaultusers = $sql->count("user") - $totalct;
		$themecount[$defaulttheme] += $defaultusers;
	 
		$text = "<form method='post' action='".e_SELF."'>
			<div style='text-align:center'>
			<select name='sitetheme' class='tbox' style='width: 95%;'>";
		$counter = 0;

		while (isset($themelist[$counter]) && $themelist[$counter]) 
		{
			$text .= "<option value='".$themelist[$counter]."' ";
			if (($themelist[$counter] == USERTHEME) || (USERTHEME == FALSE && $themelist[$counter] == $defaulttheme)) 
			{
				$text .= "selected='selected'";
			}
			$text .= ">".($themelist[$counter] == $defaulttheme ? "[ ".$themelist[$counter]." ]" : $themelist[$counter]).' ('.LAN_UMENU_THEME_3.' '.$themecount[$themelist[$counter]].")</option>\n";
			$counter++;
		}
		$text .= "</select>
			<br /><br />
			<input class='btn btn-default btn-secondary button' type='submit' name='settheme' value='".LAN_UMENU_THEME_1."' />
			</div></form>";

		$ns->tablerender(LAN_UMENU_THEME_2, $text, 'usertheme');
	}
}
