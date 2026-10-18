<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves pages through e107_admin/cpage.php: the menu button's address, and the address in a custom field of type url
 * that the page's chapter declares.
 */
class cpageScriptAddressTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const TITLE = 'cpageScriptAddressTest';

	/** @var string the edit form the overview list opens, which saves a page and its menu alike */
	const EDIT = 'mode=overview&action=edit&id=';

	/** @var int the chapter whose pages carry a custom field of type url named site */
	private $chapter = 0;

	/** @var string[] the page columns the suite's database predates and cpage.php saves, which this test added */
	private $addedColumns = array();

	protected function _before()
	{
		foreach(array('page_subtitle', 'page_metatitle', 'page_metaimage') as $column)
		{
			if(!e107::getDb()->field('page', $column) && e107::getDb()->gen('ALTER TABLE `#page` ADD `'.$column."` varchar(250) NOT NULL default ''"))
			{
				$this->addedColumns[] = $column;
			}
		}

		$this->chapter = (int) e107::getDb()->createQueryBuilder()->insert('page_chapters')->insertGetId(array(
			'chapter_parent'           => 1,
			'chapter_name'             => self::TITLE,
			'chapter_sef'              => strtolower(self::TITLE),
			'chapter_meta_description' => '',
			'chapter_fields'           => e107::serialize(array('site' => array('title' => 'Site', 'type' => 'url', 'writeParms' => '', 'help' => '')), 'json'),
		));

		self::assertGreaterThan(0, $this->chapter, 'the chapter was not seeded');
	}

	protected function _after()
	{
		$db = e107::getDb();
		$db->createQueryBuilder()->delete('page')->whereLike('page_title', self::TITLE.'%')->execute();
		$db->createQueryBuilder()->delete('page_chapters')->where('chapter_id', $this->chapter)->execute();
		$db->createQueryBuilder()->delete('menus')->where('menu_name', self::TITLE)->execute();

		foreach($this->addedColumns as $column)
		{
			$db->gen('ALTER TABLE `#page` DROP `'.$column.'`');
		}
	}

	public function testAButtonAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, array('menu_button_url' => 'javascript:void(0)'));

		self::assertSame(array(), $this->storedPages(), "the page was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Custom Button URL</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveAButtonAddressThatRunsScript()
	{
		$page = $this->create(e_UC_MAINADMIN, array('menu_button_url' => 'javascript:void(0)'));

		self::assertCount(1, $this->storedPages(), "the page was refused:\n".$page);
	}

	public function testAButtonAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, array('menu_button_url' => 'https://e107.org/'));

		self::assertCount(1, $this->storedPages(), "the page was refused:\n".$page);
	}

	public function testAnInlineEditCannotGiveTheButtonAnAddressThatRunsScript()
	{
		$id = $this->seedPage('https://e107.org/', array());

		$this->editInline($id, 'javascript:void(0)');
		self::assertSame('https://e107.org/', $this->storedPages()[self::TITLE]['menu_button_url']);

		$this->editInline($id, 'news.php');
		self::assertSame('news.php', $this->storedPages()[self::TITLE]['menu_button_url'], 'an inline edit of the address has to save it');
	}

	/**
	 * The edit form is opened first, and what it shows is posted back under a new title.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredButtonAddressPassesUnchanged($address)
	{
		$id = $this->seedPage($address, array());
		$shown = $this->shownForm($id);

		self::assertSame($address, $shown['menu_button_url'], 'the form has to show the stored address');

		$page = $this->update(e_UC_NOBODY, $id, $shown);
		$stored = $this->storedPages();

		self::assertSame(array(self::TITLE.' renamed'), array_keys($stored), "the unchanged address was refused:\n".$page);
		self::assertSame($address, $stored[self::TITLE.' renamed']['menu_button_url']);
	}

	/**
	 * Saving the form again runs the address through toDB() once more, which would store one that runs script.
	 */
	public function testAStoredButtonAddressThatSavingAgainWouldMakeRunScriptIsRefused()
	{
		$id = $this->seedPage(self::$inertScriptAddress, array());

		$page = $this->update(e_UC_NOBODY, $id, $this->shownForm($id));
		$stored = $this->storedPages();

		self::assertSame(array(self::TITLE), array_keys($stored), "the page was saved:\n".$page);
		self::assertSame(self::$inertScriptAddress, $stored[self::TITLE]['menu_button_url']);
	}

	public function testACustomFieldAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, array('page_chapter' => $this->chapter, 'page_fields__site' => 'javascript:void(0)'));

		self::assertSame(array(), $this->storedPages(), "the page was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Site</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testACustomFieldPostedUnderAnotherSpellingOfItsNameIsJudged()
	{
		$page = $this->create(e_UC_NOBODY, array('page_chapter' => $this->chapter, 'page_fields__site__x' => 'javascript:void(0)'));

		self::assertSame(array(), $this->storedPages(), "the page was saved:\n".$page);
	}

	public function testACustomFieldIsJudgedUnderTheChapterThePageMovesTo()
	{
		$id = $this->seedPage('', array());
		$shown = $this->shownForm($id);
		$shown['page_chapter'] = $this->chapter;
		$shown['page_fields__site'] = 'javascript:void(0)';

		$page = $this->update(e_UC_NOBODY, $id, $shown);

		self::assertSame(array(self::TITLE), array_keys($this->storedPages()), "the page was saved:\n".$page);
	}

	public function testCustomFieldsPostedWholeAreNotStored()
	{
		$page = $this->create(e_UC_NOBODY, array('page_chapter' => $this->chapter, 'page_fields' => array('site' => 'javascript:void(0)')));
		$stored = $this->storedPages();

		self::assertSame(array(self::TITLE), array_keys($stored), "the page was not saved:\n".$page);
		self::assertStringNotContainsString('javascript', (string) $stored[self::TITLE]['page_fields']);
	}

	public function testACustomFieldIsJudgedUnderTheChapterAFractionalNumberStoresThePageIn()
	{
		$page = $this->create(e_UC_NOBODY, array('page_chapter' => ($this->chapter - 1).'.5', 'page_fields__site' => 'javascript:void(0)'));

		foreach($this->storedPages() as $row)
		{
			self::assertFalse((int) $row['page_chapter'] === $this->chapter && strpos((string) $row['page_fields'], 'javascript') !== false, "the page's chapter has the address in a url field:\n".$page);
		}
	}

	public function testACustomFieldAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, array('page_chapter' => $this->chapter, 'page_fields__site' => 'https://e107.org/'));

		self::assertCount(1, $this->storedPages(), "the page was refused:\n".$page);
	}

	public function testAnEditCannotGiveACustomFieldAnAddressThatRunsScript()
	{
		$id = $this->seedPage('', array('site' => 'https://e107.org/'));
		$shown = $this->shownForm($id);
		$shown['page_fields__site'] = 'javascript:void(0)';

		$page = $this->update(e_UC_NOBODY, $id, $shown);

		self::assertSame(array(self::TITLE), array_keys($this->storedPages()), "the address was saved:\n".$page);
	}

	public function testAStoredCustomFieldAddressThatRunsScriptPassesUnchanged()
	{
		$id = $this->seedPage('', array('site' => 'javascript:void(0)'));
		$shown = $this->shownForm($id);

		self::assertSame('javascript:void(0)', $shown['page_fields__site'], 'the form has to show the stored address');

		$page = $this->update(e_UC_NOBODY, $id, $shown);
		$stored = $this->storedPages();

		self::assertSame(array(self::TITLE.' renamed'), array_keys($stored), "the unchanged address was refused:\n".$page);
		self::assertStringContainsString('javascript:void(0)', $stored[self::TITLE.' renamed']['page_fields']);
	}

	/**
	 * The form loads a custom field through toDB(), which takes an empty bbcode pair out, so it shows a working address
	 * where an inert one is stored; posting that back is a change.
	 */
	public function testACustomFieldAddressTheFormShowsDifferentlyIsJudgedAgainstTheStoredOne()
	{
		$id = $this->seedPage('', array('site' => self::$inertScriptAddress));

		$page = $this->update(e_UC_NOBODY, $id, $this->shownForm($id));
		$stored = $this->storedPages();

		self::assertSame(array(self::TITLE), array_keys($stored), "the page was saved:\n".$page);
		self::assertSame(array('site' => self::$inertScriptAddress), e107::unserialize($stored[self::TITLE]['page_fields']));
	}

	/**
	 * Posts every column of the page, as the form does, since the model writes null for one left out.
	 *
	 * @param int $scriptClass the class that may post script
	 * @param array $fields the fields under test
	 * @return string the page
	 */
	private function create($scriptClass, array $fields)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_admin/cpage.php', 'mode=page&action=create', $fields + array(
			'etrigger_submit'   => 'create',
			'page_title'        => self::TITLE,
			'page_subtitle'     => '',
			'page_sef'          => strtolower(self::TITLE),
			'page_chapter'      => 0,
			'page_template'     => 'default',
			'page_author'       => 1,
			'page_text'         => 'Text',
			'page_class'        => 0,
			'page_rating_flag'  => 0,
			'page_comment_flag' => 0,
			'page_password'     => '',
			'page_metatitle'    => '',
			'page_metadscr'     => '',
			'page_metakeys'     => '',
			'page_metaimage'    => '',
			'page_metarobots'   => '',
			'page_order'        => 0,
			'menu_name'         => self::TITLE,
			'menu_title'        => '',
			'menu_text'         => '',
			'menu_template'     => 'default',
			'menu_class'        => 0,
			'menu_button_text'  => '',
			'menu_button_url'   => '',
			'menu_icon'         => '',
			'menu_image'        => '',
		));
	}

	/**
	 * @param int $id
	 * @return array the inputs of the page's edit form as a browser posts them back
	 */
	private function shownForm($id)
	{
		return $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/cpage.php', self::EDIT.$id));
	}

	/**
	 * @param int $scriptClass
	 * @param int $id
	 * @param array $shown what the edit form showed, posted back under a new title
	 * @return string the page
	 */
	private function update($scriptClass, $id, array $shown)
	{
		$posted = array_intersect_key($shown, array_flip(array('menu_button_url', 'page_fields__site', 'page_chapter', 'page_sef', 'menu_name')));

		return $this->openAsScriptClass($scriptClass, 'e107_admin/cpage.php', self::EDIT.$id, $posted + array(
			'etrigger_submit' => 'update',
			'page_title'      => self::TITLE.' renamed',
			'page_text'       => 'Text',
		));
	}

	/**
	 * @param int $id
	 * @param string $address
	 */
	private function editInline($id, $address)
	{
		$this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/cpage.php', 'mode=overview&action=inline&id='.$id,
			array('name' => 'menu_button_url', 'value' => $address, 'pk' => $id), '', true);
	}

	/**
	 * @param string $address the menu button's
	 * @param array $fields the custom field values, saved in the chapter that declares them when there are any
	 * @return int
	 */
	private function seedPage($address, array $fields)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('page')->insertGetId(array(
			'page_title'      => self::TITLE,
			'page_sef'        => strtolower(self::TITLE),
			'page_chapter'    => $fields ? $this->chapter : 0,
			'page_text'       => 'Text',
			'page_fields'     => $fields ? e107::serialize($fields, 'json') : '',
			'menu_name'       => self::TITLE,
			'menu_button_url' => $address,
		));

		self::assertGreaterThan(0, $id, 'the page was not seeded');

		return $id;
	}

	/**
	 * @return array the pages this test saved, by title
	 */
	private function storedPages()
	{
		$pages = array();

		foreach(e107::getDb()->createQueryBuilder()->select('*')->from('page')->whereLike('page_title', self::TITLE.'%')->fetchAll() as $row)
		{
			$pages[$row['page_title']] = $row;
		}

		return $pages;
	}
}
