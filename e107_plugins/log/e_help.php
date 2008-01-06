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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/e_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-01-06 10:18:34 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

define("LOGPATH", e_PLUGIN."log/");
include_lan(LOGPATH."languages/admin/".e_LANGUAGE."_log_help.php");

if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
case 'export' :
  $text = LAN_STAT_HELP_04;
  break;
case 'rempage' :
  $text = LAN_STAT_HELP_03;
  break;
case 'history' :
  $text = LAN_STAT_HELP_02;
  break;
default :
  $text = LAN_STAT_HELP_05;
}
$ns -> tablerender(LAN_STAT_HELP_01, $text);
unset($text);
?>