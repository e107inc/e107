<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2016 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }


$rssMetaRows = USER_AREA
	? e107::getDb()->createQueryBuilder()
		->select('*')->from('rss')
		->where('rss_class', '0')
		->where('rss_limit', '>', 0)
		->orderBy('rss_name')
		->fetchAll()
	: array();

if($rssMetaRows)
{

    $tp = e107::getParser();

	foreach($rssMetaRows as $row)
	{
		if(strpos($row['rss_url'], "*") === false) // Wildcard topic_id's should not be listed
		{
			$name = $tp->toHTML($row['rss_name'], TRUE, 'no_hook, emotes_off');
			$title = htmlspecialchars(SITENAME, ENT_QUOTES, 'utf-8')." ".htmlspecialchars($name, ENT_QUOTES, 'utf-8');

			e107::link([
			    'rel'   => 'alternate',
			    'type'  => 'application/rss+xml',
			    'title' => $title,
			    'href'  => e107::url('rss_menu','rss', $row, array('mode'=>'full'))
			]);

			e107::link([
			    'rel'   => 'alternate',
			    'type'  => 'application/atom+xml',
			    'title' => $title,
			    'href'  => e107::url('rss_menu','atom', $row, array('mode'=>'full'))
			]);

		}
	}

	unset($name, $title);
}

