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
|     $Revision: 1.2 $
|     $Date: 2004-09-26 04:09:25 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if(!function_exists("parseheader"))
{
	function parseheader($LAYOUT){
		global $tp;
		$tmp = explode("\n", $LAYOUT);
		for($c=0; $c < count($tmp); $c++)
		{
			if(preg_match("/{.+?}/", $tmp[$c]))
			{
				echo $tp -> parseTemplate($tmp[$c]);
			}
			else
			{
				echo $tmp[$c];
			}
		}
	}
}
$aj = new textparse;
echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='iso-8859-1' ?>")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<title>".SITENAME.(defined("e_PAGETITLE") ? ": ".e_PAGETITLE : (defined("PAGE_NAME") ? ": ".PAGE_NAME : ""))."</title>
<link rel=\"stylesheet\" href=\"".e_FILE."e107.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"".THEME."style.css\" type=\"text/css\" />";
if(file_exists(e_BASE."favicon.ico")){echo "\n<link rel=\"shortcut icon\" href=\"favicon.ico\" />"; }
if(file_exists(e_FILE."style.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."style.css' type=\"text/css\" />\n"; }
if($eplug_css){ echo "\n<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n"; }
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\" />
<meta http-equiv=\"content-style-type\" content=\"text/css\" />
".($pref['meta_tag'] ? $aj -> formtparev($pref['meta_tag'])."\n" : "");

echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>";
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>\n";}
if(file_exists(e_FILE."user.js")){echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";}
if($eplug_js){ echo "<script type='text/javascript' src='{$eplug_js}'></script>\n"; }
if($htmlarea_js){ echo $htmlarea_js; }
if(function_exists("headerjs")){echo headerjs();  }

echo "<script type=\"text/javascript\">
<!--\n";
if($pref['log_activate']){
echo "
document.write( '<link rel=\"stylesheet\" type=\"text/css\" href=\"".e_PLUGIN."log/log.php?referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '\">' );";
}
//echo "var ejs_listpics = new Array();";

$fader_onload = ($sql -> db_Select("menus", "*", "menu_name='fader_menu' AND menu_location!='0' ") ? "changecontent();" : "");
$links_onload = "externalLinks();";
$body_onload =($fader_onload != "" || $links_onload != "" ? " onload='".$links_onload." ".$fader_onload."'" : "");
$ejs_listpics = "";
$handle=opendir(THEME."images");
while ($file = readdir($handle)){
if(strstr($file,".") && $file != "." && $file != ".."){
$ejs_listpics .= $file.",";
}
}
$ejs_listpics = substr($ejs_listpics, 0, -1);

closedir($handle);
echo "\n

ejs_preload('".THEME."images/','".$ejs_listpics."');\n
// -->\n
</script>
</head>
<body".$body_onload.">";
//echo "XX - ".$e107_popup;
// require $e107_popup =1; to use it as header for popup without menus
if($e107_popup != 1)
{
	if($pref['no_rightclick'])
	{
		echo "<script language=\"javascript\">
		<!--
		var message=\"Not Allowed\";
		function click(e) {
		if (document.all) {
		if (event.button==2||event.button==3) {
		alert(message);
		return false;
		}
		}
		if (document.layers) {
		if (e.which == 3) {
		alert(message);
		return false;
		}
		}
		}
		if (document.layers) {
		document.captureevents(event.mousedown);
		}
		document.onmousedown=click;
		// -->
		</script>\n";
	}

	$custompage = explode(" ", $CUSTOMPAGES);
	if(e_PAGE == "news.php" && $NEWSHEADER){
		parseheader($NEWSHEADER);
	}
	else
	{
		while(list($key, $kpage) = each($custompage))
		{
			if(strstr(e_SELF, $kpage))
			{
				$ph = TRUE;
				break;
			}
		}
		parseheader(($ph ? $CUSTOMHEADER : $HEADER));
	}
	unset($text);
}
?>