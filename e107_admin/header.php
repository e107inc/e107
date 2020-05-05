<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Header
 *
 * $URL:$
 * $Id:$
*/

if (!defined('e107_INIT'))
{
	exit;
}
if (!defined('ADMIN_AREA'))
{
	//avoid PHP warning
	define("ADMIN_AREA", TRUE);
}
if(!defined('USER_AREA'))
{
	define("USER_AREA", FALSE);
}

e107::getDebug()->logTime('(Header Top)');


if(!deftrue('e_MENUMANAGER_ACTIVE'))
{

	if (defined('THEME') && file_exists(THEME.'admin_template.php')) // Admin template
	{
		require_once (THEME.'admin_template.php');
	}
	else
	{
		require_once (e_CORE.'templates/admin_template.php');
	}


	// FIXME - remove ASAP
	if (isset($pref['del_unv']) && $pref['del_unv'] && $pref['user_reg_veri'] != 2)
	{
		$threshold = (time() - ($pref['del_unv'] * 60));
		e107::getDb()->delete("user", "user_ban = 2 AND user_join < '{$threshold}' ");
	}
}


function loadJSAddons()
{
	if(deftrue('e_MENUMANAGER_ACTIVE'))
	{
		return; 
	}

	// TODO use Library Manager. Remove unused libraries...

	e107::css('core', 'bootstrap-select/bootstrap-select.min.css', 'jquery');
	e107::js('footer', '{e_WEB}js/bootstrap-select/bootstrap-select.min.js', 'jquery', 2);
	
//	e107::css('core', 'bootstrap-multiselect/css/bootstrap-multiselect.css', 'jquery');
	e107::js('footer', '{e_WEB}js/bootstrap-multiselect/js/bootstrap-multiselect.js', 'jquery', 2);

	// TODO: remove typeahead.
	e107::js('footer', '{e_WEB}js/bootstrap-jasny/js/jasny-bootstrap.js', 'jquery', 2);
	
	e107::css('core', 'bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css', 'jquery');
	e107::js('footer', '{e_WEB}js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', 'jquery', 2);
	
	e107::js('footer','{e_WEB}js/jquery.h5validate.min.js','jquery', 2);
	
	e107::js('footer', '{e_WEB}js/jquery.elastic.js', 'jquery', 2);
	e107::js('footer', '{e_WEB}js/jquery.at.caret.min.js', 'jquery', 2);

	e107::css('core', 'core/admin.jquery.css', 'jquery');
	e107::css('core', 'core/all.jquery.css', 'jquery');

	e107::js('footer', '{e_WEB}js/core/admin.jquery.js', 'jquery', 5); // Load all default functions.
	e107::js('footer', '{e_WEB}js/core/all.jquery.js', 'jquery', 5); // Load all default functions.

	$plUpload = 'plupload/i18n/' . e_LAN . '.js';

    if(e_LAN != 'en' && file_exists(e_WEB_JS . $plUpload))
    {
     e107::js('footer', e_WEB_JS. $plUpload, 'jquery', 5);
    }
}


// Load other JS files.
loadJSAddons();






// e107::js("core",	"core/admin.js","prototype",3); // Load all default functions.


//
// *** Code sequence for headers ***
// IMPORTANT: These items are in a carefully constructed order. DO NOT REARRANGE
// without checking with experienced devs! Various subtle things WILL break.
//
// We realize this is a bit (!) of a mess and hope to make further cleanups in a future release.
// FIXME - outdated list
// A: Admin Defines and Links
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
// A: Admin Defines and Links
//


//
// B: Send HTTP headers (these come before ANY html)
// moved to boot.php

//
// B.2: Include admin LAN and icon defines
// Moved to boot.php

//
// C: Send start of HTML
//
echo "<!doctype html>\n";
echo "<html".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("CORE_LC") ? " lang=\"".CORE_LC."\"" : "").">\n";
echo "<head>\n";
echo "<meta charset='utf-8' />\n";
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=0.8, maximum-scale=1\" />\n"; // Works better for iOS but still has some issues.
echo "<title>".(defined("e_PAGETITLE") ? e_PAGETITLE." - " : (defined("PAGE_NAME") ? PAGE_NAME." - " : "")).LAN_HEADER_04." :: ".SITENAME."</title>\n";

//
// D: Send CSS
//
echo "<!-- *CSS* -->\n";
$e_js =  e107::getJs();


// Core CSS - XXX awaiting for path changes
if (!isset($no_core_css) || !$no_core_css)
{
	//echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
	$e_js->otherCSS('{e_WEB_CSS}e107.css');
}




