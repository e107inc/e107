<?php
$caption = "Menük súgó";
if(IsSet($_POST['reset'])){
	if(!check_class("FAKE","",TRUE)){
		$text = "<b>Művelet nem engedélyezett</b><br /><br />";
	} else {
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
	}
}else{
	unset($text);
}

$text .= "Beállíthatod, hogy mely menük, hol, s milyen sorrendben jelenjenek meg. A nyilak segítségével mozgasd a menüket a megfelelő pozícióba. A középen listázott menük inaktívak. Bekapcsolhatod őket, ha megadod, hogy hol jelenjenek meg.<br />Ha úgy tűnik, hogy a menükkel valami nincs rendben, nyomd meg a Frissítés gombot.

<br />
<form method='post' action='".$_SERVER['PHP_SELF']."'>
<input class='button' type='submit' name='reset' value='Frissítés' />
</form><br />
<div class='indent'><span style='color:red'>*</span> jelzi, ha a menü láthatóságát, elérhetőségét módosítottad.</div>";

$ns -> tablerender($caption, $text);
?>
