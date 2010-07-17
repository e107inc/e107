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
$text = "Esta página le permite configurar un mensaje que aparecerá al principio de su página inicial todo el tiempo que esté activada.
Puede configurar un mensaje diferente para invitados, miembros registrados/con sesión iniciada o administradores.";
$ns -> tablerender("Ayuda Mensaje de bienvenida", $text);
?>