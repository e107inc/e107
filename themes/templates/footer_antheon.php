<?php
echo "<br />";
?>
</td>
<td style="width:15%; vertical-align:top">
<?
$sql9 = new db;
$sql9 -> db_Select("menus", "*",  "menu_location='2' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
	require_once("menus/".$menu_name.".php");
}

echo "<div style=\"text-align:center\">
<a href=\"http://sourceforge.net/projects/e107/\"><img src=\"files/images/sourceforge.png\" style=\"border:0\" alt=\"\" /></a>
</div>

</td>
</tr>
</table>
<br />
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td class=\"caption2\">
<div style=\"text-align:center\">".SITEDISCLAIMER."</div>
</td>
</tr>
</table>
<br />
</div>
</body>
</html>";

$sql -> db_Close();
?>