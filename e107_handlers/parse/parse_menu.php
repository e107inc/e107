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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/parse/parse_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function parse_menu($match,$referrer){
        if($referrer != 'admin'){return "";}
        global $pref,$ns,$menu_pref,$sql,$aj;
        $m = explode(".",$match[1]);
        $fname = e_PLUGIN."{$m[2]}/{$m[2]}.php";
   @include_once(e_PLUGIN."{$m[2]}/languages/".e_LANGUAGE.".php");
        @include_once(e_PLUGIN."{$m[2]}/languages/English.php");
        if(file_exists($fname)){
                ob_end_flush();
                ob_start();
                require($fname);
                $ret = ob_get_contents();
                ob_end_clean();
                return $ret;
        }
}
?>