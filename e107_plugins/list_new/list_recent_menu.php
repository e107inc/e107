<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * List Menu Recent
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/list_new/list_recent_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	Menu for list_new plugin 
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */

if (!defined('e107_INIT')) { exit; }

if (!e107::isInstalled('list_new'))
{
	return;
}

unset($text);

global $rc;
if (!is_object($rc))
{
    require_once(e_PLUGIN."list_new/list_class.php");
    $rc = new listclass;
}

//set mode
$rc->mode = "recent_menu";

//parse menu
$text = $rc->displayMenu();

$caption = vartrue($rc->list_pref[$rc->mode."_caption"], LIST_MENU_1);
$caption = $rc->e107->tp->toHTML($caption, FALSE, 'USER_TITLE');
$text = $rc->e107->tp->toHTML($text, TRUE, 'USER_BODY');
$rc->e107->ns->tablerender($caption, $text, 'list_recent');
unset($caption);
unset($text);

