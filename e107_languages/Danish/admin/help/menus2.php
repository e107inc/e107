<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/menus2.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Menu Hjælp";
$text .= "Du kan arrangere hvor og i hvilken orden dine menuer er herfra. Brug pilene til at flytte menuerne op og ned indtil du er tilfreds med deres position.<br />
Menuerne midt på siden er de-aktiverede, du kan aktivere disse ved at vælge en placering at vise dem på.
";

$ns -> tablerender("Menuer Hjælp", $text);
?>