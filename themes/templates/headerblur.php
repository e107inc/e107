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

<div style=\"text-align:left\">
<table style=\"width:100%\" cellspacing=\"3\" class=\"".$maintableclass."\">
<tr>
<td colspan=\"3\" align=\"".$logo_align."\">
</br>
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
	
	<tr>
	<td style=\"width:2; vertical-align: top;\"><img src=\"".THEME."images/logol.png\" width=\"2\" height=\"84\" alt=\"\" /></td>
	<td style=\"width:365; vertical-align: top;\"><img src=\"".THEME."images/logo.png\" width=\"365\" height=\"84\" alt=\"\" /></td>
	<td style=\"width:100%; vertical-align: top;\"><img src=\"".THEME."images/logob.png\" width=\"100%\" height=\"84\" alt=\"\" /></td>
	<td style=\"width:28; vertical-align: top;\"><img src=\"".THEME."images/logor.png\" width=\"28\" height=\"84\" alt=\"\" /></td>
	</tr>
	
</table>
</br>
</td>
</tr>";

if($links_display == 1){
	if($links_align == "right"){
		echo "<div style=\"text-align:right\">";
		sitelinks();
		echo "</div>";
	}else{
		sitelinks();
	}
}
echo "
</table>

<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"10\">
<tr> 
<td style=\"width:15%; vertical-align: top;\">";
if(file_exists("install.php")){
	$text = "<div style=\"text-align:center\">
<b>Please delete install.php from your server!</b>
<br />
Your site is at risk until you do</div>";
$ns -> tablerender("IMPORTANT!", $text);
}

$style = "leftmenu";

$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}
require_once("menus/log_menu.php");
?>
<br />
</td>
<td style="width:70%; vertical-align: top">