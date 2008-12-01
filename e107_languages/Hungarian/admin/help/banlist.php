<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ŠSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/banlist.php,v $
|     $Revision: 1.6 $
|     $Date: 2008-12-01 16:42:39 $
|     $Author: e107hun-lac $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Kitiltások súgó";
$text = "Kitilthatsz felhasználókat az oldalról itt.<br />
Add meg a teljes IP címet, vagy használj *-ot egy IP cím tartomány kitiltásához.<br /><br />
<b>Kitiltás IP címmel:</b><br />
Az 123.123.123.123 IP cím megadásával letiltod az erről a címről érkező felhasználókat.<br />
Az 123.123.123.* IP cím megadásával letiltod az erről az IP cím tartományról érkező felhasználókat.<br /><br />
<b>Kitiltás email címmel</b><br />
A foo@bar.com email cím megadása letiltja ennek az email címnek a használatát, így e címmel senki nem fog tudni regisztrálni.<br />
Az *@bar.com cím megadása a bar.com domain-t tiltja le, így e domainről semmilyen email címmel senki nem fog tudni regisztrálni.<br /><br />
<b>Kitiltás felhasználónévvel</b><br />
Ezt végrehajthatod a felhasználók adminisztrációs oldalról.";
$ns -> tablerender($caption, $text);
?>
