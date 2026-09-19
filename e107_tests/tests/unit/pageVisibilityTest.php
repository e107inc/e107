<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A page or chapter set to "all but <class>" stores the negative class id,
 * which the page plugin's list queries filtered with IN (USERCLASS_LIST) and
 * so hid from everybody. See issue #6282.
 */
class pageVisibilityTest extends \Test\Unit
{
	const PREFIX = 'uc_probe_';

	/** @var int a userclass the runtime user is not a member of */
	private $absentClass = 42;

	/**
	 * A chapter the sample database ships, because pageHelper::addSefFields()
	 * reads the chapter list once per process and would not see one seeded
	 * after another test primed it.
	 */
	const CHAPTER = 2;

	protected function _before()
	{
		require_once(e_PLUGIN.'page/includes/pageHelper.php');
		require_once(e_PLUGIN.'page/e_sitelink.php');

		$this->assertNotContains((string) $this->absentClass, explode(',', USERCLASS_LIST), 'This test needs a userclass the runtime user is outside of.');
		$this->assertSame('0', (string) e107::getDb()->retrieve('page_chapters', 'chapter_visibility', 'chapter_id = '.self::CHAPTER), 'This test needs a chapter everybody may see.');

		$this->removeProbeRows();
		$this->addPage('public', 0);
		$this->addPage('restricted', $this->absentClass);
		$this->addPage('inverted', -$this->absentClass);
	}

	protected function _after()
	{
		$this->removeProbeRows();
	}

	public function testTheChapterPageListKeepsAPageHiddenFromAnotherClass()
	{
		$sitelink = new page_sitelink();
		$names = array_column($sitelink->pagesFromChapter(self::CHAPTER), 'link_name');

		$this->assertContains(self::PREFIX.'public', $names);
		$this->assertContains(self::PREFIX.'inverted', $names, 'A page hidden from one class is shown to everybody else.');
		$this->assertNotContains(self::PREFIX.'restricted', $names, 'A page limited to a class the user is outside of stays hidden.');
	}

	/**
	 * @param string $name
	 * @param int $class
	 * @return int page_id
	 */
	private function addPage($name, $class)
	{
		return (int) e107::getDb()->insert('page', array(
			'page_title'     => self::PREFIX.$name,
			'page_sef'       => self::PREFIX.$name,
			'page_chapter'   => self::CHAPTER,
			'page_class'     => (string) $class,
			'page_text'      => 'probe',
			'page_datestamp' => time(),
			'page_order'     => 1,
		));
	}

	private function removeProbeRows()
	{
		e107::getDb()->delete('page', "page_title LIKE '".self::PREFIX."%'");
	}
}
