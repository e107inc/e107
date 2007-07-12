global $tp;
$ret =(defined("THEME_DISCLAIMER") && $pref['displaythemeinfo'] ? THEME_DISCLAIMER : "");
return $ret;