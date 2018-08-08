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

if (!defined('e107_INIT'))
{
	exit;
}
 
// v2.x Standard
// there is missing lan for Download category in global lan file

class download_gsitemap
{
	function import()
	{
		$import = array();
		$sql = e107::getDb();
		/* public, quests */
		$userclass_list = "0,252";
		$_t = time();
		$data = $sql->retrieve("download_category", "*", " ORDER BY download_category_order ASC", true);

		foreach($data as $row)
		{
			$import[] = array(
				'name' => $row['download_category_name'],
			  'url' => e107::url('download', 'category', $row,   array('mode' => 'full' )),
				'type' => LAN_PLUGIN_DOWNLOAD_NAME
			);
		}

		$data = $sql->retrieve("download", "*", "download_class IN (" . $userclass_list . ")  AND  download_active != '0'  ORDER BY download_datestamp ASC", true);
		foreach($data as $row)
		{
			$import[] = array(
				'name' => $row['download_name'],
			  'url' => e107::url('download', 'item', $row,   array('mode' => 'full' )),
				'type' => LAN_PLUGIN_DOWNLOAD_NAME
			);
		}

		return $import;
	}
}