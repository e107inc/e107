<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/themes/templates/header2.php								|
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
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td style=\"background-color: #EFEFEF\">
&nbsp;&nbsp;<img src=\"".THEME."images/logo.png\" alt=\"\" />
<br />

</td>
<td style=\"background-color: #EFEFEF; text-align:right\">
<img src=\"button.png\" alt=\"\" />&nbsp;&nbsp;
</td>
</tr>
</table>
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td class=\"caption2\">
".SITETAG."
</td>
</tr>
</table>
<table style=\"width:100%\" cellpadding=\"2\" cellspacing=\"0\">
<tr>
<td style=\"background-color: #A8A8A8\">";


if($pref['user_reg'][1] == 1){
	if(USER == TRUE){
			echo "<table><tr>
			<td class=\"mediumtext\">Welcome ".USERNAME."&nbsp;&nbsp;&nbsp;</td>
			<td><img src=\"".THEME."images/blue.png\" alt=\"bullet\" /></td>
			<td> <a href=\"usersettings.php\">Settings</a></td>
			<td><img src=\"".THEME."images/blue.png\" alt=\"bullet\" /></td>
			<td><a href=\"".$_SERVER['PHP_SELF']."?logout\">Logout</a></td>
			</tr></table> ";
	}else{
		echo  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<p>
Username: <input class=\"tbox\" type=\"text\" name=\"username\" size=\"15\" value=\"$username\" maxlength=\"20\" />
&nbsp;&nbsp;
Password: <input class=\"tbox\" type=\"password\" name=\"userpass\" size=\"15\" value=\"\" maxlength=\"20\" />\n
&nbsp;&nbsp;
<input type=\"checkbox\" name=\"autologin\" value=\"1\"  checked> Auto Login
&nbsp;&nbsp;
<input class=\"button\" type=\"submit\" name=\"userlogin\" value=\"Login\" />\n
&nbsp;&nbsp;<a href=\"signup.php\">Signup</a>
</p>
</form>";
	}
}

echo "</td>
<td style=\"text-align:right; background-color: #A8A8A8\">
<form method=\"post\" action=\"search.php\">
<p>
<input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"\" maxlength=\"50\" />
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"Search\" />
</p>
</form>";

/*
Uncomment to use QOTD plugin in header
$qotd_file = $pref['qotd_file'][1];
if(!file_exists($qotd_file)){
	$quote = "Quote file not found ($qotd_file)";
}else{
	$quotes = file($qotd_file);
	$quote = htmlspecialchars($quotes[rand(0, count($quotes))]);
}
*/

echo "
</td>
</tr>
<tr>
<td colspan=\"2\" style=\"background-color: #000\">
</td>
</tr>


<tr>
<td colspan=\"2\" style=\"background-color: #D4D4D4\">
</td>
</tr>

<tr>
<td class=\"smalltext\" style=\"background-color: #A8A8A8\">";
if(ADMIN == TRUE){
	echo "&nbsp;<img src=\"themes/antheon/images/bullet3.gif\" alt=\"\" />&nbsp;<a href=\"admin/admin.php\">admin</a>";
}
$sql -> db_Select("links", "*", "link_category='1' ORDER BY link_order");
while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> db_Fetch()){
	echo "&nbsp;<img src=\"themes/antheon/images/bullet3.gif\" alt=\"\" />&nbsp;<a href=\"".$link_url."\">".$link_name."</a>";
}
echo "&nbsp;<img src=\"themes/antheon/images/bullet3.gif\" alt=\"\" /></td>
<td style=\"background-color: #A8A8A8; text-align:right\">
$quote
</td>
</tr>
<tr>
<td colspan=\"2\" style=\"background-color: #000\">
</td>
</tr>
</table>

<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\" cellpadding=\"3\">
<tr>
<td colspan=\"3\" align=\"".$logo_align."\">";

echo "
</td>
</tr>";


echo "<tr> 
<td style=\"width:15%; vertical-align: top;\">";

$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}
require_once("menus/log_menu.php");

function navlinks(){
	$sql = new db;
$sql -> db_Select("links", "*", "link_category='1' ");
$text .= "<select name=\"NavSelect\" onChange=\"Navigate(this.form)\" class=\"tbox\">
<option value=''>jump to ...</option>\n";
if(ADMIN == TRUE){
	$text .= "<option value=\"admin/admin.php\">admin area</option>\n";
}
	while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> db_Fetch()){
		$text .= "<option value=\"".$link_url."\">".$link_name."</option>\n";
	}
	$text .= "</select>&nbsp;&nbsp;";
	echo $text;
}
unset($text);

?>
</td>
<td style="vertical-align: top;width:50%">