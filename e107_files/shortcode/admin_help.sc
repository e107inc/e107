if (ADMIN) {
	global $ns, $pref;
	if (!($handle=opendir(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/"))) {
		$handle=opendir(e_LANGUAGEDIR."English/admin/help/");
	}
	ob_start();
	$text = "";
	while(false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && $file != "CVS") {
			if (eregi($file, e_SELF)) {
				if (file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file)) {
					@require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file);
				} else if (file_exists(e_LANGUAGEDIR."English/admin/help/".$file)) {
					@require_once(e_LANGUAGEDIR."English/admin/help/".$file);
				}
			}
		}
	}
	closedir($handle);

	$plugpath = e_PLUGIN.substr(strrchr(substr(e_SELF, 0, strrpos(e_SELF, "/")), "/"), 1)."/help.php";
	if(file_exists($plugpath)){
		@require_once($plugpath);
	}
	$help_text = ob_get_contents();
	ob_end_clean();
	return $help_text;
}


