<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * List Page
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/list_new/list.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-18 01:05:47 $
 * $Author: e107coders $
 *
*/

require_once("../../class2.php");

if (!plugInstalled('list_new'))
{
	header("Location: ".e_BASE."index.php");
	exit;
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

global $rc;
if (!is_object($rc))
{
    require_once(e_PLUGIN."list_new/list_class.php");
    $rc = new listclass;
}

unset($text);

require_once(HEADERF);

//check query
$mode = '';
if(e_QUERY)
{
	$qs = explode(".", e_QUERY);
	if($qs[0] == 'new')
	{
		$mode = $qs[0];
	}
}

//set mode
$rc->mode = (varsettrue($mode) == 'new' ? 'new_page' : 'recent_page');

//parse page
$text = $rc->displayPage();

$caption = varsettrue($rc->list_pref[$rc->mode."_caption"], LIST_MENU_1);
$rc->e107->ns->tablerender($caption, $text);
unset($text);

require_once(FOOTERF);

?>