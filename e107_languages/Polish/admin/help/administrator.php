<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107
|     e107 Polish Team
|     Polskie wsparcie: http://e107pl.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/administrator.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/administrator.php rev. 1.3
+-----------------------------------------------------------------------------+
*/
 
if (!defined('e107_INIT')) { exit; }

$caption = "Administratorzy";
$text = "Używaj tej strony do edytowania preferencji oraz usuwania administratorów strony. Administrator będzie miał tylko dostęp do tych opcji, które mu nadasz.<br /><br />
Aby utworzyć nowego administratora, przejdź do strony konfiguracji użytkowników i zaktualizuj status wybranego użytkownika do rangi administratora.";
$ns -> tablerender($caption, $text);

?>
