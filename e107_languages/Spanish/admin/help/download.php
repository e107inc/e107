<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/download.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-12-15 23:43:32 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$text = "Por favor, copie sus archivos en la carpeta ".e_FILE."downloads, sus imágenes en la carpeta ".e_FILE."downloadimages y la imágenes de muestra en la carpeta ".e_FILE."downloadthumbs.
<br /><br />
Para enviar una descarga, primero cree un padre, después las categorías dentro del padre, podrá activar las descargas dentro de cada categoría.";
$ns -> tablerender("Ayuda Descargas", $text);
?>