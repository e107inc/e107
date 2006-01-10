<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/chatbox.php,v $
|        $Revision: 1.3 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Sæt dine chatboks instillinger herfra.<br />Hvis overskriv links boksen er markeret, vil alle links der skrives blive erstattet af den tekst du skriver i tekstboksen, dette hindrer lange links i at blive vist forkert. Wordwrap vil automatisk pakke tekst der længere end det anviste her.";

$ns -> tablerender("Chatboks", $text);
?>