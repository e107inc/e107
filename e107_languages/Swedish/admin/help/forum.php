<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/forum.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-01 16:26:44 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$caption = "Forum hjälp";
$text = "<b>Generellt</b><br />
Använd denna sida för att skapa och redigera dina forum<br />
<br />
<b>Värdar/Forum</b><br />
En värd är en rubrik som andra forum visas under, detta gör layouten enklare och underlättar navigeringen i forum enklare för besökarna.
<br /><br />
<b>Tillgänglighet</b>
<br />
Du kan sätta dina forum för åtkomst endast för vissa användare. När du satt 'klassen' för besökarna kan du markera
att enbart tillåta användare i den klassen att få tillgång till forum. Du kan sätta upp både värdar och individuella forum på detta vis.
<br /><br />
<b>Moderatorer</b>
<br />
Markera namnen på de listade administratörerna för att ge dem moderatorstatus i forum. Administratören måste ha forum modereringsrättigheter för att listas här.
<br /><br />
<b>Rang</b>
<br />
Sätt dina användarranger här. Om ett bildfält fylls i kommer bilder att användas, för att använda rangnamn istället så ange namnen och se till att motsvarande bildfält är tomt.<br />Tröskelvärdet är antalet poäng en användare behöver för att nå rangen.";
$ns -> tablerender($caption, $text);
unset($text);
?>