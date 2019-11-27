<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	gSitemap addon
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard
class forum_gsitemap // plugin-folder + '_rss'
{

	function import()
	{
		$import = array();

		$sql = e107::getDb();

		$data = $sql->retrieve("forum", "*", "forum_parent!='0' ORDER BY forum_order ASC", true);

		foreach($data as $row)
		{
			$import[] = array(
					'name'  => $row['forum_name'],
					'url'   => e107::url('forum','forum',$row, array('mode'=>'full')), // ('forum/forum/view', $row['forum_id']),
					'type'  => LAN_PLUGIN_FORUM_NAME
			);

		}

		return $import;
	}



}
