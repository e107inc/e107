<?php
if(IsSet($_POST['reset'])){
	if(!check_class("FAKE","",TRUE)){
		$text = "<b>Operation Not allowed</b><br /><br />";
	} else {
		for($mc=1;$mc<=5;$mc++){
			$sql -> db_Select("menus","*", "menu_location='$mc' ORDER BY menu_order");
			$count = 1;
			$sql2 = new db;
			while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
				$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
				$count++;
			}
			$text = "<b>Ustawienia Menu wyzerowane w bazie danych</b><br /><br />";
		}
	}
}else{
	unset($text);
}



$text .= "Tutaj możesz ustalić gdzie i w jakiej kolejności pokażą się poszczególne menu. Przy pomocy strzałek przesuwaj menu w górę i w dół, aż osiągniesz pożądane ustawienie.<br />Jeżeli nowe ustawienia nie pojawiają się we właściwy sposób, przeładuj stronę w przeglądarce.

<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refresh' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> znak obok nazwy menu wskazuje jego modyfikację widzialności.</div>
";

$ns -> tablerender("Pomoc: Menu", $text);
?>