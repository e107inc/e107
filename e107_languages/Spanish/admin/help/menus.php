<?php
/*
+ ----------------------------------------------------------------------------+
|     $Sitio web e107 - Archivos del lenguaje $
|     $Versión: 0.7.16 $
|     $Date: 2009/09/16 17:51:27 $
|     $Author: E107 <www.e107.org> $
|     $Traductor: Josico <www.e107.es> $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
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