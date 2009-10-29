<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Default Header
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/header_default.php,v $
 * $Revision: 1.45 $
 * $Date: 2009-10-29 00:29:27 $
 * $Author: marj_nl_fr $
*/

if (!defined('e107_INIT')) { exit; }
if(!defined('USER_AREA'))
{
	//overload is now possible, prevent warnings
	define('USER_AREA',TRUE);
}
define('ADMIN_AREA',FALSE);

$e107 = e107::getInstance();
$e107->sql->db_Mark_Time('(Header Top)');

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
// D: Send CSS
// E: Send JS
// F: Send Meta Tags and Icon links
// G: Send final theme headers (theme_head() function)
// H: Generate JS for image preloading (setup for onload)
// I: Calculate onload() JS functions to be called
// J: Send end of html <head> and start of <body>
// K: (The rest is ignored for popups, which have no menus)
// L: Removed
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
//  header("Content-type: application/xhtml+xml; charset=utf-8", TRUE);
//else
  header("Content-type: text/html; charset=utf-8", TRUE);


echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='utf-8' "."?".">\n")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";

//
// C: Send start of HTML
//
echo "<html xmlns='http://www.w3.org/1999/xhtml'".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("XMLNS") ? " ".XMLNS." " : "").(defined("CORE_LC") ? " xml:lang=\"".CORE_LC."\"" : "").">
<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<meta http-equiv='content-style-type' content='text/css' />
";
echo (defined("CORE_LC")) ? "<meta http-equiv='content-language' content='".CORE_LC."' />\n" : "";
echo "<title>".(defined('e_PAGETITLE') ? e_PAGETITLE.' - ' : (defined('PAGE_NAME') ? PAGE_NAME.' - ' : "")).SITENAME."</title>\n\n";


// Wysiwyg JS support on or off.
if (varset($pref['wysiwyg'],FALSE) && check_class($pref['post_html']))
{
	define("e_WYSIWYG",TRUE);
}
else
{
	define("e_WYSIWYG",FALSE);
}

// [JSManager] Load JS Includes - Zone 1 - Before Library, e_header and style
e107::getJs()->renderJs('header', 1);
e107::getJs()->renderJs('header_inline', 1);

//
// D: send CSS comes first
//

//Core CSS first
if (!defined("PREVIEWTHEME") && (!isset($no_core_css) || !$no_core_css)) {

	echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
}

//Plugin specific CSS
if (isset($eplug_css) && $eplug_css)
{
    if(is_array($eplug_css))
	{
		$eplug_css_unique = array_unique($eplug_css);
		foreach($eplug_css_unique as $kcss)
		{
			echo ($kcss[0] == "<") ? $kcss : "<link rel='stylesheet' href='{$kcss}' type='text/css' />\n";
		}
	}
	else
	{
		echo "<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n";
	}

}

//Theme CSS
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
	}
}


// Load Plugin Header Files
if (varset($pref['e_header_list']) && is_array($pref['e_header_list']))
{
	foreach($pref['e_header_list'] as $val)
	{
		if(is_readable(e_PLUGIN.$val."/e_header.php"))
		{
			require_once(e_PLUGIN.$val."/e_header.php");
		}
	}
}

//
// E: Send JS
//

//FIXME: js manager (prevent duplicates, disable core JS etc)
// Send Javascript Libraries ALWAYS (for now)
$hash = md5(serialize(varset($pref['e_jslib'])).serialize(varset($THEME_JSLIB)).THEME.e_LANGUAGE.ADMIN).'_front';
echo "<script type='text/javascript' src='".e_FILE_ABS."e_jslib.php?{$hash}'></script>\n";

// [JSManager] Load JS Includes - Zone 2 - After Library
e107::getJs()->renderJs('header', 2);
e107::getJs()->renderJs('header_inline', 2);

