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
$text = "Tiene la posibilidad de manejar los archivos en su directorio de archivos desde esta página.
Si está teniendo mensajes de error acerca de los permisos para transferir archivos por favor cambie el CHMOD del directorio
al que quiere transferir a 777.";
$ns -> tablerender("Ayuda Director de Archivos", $text);
?>