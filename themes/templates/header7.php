<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/themes/templates/header7.php								|
|																						|
|	Template style 7															|
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
<table style=\"width:100%\" cellspacing=\"3\" class=\"".$maintableclass."\">
<tr>
<td colspan=\"3\" align=\"".$logo_align."\">";
if($logo_display == TRUE){
	echo "\n<img src=\"themes/shared/logo.png\" alt=\"Logo\" />
	<br />";

echo"<A href=\"index.php\"><img src=\"themes/useless/images/home_mini_header.png\" border=0 ALT=\"".Home."\"> </a>&nbsp \n"
        ." <A href=\"download.php\"><img src=\"themes/useless/images/download_mini_header.png\" border=0 ALT=\"".Download."\"></a>&nbsp \n"
        ." <A href=\"submitnews.php\"><img src=\"themes/useless/images/news_mini_header.png\" border=0 ALT=\"".News."\"></a>&nbsp \n"
        ." <A href=\"/admin/admin.php\"><img src=\"themes/useless/images/admin_mini_header.png\" border=0 ALT=\"".Admin."\"></a>&nbsp \n"
        ." <A href=\"links.php\"><img src=\"themes/useless/images/links_mini_header.png\" border=0 ALT=\"".Links."\"></a>&nbsp \n"
        ." <A href=\"forum.php\"><img src=\"themes/useless/images/forum_mini_header.png\" border=0 ALT=\"".Forum."\" ></a>&nbsp \n"
        ." <A href=\"stats.php\"><img src=\"themes/useless/images/stats_mini_header.png\" border=0 ALT=\"".Stats."\" ></a>&nbsp \n"
        ."\n";
	if($tag_display == 1){
		echo SITETAG. "\n";
	}
}else{
	echo "<span class=\"captiontext\">".SITENAME."</span><br />\n";
	if($tag_display == 1){
		echo SITETAG."\n";
	}
}

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
</td>
</tr>";



echo "<tr> 
<td style=\"width:20%; vertical-align: top;\">";
$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		if($links_display != 1){
			$menu_name();
		}
	}else{
		require_once("menus/".$menu_name.".php");
	}
}
require_once("menus/log_menu.php");
?>
<br />
</td>
<td style="width:60%; vertical-align: top;">