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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/parse/parse_picture.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function parse_picture($match,$referrer){
        $m = explode(".",$match[1]);
        if(is_numeric($m[2])){
                if($m[2] == USERID){
                        $image = USERSESS;
                } else {
                        $sql2 = new db;
                        $sql2 -> db_Select("user","user_sess","user_id = '{$m[2]}'");
                        $row = $sql2 -> db_Fetch();
                        $image=$row['user_sess'];
                }
        } elseif($m[2]) {
                $image=$m[2];
        } else {
                $image = USERSESS;
        }
        if($image && file_exists(e_FILE."public/avatars/".$image)){
                return "<img src='".e_FILE."public/avatars/{$image}' alt='' />";
        } else {
                return "";
        }
}
?>