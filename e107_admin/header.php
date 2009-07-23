<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /e107_admin/header.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|   $Source: /cvs_backup/e107_0.8/e107_admin/header.php,v $
|   $Revision: 1.41 $
|   $Date: 2009-07-23 08:14:55 $
|   $Author: marj_nl_fr $
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
define("ADMIN_AREA",TRUE);
define("USER_AREA",FALSE);
$sql->db_Mark_Time('(Header Top)');

//
// *** Code sequence for headers ***
// IMPORTANT: These items are in a carefully constructed order. DO NOT REARRANGE
// without checking with experienced devs! Various subtle things WILL break.
//
// We realize this is a bit (!) of a mess and hope to make further cleanups in a future release.
//
// A: Admin Defines and Links
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
// A: Admin Defines and Links
//
require_once(e_ADMIN.'ad_links.php');
if (isset($pref['del_unv']) && $pref['del_unv'] && $pref['user_reg_veri'] != 2)
{
	$threshold=(time() - ($pref['del_unv'] * 60));
	$sql->db_Delete("user", "user_ban = 2 AND user_join < '{$threshold}' ");
}

//
// B: Send HTTP headers (these come before ANY html)
//

// send the charset to the browser - overrides spurious server settings with the lan pack settings.
header('Content-type: text/html; charset=utf-8', TRUE);


echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='utf-8' "."?".">\n")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";

//
// B.2: Include admin LAN defines
//

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_footer.php');





if (!defined('ADMIN_WIDTH')) {
	define('ADMIN_WIDTH', "width: 95%");
}

if (!defined('ADMIN_TRUE_ICON'))
{
	define("ADMIN_TRUE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/true_16.png' alt='' />");
	define("ADMIN_TRUE_ICON_PATH", e_IMAGE."admin_images/true_16.png");
}

if (!defined('ADMIN_FALSE_ICON'))
{
	define("ADMIN_FALSE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/false_16.png' alt='' />");
	define("ADMIN_FALSE_ICON_PATH", e_IMAGE."admin_images/false_16.png");
}

if (!defined('ADMIN_EDIT_ICON'))
{
	define("ADMIN_EDIT_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/edit_16.png' alt='' title='".LAN_EDIT."' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE."admin_images/edit_16.png");
}

if (!defined('ADMIN_DELETE_ICON'))
{
	define("ADMIN_DELETE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/delete_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE."admin_images/delete_16.png");
}

if (!defined('ADMIN_UP_ICON'))
{
	define("ADMIN_UP_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_UP_ICON_PATH", e_IMAGE."admin_images/up_16.png");
}

if (!defined('ADMIN_DOWN_ICON'))
{
	define("ADMIN_DOWN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/down_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DOWN_ICON_PATH", e_IMAGE."admin_images/down_16.png");
}

if (!defined('ADMIN_WARNING_ICON'))
{
	define("ADMIN_WARNING_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/warning_16.png' alt='' />");
	define("ADMIN_WARNING_ICON_PATH", e_IMAGE."admin_images/warning_16.png");
}

if (!defined('ADMIN_INFO_ICON'))
{
	define("ADMIN_INFO_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' />");
	define("ADMIN_INFO_ICON_PATH", e_IMAGE."admin_images/info_16.png");
}

if (!defined('ADMIN_CONFIGURE_ICON'))
{
	define("ADMIN_CONFIGURE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/configure_16.png' alt='' />");
	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE."admin_images/configure_16.png");
}

if (!defined('ADMIN_ADD_ICON'))
{
	define("ADMIN_ADD_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/add_16.png' alt='' />");
	define("ADMIN_ADD_ICON_PATH", e_IMAGE."admin_images/add_16.png");
}

if (!defined('ADMIN_VIEW_ICON'))
{
	define("ADMIN_VIEW_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/search_16.png' alt='' />");
	define("ADMIN_VIEW_ICON_PATH", e_IMAGE."admin_images/admin_images/search_16.png");
}

if (!defined('ADMIN_URL_ICON'))
{
	define("ADMIN_URL_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/forums_16.png' alt='' />");
	define("ADMIN_URL_ICON_PATH", e_IMAGE."admin_images/forums_16.png");
}

if (!defined('ADMIN_INSTALLPLUGIN_ICON'))
{
	define("ADMIN_INSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_install_16.png' alt='' />");
	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_install_16.png");
}

if (!defined('ADMIN_UNINSTALLPLUGIN_ICON'))
{
	define("ADMIN_UNINSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_uninstall_16.png' alt='' />");
	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_unstall_16.png");
}

if (!defined('ADMIN_UPGRADEPLUGIN_ICON'))
{
	define("ADMIN_UPGRADEPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' />");
	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE."admin_images/up_16.png");
}




