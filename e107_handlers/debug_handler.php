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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/debug_handler.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

function search_validtheme(){
        $theme_found = 0;
        $th = substr(e_THEME, 0, -1);
        $handle = opendir($th);
        while ($file = readdir($handle)){
                if(!strstr($file, ".")){
                                $subhandle=opendir(e_THEME.$file);
                                while ($file2 = readdir($subhandle)){
                                        if($file2 == "theme.php"){
                                                $theme_found = 1;
                                                break;
                                        }
                                }
                                if($theme_found == 1){closedir($subhandle);break;}
                                closedir($subhandle);
                }
        }

        if($theme_found == 1){
                closedir($handle);
                return $file;
        }

        closedir($handle);
}

?>