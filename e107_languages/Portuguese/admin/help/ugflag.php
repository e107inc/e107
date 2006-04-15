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

$text = "Se necessitar de efectuar uma actualização ao seu sistema e107, ou desligar o seu site temporáriamente por algum motivo, deverá ligar a opção de manutenção. Os seus utilizadores serão redireccionados para uma página que os informa que o site está desligado para manutenção/actualização. Após ter desligado esta opção, o seu site voltará ao funcionamento normal.";

$ns -> tablerender("Ajuda = Manutenção do site", $text);
?>