<?php
if(IsSet($_POST['reset'])){
	if(!check_class("FAKE","",TRUE)){
		$text = "<b>Vorgang nicht erlaubt</b><br /><br />";
	} else {
		for($mc=1;$mc<=5;$mc++){
			$sql -> db_Select("menus","*", "menu_location='$mc' ORDER BY menu_order");
			$count = 1;
			$sql2 = new db;
			while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
				$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
				$count++;
			}
			$text = "<b>Menü reset Ihrer Datenbank</b><br /><br />";
		}
	}
}else{
	unset($text);
}

$text .= "
Sie können die Reihenfolge und den Platz Ihrer Menüs für Ihr CMS in diesem Bereich arrangieren.
Nutzen Sie das Dropdown Feld, um das/die Menüs an der gewüschten Stellen zu platzieren. Sie können die Positionen solange verschieben, bis Sie zufrieden sind mit ihrem Layout.
<br />
<br />
Sollten Sie der Meinung sein, dass die Ansicht nicht Ihren Einstellungen entspricht klicken Sie auf den (Ansicht) aktualisieren Button.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refresh' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> indicates menu visibility has been modified</div>
";

$ns -> tablerender("Menüs Hilfe", $text);
?>
