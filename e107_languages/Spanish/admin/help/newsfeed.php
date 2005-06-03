<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/newsfeed.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-06-03 22:17:40 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$text = "Puede recuperar y cortar los contenidos de noticias de otros sitios y
pegarlos en su sitio desde aquí.<br />
Introduzca la dirección URL entera hacia el backend (ej http://e107.org/portal).
Si el contenido que está copiando a su sitio tiene una url enlazada a un botón y
desea mostrala deje el cuadro de imágen en blanco,
de otra manera ponga una dirección a la imágen, o escriba 'none' para no mostrar ninguna imagen.
Luego pulse en las casillas para mostrar exactamente lo que desee en su menú de titulares.
Puede activar y desactivar el backend si el sitio se da de baja por ejemplo.<br /><br />
Para ver los titulares en su sitio, asegurese de que el menú de titulares está activado desde su página
de menús.";

$ns -> tablerender("Titulares", $text);
?>