// Register Plugin specific CSS (BC)
if (isset($eplug_css) && $eplug_css)
{
	e107::getMessage()->addDebug('Deprecated $eplug_css method detected. Use e107::css() in an e_header.php file instead.'.print_a($eplug_css,true)); 
	
    if(!is_array($eplug_css))
	{
		$eplug_css = array($eplug_css);
	}

	foreach($eplug_css as $kcss)
	{
		$e_js->otherCSS($kcss);
	}
}





if(deftrue('e_MENUMANAGER_ACTIVE')) // load frontend style.css
{
	$css_file = $pref['themecss'];
	$e_js->themeCSS($css_file); // Test with superhero.css for frontend bootstrap and 'dark' for backend bootstrap.

}
else // backend css.
{

	$custom = e107::getThemeGlyphs();
	foreach($custom as $val)
	{
		$e_js->otherCSS($val['path']);
	}

		//NEW - Iframe mod
		/*
	if(!deftrue('e_IFRAME') && !empty($pref['admincss']))
	{
		$css_file = file_exists(THEME.'admin_'.$pref['admincss']) ? 'admin_'.$pref['admincss'] : $pref['admincss'];
		//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		$e_js->themeCSS($css_file);

	}
	elseif(isset($pref['themecss']) && $pref['themecss'])
	{
		$css_file = (file_exists(THEME.'admin_'.$pref['themecss'])) ? 'admin_'.$pref['themecss'] : $pref['themecss'];
		//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		// $e_js->themeCSS($css_file); // Test with superhero.css for frontend bootstrap and 'dark' for backend bootstrap.
	}
	else
	{
		$css_file = (file_exists(THEME.'admin_style.css')) ? 'admin_style.css' : 'style.css';
		//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		$e_js->themeCSS($css_file);
	}*/



}







// FIXME: TEXTDIRECTION compatibility CSS (marj?)
// TODO: probably better to externalise along with some other things above
// possibility to overwrite some CSS definition according to TEXTDIRECTION
// especially usefull for rtl.css
// see _blank theme for examples
if(defined('TEXTDIRECTION') && file_exists(THEME.'/'.strtolower(TEXTDIRECTION).'.css'))
{
	$e_js->themeCSS(strtolower(TEXTDIRECTION).'.css');
}


// --- Load plugin Header  files  before all CSS nad JS zones.  --------
if (!empty($pref['e_header_list']) && is_array($pref['e_header_list']))
{
	foreach($pref['e_header_list'] as $val)
	{
		// no checks fore existing file - performance
		e107_include_once(e_PLUGIN.$val."/e_header.php");
	}
}
unset($e_headers);


// ################### RENDER CSS

echo "\n<!-- Library CSS -->\n";

$e_js->renderJs('library_css', false, 'css', false);
echo "\n<!-- footer_library_css -->\n"; // substituted in footer when detected.

// Other CSS - from unknown location, different from core/theme/plugin location or backward compatibility
$e_js->renderJs('other_css', false, 'css', false);
echo "\n<!-- footer_other_css -->\n"; // substituted in footer when detected.

// Core CSS
$e_js->renderJs('core_css', false, 'css', false);
echo "\n<!-- footer_core_css -->\n";



// Theme CSS
//echo "<!-- Theme css -->\n";
$e_js->renderJs('theme_css', false, 'css', false);
echo "\n<!-- footer_theme_css -->\n";

// Plugin CSS
$e_js->renderJs('plugin_css', false, 'css', false);
echo "\n<!-- footer_plugin_css -->\n";

// Inline CSS - not sure if this should stay at all!
$e_js->renderJs('inline_css', false, 'css', false);
echo "\n<!-- footer_inline_css -->\n";

//
// Unobtrusive JS via CSS, prevent 3rd party code overload
//
// require_once(e_FILE."/e_css.php"); //moved to e107_web/css/e107.css 

//
// E: Send JS
//
echo "<!-- *JS* -->\n";

// Wysiwyg JS support on or off.
// your code should run off e_WYSIWYG
// Moved to boot.php

// [JSManager] Load JS Includes - Zone 1 - Before Library
e107::getJs()->renderJs('header', 1);
e107::getJs()->renderJs('header_inline', 1);

// Load Javascript Library consolidation script
$jslib = e107::getObject('e_jslib', null, e_HANDLER.'jslib_handler.php');
$jslib->renderHeader('admin', false);

