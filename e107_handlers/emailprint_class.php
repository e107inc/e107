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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/emailprint_class.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-03-30 15:11:13 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
class emailprint {
	function render_emailprint($mode, $id, $look = 0) {
		// $look = 0  --->display all icons
		// $look = 1  --->display email icon only
		// $look = 2  --->display print icon only
		$text_emailprint = "";

		//new method emailprint_class : (only news is core, rest is plugin: searched for e_emailprint.php which should hold $email and $print values)
		if($mode == "news"){
			$email = "news";
			$print = "news";
		}else{
			//load the others from plugins
			$handle = opendir(e_PLUGIN);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
					$plugin_handle = opendir(e_PLUGIN.$file."/");
					while (false !== ($file2 = readdir($plugin_handle))) {
						if ($file2 == "e_emailprint.php") {
							require_once(e_PLUGIN.$file."/".$file2);
						}
					}
				}
			}
		}

		if ($look == 0 || $look == 1) {
			$ico_mail = (file_exists(THEME."generic/email.png") ? THEME."generic/email.png" : e_IMAGE."generic/".IMODE."/email.png");
			$text_emailprint .= "<a href='".e_BASE."email.php?".$email.".".$id."'><img src='".$ico_mail."' style='border:0' alt='email to someone' title='email to someone' /></a> ";
		}
		if ($look == 0 || $look == 2) {
			$ico_print = (file_exists(THEME."generic/printer.png") ? THEME."generic/printer.png" : e_IMAGE."generic/".IMODE."/printer.png");
			$text_emailprint .= "<a href='".e_BASE."print.php?".$print.".".$id."'><img src='".$ico_print."' style='border:0' alt='printer friendly' title='printer friendly'/></a>";
		}
		return $text_emailprint;
	}
}
	
?>