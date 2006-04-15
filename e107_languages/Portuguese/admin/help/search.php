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

$text = "Se a versão utilizada no seu servidor MySql suportar o método de ordenação, deverá utilizá-la uma vez que este método é mais rápido que o utilizado pelo PHP. Ver definições.<br /><br />
Se o seu site incluir línguas ideográficas tais como o Chinês e o Japonês deverá utilizar o método de ordenação do PHP e desactivar a ocorrência total de palavras.";
$ns -> tablerender("Ajuda = Pesquisar", $text);
?>