// $Id$
//<?
global $pref,$lng;
if(!defined('LANGLINKS_SEPARATOR'))
{
	define('LANGLINKS_SEPARATOR', '&nbsp;|&nbsp;');
}

if($parm == "dropdown" || !$parm)
{
	$languageList = explode(',', e_LANLIST);
	sort($languageList);		
}
elseif($parm)
{
	$languageList = explode(',', $parm);
}

if(count($languageList) < 2)
{
	return;
}

$option = array();
$href = array();

foreach($languageList as $languageFolder)
{
	$code = $lng->convert($languageFolder);
	$name = $lng->toNative($languageFolder);

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
	$sel = ($languageFolder == e_LANGUAGE) ? "selected='selected'" : '';
	$href[] =  "\n<a class='{$class}' href='{$link}'>{$name}</a>";
	$option[] =  "\n<option class='{$class}' value='{$link}' class='{$class}' {$sel}>{$name}</option>";
}

if($parm == "dropdown")
{
	return "<select class='languagelink_dropdown' onchange=\"location.href=this.options[selectedIndex].value\">".implode("\n",$option)."</select>";		
}
else
{
	return implode(LANGLINKS_SEPARATOR, $href);	
}

