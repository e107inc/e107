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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_downloads.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_downloads(){
        $sql = new db;
        $sql2 = new db;
        $aj = new textparse;
        $texto .= "<p class='caption2' style='text-align: left;' >\n
        <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('downloads_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"download.php\" >".LANSM_12."</a> <b class='smalltext' >".LANSM_13."</b>\n
        </p><br />\n";

        if($sql -> db_Select("download_category","*","download_category_parent='0' ORDER BY download_category_name ASC")){
                $texto .= "<p class='cats' id='downloads_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
                <b>".LANSM_23."</b><br />\n";
                $nbr_downloads_cat = 0;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $row[1] = $aj -> tpa($row[1]);
                        $row[2] = $aj -> tpa($row[2]);
                        if(check_class($row[5])){
                                $texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('downloads_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> ".( $row[3]!="" ? "<img src='".e_IMAGE."download_icons/".$row[3]."' alt='bullet' /> " : "" )."<a  href='download.php?' >".$row[1]."</a>\n";
                                $nbr_downloads_cat++;
                                if($sql2 -> db_Select("download_category","*","download_category_parent='".$row[0]."' ORDER BY download_category_name ASC")){
                                                $texto .= "<br /><br /><span class='subcats' id='downloads_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
                                                <b>".LANSM_37."</b><br />";
                                                while($row2 = $sql2 -> db_Fetch()){
                                                        extract($row2);
                                                        $row2[1] = $aj -> tpa($row2[1]);
                                                        $row2[1] = $aj -> tpa($row2[1]);
                                                        if(check_class($row2[5])){
                                                                $texto .= ( $row2[3]!="" ? "<img src='".e_IMAGE."download_icons/".$row2[3]."' alt='bullet' /> " : "" )."<a href=\"download.php?list.".$row2[0]."\" >".$row2[1]."</a><br />\n";
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