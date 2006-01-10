<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/newspost.php,v $
|        $Revision: 1.3 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Nyheds indlæg Hjælp";
$text = "<b>Generelt</b><br />
Tekst vil blive vist på hoved siden, udvidet vil blive vist ved at klikke på et 'Læs mere' link. Kilde og URL er hvor du har historien fra.
<br />
<br />
<b>Vis kun titel</b>
<br />
gør det muligt at kun vise titlen fra en nyhed på forsiden, med et klikbart link til hele nyheden.
<br /><br />
<b>Aktivering</b>
<br />
Hvis du ønsker at sætte en start og/eller slut dato vil din nyhed kun blive vis i det angivne tidsrum.
";
$ns -> tablerender($caption, $text);
?>