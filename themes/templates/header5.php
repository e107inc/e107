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
<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
<tr>

<td style=\"background-color:#E2E2E2; text-align:".$logo_align."\">";
if($logo_display == TRUE){
	echo "\n<img src=\"themes/e107/images/logo.png\" alt=\"Logo\" />";
}else{
	echo "<span class=\"captiontext\">".SITENAME."</span><br />\n";
	if($tag_display == TRUE){
		echo SITETAG."<br />";
	}
}

echo "</td></tr>
<tr>
<td style=\"background-color:#000\"></td>
</tr>
<tr>
<td style=\"background-color:#fff\"></td>
</tr>
<tr>
<td style=\"background-color:#ccc\">&nbsp;".SITETAG."</td>
</tr>
<tr>
<td style=\"background-color:#000\"></td>
</tr>";

if(LINKDISPLAY == 1){
	echo "<tr><td style=\"background-color:#ccc\">";
	if(LINKALIGN == "right"){
		echo "<div style=\"text-align:right\">";
		sitelinks();
		echo "</div>";
	}else{
		sitelinks();
	}
	echo "</td><tr>";
}
echo "
</table>

<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"10\">
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
<td style=\"width:".$maincolumn."; vertical-align: top\">";
$style = "default";
?>