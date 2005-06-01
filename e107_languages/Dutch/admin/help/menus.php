<?php
if(IsSet($_POST['reset'])){
	if(!check_class("FAKE","",TRUE)){
		$text = "<b>Actie niet toegestaan</b><br /><br />";
	} else {
		for($mc=1;$mc<=5;$mc++){
			$sql -> db_Select("menus","*", "menu_location='$mc' ORDER BY menu_order");
			$count = 1;
			$sql2 = new db;
			while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
				$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
				$count++;
			}
			$text = "<b>Menu's hersteld in database</b><br /><br />";
		}
	}
}else{
	unset($text);
}

$text .= "Je kunt instellen waar in op welke volgorde je menuobjecten op je site worden getoond. gebruik de pijltjes om de menu's omhoog en omlaag te verplaatsen.<br />Als je niet meteen het resultaat ziet, druk dan op de 'refresh' knop.

<br />
<form method='post' action='".$_SERVER['PHP_SELF']."'>
<input class='button' type='submit' name='reset' value='Refresh' />
</form>";

$ns -> tablerender("Menu's Help", $text);
?>