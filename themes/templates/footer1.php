<?php
echo "<br />
<div style=\"text-align:center\">".
SITEDISCLAIMER.
"</div>
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
</body>
</html>

<?
$sql -> db_Close();
?>