<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/article.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Fra denne side kan du tilføje enkelt eller multi-side artikler.<br />
 For en a multi-side artikel indel hver side med teksten [newpage], eks. <br /><code>Test1 [newpage] Test2</code><br /> vil lave en to sidet artikel med 'Test1' på side 1 og 'Test2' på side 2.
<br /><br />
Hvis din artikel indeholder HTML tags du ønsker at beholde, omkrans da koden med [html] [/html]. For eksempel, hvis du skrev teksten '&lt;table>&lt;tr>&lt;td>Hejsa &lt;/td>&lt;/tr>&lt;/table>' i din artikel, en tabel indeholdende ordet Hejsa vil blive vist. Hvis du skrev '[html]&lt;table>&lt;tr>&lt;td>Hejsa &lt;/td>&lt;/tr>&lt;/table>[/html]' vil kode du skrev blive vist og ikke tabellen koden gennererede.";
$ns -> tablerender("Artikel Hjælp", $text);
?>