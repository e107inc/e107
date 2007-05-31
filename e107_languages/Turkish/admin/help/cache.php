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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Turkish/admin/help/cache.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-05-31 13:47:59 $
|     $Author: whoisbig $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Caching";
$text = "If you have caching turned on it will vastly improve speed on your site and minimize the amount of calls to the sql database.<br /><br /><b>IMPORTANT! If you are making your own theme turn caching off as any changes you make will not be reflected.</b>";
$ns -> tablerender($caption, $text);
?>