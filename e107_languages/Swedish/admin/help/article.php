<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-01 16:26:44 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$text = "Från denna sida kan du lägga till en- eller flersidiga artiklar.<br />
 För en flersidig artikel skall du separera sidorna med texten [newpage], t.ex. kommer<br /><code>Test1 [newpage] Test2</code><br /> att skapa en tvåsidig artikel med 'Test1' på sida 1 och 'Test2' på sida 2.
<br /><br />
Om din artikel innehåller HTML taggar som du vill bevara, kapsla in koden med [html] [/html]. T.ex om du skriver texten '&lt;table>&lt;tr>&lt;td>Hallå &lt;/td>&lt;/tr>&lt;/table>' i din artikel kommer en tabell att visas innehållande ordet Hallå. Om du skriver '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' kommer koden du skrev att visas och inte tabellen koden skapar.";
$ns -> tablerender("Artikelhjälp", $text);
?>