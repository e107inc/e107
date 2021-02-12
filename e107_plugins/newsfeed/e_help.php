<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/e_help.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('newsfeed')) 
{
	return;
}

e107::includeLan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_admin_newsfeed.php');
$ns->tablerender(NFLAN_43, e107::getParser()->toHTML(NFLAN_42, true) );
