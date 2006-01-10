<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/forum.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Forum Hj&aelig;lp";
$text = "<b>Generelt</b><br />
Brug denne side til at lave eller redigere dine forums<br />
<br />
<b>Grupper/Forums</b><br />
En gruppe er en overskrift hvor andre forums bliver vist under, dette g&oslash;r layoutet mere simpelt og g&oslash;r navigation rundt i dine forums mere overskueligt for dine bes&oslash;gende.
<br /><br />
<b>Tilg&aelig;ngelighed</b>
<br />
Du kan indstille dine forums til kun at kunne ses af bestemte bes&oslash;gende. N&aring;r du har sat bruger 'klassen' af de bes&oslash;gende kan du markere 
klassen til kun at tillade disse bes&oslash;gende at se forummet. Du kan indstille grupper eller individuelle forums p&aring; denne m&aring;de.
<br /><br />
<b>Bestyrer</b>
<br />
Marker navnene p&aring; de listede administratore for at give dem bestyrer status i forummet. Administratoren skal have forum bestyrer tilladelse for at blive listet her.
<br /><br />
<b>Niveau</b>
<br />
S&aelig;t bruger rang her. Hvis billede felterne er udfyldt, vil billederne blive brugt, for at bruge rang navne skriv navnene og v&aelig;r sikker dig at det tilh&oslash;rende rang billede felt er tomt.<br />Gr&aelig;ndsen er det antal points brugeren har brug for, f&oslash;r denne niveau &aelig;ndres.";
$ns -> tablerender($caption, $text);
unset($text);
?>