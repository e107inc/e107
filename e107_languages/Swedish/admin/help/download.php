<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/download.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Var vänlig att ladda upp dina filer till ".e_FILE."downloads foldern, dina bilder till ".e_FILE."downloadimages foldern och tumnagelbilder till ".e_FILE."downloadthumbs foldern.
<br /><br />
För att skapa en nerladdning, skapa först en värd, sedan en underkategori under den värden. Efter det kommer du att kunna göra nerladdningen tillgänglig.";
$ns -> tablerender("Nerladdningshjälp", $text);

?>
