<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/e107_languages/Danish/admin/help/search.php,v $
|        $Revision: 1.4 $
|        $Date: 2006-01-17 15:27:37 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if (!defined('e107_INIT')) { exit; }

$text = "Hvis din MySql server version tillader det kan du skifte
til MySql sorterings metoden der er hurtigere end PHP sorterings metoden. Se Indstillinger<br /><br />
Hvis dit site inkluderer Ideografiske sprog som Kinesisk og Japansk skal du
bruge PHP sorterings metoden og slå sammenligning af hele ord fra.";
$ns -> tablerender("Søg Hjælp", $text);
?>