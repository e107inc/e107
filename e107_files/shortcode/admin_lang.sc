if (ADMIN) {
	global $ns, $sql, $pref;
	if ($pref['multilanguage']) {
	  	$filepath = e_PLUGIN."userlanguage_menu/languages/".e_LANGUAGE.".php";
	  	if(file_exists($filepath)){
	 		require_once($filepath);
	 	}else{
	 		require_once(e_PLUGIN."userlanguage_menu/languages/English.php");
		}
		require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$lanlist = $fl->get_dirs(e_LANGUAGEDIR);

		foreach($GLOBALS['mySQLtablelist'] as $tabs){
			$clang = strtolower($sql->mySQLlanguage);
        	if(strpos($tabs,"lan_".$clang) && $clang !=""){
            	$aff[] = str_replace(MPREFIX."lan_".$clang."_","",$tabs);
			}
		}

		$text .= "<div><img src='".e_IMAGE."admin_images/language_16.png' alt='' />&nbsp;";
		$text .= ($sql->mySQLlanguage) ? $sql->mySQLlanguage.": " : LAN_INACTIVE;
		if(isset($aff)){
			$text .= implode(",",$aff);
		}
        $text .= "<br /><br /></div>";

		$text .= "<div style='text-align:center'>
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
				$affects[] = $langname;
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