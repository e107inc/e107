<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/list_menu_conf.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-06-03 22:17:40 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$text = "En esta sección puede configurar 3 menús<br>
<b> Menú de Nuevos Artículos</b> <br>
Introduzca un número, por ejemplo '5' en el primer campo para mostrar los 5 primeros artículos o
deje en blanco para ver todos.
Puede configurar el título del enlace para el resto de los artículos en el segundo campo,
cuando deje esta opción en blanco no creará un enlace, por ejemplo: 'Todos los artículos'<br>
<b> Menú Comentarios/Foro</b> <br>
El número de comentarios predeterminado es 5,
el número predeterminado de caracteres es 10000.
El mensaje fijo es para cortar lineas muy largas, pondrá un mensaje fijo en el final,
una buena elección para esto es
'...', revise los temas originales si quiere verlos en toda la visión de conjunto.<br>

";
$ns -> tablerender("Ayuda Configuración de menú", $text);
?>