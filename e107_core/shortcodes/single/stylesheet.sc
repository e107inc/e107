// $Id$

global $THEMES_DIRECTORY;

$css = file_get_contents(THEME."style.css");
$search = array("url(images","url('images");
$replace[0] = "url(".SITEURL.$THEMES_DIRECTORY.$pref['sitetheme']."/images";
$replace[1] = "url('".SITEURL.$THEMES_DIRECTORY.$pref['sitetheme']."/images";
$CSS = str_replace($search,$replace,$css);

return "\n<style>\n".$CSS."\n</style>\n";

?>