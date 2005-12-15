<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/chatbox.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-12-15 23:43:32 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
$text = "Configura las preferencias de tu Chatbox desde aquí.<br />
Si la casilla de reemplazo está marcada, cualquier enlace que hayan introducido será reemplazado
con el texto que escribiste en la caja de texto,
esto detendrá enlaces largos que causan problemas al mostrar.
Cortar palabras autocortará los textos que sean más largos que el número especificado aquí.";

$ns -> tablerender("Chatbox", $text);
?>