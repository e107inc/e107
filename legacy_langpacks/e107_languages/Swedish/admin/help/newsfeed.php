<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/newsfeed.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Du kan h&auml;mta och visa andra sajters RSS nyhetsfl&ouml;den och visa dem p&aring; din egen sajt h&auml;rifr&aring;n.&lt;br /&gt;Ange den fullst&auml;ndiga URLen till fl&ouml;det (t.ex. http://e107.org/news.xml). Du kan ange s&ouml;kv&auml;g till en bil om du inte tycker om standardbilden, eller om det inte finns n&aring;gon definierad. Du kan aktivera och avaktivera fl&ouml;det om sajten skulle t.ex. g&aring; ner.&lt;br /&gt;&lt;br /&gt;F&ouml;r att se rubrikerna p&aring; din sajt, se till att headlines_menu &auml;r aktiverad fr&aring;n din menysida.";

$ns -&gt; tablerender("Nyhetsfl&ouml;den", $text);

?>
