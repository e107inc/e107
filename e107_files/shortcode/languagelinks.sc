//<? $Id$
global $pref,$lng;
if( ! defined('LANGLINKS_SEPARATOR'))
{
	define('LANGLINKS_SEPARATOR', '&nbsp;|&nbsp;');
}
//$cursub = explode('.', $_SERVER['HTTP_HOST']);

if($parm)
{
	$languageList = explode(',', $parm);
}
else
{
	$languageList = explode(',', e_LANLIST);
	sort($languageList);
}

if(count($languageList) < 2)
{
	return;
}

foreach($languageList as $languageFolder)
{
	$code = $lng->convert($languageFolder);
	$name = $lng->toNative($languageFolder);
	//$subdom = (isset($cursub[2])) ? $cursub[0] : '';

	

	if(defset('MULTILANG_SUBDOMAIN')==TRUE)
	{
		$code = ($languageFolder == $pref['sitelanguage']) ? 'www' : $code;		
		$link = $lng->subdomainUrl($languageFolder);
	}
	else
	{
		$link = (e_QUERY) ? e_SELF.'?['.$code.']'.e_QUERY : e_SELF.'?['.$code.']';
	}
	$class = ($languageFolder == e_LANGUAGE) ? 'languagelink_active' : 'languagelink';
	$ret[] =  "\n<a class='{$class}' href='{$link}'>{$name}</a>";
}

return implode(LANGLINKS_SEPARATOR, $ret);