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
|     $Source: /cvs_backup/e107_0.7/e107_admin/comment.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("B")){ header("location:".e_BASE."index.php"); exit; }

if(e_QUERY){
        $temp = explode("-", e_QUERY);
        $action = $temp[0];
        $id = $temp[1];
        $item = $temp[2];
        $c_item = $temp[3];
        if($action == "block"){
                $sql -> db_Update("comments", "comment_blocked='1' WHERE comment_id='$id' ");
        }
        if($action == "unblock"){
                $sql -> db_Update("comments", "comment_blocked='0' WHERE comment_id='$id' ");
        }
        if($action == "delete"){
                $sql -> db_Delete("comments", "comment_id='$id' ");
        }
        if(!clear_cache($item)){
                $tmp = explode("?", $item);
                $item = $tmp[0]."?news.".$c_item;
                clear_cache($item);
        }
}
echo "<script type='text/javascript'>window.history.go(-1);</script>\n";
?>