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
$caption = "Ayuda de Admin";
$text = "Usa esta página para editar las preferencias o eliminar administradores del sitio. Los administradores solo tendrán acceso a las características marcadas<br /><br />Para crear un nuevo administrador, vaya a la configuración de usuarios y cambie el estado a admin en el usuario seleccionado.";
$ns -> tablerender($caption, $text);
?>