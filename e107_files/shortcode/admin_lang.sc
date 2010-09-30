/* $Id$ */
//<?
if(ADMIN)
{
	global $ns, $sql, $pref, $lng;
	if($pref['multilanguage'])
	{
		include_lan(e_PLUGIN.'userlanguage_menu/languages/'.e_LANGUAGE.'.php');
		$lanlist = explode(',', e_LANLIST);
		sort($lanlist);
		foreach ($lanlist as $langval)
		{
			if(getperms($langval))
			{
				$lanperms[] = $langval;
			}
		}
			
		if(!getperms($sql->mySQLlanguage) && $lanperms)
		{
			$sql->mySQLlanguage = ($lanperms[0] != $pref['sitelanguage']) ? $lanperms[0] : "";
			if(defset('MULTILANG_SUBDOMAIN')==TRUE)
			{
				$_SESSION['e_language'] = $lanperms[0];
				header("Location:".$lng->subdomainUrl($lanperms[0]));
			}
			else
			{
				$_SESSION['e_language'] = $lanperms[0];
				setcookie('e107_language', $lanperms[0], time() + 86400, '/');
				$_COOKIE['e107_language'] = $lanperms[0];
			}
		}
		
		foreach ($GLOBALS['mySQLtablelist'] as $tabs)
		{
			$clang = strtolower($sql->mySQLlanguage);
			if(strpos($tabs, "lan_".$clang) && $clang != "")
			{
				$aff[] = str_replace(MPREFIX."lan_".$clang."_", "", $tabs);
			}
		}

		
		$text .= "<div><img src='".e_IMAGE."admin_images/language_16.png' alt='' />&nbsp;";
		if(isset($aff))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$lng->convert($sql->mySQLlanguage).")
			: <span class='button' style='cursor: pointer;' onclick='expandit(\"lan_tables\");'><a style='text-decoration:none;white-space:nowrap' title='' href=\"javascript:void(0);\" >&nbsp;&nbsp;".count($aff)." ".UTHEME_MENU_L3."&nbsp;&nbsp;</a></span><br />
			<span style='display:none' id='lan_tables'>
			";
			$text .= implode("<br />", $aff);
			$text .= "</span>";
		}
		elseif($sql->mySQLlanguage && ($sql->mySQLlanguage != $pref['sitelanguage']))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$lng->convert($sql->mySQLlanguage)."): ".LAN_INACTIVE;
		}
		else
		{
			$text .= $pref['sitelanguage'];
		}
		$text .= "<br /><br /></div>";

		if($lng->isLangDomain(e_DOMAIN))
		{
			$text .= "<div style='text-align:center'>
			<select class='tbox' name='lang_select' style='width:95%' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach ($lanperms as $olng)
			{
				$selected = ($olng == $sql->mySQLlanguage || ($olng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? "selected='selected'" : "";
				$urlval = $lng->subdomainUrl($olng);
				$text .= "<option value='".$urlval."' $selected>$olng</option>\n";
			}
			$text .= "</select></div>";
			
		}
		else
		{
		
			$text .= "<div style='text-align:center'>
			<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
			<div>
			<select name='sitelanguage' class='tbox'>";
			
			foreach ($lanperms as $olng)
			{
				$langval = ($olng == $pref['sitelanguage'] && $olng == 'English') ? "" : $olng;
				$selected = ($olng == $sql->mySQLlanguage || ($olng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? "selected='selected'" : "";
				$text .= "<option value='".$langval."' $selected>$olng</option>\n";
			}
			$text .= "</select>
			<br /><br />
	   		<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
			<input type='hidden' name='e-token' value='".e_TOKEN."' /> 
			</div>
				</form>
			</div>";
		}
		
		return $ns->tablerender(UTHEME_MENU_L2, $text, 'admin_lang', TRUE);
	}
}
