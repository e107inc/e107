<?php
echo "<br />
</td>
<td style=\"width:".$rightcolumn."; vertical-align:top\">";
$style = "rightmenu";
$mnu = new db;
$mnu -> db_Select("menus", "*", "menu_location='2' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $mnu-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}


?>
</td>
</tr>
</table>
</div>

<?php
echo "<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">

<tr>
<td style=\"background-color:#000\"></td>
</tr>
<tr>
<td style=\"background-color:#fff\"></td>
</tr>

<tr>
<td style=\"background-color:#E2E2E2; text-align:center\">
".SITEDISCLAIMER."<br />
<img src=\"files/images/php-small-trans-light.gif\" alt=\"\" /> <img src=\"button.png\" alt=\"\" /> <img src=\"files/images/poweredbymysql-88.png\" alt=\"\" />
</td>
</tr>
<tr>
<td style=\"background-color:#000\"></td>
</tr>
</table>";

?>
</body>
</html>

<?
$sql -> db_Close();
?>