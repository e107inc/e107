<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Spanish/admin/help/users.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-12-15 23:43:32 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$text = "Esta página le permite moderar sus usuarios registrados.
Puede actualizar sus configuraciones,
darles estado de administrador y configurar sus clases de usuario entre otras cosas.";
$ns -> tablerender("Ayuda Usuarios", $text);
unset($text);
?>