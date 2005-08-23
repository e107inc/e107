<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/header_default.php,v $
|     $Revision: 1.63 $
|     $Date: 2005-08-23 12:22:04 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!function_exists("parseheader")) {
	function parseheader($LAYOUT){
		global $tp;
		$tmp = explode("\n", $LAYOUT);
		for ($c=0; $c < count($tmp); $c++) {
			if (preg_match("/{.+?}/", $tmp[$c])) {
				echo $tp -> parseTemplate($tmp[$c]);
			} else {
				echo $tmp[$c];
			}
		}
	}
}
$sql->db_Mark_Time('(Header Top)');

echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='".CHARSET."' "."?".">")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' ".(defined("TEXTDIRECTION") ? "dir='".TEXTDIRECTION."'" : "")." ".(defined("CORE_LC") ? "lang=\"".CORE_LC."\" xml:lang=\"".CORE_LC."\" " : "").">
<head>
<title>".SITENAME.(defined("e_PAGETITLE") ? ": ".e_PAGETITLE : (defined("PAGE_NAME") ? ": ".PAGE_NAME : ""))."</title>\n";
echo "<meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />
<meta http-equiv='content-style-type' content='text/css' />\n";

if(isset($pref['rss_feeds']) && $pref['rss_feeds'] && file_exists(e_PLUGIN."rss_menu/rss_meta.php")){
	require_once(e_PLUGIN."rss_menu/rss_meta.php");
}

if(isset($pref['trackbackEnabled'])){
echo "<link rel='pingback' href='".SITEURLBASE.e_PLUGIN_ABS."trackback/xmlrpc.php' />\n";
}
if((isset($pref['enable_png_image_fix']) && $pref['enable_png_image_fix'] == true) || (isset($sleight) && $sleight == true)) {
	echo "<script type='text/javascript' src='".e_FILE_ABS."sleight_js.php'></script>\n";
}

if (isset($eplug_css) && $eplug_css) { echo "\n<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n"; }
if (isset($eplug_js) && $eplug_js) { echo "<script type='text/javascript' src='{$eplug_js}'></script>\n"; }

if(defined("PREVIEWTHEME")) {
	echo "<link rel='stylesheet' href='".PREVIEWTHEME."style.css' type='text/css' />\n";
} else {
	if (isset($theme_css_php) && $theme_css_php) {
		echo "<link rel='stylesheet' href='".THEME_ABS."theme-css.php' type='text/css' />\n";
	} else {
		if(isset($pref['themecss']) && $pref['themecss'] && file_exists(THEME.$pref['themecss']))
		{
			echo "<link rel='stylesheet' href='".THEME_ABS."{$pref['themecss']}' type='text/css' />\n";
		}
		else
		{

			echo "<link rel='stylesheet' href='".THEME_ABS."style.css' type='text/css' />\n";
		}
		if (!isset($no_core_css) || !$no_core_css) {
			echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
		}
	}
}

if(function_exists('theme_head')){
	echo theme_head();
}
if(function_exists('core_head')){ echo core_head(); }
if (file_exists(e_BASE."favicon.ico")) { echo "<link rel='icon' href='".SITEURL."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".SITEURL."favicon.ico' type='image/xicon' />\n"; }

echo $pref['meta_tag'] ? str_replace("&lt;", "<", $tp -> toHTML($pref['meta_tag'], FALSE, "nobreak, no_hook, no_make_clickable"))."\n" : "";
if (isset($theme_js_php) && $theme_js_php) {
	echo "<link rel='stylesheet' href='".THEME_ABS."theme-js.php' type='text/css />";
} else {
	echo "<script type='text/javascript' src='".e_FILE_ABS."e107.js'></script>\n";
	if (file_exists(THEME.'theme.js')) { echo "<script type='text/javascript' src='".THEME_ABS."theme.js'></script>\n"; }
	if (filesize(e_FILE.'user.js')) { echo "<script type='text/javascript' src='".e_FILE_ABS."user.js'></script>\n"; }
}
if (isset($WYSIWYG) && $WYSIWYG == TRUE && check_class($pref['post_html'] && isset($e_wysiwyg) && $e_wysiwyg != "")) { require_once(e_HANDLER."tiny_mce/wysiwyg.php"); echo wysiwyg($e_wysiwyg);}
if (function_exists('headerjs')){echo headerjs();  }

