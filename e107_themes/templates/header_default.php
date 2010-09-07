<?php
/*
+ ----------------------------------------------------------------------------------------------+
|     e107 website system  : http://e107.org
|     Steve Dunstan 2001-2002 : jalist@e107.org
|     Released under the terms and conditions of the GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+-----------------------------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
define("USER_AREA",TRUE);
define("ADMIN_AREA",FALSE);
$sql->db_Mark_Time('(Header Top)');

//
// *** Code sequence for headers ***
// IMPORTANT: These items are in a carefully constructed order. DO NOT REARRANGE
// without checking with experienced devs! Various subtle things WILL break.
//
// We realize this is a bit (!) of a mess and hope to make further cleanups in a future release.
//
// A: Define themable header parsing
// B: Send HTTP headers that come before any html
// C: Send start of HTML
// D: Send JS
// E: Send CSS
// F: Send Meta Tags and Icon links
// G: Send final theme headers (theme_head() function)
// H: Generate JS for image preloading (setup for onload)
// I: Calculate onload() JS functions to be called
// J: Send end of html <head> and start of <body>
// K: (The rest is ignored for popups, which have no menus)
// L: (optional) Body JS to disable right clicks
// M: Send top of body for custom pages and for news
// N: Send other top-of-body HTML
//
// Load order notes for devs
// * Browsers wait until ALL HTML has loaded before executing ANY JS
// * The last CSS tag downloaded supercedes earlier CSS tags
// * Browsers don't care when Meta tags are loaded. We load last due to
//   a quirk of e107's log subsystem.
// * Multiple external <link> file references slow down page load. Each one requires
//   browser-server interaction even when cached.
//

//
// A: Define themeable header parsing
//

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

//
// B: Send HTTP headers (these come before ANY html)
//

// send the charset to the browser - overrides spurious server settings with the lan pack settings.
// Would like to set the MIME type appropriately - but it broke other things
//if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) 
//  header("Content-type: application/xhtml+xml; charset=".CHARSET, true);
//else
  header("Content-type: text/html; charset=".CHARSET, true);


echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='".CHARSET."' "."?".">\n")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";

//
// C: Send start of HTML
//
echo "<html xmlns='http://www.w3.org/1999/xhtml'".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("XMLNS") ? " ".XMLNS." " : "").(defined("CORE_LC") ? " xml:lang=\"".CORE_LC."\"" : "").">
<head>
<meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />
<meta http-equiv='content-style-type' content='text/css' />
";
echo (defined("CORE_LC")) ? "<meta http-equiv='content-language' content='".CORE_LC."' />\n" : "";
echo "<title>".SITENAME.(defined("e_PAGETITLE") ? ": ".e_PAGETITLE : (defined("PAGE_NAME") ? ": ".PAGE_NAME : ""))."</title>\n";


//
// D: Send JS
//
echo "<!-- *JS* -->\n";

// Wysiwyg JS support on or off.
if (varset($pref['wysiwyg'],FALSE) && check_class($pref['post_html']) && varset($e_wysiwyg) != "") 
{
	require_once(e_HANDLER."tiny_mce/wysiwyg.php");
	define("e_WYSIWYG",TRUE);
	echo wysiwyg($e_wysiwyg);
}
else
{
	define("e_WYSIWYG",FALSE);
}


echo "<script type='text/javascript' src='".e_FILE_ABS."e107.js'></script>\n";
if (isset($theme_js_php) && $theme_js_php) 
{
	echo "<script type='text/javascript' src='".THEME_ABS."theme-js.php'></script>\n";
} 
else 
{
	if (is_readable(THEME.'theme.js')) { echo "<script type='text/javascript' src='".THEME_ABS."theme.js'></script>\n"; }
	if (is_readable(e_FILE.'user.js') && filesize(e_FILE.'user.js')) { echo "<script type='text/javascript' src='".e_FILE_ABS."user.js'></script>\n"; }
}


if (isset($eplug_js) && $eplug_js) 
{
	echo "\n<!-- eplug_js -->\n";
	if(is_array($eplug_js))
	{
    	foreach($eplug_js as $kjs)
		{
        	echo "<script type='text/javascript' src='{$kjs}'></script>\n";
		}
	}
	else
	{
		echo "<script type='text/javascript' src='{$eplug_js}'></script>\n";
	}

}

if((isset($pref['enable_png_image_fix']) && $pref['enable_png_image_fix'] == true) || (isset($sleight) && $sleight == true)) {
	echo "<script type='text/javascript' src='".e_FILE_ABS."sleight_js.php'></script>\n";
}

if (function_exists('headerjs')){echo headerjs();  }

//
// E: Send CSS
//
echo "<!-- *CSS* -->\n";

if (isset($eplug_css) && $eplug_css) {
	echo "\n<!-- eplug_css -->\n";
    if(is_array($eplug_css))
	{
      foreach($eplug_css as $kcss)
	  {	// Allow inline style definition - but only if $eplug_css is an array (maybe require an array later)
        if ('<style' == substr($kcss,0,6)) echo $kcss; else echo "<link rel='stylesheet' href='{$kcss}' type='text/css' />\n";
	  }
	}
	else
	{
		echo "<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n";
	}

}

echo "<!-- Theme css -->\n";
if(defined("PREVIEWTHEME")) {
	echo "<link rel='stylesheet' href='".PREVIEWTHEME."style.css' type='text/css' />\n";
} else {
	$css_default = "all";
	if (isset($theme_css_php) && $theme_css_php) {
		echo "<link rel='stylesheet' href='".THEME_ABS."theme-css.php' type='text/css' />\n";
	} else {
		if(isset($pref['themecss']) && $pref['themecss'] && file_exists(THEME.$pref['themecss']))
		{
			// Support for print and handheld media.
			if(file_exists(THEME."style_mobile.css")){
            	echo "<link rel='stylesheet' href='".THEME_ABS."style_mobile.css' type='text/css' media='handheld' />\n";
				$css_default = "screen";
			}
			if(file_exists(THEME."style_print.css")){
            	echo "<link rel='stylesheet' href='".THEME_ABS."style_print.css' type='text/css' media='print' />\n";
                $css_default = "screen";
			}
			echo "<link rel='stylesheet' href='".THEME_ABS."{$pref['themecss']}' type='text/css' media='{$css_default}' />\n";


		}
		else
		{
			// Support for print and handheld media.
			if(file_exists(THEME."style_mobile.css")){
            	echo "<link rel='stylesheet' href='".THEME_ABS."style_mobile.css' type='text/css' media='handheld' />\n";
                $css_default = "screen";
			}
			if(file_exists(THEME."style_print.css")){
            	echo "<link rel='stylesheet' href='".THEME_ABS."style_print.css' type='text/css' media='print' />\n";
                $css_default = "screen";
			}
			echo "<link rel='stylesheet' href='".THEME_ABS."style.css' type='text/css' media='{$css_default}' />\n";
		}
		if (!isset($no_core_css) || !$no_core_css) {
			echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
		}
	}
}

//
// DEPRECATED!!! This is used in log/stats.php to generate some css. Its gone in 0.8 - requirement removed
//
if(function_exists('core_head')){ echo core_head(); }


//
// F: Send Meta Tags and Icon links
//
echo "<!-- *META* -->\n";

// --- Load plugin Meta files and eplug_ before others --------
if (varset($pref['e_meta_list']) && is_array($pref['e_meta_list']))
{
	foreach($pref['e_meta_list'] as $val)
	{
		if(is_readable(e_PLUGIN.$val."/e_meta.php"))
		{
			echo "<!-- $val meta -->\n";
			require_once(e_PLUGIN.$val."/e_meta.php");
		}
	}
}

$diz_merge = (defined("META_MERGE") && META_MERGE != FALSE && $pref['meta_description'][e_LANGUAGE]) ? $pref['meta_description'][e_LANGUAGE]." " : "";
$key_merge = (defined("META_MERGE") && META_MERGE != FALSE && $pref['meta_keywords'][e_LANGUAGE]) ? $pref['meta_keywords'][e_LANGUAGE]."," : "";

function render_meta($type)
{
	global $pref,$tp;

	if (!isset($pref['meta_'.$type][e_LANGUAGE])){ return;}
	if (!$pref['meta_'.$type][e_LANGUAGE]){ return; }

	if($type == "tag")
	{
		return str_replace("&lt;", "<", $tp -> toHTML($pref['meta_tag'][e_LANGUAGE], FALSE, "nobreak, no_hook, no_make_clickable"))."\n";
	}
	else
	{
		return '<meta name="'.$type.'" content="'.$pref['meta_'.$type][e_LANGUAGE].'" />'."\n";
	}
}

echo "\n<!-- Core Meta Tags -->\n";
echo (defined("META_DESCRIPTION")) ? "<meta name=\"description\" content=\"".$diz_merge.META_DESCRIPTION."\" />\n" : render_meta('description');
echo (defined("META_KEYWORDS")) ? "<meta name=\"keywords\" content=\"".$key_merge.META_KEYWORDS."\" />\n" : render_meta('keywords');
echo render_meta('copyright');
echo render_meta('author');
echo render_meta('tag');

unset($key_merge,$diz_merge);

// ---------- Favicon ---------
if (file_exists(THEME."favicon.ico")) {
	echo "<link rel='icon' href='".THEME_ABS."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".THEME_ABS."favicon.ico' type='image/xicon' />\n";
}elseif (file_exists(e_BASE."favicon.ico")) {
	echo "<link rel='icon' href='".SITEURL."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".SITEURL."favicon.ico' type='image/xicon' />\n";
}

//
// G: Send Theme Headers
//


if(function_exists('theme_head')){
	echo "\n<!-- *THEME HEAD* -->\n";
	echo theme_head();
}


//
// H: Generate JS for image preloads
//
echo "\n<!-- *PRELOAD* -->\n";
if ($pref['image_preload']) {
	$ejs_listpics = '';
	$handle=opendir(THEME.'images');
	while ($file = readdir($handle)) {
		if(preg_match("#(jpg|jpeg|gif|bmp|png)$#i", $file)) {
			$ejs_listpics .= $file.",";
		}
	}

	$ejs_listpics = substr($ejs_listpics, 0, -1);
	closedir($handle);

	if (!isset($script_text)) $script_text = '';
	$script_text .= "ejs_preload('".THEME_ABS."images/','".$ejs_listpics."');\n";
}

if (isset($script_text) && $script_text) {
	echo "<script type='text/javascript'>\n";
	echo "<!--\n";
	echo $script_text;
	echo "// -->\n";
	echo "</script>\n";
}


//
// I: Calculate JS onload() functions for the BODY tag
//
// Fader menu
global $eMenuActive;
if(in_array('fader_menu', $eMenuActive)) $js_body_onload[] = 'changecontent(); ';

// External links handling
$js_body_onload[] = 'externalLinks();';

// Theme JS
if (defined('THEME_ONLOAD')) $js_body_onload[] = THEME_ONLOAD;
if (count($js_body_onload)) $body_onload = " onload=\"".implode(" ",$js_body_onload)."\"";

//
// J: Send end of <head> and start of <body>
//
echo "</head>
<body".$body_onload.">\n";
$sql->db_Mark_Time("Main Page Body");

//
// K: (The rest is ignored for popups, which have no menus)
//
//echo "XX - ".$e107_popup;
// require $e107_popup =1; to use it as header for popup without menus
if(!isset($e107_popup))
{
	$e107_popup = 0;
}
if ($e107_popup != 1) {
	
//
// L: (optional) Body JS to disable right clicks
//
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

//
// M: Send top of body for custom pages and for news
//
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

	$e107ParseHeaderFlag = FALSE;				// Deliberately choose a distinct Name! (Its got to reach the footer)
	if (e_PAGE == 'news.php' && isset($NEWSHEADER)) 
	{
		parseheader($NEWSHEADER);
	} 
	else 
	{
		$full_query = e_SELF."?".e_QUERY."!";
		foreach ($custompage as $key_extract => $cust_extract) 
		{
			foreach ($cust_extract as $key => $kpage) 
			{
				if ($kpage && (strstr(e_SELF, $kpage) || strstr($full_query,$kpage))) 
				{
					$e107ParseHeaderFlag = TRUE;
					if ($key_extract=='no_array') 
					{
						$cust_header = $CUSTOMHEADER ? $CUSTOMHEADER : $HEADER;
						$e107CustomFooter = $CUSTOMFOOTER ? $CUSTOMFOOTER : $FOOTER;
					} 
					else 
					{
						$cust_header = $CUSTOMHEADER[$key_extract] ? $CUSTOMHEADER[$key_extract] : $HEADER;
						$e107CustomFooter = $CUSTOMFOOTER[$key_extract] ? $CUSTOMFOOTER[$key_extract] : $FOOTER;	// Another intentionally distinct name
					}
					break;
				}
			}
		}
		parseheader(($e107ParseHeaderFlag ? $cust_header : $HEADER));
	}


//
// N: Send other top-of-body HTML
//

	if(ADMIN){
		if(file_exists(e_BASE.'install.php')){ echo "<div class='installe' style='text-align:center'><br /><b>*** ".CORE_LAN4." ***</b><br />".CORE_LAN5."</div><br /><br />"; }
	}

// Display Welcome Message when old method activated.

	echo $tp->parseTemplate("{WMESSAGE=header}");



	if(defined("PREVIEWTHEME")) {
		themeHandler :: showPreview();
	}


	unset($text);
}
?>