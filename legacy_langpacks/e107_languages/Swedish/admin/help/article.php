<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-17 14:44:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$text = "Fr&aring;n denna sida kan du l&auml;gga till en- eller flersidiga artiklar.<br />
 F&ouml;r en flersidig artikel skall du separera sidorna med texten [newpage], t.ex. kommer<br /><code>Test1 [newpage] Test2</code><br /> att skapa en tv&aring;sidig artikel med 'Test1' p&aring; sida 1 och 'Test2' p&aring; sida 2.
<br /><br />
Om din artikel inneh&aring;ller HTML taggar som du vill bevara, kapsla in koden med [html] [/html]. T.ex om du skriver texten '&lt;table>&lt;tr>&lt;td>Hall&aring; &lt;/td>&lt;/tr>&lt;/table>' i din artikel kommer en tabell att visas inneh&aring;llande ordet Hall&aring;. Om du skriver '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]' kommer koden du skrev att visas och inte tabellen koden skapar.";
$ns -> tablerender("Artikelhj&auml;lp", $text);

?>
