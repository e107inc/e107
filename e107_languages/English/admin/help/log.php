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
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/admin/help/log.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-12-14 17:37:43 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Activate site stats logging from this page. If you are short of server space tick the domain only box as referer logging, this will only log the domain as opposed to the whole url, ie 'jalist.com' instead of 'http://jalist.com/links.php' ";
$ns -> tablerender("Logging Help", $text);
?>