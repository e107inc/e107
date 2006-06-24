// $Id: languagelinks.sc,v 1.2 2006-06-24 06:55:26 e107coders Exp $

$sep = (defined("LANGLINKS_SEPARATOR")) ? LANGLINKS_SEPARATOR : "|&nbsp;";

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
		$code = $lng->convert($val);
		$name = $lng->toNative($val);
		$link = (e_QUERY) ? e_SELF."?[".$code."]".e_QUERY : e_SELF."?[".$code."]";
		$class = ($val == e_LANGUAGE) ? "languagelink_active" : "languagelink";
    	$ret[] =  "<a class='{$class}' href='{$link}' title=\"".$name."\">".$name."</a>\n";
	}

return implode($sep,$ret);
