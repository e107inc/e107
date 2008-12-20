<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/e_help.php,v $
 * $Revision: 1.1 $
 * $Date: 2008-12-20 10:39:29 $
 * $Author: e107steved $
 *
*/
if (!defined('e107_INIT')) { exit; }
if (!plugInstalled('newsfeed')) 
{
	return;
}

@include_lan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_admin_newsfeed.php');
$ns->tablerender(NFLAN_43, NFLAN_42);
?>