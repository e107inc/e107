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
|   $Revision: 1.6 $
|   $Date: 2005-01-05 16:57:37 $
|   $Author: sweetas $
+---------------------------------------------------------------+
*/
if(!defined("e_HTTP")){ exit; }
echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='".CHARSET."' ?>");
if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php")){@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");} else {@include_once(e_LANGUAGEDIR."English/admin/lan_header.php");}
if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_footer.php")){@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_footer.php");} else {@include_once(e_LANGUAGEDIR."English/admin/lan_footer.php");}
if(!defined('ADMIN_WIDTH')){ define('ADMIN_WIDTH', 'width: 95%'); }
if(file_exists(THEME."admin_template.php")){
	require_once(THEME."admin_template.php");
}else{
	require_once(e_BASE.$THEMES_DIRECTORY."templates/admin_template.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo SITENAME." : ".LAN_head_4; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<?php if(file_exists(e_FILE."e107.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."e107.css' />\n"; } ?>
<?php if(file_exists(e_FILE."style.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."style.css' />\n"; } ?>
<?php
if($admin_alt_nav){
	if (file_exists(THEME.'admin_nav.css')) {
		echo "<link rel='stylesheet' href='".THEME."admin_nav.css' />";
	} else {
		echo "<link rel='stylesheet' href='".e_FILE."admin_nav.css' />";
	}
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
 echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>\n";
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>\n";}
 if(file_exists(e_FILE."user.js")){echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";}
if(function_exists("headerjs")){     echo headerjs(); }
if($htmlarea_js){ echo $htmlarea_js; }
if($eplug_js){ echo "<script type='text/javascript' src='{$eplug_js}'></script>\n"; }
if($eplug_css){ echo "\n<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n"; }
?>
</head>
<body>
<?php

$ns = new e107table;
$e107_var = array();

if(!function_exists("parse_admin"))
{
	function parse_admin($ADMINLAYOUT){
		global $tp;
		$adtmp = explode("\n", $ADMINLAYOUT);
		for($a=0; $a < count($adtmp); $a++)
		{
			if(preg_match("/{.+?}/", $adtmp[$a]))
			{
				echo $tp -> parseTemplate($adtmp[$a]);
			}
			else
			{
				echo $adtmp[$a];
			}
		}
	}
}

parse_admin($ADMIN_HEADER);

function show_admin_menu($title,$page,$e107_vars){
	global $ns;
	$text = "<div style='text-align:center; width:100%'><table class='fborder' style='width:98%;'>";
	foreach(array_keys($e107_vars) as $act)
	{
		$pre = "";
		$post = "";
		if($page == $act){
			$pre = "<b>&laquo;&nbsp;";
			$post = "&nbsp;&raquo;</b>";
		}
		$t=str_replace(" ","&nbsp;",$e107_vars[$act]['text']);
		if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm']))
		{
			$text .= "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:hand; cursor:pointer; text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$pre}{$t}{$post}</a></div></td></tr>";
		}
	}
	$text .= "</table></div>";
	if($title=="")
	{
		return $text;
	}
	$ns -> tablerender($title,$text);
}

function get_admin_treemenu($title,$page,$e107_vars,$sortlist=FALSE)
{
	global $ns;

	if($sortlist == TRUE)
	{
		$temp = $e107_vars;
		unset($e107_vars);
		foreach(array_keys($temp) as $key)
		{
			$func_list[]=$temp[$key]['text'];
		}

    usort($func_list, 'strcoll');

		foreach($func_list as $func_text)
		{
			foreach(array_keys($temp) as $key)
			{
				if($temp[$key]['text'] == $func_text)
				{
					$e107_vars[] = $temp[$key];
				}
			}
		}
	}
	
	$idtitle="yop_".str_replace(" ","",$title);
	$text = "<div style='text-align:center; width:100%'><table class='fborder' style='width:100%;'>";
	$text .= "<tr><td class='button'><a style='text-align:center; cursor:hand; cursor:pointer; text-decoration:none;' onclick=\"expandit('{$idtitle}');\" >{$title}</a></td></tr>";
	$text .= "<tr id=\"{$idtitle}\" style=\"display: none;\" ><td class='forumheader3' style='text-align:left;'>";
	foreach(array_keys($e107_vars) as $act)
	{
		$pre = "";
		$post = "";
		if($page == $act)
		{
			$pre = "<b> &laquo; ";
			$post = " &raquo; </b>";
		}
		if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm']))
		{
			$text .= "{$pre}<a style='text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$e107_vars[$act]['text']}</a>{$post}<br />";
		}
	}

	$text .= "</td></tr>";
	$text .= "</table></div>";
	return $text;
}
?>
