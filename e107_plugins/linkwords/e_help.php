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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/linkwords/e_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-12-07 21:55:01 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."linkwords/languages/".e_LANGUAGE."_admin_linkwords.php");

if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'words';

switch ($action)
{
case 'options' :
  $text = LAN_LW_HELP_01;
  break;
case 'words' :
case 'edit'  :
default :
  $text = LAN_LW_HELP_02;
}
$ns -> tablerender(LAN_LW_HELP_00, $text);
unset($text);
?>