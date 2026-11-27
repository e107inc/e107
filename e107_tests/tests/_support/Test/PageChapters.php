<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

use e107;

/**
 * Custom Pages chapters a unit test plants, each giving its pages one custom field, and takes away again.
 */
trait PageChapters
{
	/** @var int[] every chapter planted */
	private $plantedChapters = array();

	/**
	 * @param string $name
	 * @param string $field the custom field's name, which its title capitalises
	 * @param string $type the custom field's type, e.g. 'text' or 'url'
	 * @return int the new chapter's id
	 */
	protected function haveChapterWithField($name, $field, $type)
	{
		$id = (int) e107::getDb()->insert('page_chapters', array(
			'chapter_parent'           => 1,
			'chapter_name'             => $name,
			'chapter_sef'              => strtolower($name),
			'chapter_meta_description' => '',
			'chapter_fields'           => e107::serialize(array($field => array('title' => ucfirst($field), 'type' => $type, 'writeParms' => '', 'help' => '')), 'json'),
		));

		self::assertGreaterThan(0, $id, 'the chapter was not seeded');

		$this->plantedChapters[] = $id;

		return $id;
	}

	/**
	 * @return void
	 */
	protected function dropChapters()
	{
		if($this->plantedChapters)
		{
			e107::getDb()->delete('page_chapters', 'chapter_id IN ('.implode(',', array_map('intval', $this->plantedChapters)).')');
		}

		$this->plantedChapters = array();
	}
}
