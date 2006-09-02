// $Id: languagelinks.sc,v 1.3 2006-09-02 23:35:02 e107coders Exp $
global $pref;
$sep = (defined("LANGLINKS_SEPARATOR")) ? LANGLINKS_SEPARATOR : "|&nbsp;";
$cursub = explode(".",$_SERVER['HTTP_HOST']);

require_once(e_HANDLER."language_class.php");
$lng = new language;

	if($parm)
	{
		$tmp = explode(",",$parm);
	}
	else
	{
		$tmp = explode(",",e_LANLIST);
		sort($tmp);
	}

	if(count($tmp) < 2)
	{
		return;
	}

	foreach($tmp as $val)
	{
		$code = ($val != $pref['sitelanguage']) ? $lng->convert($val) : "www";
		$name = $lng->toNative($val);
		$subdom = (isset($cursub[2])) ? $cursub[0] : "";

		if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain']){
        	$link = (e_QUERY) ? str_replace($subdom,$code,e_SELF)."?".e_QUERY : str_replace($subdom,$code,e_SELF);
		}else{
			$link = (e_QUERY) ? e_SELF."?[".$code."]".e_QUERY : e_SELF."?[".$code."]";
		}
		$class = ($val == e_LANGUAGE) ? "languagelink_active" : "languagelink";
    	$ret[] =  "<a class='{$class}' href='{$link}' title=\"".$name."\">".$name."</a>\n";
	}

return implode($sep,$ret);