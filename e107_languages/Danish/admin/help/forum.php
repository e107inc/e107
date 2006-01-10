<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/forum.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Forum Hjælp";
$text = "<b>Generelt</b><br />
Brug denne side til at lave eller redigere dine forums<br />
<br />
<b>Grupper/Forums</b><br />
En gruppe er en overskrift hvor andre forums bliver vist under, dette gør layoutet mere simpelt og gør navigation rundt i dine forums mere overskueligt for dine besøgende.
<br /><br />
<b>Tilgængelighed</b>
<br />
Du kan indstille dine forums til kun at kunne ses af bestemte besøgende. Når du har sat bruger 'klassen' af de besøgende kan du markere 
klassen til kun at tillade disse besøgende at se forummet. Du kan indstille grupper eller individuelle forums på denne måde.
<br /><br />
<b>Bestyrer</b>
<br />
Marker navnene på de listede administratore for at give dem bestyrer status i forummet. Administratoren skal have forum bestyrer tilladelse for at blive listet her.
<br /><br />
<b>Niveau</b>
<br />
Sæt bruger rang her. Hvis billede felterne er udfyldt, vil billederne blive brugt, for at bruge rang navne skriv navnene og vær sikker dig at det tilhørende rang billede felt er tomt.<br />Grændsen er det antal points brugeren har brug for, før denne niveau ændres.";
$ns -> tablerender($caption, $text);
unset($text);
?>