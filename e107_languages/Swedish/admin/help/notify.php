<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/notify.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-01 16:26:44 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$text = "Notifiering skickar e-postnotifieringar när någon e107-händelse uppstår.<br /><br />
Till exempel, om du sätter 'IP spärrad för flödning av sajten' till klassen 'Admin' så kommer alla admins att få ett brev med e-post när
sajten blir flödad.<br /><br />
Du kan ocks, som ett annat exempel, sätta 'Nyhet postad av admin' till användarklass 'Medlemmar' och alla dina medlemmar kommer att få ett brev
skickat till sig när du postar en nyhet på sajten.<br /><br />
Om du vill att notiserna skall skickas till en alternativ e-postadress - välj då 'E-post' alternativet och
ange e-postadressen i fältet avsett för detta.";

$ns -> tablerender("Notifieringshjälp", $text);
?>