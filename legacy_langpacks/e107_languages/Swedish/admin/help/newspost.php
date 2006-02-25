<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/newspost.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Hj&auml;lp nyhetsposter";
$text = "&lt;b&gt;Generellt&lt;/b&gt;&lt;br /&gt;
Texten kommer att visas p&aring; huvudsidan, den ut&ouml;kade texten kan l&auml;sas genom att klicka p&aring; en 'L&auml;s mer' l&auml;nk.
&lt;br /&gt;
&lt;br /&gt;
&lt;b&gt;Visa endast rubrik&lt;/b&gt;
&lt;br /&gt;
Aktivera detta f&ouml;r att endast visa rubriken p&aring; f&ouml;rstasidan, med en klickbar l&auml;nk till hela nyhetsartikeln.
&lt;br /&gt;&lt;br /&gt;
&lt;b&gt;Aktivering&lt;/b&gt;
&lt;br /&gt;
Om du s&auml;tter ett start och/eller stlutdatum p&aring; din nyhet kommer den endast att visas mellan dessa datum.
";
$ns -&gt; tablerender($caption, $text);

?>
