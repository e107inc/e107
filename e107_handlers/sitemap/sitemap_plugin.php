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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitemap/sitemap_plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function sm_plugins(){
        $texto = "";
        $nb_sm_plugin = 0;
        $handle=opendir(e_PLUGIN);
        while(false !== ($file = readdir($handle))){
                if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
                        $plugin_handle=opendir(e_PLUGIN.$file."/");
                        while(false !== ($file2 = readdir($plugin_handle))){
                                if($file2 == "sitemap.php"){
                                        require_once(e_PLUGIN.$file."/".$file2);
                                }
                        }
                }
        }

        $textostart = "<p class='caption2' style='text-align: left;' >\n
        <a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('plugin_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('plugin_cats');ejs_func_todo='view'\" >".LANSM_25."</a>\n
        </p><br />\n";

        $textostart .= "<p class='cats' id='plugin_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
        \n";

        $textoend = "</p>";
        if($texto != ""){$texto = $textostart.$texto.$textoend;}
        return $texto;
}
?>