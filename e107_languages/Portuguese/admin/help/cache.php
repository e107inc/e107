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

$caption = "Ajuda = Sistema de cache";
$text = "Ao ligar o sistema de cache irá aumentar substancialmente a velocidade no seu site uma vez que minimizará a quantidade de chamadas à base de dados SQL.<br /><br /><b>IMPORTANTE!</b> <i>Se está a realizar/experimentar o seu próprio tema, deverá desligar o sistema de cache, uma vez que as alterações que efectuar não serão visualizadas.</i>";
$ns -> tablerender($caption, $text);
?>