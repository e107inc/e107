<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chatbox plugin - Status
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/chatbox_menu/e_status.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Chatbox plugin
 *
 *	@package	e107_plugins
 *	@subpackage	chatbox
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

$chatbox_posts = $sql -> db_Count('chatbox');
$text .= "<div style='padding-bottom: 2px;'><img src='".e_PLUGIN_ABS."chatbox_menu/images/chatbox_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ".ADLAN_115.": ".$chatbox_posts."</div>";
?>