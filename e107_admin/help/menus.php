<?php
if(IsSet($_POST['reset'])){
	for($mc=1;$mc<=5;$mc++){
		$sql -> db_Select("menus","*", "menu_location='$mc' ORDER BY menu_order");
		$count = 1;
		$sql2 = new db;
		while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
			$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
			$count++;
		}
		$text = "<b>Menus reset in database</b><br /><br />";
	}
}else{
	unset($text);
}



$text .= "You can arrange where and in which order your menu items are from here. Use the arrows to move the menus up and down until you are satisfied with their positioning.<br />If you find the menus are not updating properly click on the refresh button.

<br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"reset\" value=\"Refresh\" />
</form>";

$ns -> tablerender("Menus Help", $text);
?>