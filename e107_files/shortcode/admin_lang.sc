if (ADMIN) {
	global $ns, $sql, $pref;
	if ($pref['multilanguage']) {
		require_once(e_PLUGIN."userlanguage_menu/languages/".e_LANGUAGE.".php");
		require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$lanlist = $fl->get_dirs(e_LANGUAGEDIR);

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<div>
		<select name='sitelanguage' class='tbox'>
		<option value=''>".$pref['sitelanguage']."</option>";

		sort($lanlist);

		foreach($lanlist as $langval){
			$langname = $langval;
			$langval = ($langval == $pref['sitelanguage']) ? "" : $langval;
			$selected = ($langval == $sql->mySQLlanguage) ? "selected='selected'" : "";
			if (table_exists("lan_".$langname)) {
				$text .= "<option value='".$langval."' $selected>$langname</option>\n ";
			}
		}

		$text .= "</select>
		<br /><br />
		<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
		</div>
		</form>
		</div>";

		return $ns -> tablerender(UTHEME_MENU_L2, $text, '', TRUE);
	}
}