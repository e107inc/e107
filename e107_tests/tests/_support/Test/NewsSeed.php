<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * PHP that a booted child runs to seed a news item, for the tests that search for one.
 */
final class NewsSeed
{
	/**
	 * @param string $title
	 * @param string $category the news_category value, as PHP the child evaluates
	 * @return string PHP that inserts the item and leaves its id in $news
	 */
	public static function php($title, $category)
	{
		$title = var_export((string) $title, true);

		return "\$news = e107::getDb()->insert('news', array('news_title' => ".$title.", 'news_body' => '', "
			. "'news_extended' => '', 'news_summary' => '', 'news_meta_description' => '', "
			. "'news_thumbnail' => '', 'news_category' => ".$category.", 'news_datestamp' => time() - 60, "
			. "'news_start' => 0, 'news_end' => 0, 'news_class' => 0, 'news_render_type' => 0)); ";
	}
}
