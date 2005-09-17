<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/banlist.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-17 14:44:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/
$caption = "Sp&auml;rra anv&auml;ndare fr&aring;n din sajt";
$text = "Fr&aring;n denna sida kan du sp&auml;rra anv&auml;ndare fr&aring;n din sajt.<br />
Antingen anger du en fullst&auml;ndig IP-adress eller s&aring; anv&auml;nder du jokertecken f&ouml;r att ange ett omr&aring;de av IP-adresser. Du kan ocks&aring; ange en e-postadress f&ouml;r att hindra att n&aring;gon registrerar sig med den adressen.<br /><br />
<b>Sp&auml;rra via IP-adress:</b><br />
Anger du IP-adressen 123.123.123.123 kommer det att hindra en anv&auml;ndare med den adressen att bes&ouml;ka din sajt.<br />
Anger du ist&auml;llet 123.123.123.* kommer det att hindra att n&aring;gon inom adressomr&aring;det 123.123.123.0 till och med 123.123.123.255 bes&ouml;ker sajten.<br /><br />
<b>Sp&auml;rra via e-postadress</b><br />
Om du anger adressen foo@bar.com kan en anv&auml;ndare inte registrera sig som medlem med den adressen.<br />
Anger du ist&auml;llet e-postadressen *@bar.com kommer ingen med en adress fr&aring;n dom&auml;nen bar.com att kunna registrera sig som medlem.";
$ns -> tablerender($caption, $text);

?>
