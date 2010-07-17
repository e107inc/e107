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
$text = "Por favor, copie sus archivos en la carpeta ".e_FILE."downloads, sus imágenes en la carpeta ".e_FILE."downloadimages y la imágenes de muestra en la carpeta ".e_FILE."downloadthumbs.
<br /><br />
Para enviar una descarga, primero cree un padre, después las categorías dentro del padre, podrá activar las descargas dentro de cada categoría.";
$ns -> tablerender("Ayuda Descargas", $text);
?>