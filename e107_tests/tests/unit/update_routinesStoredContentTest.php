<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The 2.x update, for stored content it has no business rewriting.
 */
class update_routinesStoredContentTest extends \Codeception\Test\Unit
{
	const ID = 987654;

	const BODY = 'Hi <iframe src=&quot;JAVASCRIPT:top.x=1&quot;></iframe> <a href=&quot;javascrIpt:y&quot;>a</a>';

	protected function _before()
	{
		require_once(e_ADMIN . 'update_routines.php');
		$this->forget();
	}

	protected function _after()
	{
		$this->forget();
	}

	/**
	 * @return array primary key and stored columns by core table
	 */
	private function seeds()
	{
		return array(
			'news'       => array('news_id', array('news_title' => 'Stored before the update', 'news_body' => self::BODY, 'news_extended' => self::BODY, 'news_meta_description' => '', 'news_summary' => '', 'news_thumbnail' => '')),
			'comments'   => array('comment_id', array('comment_comment' => self::BODY)),
			'submitnews' => array('submitnews_id', array('submitnews_item' => self::BODY, 'submitnews_file' => '')),
			'upload'     => array('upload_id', array('upload_description' => self::BODY)),
		);
	}

	private function forget()
	{
		foreach($this->seeds() as $table => $seed)
		{
			e107::getDb()->delete($table, $seed[0].' = '.self::ID);
		}
	}

	public function testTheUpdateLeavesStoredMarkupAsItWasSaved()
	{
		$sql = e107::getDb();

		foreach($this->seeds() as $table => $seed)
		{
			list($key, $columns) = $seed;
			$sql->insert($table, array(
				'data'         => array($key => self::ID) + $columns,
				'_FIELD_TYPES' => array($key => 'int') + array_fill_keys(array_keys($columns), 'str'),
			));
		}

		update_20x_to_latest('do');

		foreach($this->seeds() as $table => $seed)
		{
			list($key, $columns) = $seed;
			$row = $sql->retrieve($table, $key.', '.implode(', ', array_keys($columns)), $key.' = '.self::ID);
			$this->assertSame($columns, array_intersect_key($row, $columns), $table);
		}
	}
}
