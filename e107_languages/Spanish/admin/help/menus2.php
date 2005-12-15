<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/menus2.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-12-15 23:43:32 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$caption = "Ayuda de Menú";
$text .= "Puede indicar donde y en que orden se mostrarán los menús.
Use las flechas para mover los menús arriba y abajo hasta que esté conforme con el aspecto.<br />
Los menú que están en la mitad de la pantalla están desactivados,
puede activarlos eligiendo una locación para colocarlos.
";

$ns -> tablerender("Ayuda de Menús", $text);
?>