//
// C: Send start of HTML
//

echo "<html xmlns='http://www.w3.org/1999/xhtml'".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("CORE_LC") ? " xml:lang=\"".CORE_LC."\"" : "").">
<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<meta http-equiv='content-style-type' content='text/css' />
";
echo (defined("CORE_LC")) ? "<meta http-equiv='content-language' content='".CORE_LC."' />\n" : "";
echo "<title>".SITENAME." : ".LAN_head_4.(defined("e_PAGETITLE") ? ": ".e_PAGETITLE : (defined("PAGE_NAME") ? ": ".PAGE_NAME : ""))."</title>\n";

//
// D: Send JS
//
echo "<!-- *JS* -->\n";

// Wysiwyg JS support on or off.
// your code should run off e_WYSIWYG
if (varset($pref['wysiwyg'],FALSE) && check_class($pref['post_html'])) {
	define("e_WYSIWYG",TRUE);
}else{
	define("e_WYSIWYG",FALSE);
}

// Load Javascript Libraries
$hash = md5(serialize(varset($pref['e_jslib'])).serialize(varset($THEME_JSLIB)).THEME.e_LANGUAGE.ADMIN).'_admin';
//echo "<script type='text/javascript' src='".e_FILE_ABS."e_js.php'></script>\n";
echo "<script type='text/javascript' src='".e_FILE_ABS."e_jslib.php?{$hash}'></script>\n";

if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {

	//echo "<script type='text/javascript' src='".e_FILE_ABS."e_ajax.php'></script>\n";
}
	if (file_exists(THEME.'theme.js')) { echo "<script type='text/javascript' src='".THEME_ABS."theme.js'></script>\n"; }
	if (is_readable(e_FILE.'user.js') && filesize(e_FILE.'user.js')) { echo "<script type='text/javascript' src='".e_FILE_ABS."user.js'></script>\n"; }

if (isset($eplug_js) && $eplug_js) {
	echo "\n<!-- eplug_js -->\n";
	echo "<script type='text/javascript' src='{$eplug_js}'></script>\n";
}


if ((strpos(e_SELF, 'fileinspector.php') === FALSE) && getperms("0"))
{
echo "<script type='text/javascript'>
<!--
function savepreset(ps,pid){
	if(confirm('".$tp->toJS(LAN_PRESET_CONFIRMSAVE)."'))
	{
		document.getElementById(ps).action='".e_SELF."?savepreset.'+pid;
   		document.getElementById(ps).submit();
	}
}
//-->
</script>\n";
}

//iepngfix - IE6 only
if((isset($pref['enable_png_image_fix']) && $pref['enable_png_image_fix'] == true) || (isset($sleight) && $sleight == true)) {
    /*
     * The only problem is that the browser is REALLY,
     * REALLY slow when it has to render more elements
     * try e.g. "div, img, td, input" (or just *) instead only img rule
     * However I hope this will force IE6 user to hate it :)
     */
	echo "<!--[if lte IE 6]>\n";
	echo "<style type='text/css'>\n";
	echo "img {\n";
	echo "  behavior: url('".e_FILE_ABS."iepngfix.htc.php');\n";
	echo "}\n";
	echo "</style>\n";
	echo "<![endif]-->\n";
}

