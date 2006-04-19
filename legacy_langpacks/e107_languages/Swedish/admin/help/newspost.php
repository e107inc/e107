<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/newspost.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Hj&auml;lp nyhetsposter";
$text = "&lt;b&gt;Generellt&lt;/b&gt;<br />
Texten kommer att visas p&aring; huvudsidan, den ut&ouml;kade texten kan l&auml;sas genom att klicka p&aring; en 'L&auml;s mer' l&auml;nk.
<br />
<br />
&lt;b&gt;Visa endast rubrik&lt;/b&gt;
<br />
Aktivera detta f&ouml;r att endast visa rubriken p&aring; f&ouml;rstasidan, med en klickbar l&auml;nk till hela nyhetsartikeln.
<br /><br />
&lt;b&gt;Aktivering&lt;/b&gt;
<br />
Om du s&auml;tter ett start och/eller stlutdatum p&aring; din nyhet kommer den endast att visas mellan dessa datum.
";
$ns -> tablerender($caption, $text);

?>
