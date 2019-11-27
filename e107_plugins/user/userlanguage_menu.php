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
 * $Revision$
 * $Date$
 * $Author$
 */
//TODO homogenisation with languagelinks + do not force www + unobtrusive redirect
if ( ! defined('e107_INIT')) { exit(); }

require_once(e_HANDLER.'language_class.php');
$slng = new language;

$languageList = explode(',', e_LANLIST);
sort($languageList);

if(varset($pref['multilanguage_subdomain']))
{
	$action = e_REQUEST_URI;
	$text = '
		<div style="text-align:center">
			<select class="tbox form-control" name="lang_select" style="width:95%" onchange="location.href=this.options[selectedIndex].value">';
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
	$action = e_REQUEST_URI;
	$text = '
	<form method="post" action="'.$action.'">
		<div class="center">
			<select name="sitelanguage" class="tbox form-control">';
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
			<button class="btn btn-default btn-secondary button" type="submit" name="setlanguage" value="no-value"><span>'.UTHEME_MENU_L1.'</span></button>';
	$text .= '
		</div>
	</form>';
}

$ns->tablerender(UTHEME_MENU_L2, $text, 'user_lan');

