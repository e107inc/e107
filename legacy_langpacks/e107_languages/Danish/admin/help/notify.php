<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/notify.php,v $
|        $Revision: 1.2 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Underretning sender email underretninger n&aring;r e107 begivenheder sker.<br /><br />
F.eks., s&aelig;t 'IP banlyst for gentagne handlinger(flooded) mod site' til brugergruppe 'Admin' og alle admins vil f&aring; sendt en email n&aring;r dit 
site bliver flooded.<br /><br />
Du kan ogs&aring;, som et andet eksempel, s&aelig;tte 'Nyhed postet af admin' til brugergruppe 'Medlemmer' og alle dine brugere vil blive 
sendt nyheden du skriver p&aring; sitet i en email.<br /><br />
Hvis du &oslash;nsker at email underretninger bliver sendt til en alternativ email adresse - v&aelig;lg 'Email' egenskaben og 
skriv email adressen i felter der vises.";

$ns -> tablerender("Underretning Hj&aelig;lp", $text);
?>