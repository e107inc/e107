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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/parser.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:36 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function pm_menu_parse($match,$referrer){
        global $pref;
        @include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");
        @include_once(e_PLUGIN."pm_menu/languages/English.php");

        if('SENDPM' == $match[1] AND (check_class($pref['pm_userclass']) || $referrer=='admin')){
                $img=(file_exists(THEME."forum/pm.png")) ? "<img src='".THEME."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />" : "<img src='".e_IMAGE."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />";
                return  "<a href='".e_PLUGIN."pm_menu/pm.php?send.{$match[2]}'>{$img}</a>";
        }
}
?>