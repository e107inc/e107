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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_custom.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

//////////////////////
// To custom this file for your site
// Uncomment from line 30 in this file and add your links
/////////////////////

function sm_custom(){
        $sql = new db;
        $texto = "<p class='caption2' style='text-align: left;' >\n
        <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('custom_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('custom_cats');ejs_func_todo='view'\" >".LANSM_44."</a>\n
        </p><br />\n";

        $texto .= "<p class='cats' id='custom_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
        \n";

        // Other e107 pages (don't edit this part)
        // Search
        if(USER || $pref['search_restrict']!=1){
                $texto .= "<a href=\"search.php\" >".LANSM_47."</a><br />\n";
        }
        // Submit news
        $texto .= "<a href=\"submitnews.php\" >".LANSM_48."</a><br />\n";

        // Submit content
        $texto .= "<a href=\"subcontent.php?article\" >".LANSM_49."</a><br />\n";

        // Submit links
        $texto .= "<a href=\"links.php?submit\" >".LANSM_51."</a><br />\n";

        // Top Posters
        $texto .= "<a href=\"top.php\" >".LANSM_50."</a><br />\n";

        // Chat
        $sql -> db_Select("menus", "*", "menu_name='chatbox_menu'");
        $row = $sql -> db_Fetch(); extract($row);
        if(check_class($menu_class)){
                $texto .= "<a href=\"chat.php\" >".LANSM_42."</a> <b class='smalltext' >".LANSM_43."</b><br />\n";
        }
        // Polls
        $texto .= "<a href=\"oldpolls.php\" >".LANSM_28."</a><br />\n";

//////////////////////
        /* Uncomment to add your links...
        // See next line for an example with multilanguage (e107_langauges/your_languages/lan_sitemap.php to edit... check the end of the file)
        $texto .= "<a  href='test.php' >".LANSM_Custom_1."</a>\n";
        // See next lines for normal links without multilanguage.
        $texto .= "<a  href='http://e107.org' >e107.org</a> (Site partenaire...)<br />\n";
        $texto .= "<a  href='http://etalkers.org' >etalkers.org</a> (Site partenaire...)<br />\n";
        */
//////////////////////

        $texto .= "</p>";

        return $texto;
}
?>