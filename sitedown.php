<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107/sitedown.php,v $
|     $Revision: 1.6 $
|     $Date: 2004-09-10 02:58:10 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
$tp = new textparse;
$text = "<font style='font-size: 14px; color: black; font-family: Tahoma, Verdana, Arial, Helvetica; text-decoration: none'>
<div style='text-align:center'>
<img src='".e_IMAGE."logo.png' alt='Logo' />
</div>
<hr />
<br />

<div style='text-align:center'>".($pref['maintainance_text'] ? $tp -> tpa($pref['maintainance_text'],"","admin") : "<b>- ".SITENAME." ".LAN_00." -</b><br /><br />".LAN_01 )."</div>";
echo "<html><head><title>".PAGE_NAME."</title></head><body>";

echo $text;

echo "</body></html>";

?>