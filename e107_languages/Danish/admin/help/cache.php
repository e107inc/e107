<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/cache.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$caption = "Caching";
$text = "Hvis du har caching slået til vil det mærkbart forbedre hastigheder på dit site og minimere mængden af kald til sql databasen.<br /><br /><b>VIGTIGT! hvis du laver dit eget tema slå caching fra da mange ændringer du laver ikke vil blive afspejlet.</b>";
$ns -> tablerender($caption, $text);
?>