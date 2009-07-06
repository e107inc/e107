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
|     $Source: /cvs_backup/e107_0.8/e107_admin/theme.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-07-06 05:59:42 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'theme_manage';

require_once("auth.php");

require_once(e_HANDLER."theme_handler.php");
$themec = new themeHandler;

$mode = e_QUERY;

if($_POST['selectadmin'])
{
	$mode = "admin";
}

if($_POST['selectmain'])
{
	$mode = "main";
}

$themec -> showThemes($mode);


function theme_adminmenu()
{
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


		e_admin_menu(TPVLAN_26, $selected, $var);
}

require_once("footer.php");



?>