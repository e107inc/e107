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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/admin_log.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-12-15 17:59:49 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "System Logs Help";
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

function common_filters()
{
  $ret = "<b>Data filters</b><br />
  You can specify various filters which restrict the amount of data to view. These are retained in a cookie until you log off.<br />
  The start and end date/time filters have to be enabled through their respective checkboxes.<br />
  Other filters are active when there is a value in the box.<br />
  ";
  return $ret;
}


switch ($action)
{
case 'auditlog' :
  $text = "This page displays that user activity which you have chosen to log.<br /><br />";
  $text .= common_filters();
  break;
case 'config' :
  $text = "This page configures various options for the system logs.<br /><br />
  <b>Common Settings</b><br />
  Sets the number of lines on the log display.<br /><br />
  <b>Admin Log</b><br />
  This log retains events until specifically deleted; use this option to delete old events.<br /><br />
  <b>User Audit Log</b><br />
  This log tracks user activity. For registered users, only those in the specified class are tracked - you can either make this 'Members' to track 
  everyone, or create a specific user class for logging. You then determine the types of activity which you wish to log.<br />
  Registration events may be tracked separately.<br /><br />
  <b>Rolling Log</b><br />
  This log is used to track abnormal events, assist with debugging etc. It can be disabled. Events are automatically removed after the set number of days.
  ";
  break;
case 'rolllog' :
  $text = "The rolling log displays various abnormal events which are not otherwise logged. It may also be used for code debugging and monitoring.<br /><br />";
  $text .= common_filters();
  break;
case 'adminlog' :
default :
  $text = "This page displays administrator activity.<br /><br />
  (At present, logging is still being added to the code, so the list is not complete.)<br /><br />";
  $text .= common_filters();
}
$ns -> tablerender($caption, $text);
?>