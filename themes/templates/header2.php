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
function Navigate() {
var number = NavSelect.selectedIndex;
location.href = NavSelect.options[number].value; }
</script>
  </head>
<body>
<?php

$ns = new table;
echo "
<div style=\"text-align:center\">

<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width: 100%; background-color: #D5D5D5;\">
<tr>
<td colspan=\"4\" style=\"background-color: #D5D5D5; padding: 3px; text-align: ".$logo_align.";\">";

if($logo_display == TRUE){
	echo "\n<img src=\"themes/e107/images/logo.png\" alt=\"Logo\" />";
}else{
	echo "
	<table style=\"width:100%\"><tr><td style=\"vertical-align:top; width:2%\"><img src=\"".THEME."images/bullet3.gif\" alt=\"\" style=\"vertical-align:absmiddle\" /></td>
	<td style=\"vertical-align:top\"><span class=\"captiontext\">".SITENAME."</span><br />".SITETAG."\n</td>";

	if(LINKDISPLAY == 1){
		echo "<td style=\"text-align:".LINKALIGN."; vertical-align:bottom\">";
		sitelinks();
		echo "</td>";
	}



	echo "</td></tr></table>";
}

echo "</td>
</tr>
<tr>
<td style=\"padding: 0px; margin: 0px; width: 150px;\"></td>
<td class=\"bar1 \"><img src=\"themes/shared/generic/blank.gif\" width=\"37\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
<td class=\"bar2\"><img src=\"themes/shared/generic/blank.gif\" width=\"2\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
<td class=\"bar2\"><img src=\"themes/shared/generic/blank.gif\" width=\"2\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
</tr>
<tr>
<td style=\"padding: 0px; margin: 0px; width: 150px; vertical-align:top\"><hr />";

$style = "leftmenu";

if(LINKDISPLAY == 2){ sitelinks(); }
$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
	echo "<hr />";
}
require_once("menus/log_menu.php");

echo "<br />
</td>
<td class=\"bar3\"><img src=\"images/blank.gif\" width=\"37\" height=\"2\" alt=\"\" style=\"display: block;\" /></td>
<td style=\"padding: 4px; margin: 0px; width: *; background-color: #EEF4F4; vertical-align:top;\">";

$style = "default";
?>