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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_stats.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_stats(){
        $texto = "<p class='caption2' style='text-align: left;' >\n
        <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"stats.php\" >".LANSM_20."</a> <b class='smalltext' >".LANSM_33."</b>\n
        </p><br />\n";


        return $texto;
}
?>