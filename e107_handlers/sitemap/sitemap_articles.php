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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_articles.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_articles(){
        $sql = new db;
        $sql2 = new db;
        $aj = new textparse;
        $texto .= "<p class='caption2' style='text-align: left;' >\n
        <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('articles_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"content.php?article\" >".LANSM_14."</a> <b class='smalltext' >".LANSM_15."</b>\n
        </p><br />\n";

        if($sql -> db_Select("content","content_id, content_heading, content_type, content_class","content_parent='0' AND (content_type='0' OR content_type='6') ORDER BY content_heading ASC")){
                $texto .= "<p class='cats' id='articles_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
                <b>".LANSM_23."</b><br />\n";
                $nbr_articles_cat = 0;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $row[1] = $aj -> tpa($row[1]);
                        if(check_class($row[3]) && $row[2]=="0"){
                                $texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('articles_subcats2_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a  href='content.php?article.cat.0' >".LANSM_40."</a>\n";
                                $nbr_articles_cat++;
                                if($sql2 -> db_Select("content","content_id, content_heading, content_class","content_parent='0' AND content_type='0' ORDER BY content_heading ASC")){
                                                $texto .= "<br /><br /><span class='subcats' id='articles_subcats2_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
                                        <b>".LANSM_38."</b><br />";
                                                while($row2 = $sql2 -> db_Fetch()){
                                                        extract($row2);
                                                        $row2[1] = $aj -> tpa($row2[1]);
                                                        if(check_class($row2[2])){
                                                                $texto .= "<a href=\"content.php?item.".$row2[0]."\" >".$row2[1]."</a><br />\n";
                                                        }
                                                }
                                                $texto .= "<br /><br /></div></span>\n";
                                }else{
                                        $texto .= "<br /><br />";
                                }
                        }else if(check_class($row[3])){
                                $texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('articles_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a  href='content.php?article.cat.".$row[0]."' >".$row[1]."</a>\n";
                                $nbr_articles_cat++;
                                if($sql2 -> db_Select("content","content_id, content_heading, content_class","content_parent='".$row[0]."' ORDER BY content_heading ASC")){
                                                $texto .= "<br /><br /><span class='subcats' id='articles_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
                                        <b>".LANSM_38."</b><br />";
                                                while($row2 = $sql2 -> db_Fetch()){
                                                        extract($row2);
                                                        if(check_class($row2[2])){
                                                                $texto .= "<a href=\"content.php?item.".$row2[0]."\" >".$row2[1]."</a><br />\n";
                                                        }
                                                }
                                                $texto .= "<br /><br /></div></span>\n";
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