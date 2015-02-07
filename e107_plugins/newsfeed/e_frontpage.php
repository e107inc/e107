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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/e_frontpage.php,v $
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

include_lan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_frontpage.php');

$front_page['newsfeed']['title'] = NWSF_FP_1.': '.vartrue($row['content_heading']);
$front_page['newsfeed']['page'][] = array('page' => $PLUGINS_DIRECTORY.'newsfeed/newsfeed.php', 'title' => NWSF_FP_2);

if ($sql -> db_Select("newsfeed", "newsfeed_id, newsfeed_name")) {
	while ($row = $sql -> db_Fetch()) {
		$front_page['newsfeed']['page'][] = array('page' => $PLUGINS_DIRECTORY.'newsfeed/newsfeed.php?show.'.$row['newsfeed_id'], 'title' => $row['newsfeed_name']);
	}
}

?>