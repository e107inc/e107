<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/themes/templates/header1.php								|
|																						|
|	Template style 1															|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo SITENAME; ?></title>
    <link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="content-style-type" content="text/css" />
<script type="text/javascript">
<!--
var ref=""+escape(top.document.referrer);
var colord = window.screen.colorDepth; 
var res = window.screen.width + "x" + window.screen.height;
var self = document.location;
document.write("<img src='plugins/log2.php?referer=" + ref + "&amp;color=" + colord + "&amp;self=" + self + "&amp;res=" + res + "' style='float:left; border:0' alt='' />");
//-->
</script>
  </head>
<body>
<?php

$ns = new table;
echo "
<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\" class=\"".$maintableclass."\">
<tr>
<td colspan=\"3\" style=\"text-align:".$logo_align."\">";
if($logo_display == TRUE){
	echo "\n<img src=\"themes/shared/logo.png\" alt=\"Logo\" />
	<br />";
	if($tag_display == TRUE){
		echo SITETAG. "<br />";
	}
}else{
	echo "<span class=\"captiontext\">".SITENAME."</span><br />\n";
	if($tag_display == TRUE){
		echo SITETAG."<br />";
	}
}

if(LINKDISPLAY == 1){
	echo "<div style=\"text-align:".LINKALIGN."\">";
	sitelinks();
	echo "</div>";
}
echo "
</td>
</tr>
<tr> 
<td style=\"width:".$leftcolumn."; vertical-align: top;\">";
$style = "leftmenu";
if(LINKDISPLAY == 2){ sitelinks(); }
$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}
require_once("menus/log_menu.php");

echo "<br />
</td>
<td style=\"width:".$maincolumn."; vertical-align: top;\">";
$style = "default";
unset($text);
?>