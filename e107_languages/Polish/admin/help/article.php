<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.3 $
|     $Date: 2006-11-24 15:37:55 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/article.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/article.php rev. 1.
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$text = "Na tej stronie możesz tworzyć jedno oraz wielostronicowe artykuły.<br />
Aby utworzyć artykuł wielostronicowy, oddziel każdą stronę tekstem [newpage], np. <br /><code>Strona testowa 1 [newpage] Strona testowa 2</code><br /> utworzy artykuł dwustronicowy z tekstem 'Strona testowa 1' na pierwszej stronie oraz 'Strona testowa 2' na stronie drugiej.
<br /><br />
Jeśli Twój artykuł zawiera znaczniki HTML, które chcesz wyświetlić na stronie, otocz wybrany kod znacznikami [html] [/html]. Na przykład, jeśli w swoim artykule wpiszesz tekst '&lt;table>&lt;tr>&lt;td>Cześć &lt;/td>&lt;/tr>&lt;/table>', zostanie wyświetlona tabela zawierająca słowo 'Cześć'. Jeśli natomiast wpiszesz '[html]&lt;table>&lt;tr>&lt;td>Cześć &lt;/td>&lt;/tr>&lt;/table>[/html]', zostanie wyświetlony kod, który wpisałeś, a nie tabela, którą kod ten generuje.";
$ns -> tablerender("Artykuły", $text);

?>
