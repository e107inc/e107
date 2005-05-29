<?php
$text = "Desde esta página puede crear menús hechos a medida con su propio contenido.<br /><br /><b>
Importante</b> - 
para usar esta característica necesitará configurar los permisos de la carpeta pluglins/custom a CHMOD 777.
<br /><br />
<i>Menú Nombre de archivo</i>: El nombre de su menú a medida, 
el menú será guardado como 'custom_nombre.php' en el directorio plugins<br />
<i>Menú Título</i>: El texto desplegado en la barra de título del menú<br />
<i>Menú Texto</i>: Los datos actuales que son desplegados en el cuerpo del menú, pueden ser textos, 
imágenes, etc";

$ns -> tablerender(CUSLAN_18, $text);
?>