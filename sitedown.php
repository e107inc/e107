<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/sitedown.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

$text = "<font style='FONT-SIZE: 14px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none'>
<div style='text-align:center'>
<img src='".e_IMAGE."logo.png' alt='Logo' />
</div>
<hr />
<br />

<div style='text-align:center'>".($pref['maintainance_text'] ? $pref['maintainance_text'] : LAN_00)."</div>";
echo $text;

?>