if (function_exists('headerjs')){echo headerjs();  }

//
// E: Send CSS
//
echo "<!-- *CSS* -->\n";

if (isset($eplug_css) && $eplug_css) {
	echo "\n<!-- eplug_css -->\n";
	echo "<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n";
}

echo "<!-- Theme css -->\n";
if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE && isset($pref['admincss']) && $pref['admincss'] && file_exists(THEME.$pref['admincss'])) {
	$css_file = file_exists(THEME.'admin_'.$pref['admincss']) ? THEME_ABS.'admin_'.$pref['admincss'] : THEME_ABS.$pref['admincss'];
	echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
} else if (isset($pref['themecss']) && $pref['themecss'] && file_exists(THEME.$pref['themecss']))
{
	$css_file = file_exists(THEME.'admin_'.$pref['themecss']) ? THEME_ABS.'admin_'.$pref['themecss'] : THEME_ABS.$pref['themecss'];
	echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";


}
else
{
	$css_file = file_exists(THEME.'admin_style.css') ? THEME_ABS.'admin_style.css' : THEME_ABS.'style.css';
	echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
}
if (!isset($no_core_css) || !$no_core_css) {
	echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
}


//
// F: Send Meta Tags and Icon links
//
echo "<!-- *META* -->\n";

// --- Load plugin Meta files and eplug_ before others --------
if (is_array($pref['e_meta_list']))
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
// H: Generate JS for image preloads [user mode only]
//
echo "\n<!-- *PRELOAD* -->\n";

//
// I: Calculate JS onload() functions for the BODY tag [user mode only]
//
$body_onload = "";


//
// J: Send end of <head> and start of <body>
//


/*
 * Admin LAN
 */
