<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Swedish/admin/help/log.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-24 12:49:09 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Aktivera sajtstatistik loggning från denna sida. Om du har ont om utrymme på derverm markera endast domän rutan istället för loggning av hänvisningar, detta kommer endast att logga domänen till skillnad från hela URLen, t.ex. 'jalist.com' istället för 'http://jalist.com/links.php' ";
$ns -> tablerender("Loggningshjälp", $text);

?>
