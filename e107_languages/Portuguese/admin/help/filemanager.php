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

$text = "Poderá gerir os seus ficheiros na directoria '/files' a partir desta página. Se obtiver um erro de permissões durante a transferência de um ficheiro, deverá efectuar um CHMOD 777 à directoria para a qual está a transferir o arquivo.";
$ns -> tablerender("Ajuda = Gestor de ficheiros", $text);
?>