require_once(e_HANDLER.'js_helper.php');
echo "
<script type='text/javascript'>
	(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
	(".e_jshelper::toString(LAN_DELETE).").addModLan('core', 'delete');
</script>
";


/*
 * Fire Event e107:loaded
 */
echo "<script type='text/javascript'>\n";
echo "<!--\n";
echo "document.observe('dom:loaded', function () {\n";
echo "e107Event.trigger('loaded', null, document);\n";
echo "});\n";
echo "// -->\n";
echo "</script>\n";


echo "</head>
<body".$body_onload.">\n";
$sql->db_Mark_Time("End Head, Start Body");

//
// K: (The rest is ignored for popups, which have no menus)
//

// require $e107_popup =1; to use it as header for popup without menus
if(!isset($e107_popup))
{
	$e107_popup = 0;
}
if ($e107_popup != 1) {


//
// L: (optional) Body JS to disable right clicks [reserved; user mode]
//

//
// M: Send top of body for custom pages and for news [user mode only]
//

//
// N: Send other top-of-body HTML
//

$ns = new e107table;
$e107_var = array();

/**
 * Build admin menus - addmin menus are now supporting unlimitted number of submenus
 * TODO - add this to a handler for use on front-end as well (tree, sitelinks.sc replacement)
 *
 * $e107_vars structure:
 * $e107_vars['action']['text'] -> link title
 * $e107_vars['action']['link'] -> if empty '#action' will be added as href attribute
 * $e107_vars['action']['image'] -> (new) image tag
 * $e107_vars['action']['perm'] -> permissions
 * $e107_vars['action']['include'] -> additional <a> tag attributes
 * $e107_vars['action']['sub'] -> (new) array, exactly the same as $e107_vars' first level e.g. $e107_vars['action']['sub']['action2']['link']...
 * $e107_vars['action']['sort'] -> (new) used only if found in 'sub' array - passed as last parameter (recursive call)
 * $e107_vars['action']['link_class'] -> (new) additional link class
 * $e107_vars['action']['sub_class'] -> (new) additional class used only when sublinks are being parsed
 *
 * @param string $title
 * @param string $active_page
 * @param array $e107_vars
 * @param array $tmpl
 * @param array $sub_link
 * @param bool $sortlist
 * @return string parsed admin menu (or empty string if title is empty)
 */
function e_admin_menu($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
{
	global $E_ADMIN_MENU, $e107;
	if(!$tmpl) $tmpl = $E_ADMIN_MENU;

	/*
	 * Search for id
	 */
	$temp = explode('--id--', $title, 2);
	$title = $temp[0];
	$id = str_replace(array(' ', '_'), '-',varset($temp[1]));

	unset($temp);

	/*
	 * SORT
	 */
	if ($sortlist == TRUE)
	{
		$temp = $e107_vars;
		unset($e107_vars);
		$func_list = array();
		foreach (array_keys($temp) as $key)
		{
			$func_list[] = $temp[$key]['text'];
		}

		usort($func_list, 'strcoll');

		foreach ($func_list as $func_text)
		{
			foreach (array_keys($temp) as $key)
			{
				if ($temp[$key]['text'] == $func_text)
				{
					$e107_vars[] = $temp[$key];
				}
			}
		}
		unset($temp);
	}

	$kpost = '';
	$text = '';
	if($sub_link)
	{
		$kpost = '_sub';
	}
	else $text = $tmpl['start'];


	//FIXME - e_parse::array2sc()
	$search = array();
	$search[0] = '/\{LINK_TEXT\}(.*?)/si';
	$search[1] = '/\{LINK_URL\}(.*?)/si';
	$search[2] = '/\{ONCLICK\}(.*?)/si';
	$search[3] = '/\{SUB_HEAD\}(.*?)/si';
	$search[4] = '/\{SUB_MENU\}(.*?)/si';
	$search[5] = '/\{ID\}(.*?)/si';
	$search[6] = '/\{SUB_ID\}(.*?)/si';
	$search[7] = '/\{LINK_CLASS\}(.*?)/si';
	$search[8] = '/\{SUB_CLASS\}(.*?)/si';
	$search[9] = '/\{LINK_IMAGE\}(.*?)/si';
	foreach (array_keys($e107_vars) as $act)
	{
		$replace = array();
		if ($active_page == $act || (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act)))
		{
			$temp = $tmpl['button_active'.$kpost];
		}
		else
		{
			$temp = $tmpl['button'.$kpost];
		}

		$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
		$replace[1] = varsettrue($e107_vars[$act]['link'], "#{$act}");
		$replace[2] = '';
		if (varsettrue($e107_vars[$act]['include']))
		{
			$replace[2] = $e107_vars[$act]['include'];
			//$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
		}
		$replace[3] = $title;
		$replace[4] = '';

		$rid = str_replace(array(' ', '_'), '-', $act).($id ? "-{$id}" : '');
		$replace[5] = $id ? " id='eplug-nav-{$rid}'" : '';
		$replace[6] = '';
		$replace[7] = varset($e107_vars[$act]['link_class']);
		$replace[8] = '';
		$replace[9] = $e107_vars[$act]['image'];

		if(varsettrue($e107_vars[$act]['sub']))
		{
			$replace[6] = $id ? " id='eplug-nav-{$rid}-sub'" : '';
			$replace[7] = ' '.varset($e107_vars[$act]['link_class'], 'e-expandit');
			$replace[8] = ' '.varset($e107_vars[$act]['sub_class'], 'e-hideme e-expandme');
			$replace[4] = preg_replace($search, $replace, $tmpl['start_sub']);
			$replace[4] .= e_admin_menu(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
			$replace[4] .= $tmpl['end_sub'];
		}

		$text .= preg_replace($search, $replace, $temp);
	}

	$text .= !$sub_link ? $tmpl['end'] : '';
	if($sub_link || empty($title)) return $text;


	$e107->ns->tablerender($title, $text, array('id' => $id, 'style' => 'button_menu'));
	return '';
}

/*
 *  DEPRECATED - use e_admin_menu()
 */
if (!function_exists('show_admin_menu')) {
	function show_admin_menu($title, $active_page, $e107_vars, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE) {

		//return e_admin_menu($title, $active_page, $e107_vars, false, false, $sortlist);

		global $ns, $BUTTON, $BUTTON_OVER, $BUTTONS_START, $BUTTONS_END, $SUB_BUTTON, $SUB_BUTTON_OVER, $SUB_BUTTONS_START, $SUB_BUTTONS_END;

		$id_title = "yop_".str_replace(" ", "", $title);
		if (!isset($BUTTONS_START)) {
			$BUTTONS_START = "<div style='text-align:center; width:100%'><table class='fborder' style='width:98%;'>\n";
		}
		if (!isset($BUTTON)) {
			$BUTTON = "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:pointer; text-decoration:none;' {ONCLICK} >{LINK_TEXT}</a></div></td></tr>\n";
		}
		if (!isset($BUTTON_OVER)) {
			$BUTTON_OVER = "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:pointer; text-decoration:none;' {ONCLICK} ><b>&laquo;&nbsp;{LINK_TEXT}&nbsp;&raquo;</b></a></div></td></tr>\n";
		}
		if (!isset($BUTTONS_END)) {
			$BUTTONS_END = "</table></div>\n";
		}
		if (!isset($SUB_BUTTON)) {
			$SUB_BUTTON = "<a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a><br />";
		}
		if (!isset($SUB_BUTTON_OVER)) {
			$SUB_BUTTON_OVER = "<b> &laquo; <a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a> &raquo; </b><br />";
		}
		if (!isset($SUB_BUTTONS_START)) {
			$SUB_BUTTONS_START = "<div style='text-align:center; width:100%'><table class='fborder' style='width:98%;'>
			<tr><td class='button'><a style='text-align:center; cursor:pointer; text-decoration:none;'
			onclick=\"expandit('{SUB_HEAD_ID}');\" >{SUB_HEAD}</a></td></tr>
			<tr id='{SUB_HEAD_ID}' style='display: none' ><td class='forumheader3' style='text-align:left;'>";
		}
		if (!isset($SUB_BUTTONS_END)) {
			$SUB_BUTTONS_END = "</td></tr></table></div>";
		}

		if ($sortlist == TRUE) {
			$temp = $e107_vars;
			unset($e107_vars);
			foreach (array_keys($temp) as $key) {
				$func_list[] = $temp[$key]['text'];
			}

			usort($func_list, 'strcoll');

			foreach ($func_list as $func_text) {
				foreach (array_keys($temp) as $key) {
					if ($temp[$key]['text'] == $func_text) {
						$e107_vars[] = $temp[$key];
					}
				}
			}
		}

		$search[0] = "/\{LINK_TEXT\}(.*?)/si";
		$search[1] = "/\{LINK_URL\}(.*?)/si";
		$search[2] = "/\{ONCLICK\}(.*?)/si";
		$search[3] = "/\{SUB_HEAD\}(.*?)/si";
		$search[4] = "/\{SUB_HEAD_ID\}(.*?)/si";

		if ($sub_link) {
			$replace[0] = '';
			$replace[1] = '#';
			$replace[2] = '';
			$replace[3] = $title;
			$replace[4] = $id_title;
			$text = preg_replace($search, $replace, $SUB_BUTTONS_START);
		} else {
			$text = $BUTTONS_START.'';
		}

		foreach (array_keys($e107_vars) as $act) {
			if (!isset($e107_vars[$act]['perm']) || !$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm'])) {
				if ($active_page == $act || (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act))) {
					$BUTTON_TEMPLATE = $sub_link ? $SUB_BUTTON_OVER : $BUTTON_OVER;
				} else {
					$BUTTON_TEMPLATE = $sub_link ? $SUB_BUTTON : $BUTTON;
				}
				$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
				$replace[1] = varset($e107_vars[$act]['link'], "#{$act}");
				if (!empty($e107_vars[$act]['include'])) {
					$replace[2] = $e107_vars[$act]['include'];
				} else {
					$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
				}
				$replace[3] = $title;
				$replace[4] = $id_title;
				$text .= preg_replace($search, $replace, $BUTTON_TEMPLATE);
			}
		}
		$text .= $sub_link ? $SUB_BUTTONS_END : ''.$BUTTONS_END;

		if ($title == "" || $sub_link) {
			return $text;
		} else {
			$ns -> tablerender($title, $text, array('id' => $id_title, 'style' => 'button_menu'));
		}
	}
}

if (file_exists(THEME.'admin_template.php')) {
  	require_once(THEME.'admin_template.php');
} else {
  	require_once(e_BASE.$THEMES_DIRECTORY.'templates/admin_template.php');
}

if (!function_exists("parse_admin")) {
	function parse_admin($ADMINLAYOUT) {
		global $tp;
		$adtmp = explode("\n", $ADMINLAYOUT);
		for ($a = 0; $a < count($adtmp); $a++) {
			if (preg_match("/{.+?}/", $adtmp[$a])) {
				echo $tp->parseTemplate($adtmp[$a]);
			} else {
				echo $adtmp[$a];
			}
		}
	}
}

/**
 * Automate DB system messages
 * NOTE: default value of $output parameter will be changed to false (no output by default) in the future
 *
 * @param integer|bool $update return result of db::db_Query
 * @param string $type update|insert|update
 * @param string $success forced success message
 * @param string $failed forced error message
 * @param bool $output false suppress any function output
 * @return integer|bool db::db_Query result
 */
function admin_update($update, $type = 'update', $success = false, $failed = false, $output = true) {
	require_once(e_HANDLER."message_handler.php");
	$emessage = &eMessage::getInstance();

	if (($type == 'update' && $update) || ($type == 'insert' && $update !== false)) {
		$emessage->add(($success ? $success : ($type == 'update' ? LAN_UPDATED : LAN_CREATED)), E_MESSAGE_SUCCESS);
	}
	elseif ($type == 'delete' && $update)
	{
		$emessage->add(($success ? $success : LAN_DELETED), E_MESSAGE_SUCCESS);
	}
	elseif (!mysql_errno())
	{
		if ($type == 'update')
		{
			$emessage->add(LAN_NO_CHANGE.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
		}
		elseif ($type == 'delete')
		{
			$emessage->add(LAN_DELETED_FAILED.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
		}
	}
	else
	{
		switch ($type) {
			case 'insert':
				$msg = LAN_CREATED_FAILED;
			break;
			case 'delete':
				$msg = LAN_DELETED_FAILED;
			break;
			default:
				$msg = LAN_UPDATED_FAILED;
			break;
		}

		$text = ($failed ? $failed : $msg." - ".LAN_TRY_AGAIN)."<br />".LAN_ERROR." ".mysql_errno().": ".mysql_error();
		$emessage->add($text, E_MESSAGE_ERROR);
	}

	if($output) echo $emessage->render();
	return $update;
}

function admin_purge_related($table, $id)
{
	global $ns, $tp;
	$msg = "";
	$tp->parseTemplate("");

	// Delete any related comments
	require_once(e_HANDLER."comment_class.php");
	$_com = new comment;
	$num = $_com->delete_comments($table, $id);
	if($num)
	{
		$msg .= $num." ".ADLAN_114." ".LAN_DELETED."<br />";
	}

	// Delete any related ratings
	require_once(e_HANDLER."rate_class.php");
	$_rate = new rater;
	$num = $_rate->delete_ratings($table, $id);
	if($num)
	{
		$msg .= LAN_RATING." ".LAN_DELETED."<br />";
	}

	if($msg)
	{
		$ns->tablerender(LAN_DELETE, $msg);
	}
}



$sql->db_Mark_Time('Parse Admin Header');
if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {
	parse_admin($ADMIN_HEADER);
}
$sql->db_Mark_Time('(End: Parse Admin Header)');
}

if(!varset($emessage) && !is_object($emessage))
{
	require_once(e_HANDLER."message_handler.php");
	$emessage = &eMessage::getInstance();
}
?>