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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/newsfeed_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('newsfeed')) 
{
	return '';
}

e107::includeLan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_newsfeed.php');

if(!class_exists('newsfeedClass'))
{
	require_once(e_PLUGIN.'newsfeed/newsfeed_functions.php');
}
global $newsFeed;
if (!is_object($newsFeed)) 
{
	$newsFeed = new newsfeedClass;
}
$info = $newsFeed->newsfeedInfo('all', 'menu');
if($info['text'])
{
	$ns->tablerender($info['title'], $info['text']);
}


