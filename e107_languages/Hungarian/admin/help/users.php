<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/users.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-02-18 01:59:51 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/
$caption = "Felhasználók súgó";
$text = "Itt a regisztrált felhasználókra vonatkozó beállításokat tudod módosítani. Módosíthatod a beállításaikat, adminisztrátori jogot adhatsz nekik, új csoportba sorolhatod őket, stb...";
$text .= "<br /><br />Ezen kívül e-mailt is küldhetsz nekik, az E-mail küldése opcióval - itt egy adott tagnak nem tudsz e-mailt küldeni, csak egy adott csoportnak, vagy mindenkinek. (A kitiltott, illetve a regisztrációjukat nem megerősített felhasználók kivételével.)";
$ns -> tablerender($caption, $text);
unset($text);
?>
