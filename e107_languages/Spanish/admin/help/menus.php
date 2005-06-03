<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/menus.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-03 20:07:26 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
if(IsSet($_POST['reset'])){
	for($mc=1;$mc<=5;$mc++){
		$sql -> db_Select("menus","*", "menu_location='$mc' ORDER BY menu_order");
		$count = 1;
		$sql2 = new db;
		while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
			$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
			$count++;
		}
		$text = "<b>Reiniciar menús en base de datos</b><br /><br />";
	}
}else{
	unset($text);
}



$text .= "Puede organizar el lugar y el orden en que los menús se mostrarán desde aquí. 
Use las flechas para mover los menús arriba y abajo hasta que esté conforme con la organización.<br />
Si encuentra que los menús no se están actualizando apropiadamente pulse en el botón refrescar.

<br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"reset\" value=\"Refrescar\" />
</form>";

$ns -> tablerender("Ayuda Menús", $text);
?>