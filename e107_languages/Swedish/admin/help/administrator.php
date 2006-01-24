<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/administrator.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Sajtadmin hjälp";
$text = "Använd denna sida för att lägga till eller ta bort sajtadministratörer. Administratören kommer endast att ha tillgång till de markerade funktionerna.<br /><br />
För att skapa en ny admin, gå till användarkonfigureringssidan och uppdatera en befintlig användare till admin status.";
$ns -> tablerender($caption, $text);

?>
