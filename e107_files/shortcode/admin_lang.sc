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
		<select name='sitelanguage' class='tbox'>";
		if(getperms($pref['sitelanguage'])){
		$text .= "<option value=''>".$pref['sitelanguage']."</option>";
			$valid = $pref['sitelanguage'];
        }


		foreach($lanlist as $langval){
			$langname = $langval;
			$langval = ($langval == $pref['sitelanguage']) ? "" : $langval;
			$selected = ($langval == $sql->mySQLlanguage) ? "selected='selected'" : "";
			if (table_exists("lan_".$langname) && getperms($langname)) {
				$text .= "<option value='".$langval."' $selected>$langname</option>\n ";
				$valid = $langname;
			}
		}

		$text .= "</select>
		<br /><br />
		<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
		</div>
		</form>
		</div>";

		if(!getperms($sql->mySQLlanguage) && !$valid){
			Header("Location: ".e_BASE."index.php");
		}elseif($valid && !getperms($sql->mySQLlanguage)){
				if($valid == $pref['sitelanguage']){
                 	$sql->mySQLlanguage = "";
				}else{
            		$sql->mySQLlanguage = $valid;
				}
            	if ($pref['user_tracking'] == "session") {
					$_SESSION['e107language_'.$pref['cookie_name']] = $valid;
				} else {
					setcookie('e107language_'.$pref['cookie_name'], $valid, time() + 86400);
					$_COOKIE['e107language_'.$pref['cookie_name']]= $valid;
          		}
		    Header("Location: ".e_SELF);
		}

		return $ns -> tablerender(UTHEME_MENU_L2, $text, '', TRUE);
	}
}