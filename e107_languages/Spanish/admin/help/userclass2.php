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
$caption = "Ayuda Uso de Clase";
$text = "Puede crear o editar/borrar clases existentes desde esta página.<br />
Esto es útil para restringir usuarios a ciertos sectores de su sitio.
Por ejemplo, Puede crear una clase llamada PRUEBA, luego crear un foro en donde solo los usuarios
de clase PRUEBA puedan acceder.
<br /> usando clases puede fácilmente construir un área solo para miembros en su web.";
$ns -> tablerender($caption, $text);
?>