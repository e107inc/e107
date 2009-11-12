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
 * $Source: /cvs_backup/e107_0.8/e107_admin/theme.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-12 14:30:07 $
 * $Author: marj_nl_fr $
 */

require_once("../class2.php");
if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'theme_manage';

require_once("auth.php");

require_once(e_HANDLER."theme_handler.php");
$themec = new themeHandler;

$mode = (e_QUERY) ? e_QUERY :"main" ;

if($_POST['selectadmin'])
{
	$mode = "admin";
}

if($_POST['upload'])
{
	$mode = "choose";
}

if($_POST['selectmain'] || varset($_POST['setUploadTheme']))
{
	$mode = "main";
}

$themec -> showThemes($mode);


require_once("footer.php");

function theme_adminmenu()
{
	global $mode;
   	$e107 = &e107::getInstance();

		$var['main']['text'] = TPVLAN_33;
		$var['main']['link'] = e_SELF;

		$var['admin']['text'] = TPVLAN_34;
		$var['admin']['link'] = e_SELF."?admin";

		$var['choose']['text'] = TPVLAN_51;
		$var['choose']['link'] = e_SELF."?choose";

		$var['upload']['text'] = TPVLAN_38;
		$var['upload']['link'] = e_SELF."?upload";

        $selected = (e_QUERY) ? e_QUERY : "main";


		e_admin_menu(TPVLAN_26, $mode, $var);
}





?>