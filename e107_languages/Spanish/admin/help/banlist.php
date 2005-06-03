<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/admin/help/banlist.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-03 20:07:25 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$caption = "Expulsando usuarios de su sitio";
$text = "Usted puede expulsar usuarios de su sitio desde este área.<br />
Escriba una dirección IP completa o use comodines para expulsar un rango completo de direcciones IP. 
También puede escribir una dirección email para impedir el registro como miembro en su sitio.<br /><br />
<b>Expulsando por dirección IP:</b><br />
Escribiendo la dirección IP 123.123.123.123 expulsará a los visitantes de su sitio que accedan desde esa dirección IP.<br />
Escribiendo la dirección IP 123.123.123.* expulsará a todos los visitantes que accedan desde una dirección IP dentro de ese rango.<br /><br />
<b>Expulsando por dirección email</b><br />
Escribiendo la dirección email foo@bar.com expulsará o no permitirá el registro al miembro registrado con esa dirección email.<br />
Escribiendo la dirección email *@bar.com expulsará o no permitirá el registro a los miembros que usen ese dominio en su dirección email.";
$ns -> tablerender($caption, $text);
?>