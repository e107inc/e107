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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/powered_by_menu/powered_by_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:38 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$text = "
<div style='text-align:center'>
<a href='http://e107.org' rel='external'><img src='".e_IMAGE."button.png' alt='e107' style='border:0' /></a>
<br />
<a href='http://php.net' rel='external'><img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='PHP' style='border:0' /></a>
<br />
<a href='http://mysql.com' rel='external'><img src='".e_IMAGE."generic/poweredbymysql-88.png' alt='mySQL' style='border:0' /></a>
</div>";
$ns -> tablerender(POWEREDBY_L1,  $text);
?>