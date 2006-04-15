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

$text = "As revisões são similares as artigos mas serão listadas num menu próprio.<br />
Para revisões com várias páginas deve utilizar o texto [newpage] no final de cada página. P.exo.:<br /><code>Teste1 [newpage] Teste2</code><br /> iria criar uma revisão com duas páginas com 'Teste1' na primeira página e 'Teste2' na segunda.";
$ns -> tablerender("Ajuda = Revisões", $text);
?>