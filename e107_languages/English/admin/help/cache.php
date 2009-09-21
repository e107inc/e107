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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/cache.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-09-21 19:59:22 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Caching";
$text = "If you have caching turned on it will vastly improve speed on your site and minimise the number of calls to the sql database.<br /><br /><b>IMPORTANT! If you are making your own theme turn caching off otherwise any changes you make will not be reflected immediately.</b>";
$ns -> tablerender($caption, $text);
?>