// [JSManager] Load JS Includes - Zone 2 - After Library, before CSS
e107::getJs()->renderJs('header', 2);
e107::getJs()->renderJs('header_inline', 2);

/* @deprecated -use e107::js('myplug','js/my.js') instead; */
if (!empty($eplug_js))
{
	e107::getMessage()->addDebug('Deprecated $eplug_js method detected. Use e107::js() function inside an e_header.php file instead.'.print_a($eplug_js,true));
	echo "\n<!-- eplug_js -->\n";

	if(!is_array($eplug_js))
	{
		$eplug_js = array($eplug_js);
	}

	foreach($eplug_js as $vjss)
	{
		echo "<script type='text/javascript' src='{$vjss}'></script>\n";
	}

}

//FIXME - theme.js/user.js should be registered/rendered through e_jsmanager
// BC Fix.
if (file_exists(THEME.'theme.js'))
{
	e107::js('theme','theme.js',null,3); 
//	echo "<script type='text/javascript' src='".THEME_ABS."theme.js'></script>\n";
}


if (is_readable(e_FILE.'user.js') && filesize(e_FILE.'user.js'))
{
	echo "<script type='text/javascript' src='".e_FILE_ABS."user.js'></script>\n";
}


// [JSManager] Load JS Includes - Zone 3 - before e_meta and headerjs()
e107::getJs()->renderJs('header', 3);
e107::getJs()->renderJs('header_inline', 3);

//
// F: Send Meta Tags and Icon links
//
echo "<!-- *META* -->\n";

// --- Load plugin Meta files and eplug_ before others --------

/** @var array $incompatibleMeta - plugins that may cause jQuery conflicts etc if loaded.  */
$incompatibleMeta = array('aa_jquery', 'fancybox', 'lightwindow', 'e107slider' );

if (!empty($pref['e_meta_list']))
{
	foreach ($pref['e_meta_list'] as $val)
	{
	    if(in_array($val,$incompatibleMeta))
        {
            continue;
        }

		if (is_readable(e_PLUGIN.$val."/e_meta.php"))
		{
			echo "\n\n<!-- $val meta -->\n";
			require_once (e_PLUGIN.$val."/e_meta.php");
		}
	}
}

unset($incompatibleMeta);


if (!USER && ($pref['user_tracking'] == "session") && varset($pref['password_CHAP'],0))
{
	if ($pref['password_CHAP'] == 2)
  	{
		// *** Add in the code to swap the display tags
//		$js_body_onload[] = "expandit('loginmenuchap','nologinmenuchap');";
		$js_body_onload[] = "expandit('loginmenuchap');";
		$js_body_onload[] = "expandit('nologinmenuchap');";
  	}
  	echo "<script type='text/javascript' src='".e_JS."chap_script.js'></script>\n";
  	$js_body_onload[] = "getChallenge();";
}


if (function_exists('headerjs'))// required for BC.
{
	echo headerjs();
}

// Admin UI - send header content if any - headerjs() replacement
$tmp = e107::getAdminUI();
if($tmp)
{
	// Note: normally you shouldn't send JS content here, former is (much better) handled by JS manager (both files and inline)
	echo $tmp->getHeader();
}
unset($tmp);

// [JSManager] Load JS Includes - Zone 4 - After e_meta, headerjs, before Admin UI headers
e107::getJs()->renderJs('header', 4);
e107::getJs()->renderJs('header_inline', 4);

// ---------- Favicon ---------

$sitetheme = e107::getPref('sitetheme');
if (file_exists(e_THEME.$sitetheme."/favicon.ico"))
{
	echo "<link rel='icon' href='".e_THEME_ABS.$sitetheme."/favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".e_THEME_ABS.$sitetheme."/favicon.ico' type='image/xicon' />\n";
}
elseif (file_exists(e_BASE."favicon.ico"))
{
	echo "<link rel='icon' href='".SITEURL."favicon.ico' type='image/x-icon' />\n<link rel='shortcut icon' href='".SITEURL."favicon.ico' type='image/xicon' />\n";
}
unset($sitetheme);
//
// G: Send Theme Headers
//

if (function_exists('theme_head'))
{
	echo "\n<!-- *THEME HEAD* -->\n";
	echo theme_head();
}

//
// H: Generate JS for image preloads [user mode only]
//
echo "\n<!-- *PRELOAD* -->\n";

//
// I: Calculate JS onload() functions for the BODY tag [user mode only]
//
// XXX DEPRECATED $body_onload and related functionality
if (defined('THEME_ONLOAD')) $js_body_onload[] = THEME_ONLOAD;
$body_onload='';
if (count($js_body_onload)) $body_onload = " onload=\"".implode(" ",$js_body_onload)."\"";


