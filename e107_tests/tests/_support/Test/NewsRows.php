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
 * News items a test plants, published and readable by everybody unless the test says otherwise, and takes away again.
 */
trait NewsRows
{
	/** @var int[] news_id of every item planted and not yet taken away */
	private $newsRows = array();

	/**
	 * @param string $title how the test finds the item again
	 * @param array $columns news columns that differ from a published item everybody can read
	 * @return int news_id
	 */
	protected function haveNewsItem($title, array $columns = array())
	{
		$id = \e107::getDb()->createQueryBuilder()->insert('news')->insertGetId(array_merge(array(
			'news_title'       => $title,
			'news_body'        => 'body',
			'news_class'       => '0',
			'news_author'      => 1,
			'news_datestamp'   => time() - 60,
			'news_start'       => 0,
			'news_end'         => 0,
			'news_render_type' => '0',
		), $columns));

		$this->assertNotEmpty($id, 'could not plant the news item this test needs');
		$this->newsRows[] = (int) $id;

		return (int) $id;
	}

	/**
	 * Deletes every item {@see NewsRows::haveNewsItem()} planted; call it from _after().
	 *
	 * @return void
	 */
	protected function removeNewsItems()
	{
		foreach($this->newsRows as $id)
		{
			\e107::getDb()->createQueryBuilder()->delete('news')->where('news_id', $id)->execute();
		}

		$this->newsRows = array();
	}
}
