<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/download.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Upload dine filer til ".e_FILE."downloads folderen, dine billeder til ".e_FILE."downloadimages folderen og minature billeder til ".e_FILE."downloadthumbs folderen.
<br /><br />
For at tilf&oslash;je et download, skal du f&oslash;rst lave en gruppe, dern&aelig;st lave en kategori under den gruppe, du vil s&aring; v&aelig;re i stand til at lave dit download tilg&aelig;ngelig.";
$ns -> tablerender("Download Hj&aelig;lp", $text);
?>