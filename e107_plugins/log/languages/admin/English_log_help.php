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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/languages/admin/English_log_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-01-06 10:18:34 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

define('LAN_STAT_HELP_01','Statistics Logging');
define('LAN_STAT_HELP_02','This option deletes historical data from the database. It does not affect the \'all-time\' figures.<br /><br />
Caution! Once deleted, this data cannot be recovered. Back up and/or export the data you may require first.');
define('LAN_STAT_HELP_03','This option allows you to delete the data relating to a specific site page.');
define('LAN_STAT_HELP_04','This option allows export of statistics data in CSV format. This can be imported into many other applications for
detailed analysis. Refer to the wiki page on the stats logging plugin for more detail on file formats etc');
define('LAN_STAT_HELP_05','<b>Enable Stats Logging</b><br />No logging takes place if disabled<br /><br />
<b>Stats Page Access</b><br />
Determines who can see the site statistics<br /><br />
<b>Count Admin Visits</b><br />
Frequent visits by the admins can distort site statistics, so you can exclude them<br /><br />
<b>Maximum records to display...</b><br />
Sets the number of \'recent visitors\' retained<br /><br />
<b>Statistic Types</b><br />
Determines which information is logged. Recording monthly data will take up more database space, and gives better visibility.
If monthly statistics are being collected, you can set whether just the current month, or current month and previous month, are displayed<br /><br />
<b>Reset Stats</b><br />
Clears the selected all-time data to zero<br /><br />
');
define('LAN_STAT_HELP_06','');

?>