// Send Plugin JS Files
//DEPRECATED - use e107::getJs()->headerFile('{e_PLUGIN}myplug/js/my.js', $zone = 2)
if (isset($eplug_js) && $eplug_js)
{
	echo "\n<!-- eplug_js -->\n";
	if(is_array($eplug_js))
	{
	   	$eplug_js_unique = array_unique($eplug_js);
    	foreach($eplug_js_unique as $kjs)
		{
        	echo ($kjs[0] == "<") ? $kjs : "<script type='text/javascript' src='{$kjs}'></script>\n";
		}
	}
	else
	{
    	echo "<script type='text/javascript' src='{$eplug_js}'></script>\n";
	}
}

// Send Theme JS Files
//DEPRECATE this as well?
if (isset($theme_js_php) && $theme_js_php)
{
	echo "<script type='text/javascript' src='".THEME_ABS."theme-js.php'></script>\n";
}
else
{
	if (file_exists(THEME.'theme.js')) { echo "<script type='text/javascript' src='".THEME_ABS."theme.js'></script>\n"; }
	if (is_readable(e_FILE.'user.js') && filesize(e_FILE.'user.js')) { echo "<script type='text/javascript' src='".e_FILE_ABS."user.js'></script>\n"; }
	if (file_exists(THEME.'theme.vbs')) { echo "<script type='text/vbscript' src='".THEME_ABS."theme.vbs'></script>\n"; }
 	if (is_readable(e_FILE.'user.vbs') && filesize(e_FILE.'user.vbs')) { echo "<script type='text/vbscript' src='".e_FILE_ABS."user.vbs'></script>\n"; }
}

//XXX - CHAP JS
// TODO - convert it to e107::getJs()->header/footerFile() call
if (!USER && ($pref['user_tracking'] == "session") && varset($pref['password_CHAP'],0))
{
  if ($pref['password_CHAP'] == 2)
  {
		// *** Add in the code to swap the display tags
	$js_body_onload[] = "expandit('loginmenuchap','nologinmenuchap');";
  }
  echo "<script type='text/javascript' src='".e_FILE_ABS."chap_script.js'></script>\n";
  $js_body_onload[] = "getChallenge();";
}

/*
if((isset($pref['enable_png_image_fix']) && $pref['enable_png_image_fix'] == true) || (isset($sleight) && $sleight == true))
{
	echo "<script type='text/javascript' src='".e_FILE_ABS."sleight_js.php'></script>\n\n";
}
*/

//IEpngfix - visible by IE6 only
if((isset($pref['enable_png_image_fix']) && $pref['enable_png_image_fix'] == true) || (isset($sleight) && $sleight == true)) {
    /*
     * The only problem is that the browser is REALLY,
     * REALLY slow when it has to render more elements
     * try e.g. "div, img, td, input" (or just *) instead only img rule
     * However I hope it'll force IE6 users to switch to a modern browser...
     */
	echo "<!--[if lte IE 6]>\n";
	echo "<style type='text/css'>\n";
	echo "img {\n";
	echo "  behavior: url('".e_FILE_ABS."iepngfix.htc.php');\n";
	echo "}\n";
	echo "</style>\n";
	echo "<![endif]-->\n";
}

//headerjs moved below

// Deprecated function finally removed
//if(function_exists('core_head')){ echo core_head(); }

// [JSManager] Load JS Includes - Zone 3 - After e_plug/theme.js, before e_meta and headerjs()
e107::getJs()->renderJs('header', 3);
e107::getJs()->renderJs('header_inline', 3);


//
// F: Send Meta Tags, Icon links, headerjs()
//

// --- Load plugin Meta files and eplug_ before others --------
if (is_array($pref['e_meta_list']))
{
	foreach($pref['e_meta_list'] as $val)
	{
		if(is_readable(e_PLUGIN.$val."/e_meta.php"))
		{
			require_once(e_PLUGIN.$val."/e_meta.php");
		}
	}
}

//headerjs moved here - it should be able to read any JS/code sent by e_meta
if (function_exists('headerjs')) {echo headerjs();  }

