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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/emailprint_class.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_print.php");
@include_once(e_LANGUAGEDIR."English/lan_print.php");
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_email.php");
@include_once(e_LANGUAGEDIR."English/lan_email.php");

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
			$ico_mail = (file_exists(THEME."images/email.png") ? THEME."images/email.png" : e_IMAGE."generic/".IMODE."/email.png");
			$text_emailprint .= "<a href='".e_BASE."email.php?".$email.".".$id."'><img src='".$ico_mail."' style='border:0' alt='".LAN_EMAIL_7."' title='".LAN_EMAIL_7."' /></a> ";
		}
		if ($look == 0 || $look == 2) {
			$ico_print = (file_exists(THEME."images/printer.png") ? THEME."images/printer.png" : e_IMAGE."generic/".IMODE."/printer.png");
			$text_emailprint .= "<a href='".e_BASE."print.php?".$print.".".$id."'><img src='".$ico_print."' style='border:0' alt='".LAN_PRINT_1."' title='".LAN_PRINT_1."' /></a>";
		}
		return $text_emailprint;
	}
}

?>