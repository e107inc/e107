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

?>
</td>
</tr>
</table>
</div>


</body>
</html>

<?
$sql -> dbClose();
?>