<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /sitedown.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
$tp = new textparse;
$text = "<font style='font-size: 14px; color: black; font-family: Tahoma, Verdana, Arial, Helvetica; text-decoration: none'>
<div style='text-align:center'>
<img src='".e_IMAGE."logo.png' alt='Logo' />
</div>
<hr />
<br />

<div style='text-align:center'>".($pref['maintainance_text'] ? $tp -> tpa($pref['maintainance_text'],"","admin") : LAN_00)."</div>";
echo $text;

?>