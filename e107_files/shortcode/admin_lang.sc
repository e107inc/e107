if (ADMIN) {
	global $ns, $sql, $pref;
	if ($pref['multilanguage']) {
		require_once(e_PLUGIN."userlanguage_menu/languages/".e_LANGUAGE.".php");
		$handle=opendir(e_LANGUAGEDIR);
		while ($file = readdir($handle)){
			if($file != "." && $file != ".." && $file != "/" && $file != "CVS" ){
				$lanlist[] = $file;
			}
		}
		closedir($handle);

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
			if (table_exists($langname)) {
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