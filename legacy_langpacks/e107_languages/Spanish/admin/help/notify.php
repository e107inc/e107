<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Spanish/admin/help/notify.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-08-28 09:26:19 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
$text = "Notificaciones envía notificaciones de correo cuando ocurren eventos en el sitio.<br /><br />
Por ejemplo, fijar 'IP expulsada por flooding' a la clase 'Admin' y a todos los Admins se les enviará una notificación
cuando el lugar esté siendo floodeado.<br /><br />
También puede, como otro ejemplo, fijar 'Nuevos elementos enviados por admin' a la clase de usuario 'Miembros' y a todos los usuarios se les mandará
nuevos elementos que envíe en un correo.<br /><br />
Si desea que las notificaciones se envíen en una dirección alternativa - seleccione la opción 'Email' y
escriba la dirección en el campo proporcionado.";

$ns -> tablerender("Ayufa Notificaciones", $text);
?>