if(deftrue('e_MENUMANAGER_ACTIVE'))
{
	$body_onload .= " id=\"layout-".e107::getForm()->name2id(THEME_LAYOUT)."\" ";
}
else
{
	$bodyID = deftrue("e_CURRENT_PLUGIN") ? e_CURRENT_PLUGIN : str_replace(".php","",e_PAGE);
	$bodyID .= (!empty($_GET['mode']) && !empty($_GET['action'])) ? "-".$_GET['mode'].'-'.$_GET['action'] : '';
	$body_onload .= " id=\"admin-".e107::getForm()->name2id($bodyID)."\" ";
	unset($bodyID);
}

//
// J: Send end of <head> and start of <body>
//

/*
 * Admin LAN
 * TODO - remove it from here
 *//*
e107::js('inline',"
	(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
	(".e_jshelper::toString(LAN_DELETE).").addModLan('core', 'delete');

",'prototype',5);*/

// [JSManager] Load JS Includes - Zone 5 - After theme_head, before e107:loaded trigger

// unobtrusive JS - moved here from external e_css.php

e107::getJs()->renderJs('header', 5);


/*
 * Fire Event e107:loaded - Prototype only
 */
//\$('e-js-css').remove();
/*
e107::js('inline',"
document.observe('dom:loaded', function () {
e107Event.trigger('loaded', null, document);
});
",'prototype',5);
 */

e107::getJs()->renderJs('header_inline', 5);

echo "</head>";

if(deftrue('e_MENUMANAGER_ACTIVE') && defset('THEME_LAYOUT') && e_theme::loadLayout(THEME_LAYOUT)) // v2.2.2+ html layout is active which contains <body> tag.
{
	echo "\n\n<!-- Start theme.html -->\n";
}
else
{
	echo "
	<body".$body_onload.">\n";

	echo getModal();
	echo getAlert();
}

function getModal()
{

	if(deftrue('BOOTSTRAP'))  // see bootstrap3/admin_template.php
    {
		return '';
	}

	if(e_PAGE == 'menus.php' && vartrue($_GET['configure'])) // Menu Manager iFrame disable
	{
		return null;
	}
	/*
	if(e_PAGE == "image.php")
	{
			return;
	}
		*/
		
	return '
       
         <div id="uiModal" class="modal  fade" tabindex="-1" role="dialog"  aria-hidden="true">
            <div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
            			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
             			<h4 class="modal-caption">&nbsp;</h4>
            		 </div>

             		<div class="modal-body">
             			<p>Loading…</p>
             		</div>

             		<div class="modal-footer">
                		<a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
            		</div>
               </div>
		    </div>
        </div>';        
            
}

function getAlert()
{
	return '<div id="uiAlert" class="notifications center"><!-- empty --></div>';
}



// Header included notification, from this point header includes are not possible
define('HEADER_INIT', TRUE);

e107::getDebug()->logTime("End Head, Start Body");

//
// K: (The rest is ignored for popups, which have no menus)
//

// require $e107_popup =1; to use it as header for popup without menus
if (!isset($e107_popup))
{
	$e107_popup = 0;
}
if ($e107_popup != 1)
{

	//
	// L: (optional) Body JS to disable right clicks [reserved; user mode]
	//

	//
	// M: Send top of body for custom pages and for news [user mode only]
	//

	//
	// N: Send other top-of-body HTML
	//

	// moved to boot.php
	//$ns = new e107table;
	//$e107_var = array();
	
	// function e_admin_me/nu moved to boot.php (e107::getNav()->admin)
	// legacy function show_admin_menu moved to boot.php
	// include admin_template.php moved to boot.php
	// function parse_admin moved to boot.php
	// legacy function admin_updatXXe moved to boot.php
	// (legacy?) function admin_purge_related moved to boot.php


	e107::getDebug()->logTime('Parse Admin Header');
		
	//NEW - Iframe mod
	if (!deftrue('e_IFRAME'))
	{
		//removed  check strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE
		parse_admin($ADMIN_HEADER);
	}
	elseif(!vartrue($_GET['configure'])) 
	{
		e107::css("inline","body { padding:0px } "); // default padding for iFrame-only. 
	}

	e107::getDebug()->logTime('(End: Parse Admin Header)');
}

// XXX - we don't need this (use e107::getMessage()) - find out what's using it and remove it
if (!varset($emessage) && !is_object($emessage))
{
	$emessage = e107::getMessage();
}
