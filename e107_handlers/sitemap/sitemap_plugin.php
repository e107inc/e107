<?php
/*
|	©Lolo Irie 2001-2004 (e107 Dev Team)
|	http://etalkers.org

|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org) for the e107 project.
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

	$textostart = "<div class='caption2' style='text-align: left;' >\n
	<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('plugin_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('plugin_cats');ejs_func_todo='view'\" >".LANSM_25."</a>\n
	</div><br />\n";
		
	$textostart .= "<div class='cats' id='plugin_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
	\n";
	
	$textoend = "</div>";
	if($texto != ""){$texto = $textostart.$texto.$textoend;}
	return $texto;
}
?>