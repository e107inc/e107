if (ADMIN) {
	global $ns, $sql, $pref;
	if ($pref['multilanguage'])
	{
		$filepath = e_PLUGIN."userlanguage_menu/languages/".e_LANGUAGE.".php";
		if(file_exists($filepath))
		{
			require_once($filepath);
		}
		else
		{
			require_once(e_PLUGIN."userlanguage_menu/languages/English.php");
		}
		$lanlist = explode(",",e_LANLIST);
        sort($lanlist);
		foreach($lanlist as $langval)
		{
			if (getperms($langval))
			{
				$lanperms[] = $langval;
			}
		}

		require_once(e_HANDLER."language_class.php");
		$slng = new language;


		if(!getperms($sql->mySQLlanguage) && $lanperms)
		{
			$sql->mySQLlanguage = ($lanperms[0] != $pref['sitelanguage']) ? $lanperms[0] : "";
			if ($pref['user_tracking'] == "session")
			{
				$_SESSION['e107language_'.$pref['cookie_name']] = $lanperms[0];
				if($pref['multilanguage_subdomain']){
					header("Location:".$slng->subdomainUrl($lanperms[0]));
				}
			}
			else
			{
				setcookie('e107language_'.$pref['cookie_name'], $lanperms[0], time() + 86400, '/');
				$_COOKIE['e107language_'.$pref['cookie_name']]= $lanperms[0];
			}
		}

		foreach($GLOBALS['mySQLtablelist'] as $tabs)
		{
			$clang = strtolower($sql->mySQLlanguage);
			if(strpos($tabs,"lan_".$clang) && $clang !="")
			{
				$aff[] = str_replace(MPREFIX."lan_".$clang."_","",$tabs);
			}
		}


		$text .= "<div><img src='".e_IMAGE."admin_images/language_16.png' alt='' />&nbsp;";
		if(isset($aff))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$slng->convert($sql->mySQLlanguage).")
			: <span class='button' style='cursor: pointer;' onclick='expandit(\"lan_tables\");'><a style='text-decoration:none' title='' href=\"javascript:void(0);\" >&nbsp;&nbsp;".count($aff)." ".UTHEME_MENU_L3."&nbsp;&nbsp;</a></span><br />
			<span style='display:none' id='lan_tables'>
			";
			$text .= implode("<br />",$aff);
			$text .= "</span>";
		}
		elseif($sql->mySQLlanguage && ($sql->mySQLlanguage != $pref['sitelanguage']))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$slng->convert($sql->mySQLlanguage)."): ".LAN_INACTIVE;
		}
		else
		{
			$text .= $pref['sitelanguage'];
		}
		$text .= "<br /><br /></div>";



		if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain'])
		{
        	$text .= "<div style='text-align:center'>
			<select class='tbox' name='lang_select' style='width:95%' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach($lanperms as $lng)
			{
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? "selected='selected'" : "";
                $urlval = $slng->subdomainUrl($lng);
				$text .= "<option value='".$urlval."' $selected>$lng</option>\n";
			}
			$text .= "</select></div>";

		}
        else
		{

			$text .= "<div style='text-align:center'>
			<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
			<div>
			<select name='sitelanguage' class='tbox'>";

			foreach($lanperms as $lng)
			{
				$langval = ($lng == $pref['sitelanguage'] && $lng == 'English') ? "" : $lng;
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? "selected='selected'" : "";
				$text .= "<option value='".$langval."' $selected>$lng</option>\n";
			}
			$text .= "</select>
			<br /><br />
	   		<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
			</div>
				</form>
			</div>";
		}

		return $ns -> tablerender(UTHEME_MENU_L2, $text, '', TRUE);
	}
}