// [JSManager] Load JS Includes - Zone 4 - Just after e_meta, headerjs
e107::getJs()->renderJs('header', 4);
e107::getJs()->renderJs('header_inline', 4);

// FIXME description and keywords meta tags shouldn't be sent on all pages
$diz_merge = (defined("META_MERGE") && META_MERGE != FALSE && $pref['meta_description'][e_LANGUAGE]) ? $pref['meta_description'][e_LANGUAGE]." " : "";
$key_merge = (defined("META_MERGE") && META_MERGE != FALSE && $pref['meta_keywords'][e_LANGUAGE]) ? $pref['meta_keywords'][e_LANGUAGE]."," : "";

function render_meta($type)
{
	global $pref,$tp;

	if (!isset($pref['meta_'.$type][e_LANGUAGE]) || empty($pref['meta_'.$type][e_LANGUAGE]))
	{ 
		return '';
	}

	if($type == "tag")
	{
		return str_replace("&lt;", "<", $tp -> toHTML($pref['meta_tag'][e_LANGUAGE], FALSE, "nobreak, no_hook, no_make_clickable"))."\n";
	}
	else
	{
		return '<meta name="'.$type.'" content="'.$pref['meta_'.$type][e_LANGUAGE].'" />'."\n";
	}
}

echo (defined("META_DESCRIPTION")) ? "\n<meta name=\"description\" content=\"".$diz_merge.META_DESCRIPTION."\" />\n" : render_meta('description');
echo (defined("META_KEYWORDS")) ? "\n<meta name=\"keywords\" content=\"".$key_merge.META_KEYWORDS."\" />\n" : render_meta('keywords');
echo render_meta('copyright');
echo render_meta('author');
echo render_meta('tag');

unset($key_merge,$diz_merge);

// ---------- Favicon ---------
if (file_exists(THEME."favicon.ico")) 
{
	echo "<link rel='icon' href='".THEME_ABS."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".THEME_ABS."favicon.ico' type='image/xicon' />\n";
}
elseif (file_exists(e_BASE."favicon.ico")) 
{
	echo "<link rel='icon' href='".SITEURL."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".SITEURL."favicon.ico' type='image/xicon' />\n";
}

//
// G: Send Theme Headers
//
if(function_exists('theme_head'))
{
	echo theme_head();
}

// FIXME: TEXTDIRECTION compatibility CSS (marj?)
// TODO: probably better to externalise along with some other things above
// possibility to overwrite some CSS definition according to TEXTDIRECTION
// especially usefull for rtl.css
// see _blank theme for examples
if(defined('TEXTDIRECTION') && file_exists(e_FILE.'/'.strtolower(TEXTDIRECTION).'.css'))
{
	echo '
	<link rel="stylesheet" href="'.THEME_ABS.strtolower(TEXTDIRECTION).'.css" type="text/css" media="all" />';
}
//
// Unobtrusive JS, prevent 3rd party code overload
//
require_once(e_FILE."/e_css.php");


//
// FIXME H: Generate JS for image preloads (do we really need this?)
//

if ($pref['image_preload'] && is_dir(THEME.'images'))
{
	$ejs_listpics = '';

	$handle=opendir(THEME.'images');
	while ($file = readdir($handle)) 
	{
		if(preg_match("#(jpg|jpeg|gif|bmp|png)$#i", $file)) 
		{
			$ejs_listpics .= $file.",";
		}
	}

	$ejs_listpics = substr($ejs_listpics, 0, -1);
	closedir($handle);

	if (!isset($script_text)) $script_text = '';
	$script_text .= "ejs_preload('".THEME_ABS."images/','".$ejs_listpics."');\n";
}

if (isset($script_text) && $script_text) 
{
	echo "<script type='text/javascript'>\n";
	echo "<!--\n";
	echo $script_text;
	echo "// -->\n";
	echo "</script>\n";
}


