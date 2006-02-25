<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/forum.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Forum hj&auml;lp";
$text = "&lt;b&gt;Generellt&lt;/b&gt;&lt;br /&gt;
Anv&auml;nd denna sida f&ouml;r att skapa och redigera dina forum&lt;br /&gt;
&lt;br /&gt;
&lt;b&gt;V&auml;rdar/Forum&lt;/b&gt;&lt;br /&gt;
En v&auml;rd &auml;r en rubrik som andra forum visas under, detta g&ouml;r layouten enklare och underl&auml;ttar navigeringen i forum enklare f&ouml;r bes&ouml;karna.
&lt;br /&gt;&lt;br /&gt;
&lt;b&gt;Tillg&auml;nglighet&lt;/b&gt;
&lt;br /&gt;
Du kan s&auml;tta dina forum f&ouml;r &aring;tkomst endast f&ouml;r vissa anv&auml;ndare. N&auml;r du satt 'klassen' f&ouml;r bes&ouml;karna kan du markera
att enbart till&aring;ta anv&auml;ndare i den klassen att f&aring; tillg&aring;ng till forum. Du kan s&auml;tta upp b&aring;de v&auml;rdar och individuella forum p&aring; detta vis.
&lt;br /&gt;&lt;br /&gt;
&lt;b&gt;Moderatorer&lt;/b&gt;
&lt;br /&gt;
Markera namnen p&aring; de listade administrat&ouml;rerna f&ouml;r att ge dem moderatorstatus i forum. Administrat&ouml;ren m&aring;ste ha forum modereringsr&auml;ttigheter f&ouml;r att listas h&auml;r.
&lt;br /&gt;&lt;br /&gt;
&lt;b&gt;Rang&lt;/b&gt;
&lt;br /&gt;
S&auml;tt dina anv&auml;ndarranger h&auml;r. Om ett bildf&auml;lt fylls i kommer bilder att anv&auml;ndas, f&ouml;r att anv&auml;nda rangnamn ist&auml;llet s&aring; ange namnen och se till att motsvarande bildf&auml;lt &auml;r tomt.&lt;br /&gt;Tr&ouml;skelv&auml;rdet &auml;r antalet po&auml;ng en anv&auml;ndare beh&ouml;ver f&ouml;r att n&aring; rangen.";
$ns -&gt; tablerender($caption, $text);
unset($text);

?>
