<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/log.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Aktiver site statistik føring fra denne side. Hvis du ikke har meget server plads marker da Domæne boxen som logning type, dette vil kun logge domænet i modsætning til hele URL'en, eks. 'jalist.com' istedet for 'http://jalist.com/links.php' ";
$ns -> tablerender("Logning Hjælp", $text);
?>