if (isset($pref['statActivate']) && $pref['statActivate']) {
	if(!$pref['statCountAdmin'] && ADMIN) {
		/* don't count admin visits */
	} else {
		require_once(e_PLUGIN."log/consolidate.php");
		$script_text = "document.write( '<link rel=\"stylesheet\" type=\"text/css\" href=\"".e_PLUGIN_ABS."log/log.php?referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '\">' );\n";
	}
}

if ($pref['image_preload']) {
	$ejs_listpics = '';
	$handle=opendir(THEME.'images');
	while ($file = readdir($handle)) {
		if (!strstr($file, "._") && strstr($file,".") && $file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store") {
			$ejs_listpics .= $file.",";
		}
	}

	$ejs_listpics = substr($ejs_listpics, 0, -1);
	closedir($handle);

	$script_text .= "ejs_preload('".THEME_ABS."images/','".$ejs_listpics."');\n";
}
if (isset($script_text) && $script_text) {
	echo "<script type='text/javascript'>\n";
	echo "<!--\n";
	echo $script_text;
	echo "// -->\n";
	echo "</script>\n";
}

$fader_onload='';
if(in_array('fader_menu', $eMenuActive))
{
	$fader_onload = 'changecontent(); ';
}

$links_onload = 'externalLinks();';
$body_onload = ($fader_onload != '' || $links_onload != '') ? " onload='".$fader_onload.$links_onload."'" : "";

echo "</head>
<body".$body_onload.">\n";
//echo "XX - ".$e107_popup;
// require $e107_popup =1; to use it as header for popup without menus
if(!isset($e107_popup))
{
	$e107_popup = 0;
}
if ($e107_popup != 1) {
	if (isset($pref['no_rightclick']) && $pref['no_rightclick']) {
		echo "<script language='javascript'>\n";
		echo "<!--\n";
		echo "var message=\"Not Allowed\";\n";
		echo "function click(e) {\n";
		echo "	if (document.all) {\n";
		echo "		if (event.button==2||event.button==3) {\n";
		echo "			alert(message);\n";
		echo "			return false;\n";
		echo "		}\n";
		echo "	}\n";
		echo "	if (document.layers) {\n";
		echo "		if (e.which == 3) {\n";
		echo "			alert(message);\n";
		echo "			return false;\n";
		echo "		}\n";
		echo "	}\n";
		echo "}\n";
		echo "if (document.layers) {\n";
		echo "	document.captureevents(event.mousedown);\n";
		echo "}\n";
		echo "document.onmousedown=click;\n";
		echo "// -->\n";
		echo "</script>\n";
	}

	if(isset($CUSTOMPAGES))
	{
		if (is_array($CUSTOMPAGES))
		{
			foreach ($CUSTOMPAGES as $cust_key => $cust_value)
			{
				$custompage[$cust_key] = explode(' ', $cust_value);
			}
		}
		else
		{
			$custompage['no_array'] = explode(' ', $CUSTOMPAGES);
		}
	}
	else
	{
		$custompage['no_array'] = array();
	}

	$ph = FALSE;
	if (e_PAGE == 'news.php' && isset($NEWSHEADER)) {
		parseheader($NEWSHEADER);
	} else {
		foreach ($custompage as $key_extract => $cust_extract) {
			foreach ($cust_extract as $key => $kpage) {
				if ($kpage && strstr(e_SELF, $kpage)) {
					$ph = TRUE;
					if ($key_extract=='no_array') {
						$cust_header = $CUSTOMHEADER ? $CUSTOMHEADER : $HEADER;
						$cust_footer = $CUSTOMFOOTER ? $CUSTOMFOOTER : $FOOTER;
					} else {
						$cust_header = $CUSTOMHEADER[$key_extract] ? $CUSTOMHEADER[$key_extract] : $HEADER;
						$cust_footer = $CUSTOMFOOTER[$key_extract] ? $CUSTOMFOOTER[$key_extract] : $FOOTER;
					}
					break;
				}
			}
		}
		parseheader(($ph ? $cust_header : $HEADER));
	}
	$sql->db_Mark_Time("Main Page Body");


	if(defined("PREVIEWTHEME")) {
		themeHandler :: showPreview();
	}


	unset($text);
}
?>