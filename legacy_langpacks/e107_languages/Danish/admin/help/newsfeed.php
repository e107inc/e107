<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/newsfeed.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Du kan hente og frmvise andre site's backend RSS nyheds feeds p&aring; dit eget site herfra.<br />Skriv hele URL stien til backenden (eks. http://e107.org/news.xml). Hvis dem RSS feed du henter har en url til en link knap og du vil have den fremvist lad da billede boxen v&aelig;re tom, ellers skriv stien til det billede du vil have vist, eller skriv 'none' for ikke at fremvise et billede. Afm&aelig;rk s&aring; de boxe for at vise presis hvad du vil vise i dine Overskrifter menu. Du kan aktivere og de-aktivere backenden hvis sitet eks. g&aring;r ned.<br /><br />For at se Overskrifterne p&aring; dit site, v&aelig;r sikker p&aring; at  headlines_menu er aktiveret p&aring; din menu side.";

$ns -> tablerender("Overskrifter", $text);
?>