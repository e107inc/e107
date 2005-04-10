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
|   $Source: /cvs_backup/e107_0.7/e107_admin/header.php,v $
|   $Revision: 1.31 $
|   $Date: 2005-04-10 02:09:31 $
|   $Author: sweetas $
+---------------------------------------------------------------+
*/
if (!defined('e_HTTP')) {
	exit;
}
require_once(e_ADMIN.'ad_links.php');
echo defined('STANDARDS_MODE') ? "" :
 "<?xml version='1.0' encoding='".CHARSET."' ?>";
if (file_exists(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_header.php')) {
	@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");
} else {
	@include_once(e_LANGUAGEDIR."English/admin/lan_header.php");
}
if (file_exists(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_footer.php')) {
	@include_once(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_footer.php');
} else {
	@include_once(e_LANGUAGEDIR.'English/admin/lan_footer.php');
}
if (!defined('ADMIN_WIDTH')) {
	define('ADMIN_WIDTH', 'width: 95%');
}
if (file_exists(THEME.'admin_template.php')) {
  	require_once(THEME.'admin_template.php');
} else {
  	require_once(e_BASE.$THEMES_DIRECTORY.'templates/admin_template.php');
}

if (!defined('ADMIN_EDIT_ICON'))
{
	define("ADMIN_EDIT_ICON", "<img src='".e_IMAGE."admin_images/edit_16.png' alt='' title='".LAN_EDIT."' style='border:0px; height:16px; width:16px' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE."admin_images/edit_16.png");
}

if (!defined('ADMIN_DELETE_ICON'))
{
	define("ADMIN_DELETE_ICON", "<img src='".e_IMAGE."admin_images/delete_16.png' alt='' title='".LAN_DELETE."' style='border:0px; height:16px; width:16px' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE."admin_images/delete_16.png");
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
	<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
	<title>".SITENAME." : ".LAN_head_4."</title>\n";
echo "<meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />
	<meta http-equiv='content-style-type' content='text/css' />\n";
if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE && $pref['admincss'] && file_exists(THEME.$pref['admincss'])) {
	echo "<link rel='stylesheet' href='".THEME.$pref['admincss']."' />\n";
} else {
	echo "<link rel='stylesheet' href='".THEME."style.css' />\n";
}

if (!$no_core_css) {
	echo "<link rel='stylesheet' href='".e_FILE."e107.css' type='text/css' />\n";
}
if (function_exists('theme_head')) {
   	echo theme_head();
}
if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {
	echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>\n";
}
if (file_exists(THEME."theme.js")) {
	echo "<script type='text/javascript' src='".THEME."theme.js'></script>\n";
}
if (filesize(e_FILE.'user.js')) {
	echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";
}
if (function_exists("headerjs")) {
	echo headerjs();
}
if (isset($htmlarea_js) && $htmlarea_js) {
	echo $htmlarea_js;
}
echo "<script type='text/javascript'>
		function savepreset(ps,pid){
			document.getElementById(ps).action='".e_SELF."?savepreset.'+pid;
			document.getElementById(ps).submit();
		}
	</script> ";
if (isset($eplug_js) && $eplug_js) {
	echo "<script type='text/javascript' src='{$eplug_js}'></script>\n";
}
if (isset($eplug_css) && $eplug_css) {
	echo "\n<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n";
}
if((ADMIN || $pref['allow_html']) && $pref['wysiwyg'] && $e_wysiwyg == TRUE){
  	require_once(e_HANDLER."tiny_mce/wysiwyg.php");
	echo wysiwyg($e_wysiwyg);
}
echo "</head>
	<body>";

$ns = new e107table;
$e107_var = array();

if (!function_exists('show_admin_menu')) {
	function show_admin_menu($title, $active_page, $e107_vars, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE) {
		global $ns, $BUTTON, $BUTTON_OVER, $BUTTONS_START, $BUTTONS_END, $SUB_BUTTON, $SUB_BUTTON_OVER, $SUB_BUTTONS_START, $SUB_BUTTONS_END;
		$id_title = "yop_".str_replace(" ", "", $title);
		if (!isset($BUTTONS_START)) {
			$BUTTONS_START = "<div style='text-align:center; width:100%'><table class='fborder' style='width:98%;'>\n";
		}
		if (!isset($BUTTON)) {
			$BUTTON = "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:hand; cursor:pointer; text-decoration:none;' {ONCLICK} >{LINK_TEXT}</a></div></td></tr>\n";
		}
		if (!isset($BUTTON_OVER)) {
			$BUTTON_OVER = "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:hand; cursor:pointer; text-decoration:none;' {ONCLICK} ><b>&laquo;&nbsp;{LINK_TEXT}&nbsp;&raquo;</b></a></div></td></tr>\n";
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
			$SUB_BUTTONS_START = "<div style='text-align:center; width:100%'><table class='fborder' style='width:100%;'>
			<tr><td class='button'><a style='text-align:center; cursor:hand; cursor:pointer; text-decoration:none;' 
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
			$replace[1] = '';
			$replace[2] = '';
			$replace[3] = $title;
			$replace[4] = $id_title;
			$text = preg_replace($search, $replace, $SUB_BUTTONS_START);
		} else {
			$text = $BUTTONS_START;
		}

		foreach (array_keys($e107_vars) as $act) {
			if (!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm'])) {
				if ($active_page == $act || (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act))) {
					$BUTTON_TEMPLATE = $sub_link ? $SUB_BUTTON_OVER : $BUTTON_OVER;
				} else {
					$BUTTON_TEMPLATE = $sub_link ? $SUB_BUTTON : $BUTTON;
				}
				$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
				$replace[1] = $e107_vars[$act]['link'];
				$replace[2] = $js ? "onclick=\"showhideit('".$act."');\"" : "onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
				$replace[3] = $title;
				$replace[4] = $id_title;
				$text .= preg_replace($search, $replace, $BUTTON_TEMPLATE);
			}
		}
		$text .= $sub_link ? $SUB_BUTTONS_END : $BUTTONS_END;
	
		if ($title == "" || $sub_link) {
			return $text;
		} else {
			$ns -> tablerender($title, $text, array('id' => $id_title, 'style' => 'button_menu'));
		}
	}
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

if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE) {
	parse_admin($ADMIN_HEADER);
}

?>