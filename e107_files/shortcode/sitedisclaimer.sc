global $tp;
$ret = $tp->toHtml(SITEDISCLAIMER,TRUE,"constants defs");
$ret .=(defined("THEME_DISCLAIMER") && $pref['displaythemeinfo'] ? THEME_DISCLAIMER : "");
return $ret;