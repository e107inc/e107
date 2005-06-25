<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/banlist.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-25 11:07:34 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$caption = "Spärra användare från din sajt";
$text = "Från denna sida kan du spärra användare från din sajt.<br />
Antingen anger du en fullständig IP-adress eller så använder du jokertecken för att ange ett område av IP-adresser. Du kan också ange en e-postadress för att hindra att någon registrerar sig med den adressen.<br /><br />
<b>Spärra via IP-adress:</b><br />
Anger du IP-adressen 123.123.123.123 kommer det att hindra en användare med den adressen att besöka din sajt.<br />
Anger du istället 123.123.123.* kommer det att hindra att någon inom adressområdet 123.123.123.0 till och med 123.123.123.255 besöker sajten.<br /><br />
<b>Spärra via e-postadress</b><br />
Om du anger adressen foo@bar.com kan en användare inte registrera sig som medlem med den adressen.<br />
Anger du istället e-postadressen *@bar.com kommer ingen med en adress från domänen bar.com att kunna registrera sig som medlem.";
$ns -> tablerender($caption, $text);

?>
