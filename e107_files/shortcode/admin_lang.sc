/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: admin_lang.sc,v 1.5 2008-12-17 17:27:07 secretr Exp $
 *
 * Admin Language Shortcode
 *
*/
if (!ADMIN || !$pref['multilanguage']) return '';

global $e107, $sql, $pref;
include_lan(e_PLUGIN."user_menu/languages/English.php");
$params = array();
parse_str($parm, $params);

	$lanlist = explode(",",e_LANLIST);
    sort($lanlist);
    $text = '';

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
			setcookie('e107language_'.$pref['cookie_name'], $lanperms[0], time() + 86400);
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


		$text .= "<div><img src='".e_IMAGE_ABS."admin_images/language_16.png' alt='' />&nbsp;";
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


		$select = '';
		if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain'])
		{
        	$select .= "
			<select class='tbox' name='lang_select' id='sitelanguage' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach($lanperms as $lng)
			{
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
                $urlval = $slng->subdomainUrl($lng);
				$select .= "<option value='".$urlval."'{$selected}>$lng</option>\n";
			}
			$select .= "</select>";

		}
		elseif(isset($params['nobutton']))
		{
			$select .= "
			<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
			<div>
			<select name='sitelanguage' id='sitelanguage' class='tbox' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach($lanperms as $lng)
			{
				$langval = e_SELF.'?['.$slng->convert($lng).']'.e_QUERY;
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				$select .= "<option value='".$langval."'{$selected}>$lng</option>\n";
			}
			$select .= "</select>
			</div>
				</form>
			";
		}
        else
		{
			$select .= "
			<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
			<div>
			<select name='sitelanguage' id='sitelanguage' class='tbox'>";
			foreach($lanperms as $lng)
			{
				$langval = ($lng == $pref['sitelanguage'] && $lng == 'English') ? "" : $lng;
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				$select .= "<option value='".$langval."'{$selected}>$lng</option>\n";
			}
			$select .= "</select> ".(!isset($params['nobutton']) ? "<button class='update' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."'><span>".UTHEME_MENU_L1."</span></button>" : '')."
			</div>
				</form>
			";
		}

		if(isset($params['nomenu'])) return $select;

		if($select) $text .= "<div class='center'>{$select}</div>";

		return $e107->ns->tablerender(UTHEME_MENU_L2, $text, '', TRUE);

