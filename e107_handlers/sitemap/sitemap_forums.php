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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_forums.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-07 14:46:28 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/
function sm_forums(){
        $sql = new db;
        $sql2 = new db;
        $aj = new textparse;
        $texto .= "<p class='caption2' style='text-align: left;' >
        <a href=\"#\" onfocus=\"this.blur;\" onclick=\"if(document.getElementById('forum_cats')){expandit('forum_cats');}ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"forum.php\" >".LANSM_10."</a> <b class='smalltext' >".LANSM_11."</b>\n
        </p><br />\n";

        if($sql -> db_Select("forum","forum_id, forum_name, forum_class","forum_parent='0' ORDER BY forum_order")){
                $texto .= "<p class='cats' id='forum_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
                <b>".LANSM_23."</b><br />\n";
                $nbr_forum_cat = 0;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $row[1] = $aj -> tpa($row[1]);
                        if(check_class($row[2])){
                                $texto .= "<a href=\"#\" onfocus=\"this.blur;\" onclick=\"if(document.getElementById('forum_subcats_".$row[0]."')){expandit('forum_subcats_".$row[0]."');}ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a href=\"forum.php\" class='smalltext' >".$row[1]."</a>\n";
                                $nbr_forum_cat++;
                                if($sql2 -> db_Select("forum","forum_id, forum_name, forum_threads, forum_replies, forum_class","forum_parent='".$row[0]."' ORDER BY forum_order ASC")){
                                                $texto .= "<br /><br /><span class='subcats' id='forum_subcats_".$row[0]."' style='display:none;' ><span style='margin: 0px 0px 0px 30px;' >\n
                                                <b>".LANSM_10."</b><br />";
                                                while($row2 = $sql2 -> db_Fetch()){
                                                        extract($row2);
                                                        $row2[1] = $aj -> tpa($row2[1]);
                                                        if(check_class($row2[4])){
                                                                $texto .= "<a href=\"forum_viewforum.php?".$row2[0]."\" >".$row2[1]."</a> ".LANSM_39.": ".$row2[2]."/".$row2[3].")<br />\n";
                                                        }
                                                }
                                                $texto .= "<br /><br /></span></span>\n";
                                }else{
                                        $texto .= "<br /><br />";
                                }
                        }
                }
                $texto .= "</p>";

        }
        return $texto;
}
?>
