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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/parse/parse_emailto.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function parse_emailto($match,$referrer){
        global $pref;
        $image = (file_exists(THEME."forum/email.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='' style='border:0' />";
        $m = explode(".",$match[1],3);
        if(is_numeric($m[2])){
                if(!$pref['emailusers']){return "";}
                return "<a href='".e_BASE."emailmember.php?id.{$match[2]}'>{$image}</a>";
        } else {
                return "<a href='mailto:{$m[2]}'>{$image}</a>";
        }
}
?>