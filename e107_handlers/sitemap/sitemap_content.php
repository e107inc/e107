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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_content.php,v $
|     $Revision: 1.3 $
|     $Date: 2004-11-06 01:38:44 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/
function sm_content(){
        $sql = new db;
        $sql2 = new db;
        $aj = new textparse;
        $texto .= "<p class='forumheader2' >\n
        <a href=\"#\" onfocus=\"this.blur;\" onclick=\"if(document.getElementById('contents_cats')){expandit('contents_cats');}ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"content.php\" >".LANSM_4."</a> <b class='smalltext' >".LANSM_36."</b>\n
        </p><br />\n";

        if($sql -> db_Select("content","content_id, content_heading, content_type, content_class","content_type='1' ORDER BY content_heading ASC")){
                $texto .= "<p class='cats' id='contents_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
                <b>".LANSM_38."</b><br />\n";
                $nbr_contents_cat = 0;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $row[1] = $aj -> tpa($row[1]);
                        if(check_class($row[3])){
                                $texto .= "<a  href='content.php?content.".$row[0]."' >".$row[1]."</a><br />\n";
                                $nbr_contents_cat++;
                        }
                }
                $texto .= "<br /></p>";

        }
        return $texto;
}
?>
