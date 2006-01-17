<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/administrator.php,v $
|        $Revision: 1.3 $
|        $Date: 2006-01-17 14:50:45 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Site Administrator Hjælp";
$text = "Brug denne side til at lave nye, eller slette site administratorer. Administratoren vil kun have rettigheder og adgang til de emner der er afkrydset.<br /><br />
For oprette en ny admin gå til bruger konfigurations siden og opdater en eksisterende bruger til admin status.";

$ns -> tablerender($caption, $text);
?>