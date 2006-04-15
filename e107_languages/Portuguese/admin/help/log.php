<?php
/*
+----------------------------------------------------------------------------+
|     e107 website system - PT Language File.
|
|     $Revision: 1.1 $
|     $Date: 2006-04-15 20:48:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Activar o registo de logs a partir deste site. Se tem pouco espaço no seu servidor, seleccione a  caixa de apenas dominio para referenciar o registo de logs. Desta forma irá registar apenas logs do domínio em vez do URL completo, p.exo. 'e107.org' em vez de 'http://e107.org/links' ";
$ns -> tablerender("Ajuda = Logs/Registos", $text);
?>