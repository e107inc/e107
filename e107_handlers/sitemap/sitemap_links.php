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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_links.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_links(){
        $sql = new db;
        $sql2 = new db;
        $aj = new textparse;
        if($sql -> db_Select("news","news_id")){
                $texto .= "<p class='caption2' style='text-align: left;' >\n
                <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('link_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"links.php\" >".LANSM_8."</a> <b class='smalltext' >".LANSM_9."</b>\n
                </p><br />\n";

                if($sql -> db_Select("link_category","*","link_category_id!='1' ORDER BY link_category_name")){
                        $texto .= "<p class='cats' id='link_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
                        <b>".LANSM_23."</b><br />\n";
                        $nbr_link_cat = 0;
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $row[1] = $aj -> tpa($row[1]);
                                $row[2] = $aj -> tpa($row[2]);
                                $texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('link_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> ".($row[3]!="" ? "<img src='".e_IMAGE."link_icons/".$row[3]."' alt='bullet' /> " : "")."<a  href='links.php?cat.".$row[0]."' >".$row[1]."</a>\n";
                                $nbr_link_cat++;
                                if($sql2 -> db_Select("links","link_id, link_name, link_url, link_class","link_category='".$row[0]."' ORDER BY link_name DESC")){
                                        $texto .= "<br /><br /><span class='subcats' id='link_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
                                        <b>".LANSM_8."</b><br />";
                                        while($row2 = $sql2 -> db_Fetch()){
                                                extract($row2);
                                                $row2[1] = $aj -> tpa($row2[1]);
                                                if(check_class($row2[3])){
                                                        $texto .= "<a href=\"".$row2[2]."\" >".$row2[1]."</a><br />";
                                                }
                                        }
                                        $texto .= "<br /><br /></div></span>";
                                }else{
                                        $texto .= "<br /><br />";
                                }

                        }
                        $texto .= "</p>";
                }
        }
        return $texto;
}
?>