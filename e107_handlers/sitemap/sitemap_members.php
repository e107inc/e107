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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_members.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_members(){
        $texto = "<p class='caption2' style='text-align: left;' >\n
        <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('members_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('members_cats');ejs_func_todo='view'\" >".LANSM_19."</a> <b class='smalltext' >".LANSM_41."</b>\n
        </p><br />\n";

        $texto .= "<p class='cats' id='members_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
        \n";

        // Links for guests
        if(!USER){$texto .= "<a href=\"signup.php\" >".LANSM_45."</a><br /><br />\n";}
        // Links for members
        if(USER){$texto .= "<a href=\"user.php\" >".LANSM_32."</a><br />\n";
        $texto .= "<a href=\"usersettings.php?".USERID."\" >".LANSM_21."</a><br />\n";
        $texto .= "<a href=\"userposts.php?0.comments.".USERID."\" >".LANSM_52."</a> <b class=\"smalltext\" >".LANSM_53."</b><br />\n";
        $texto .= "<a href=\"fpw.php\" >".LANSM_46."</a><br /><br />\n";
        }
        $texto .= "</p>";

        return $texto;
}
?>