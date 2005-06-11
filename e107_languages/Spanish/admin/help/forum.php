<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/forum.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-06-11 12:22:26 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$caption = "Foro de Ayuda";
$text = "<b>General</b><br />
Use este área para crear o editar tus foros<br />
<br />
<b>Foros/Categorías</b><br />
Un Padre es un encabezado desde el que se despliegan otros foros,
esto hará las exposiciones más simples y hará que la navegación en sus foros sea más simple
para sus usuarios.
<br /><br />
<b>Accesibilidad</b>
<br />
Puede configurar sus foros para hacerlos accesibles solo para ciertos visitantes.
Una vez que haya configurado la 'clase' de los usuarios puede marcar la
clase para permitir solo los accesos de esos visitantes a los foros.
Puede configurar cagtegorías o foros de esta misma manera.
<br /><br />
<b>Moderadores</b>
<br />
Marque los nombres de los administradores listados para darles estado de moderador en el foro.
El administrador debe tener permisos de moderación de foro para estar listado aquí.
<br /><br />
<b>Rangos</b>
<br />
Configure sus rangos de usuario desde aquí. Si el campo de imágenes esta relleno se usarán imágenes, para usar nombres de rango escriba los nombres y asegúrese de que la imagen en el campo del rango correspondiente esté en blanco.
<br />El umbral es el número de puntos que el usuario necesita aumentar antes de que su nivel cambie.";

$ns -> tablerender($caption, $text);
unset($text);
?>