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
 * Custom pages a test plants, outside any chapter and readable by everybody unless the test says otherwise, and takes away again.
 */
trait PageRows
{
	/** @var int[] page_id of every page planted and not yet taken away */
	private $pageRows = array();

	/**
	 * @param string $title how the test finds the page again
	 * @param array $columns page columns that differ from a page everybody can read outside any chapter
	 * @return int page_id
	 */
	protected function havePage($title, array $columns = array())
	{
		$id = \e107::getDb()->createQueryBuilder()->insert('page')->insertGetId(array_merge(array(
			'page_title'     => $title,
			'page_sef'       => strtolower($title),
			'page_chapter'   => 0,
			'page_class'     => '0',
			'page_text'      => 'probe',
			'page_datestamp' => time(),
			'page_order'     => 1,
		), $columns));

		$this->assertNotEmpty($id, 'could not plant the page this test needs');
		$this->pageRows[] = (int) $id;

		return (int) $id;
	}

	/**
	 * Deletes every page {@see PageRows::havePage()} planted; call it from _after().
	 *
	 * @return void
	 */
	protected function removePages()
	{
		foreach($this->pageRows as $id)
		{
			\e107::getDb()->createQueryBuilder()->delete('page')->where('page_id', $id)->execute();
		}

		$this->pageRows = array();
	}
}
