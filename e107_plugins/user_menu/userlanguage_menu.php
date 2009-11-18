<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/user_menu/userlanguage_menu.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-11-18 01:06:01 $
 * $Author: e107coders $
 */
//TODO homogenisation with languagelinks + do not force www + unobtrusive redirect
if ( ! defined('e107_INIT')) { exit(); }

require_once(e_HANDLER.'language_class.php');
$slng = new language;

$languageList = explode(',', e_LANLIST);
sort($languageList);

if(varset($pref['multilanguage_subdomain']))
{
	$action = (e_QUERY) ? e_SELF.'?'.e_QUERY : e_SELF;
	$text = '
		<div style="text-align:center">
			<select class="tbox" name="lang_select" style="width:95%" onchange="location.href=this.options[selectedIndex].value">';
	foreach($languageList as $languageFolder)
	{
		$selected = ($languageFolder == e_LANGUAGE) ? ' selected="selected"' : '';
		$urlval   = $slng->subdomainUrl($languageFolder);
		$text .= '
				<option value="'.$urlval.'"'.$selected.'>'.$languageFolder.'</option>';
	}
	$text .= '
			</select>
		</div>';
}
else
{
	//FIXME may not work with session
	$action = (e_QUERY && ! $_GET['elan']) ? e_SELF.'?'.e_QUERY : e_SELF;
	$text = '
	<form method="post" action="'.$action.'">
		<div class="center">
			<select name="sitelanguage" class="tbox">';
	foreach($languageList as $languageFolder)
	{
		$selected = ($languageFolder == e_LANGUAGE) ? ' selected="selected"' : '';
		$text .= '
				<option value="'.$languageFolder.'"'.$selected.'>'.$languageFolder.'</option>';
	}

	$text .= '
			</select>
			<br />
			<br />
			<button class="button" type="submit" name="setlanguage" value="no-value"><span>'.UTHEME_MENU_L1.'</span></button>';
	$text .= '
		</div>
	</form>';
}

$ns->tablerender(UTHEME_MENU_L2, $text, 'user_lan');

