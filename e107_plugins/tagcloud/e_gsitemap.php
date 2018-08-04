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

class tagcloud_gsitemap

// plugin-folder + '_rss'

{
	function import()
	{
		$import = array();
		$sql = e107::getDb();

		// tags for news

		$data = $sql->retrieve('news', 'news_id, news_meta_keywords', "news_meta_keywords !='' ", true);
		if ($data)
		{
			foreach($data as $row)
			{
				$tmp = explode(",", $row['news_meta_keywords']);
				foreach($tmp as $word)
				{
					$tags[$word] = $word;
				}
			}

			foreach($tags as $row)
			{
				$url = e107::getUrl()->create('news/list/tag', array(
					'tag' => $row,
					'full' => 1
				));
				$title = $row;
				$type = LAN_PLUGIN_TAGCLOUD_NAME;
				$import[] = array(
					'name' => $title,
					'url' => $url, // ('forum/forum/view', $row['forum_id']),
					'type' => $type,
				);
			}

			return $import;
		}
	}
}