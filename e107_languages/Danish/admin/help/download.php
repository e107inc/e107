<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/download.php,v $
|        $Revision: 1.3 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Upload dine filer til ".e_FILE."downloads folderen, dine billeder til ".e_FILE."downloadimages folderen og minature billeder til ".e_FILE."downloadthumbs folderen.
<br /><br />
For at tilføje et download, skal du først lave en gruppe, dernæst lave en kategori under den gruppe, du vil så være i stand til at lave dit download tilgængelig.";
$ns -> tablerender("Download Hjælp", $text);
?>