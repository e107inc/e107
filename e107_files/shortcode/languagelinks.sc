//<? $Id: languagelinks.sc,v 1.3 2009-08-03 21:12:45 marj_nl_fr Exp $
global $pref;
if( ! defined('LANGLINKS_SEPARATOR'))
{
	define('LANGLINKS_SEPARATOR', '&nbsp;|&nbsp;');
}
//$cursub = explode('.', $_SERVER['HTTP_HOST']);

require_once(e_HANDLER.'language_class.php');
$slng = new language;

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
	$code = $slng->convert($languageFolder);
	$name = $slng->toNative($languageFolder);
	//$subdom = (isset($cursub[2])) ? $cursub[0] : '';

	if(varset($pref['multilanguage_subdomain']))
	{
		$code = ($languageFolder == $pref['sitelanguage']) ? 'www.' : $code;
		$link = (e_QUERY)
		        ? str_replace($_SERVER['HTTP_HOST'], $code.'.'.e_DOMAIN, e_SELF).'?'.e_QUERY
		        : str_replace($_SERVER['HTTP_HOST'], $code.'.'.e_DOMAIN, e_SELF);
	}
	else
	{
		$link = (e_QUERY) ? e_SELF.'?['.$code.']'.e_QUERY : e_SELF.'?['.$code.']';
	}
	$class = ($languageFolder == e_LANGUAGE) ? 'languagelink_active' : 'languagelink';
	$ret[] =  "\n<a class='{$class}' href='{$link}'>{$name}</a>";
}

return implode(LANGLINKS_SEPARATOR, $ret);