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
$text = "Esta página le permite moderar sus usuarios registrados.
Puede actualizar sus configuraciones,
darles estado de administrador y configurar sus clases de usuario entre otras cosas.";
$ns -> tablerender("Ayuda Usuarios", $text);
unset($text);
?>