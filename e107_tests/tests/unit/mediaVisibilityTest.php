<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A media item set to "all but <class>" stores the negative class id, which
 * the media handler matched with an IN () of the visitor's classes and so hid
 * from everybody, and whose builder form bound the visitor's ids as strings,
 * which also hid a blank class, the column's default. See issue #6282.
 */
class mediaVisibilityTest extends \Test\Unit
{
	const PREFIX = 'uc_probe_';
	const CATEGORY = 'uc_probe';

	/** @var int a userclass the runtime user is not a member of */
	private $absentClass = 42;

	protected function _before()
	{
		$this->assertNotContains((string) $this->absentClass, explode(',', USERCLASS_LIST), 'This test needs a userclass the runtime user is outside of.');

		$this->removeProbeRows();
		$this->addImage('public', '0');
		$this->addImage('blank', '');
		$this->addImage('restricted', (string) $this->absentClass);
		$this->addImage('inverted', (string) -$this->absentClass);
	}

	protected function _after()
	{
		$this->removeProbeRows();
	}

	public function testTheImageListKeepsAnImageHiddenFromAnotherClass()
	{
		$names = array_column(e107::getMedia()->getImages(self::CATEGORY), 'media_name');

		$this->assertContains(self::PREFIX.'public', $names);
		$this->assertContains(self::PREFIX.'blank', $names, 'A blank class, the column default, is public, as it was before the media queries bound the visitor\'s ids as strings.');
		$this->assertContains(self::PREFIX.'inverted', $names, 'An image hidden from one class is shown to everybody else.');
		$this->assertNotContains(self::PREFIX.'restricted', $names, 'An image limited to a class the user is outside of stays hidden.');
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @return int media_id
	 */
	private function addImage($name, $class)
	{
		return (int) e107::getDb()->insert('core_media', array(
			'media_type'      => 'image/jpeg',
			'media_name'      => self::PREFIX.$name,
			'media_caption'   => '',
			'media_description' => '',
			'media_category'  => self::CATEGORY,
			'media_datestamp' => time(),
			'media_author'    => 1,
			'media_url'       => '{e_MEDIA_IMAGE}'.self::PREFIX.$name.'.jpg',
			'media_size'      => 1,
			'media_dimensions' => '1 x 1',
			'media_userclass' => $class,
			'media_usedby'    => '',
			'media_tags'      => '',
		));
	}

	private function removeProbeRows()
	{
		e107::getDb()->delete('core_media', "media_category = '".self::CATEGORY."'");
	}
}
