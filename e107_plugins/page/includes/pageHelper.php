<?php


class pageHelper
{

	public static function load()
	{

		$books = e107::getDb('pageHelper')->retrieve("SELECT chapter_id,chapter_sef,chapter_name,chapter_parent FROM #page_chapters ORDER BY chapter_id ASC", true);

		$chapter = array();
		foreach($books as $row)
		{
			$id = (int) $row['chapter_id'];
			$chapter[$id] = $row;
		}

		return $chapter;

	}

	/**
	 * Takes an existing array (eg. from page table) and adds in chapter and book field data based on the given chapter field.
	 * @param array $row
	 * @param string $chapterField
	 * @return array|false
	 */
	public static function addSefFields(&$row = array(), $chapterField = 'page_chapter')
	{
		if($chapterField ==='page_chapter' && empty($row['page_sef']))
		{
			$row['page_sef'] = '--sef-not-assigned--';
		}

		if(empty($row[$chapterField])) // nothing to add, so return what was sent.
		{
			return $row;
		}

		$chapID = (int) $row[$chapterField];

		static $chaptersList;

		if(empty($chaptersList))
		{
			$chaptersList = self::load();
		}

		// merge in the chapter data.
		foreach($chaptersList[$chapID] as $k => $v)
		{
			if(!isset($row[$k]))
			{
				$row[$k] = $v;
			}
		}

/*		if(isset($row['book_id']))
		{
			return $row;
		}*/

		// merge in the book data.
		$parent = (int) $row['chapter_parent'];

		$row['book_id']     = $parent;
		$row['book_name'] 	= varset($chaptersList[$parent]['chapter_name'], '--sef-not-assigned--');
		$row['book_sef']    = vartrue($chaptersList[$parent]['chapter_sef'], '--sef-not-assigned--');

		return $row;
	}
}