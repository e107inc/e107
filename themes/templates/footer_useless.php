<?php
echo "<br />
<div style=\"text-align:center\">".
SITEDISCLAIMER.
"</div>";
?>
</td>
<td style="width:20%; vertical-align:top">
<?
$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='2' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		$menu_name();
	}else{
		require_once("menus/".$menu_name.".php");
	}
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