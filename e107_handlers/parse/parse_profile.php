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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/parse/parse_profile.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function parse_profile($match,$referrer){
        $m = explode(".",$match[1]);
        $id = ($m[2]) ? $m[2] : USERID;
        $image = (file_exists(THEME."forum/profile.png")) ? "<img src='".THEME."forum/profile.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/profile.png' alt='' style='border:0' />";
        return "<a href='".e_BASE."user.php?id.{$id}'>{$image}</a>";
}
?>