<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/forum.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Forum hj&auml;lp";
$text = "&lt;b&gt;Generellt&lt;/b&gt;<br />
Anv&auml;nd denna sida f&ouml;r att skapa och redigera dina forum<br />
<br />
&lt;b&gt;V&auml;rdar/Forum&lt;/b&gt;<br />
En v&auml;rd &auml;r en rubrik som andra forum visas under, detta g&ouml;r layouten enklare och underl&auml;ttar navigeringen i forum enklare f&ouml;r bes&ouml;karna.
<br /><br />
&lt;b&gt;Tillg&auml;nglighet&lt;/b&gt;
<br />
Du kan s&auml;tta dina forum f&ouml;r &aring;tkomst endast f&ouml;r vissa anv&auml;ndare. N&auml;r du satt 'klassen' f&ouml;r bes&ouml;karna kan du markera
att enbart till&aring;ta anv&auml;ndare i den klassen att f&aring; tillg&aring;ng till forum. Du kan s&auml;tta upp b&aring;de v&auml;rdar och individuella forum p&aring; detta vis.
<br /><br />
&lt;b&gt;Moderatorer&lt;/b&gt;
<br />
Markera namnen p&aring; de listade administrat&ouml;rerna f&ouml;r att ge dem moderatorstatus i forum. Administrat&ouml;ren m&aring;ste ha forum modereringsr&auml;ttigheter f&ouml;r att listas h&auml;r.
<br /><br />
&lt;b&gt;Rang&lt;/b&gt;
<br />
S&auml;tt dina anv&auml;ndarranger h&auml;r. Om ett bildf&auml;lt fylls i kommer bilder att anv&auml;ndas, f&ouml;r att anv&auml;nda rangnamn ist&auml;llet s&aring; ange namnen och se till att motsvarande bildf&auml;lt &auml;r tomt.<br />Tr&ouml;skelv&auml;rdet &auml;r antalet po&auml;ng en anv&auml;ndare beh&ouml;ver f&ouml;r att n&aring; rangen.";
$ns -> tablerender($caption, $text);
unset($text);

?>
