<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Hungarian Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/ugflag.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-02-18 01:59:50 $
|     $Author: lisa_
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "If you are upgrading e107 or just need your site to be offline for a while just tick the maintenance box and your visitors will be redirected to a page explaining the site is down for repair. After you've finished un-tick the box to return site to normal.";

$ns -> tablerender("Maintenance", $text);
?>
