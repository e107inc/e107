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

$text = "Através desta página poderá adicionar páginas de artigos simples ou multiplas.<br />
Para artigos com várias páginas deve utilizar o texto [newpage] no final de cada página. P.exo.:<br /><code>Teste1 [newpage] Teste2</code><br /> iria criar um artigo com duas páginas com 'Teste1' na primeira página e 'Teste2' na segunda.
<br /><br />
Se o seu artigo incluir etiquetas HTML e desejar preservá-las, deverá adicionar o código [html] [/html]. Por exemplo, se escrever o texto '&lt;table>&lt;tr>&lt;td>Olá &lt;/td>&lt;/tr>&lt;/table>' no seu artigo, será mostrada uma tabela contendo a palavra Olá. Se inserir '[html]&lt;table>&lt;tr>&lt;td>Olá &lt;/td>&lt;/tr>&lt;/table>[/html]' será mostrada toda a definição do código e não a tabela produzida pelo mesmo.";
$ns -> tablerender("Ajuda = Artigos", $text);
?>