<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/notify.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Notifiering skickar e-postnotifieringar n&auml;r n&aring;gon e107-h&auml;ndelse uppst&aring;r.&lt;br /&gt;&lt;br /&gt;
Till exempel, om du s&auml;tter 'IP sp&auml;rrad f&ouml;r fl&ouml;dning av sajten' till klassen 'Admin' s&aring; kommer alla admins att f&aring; ett brev med e-post n&auml;r
sajten blir fl&ouml;dad.&lt;br /&gt;&lt;br /&gt;
Du kan ocks, som ett annat exempel, s&auml;tta 'Nyhet postad av admin' till anv&auml;ndarklass 'Medlemmar' och alla dina medlemmar kommer att f&aring; ett brev
skickat till sig n&auml;r du postar en nyhet p&aring; sajten.&lt;br /&gt;&lt;br /&gt;
Om du vill att notiserna skall skickas till en alternativ e-postadress - v&auml;lj d&aring; 'E-post' alternativet och
ange e-postadressen i f&auml;ltet avsett f&ouml;r detta.";

$ns -&gt; tablerender("Notifieringshj&auml;lp", $text);

?>