//
// FIXME - I: Calculate JS onload() functions for the BODY tag
//
// Fader menu
// OLD CODE REMOVAL
//global $eMenuActive, $eMenuArea;
//if(in_array('fader_menu', $eMenuActive)) $js_body_onload[] = 'changecontent(); ';

// External links handling
$js_body_onload = array();//'externalLinks();'; - already registered to e107:loaded Event by the new JS API

// Theme JS
// XXX DEPRECATED $body_onload and related functionality
if (defined('THEME_ONLOAD')) $js_body_onload[] = THEME_ONLOAD;
$body_onload='';
if (count($js_body_onload)) $body_onload = " onload=\"".implode(" ",$js_body_onload)."\"";

//
// J: Send end of <head> and start of <body>
//

// [JSManager] Load JS Includes - Zone 5 - After theme_head, just before e107:loaded trigger
e107::getJs()->renderJs('header', 5);
e107::getJs()->renderJs('header_inline', 5);

/*
 * Fire Event e107:loaded
 * FIXME - disable core JS
 */
echo "<script type='text/javascript'>\n";
echo "<!--\n";
echo "document.observe('dom:loaded', function() {\n";
echo "e107Event.trigger('loaded', {element: null}, document);\n";
echo "});\n";
echo "// -->\n";
echo "</script>\n";

echo "</head>
<body".$body_onload.">\n";

// Header included notification, from this point header includes are not possible
define('HEADER_INIT', TRUE);

$e107->sql->db_Mark_Time("Main Page Body");

//
// K: (The rest is ignored for popups, which have no menus)
//
//echo "XXX - ".$e107_popup;
// require $e107_popup =1; to use it as header for popup without menus
if(!isset($e107_popup))
{
	$e107_popup = 0;
}
if ($e107_popup != 1) {

//
// L: Removed
//

//
// M: Send top of body for custom pages and for news
//

// ---------- New in 0.8 -------------------------------------------------------

    $def = THEME_LAYOUT;  // The active layout based on custompage matches.

  //  echo "DEF = ".$def."<br />";

	if($def == 'legacyCustom' || $def=='legacyDefault' )  // 0.6 themes.
	{
	  //	echo "MODE 0.6";
	 	if($def == 'legacyCustom')
		{
			$HEADER = ($CUSTOMHEADER) ? $CUSTOMHEADER : $HEADER;
			$FOOTER = ($CUSTOMFOOTER) ? $CUSTOMFOOTER : $FOOTER;
		}
	}
	elseif($def && $def != "legacyCustom" && (isset($CUSTOMHEADER[$def]) || isset($CUSTOMHEADER[$def]))) // 0.7 themes
	{
	  //	echo " MODE 0.7";
		$HEADER = ($CUSTOMHEADER[$def]) ? $CUSTOMHEADER[$def] : $HEADER;
		$FOOTER = ($CUSTOMFOOTER[$def]) ? $CUSTOMFOOTER[$def] : $FOOTER;
	}
    elseif($def && isset($HEADER[$def]) && isset($FOOTER[$def])) // 0.8 themes - we use only $HEADER and $FOOTER arrays.
	{
	  //	echo " MODE 0.8";
		$HEADER = $HEADER[$def];
		$FOOTER = $FOOTER[$def];
	}

	//XXX - remove all page detections
	if (e_PAGE == 'news.php' && isset($NEWSHEADER))
	{
		parseheader($NEWSHEADER);
	}
	else
	{
    	parseheader($HEADER);
	}

	unset($def);

// -----------------------------------------------------------------------------

//
// N: Send other top-of-body HTML
//

	if(ADMIN)
	{
		if(file_exists(e_BASE.'install.php')){ echo "<div class='installer'><br /><b>*** ".CORE_LAN4." ***</b><br />".CORE_LAN5."</div><br /><br />"; }
	}

// Display Welcome Message when old method activated.

	echo $e107->tp->parseTemplate("{WMESSAGE=header}");



	if(defined("PREVIEWTHEME")) 
	{
		themeHandler::showPreview();
	}


	unset($text);
}
//Trim whitepsaces after end of the script