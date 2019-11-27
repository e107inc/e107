<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

// DEPRECATED FILE - moved renderHelp() in admin_config.php 

if (!defined('e107_INIT')) { exit; }
return; 

/*

e107::lan('linkwords',e_LANGUAGE."_admin_linkwords.php"); 

if (e_QUERY) list($action,$junk) = explode('.',e_QUERY.'.'); else $action = 'words';

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
?>*/