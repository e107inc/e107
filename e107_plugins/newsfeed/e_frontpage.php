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

e107::includeLan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_frontpage.php');


//v2.x spec.
class newsfeed_frontpage // include plugin-folder in the name.
{
	function config()
	{
		$frontPage = array();
		$frontPage['title'] = LAN_PLUGIN_NEWSFEEDS_NAME; // .': '.vartrue($row['content_heading']); LAN_PLUGIN_NEWSFEEDS_NAME ?
		$frontPage['page'][] = array('page' => '{e_PLUGIN}newsfeed/newsfeed.php', 'title' => NWSF_FP_2);

		if (e107::getDb()->select("newsfeed", "newsfeed_id, newsfeed_name"))
		{
			while ($row = e107::getDb()->fetch())
			{
				$frontPage['page'][] = array('page' => '{e_PLUGIN}newsfeed/newsfeed.php?show.'.$row['newsfeed_id'], 'title' => $row['newsfeed_name']);
			}
		}


		return $frontPage;
	}
}



