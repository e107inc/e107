<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves hero slides through the hero plugin's admin area, installing the plugin for its table when the site lacks it.
 */
class hero_adminScriptAddressTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const TITLE = 'hero_adminScriptAddressTest';

	/** @var bool whether this test installed the plugin */
	private $installed = false;

	protected function _before()
	{
		if(!e107::getDb()->execute("SHOW TABLES LIKE '".MPREFIX."hero'"))
		{
			e107::getPlugin()->install('hero');
			$this->installed = true;
		}
	}

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('hero')->whereLike('hero_title', self::TITLE.'%')->execute();

		if($this->installed)
		{
			e107::getPlugin()->uninstall('hero', array('delete_tables' => true));
		}
	}

	public function testAButtonAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'javascript:void(0)');

		self::assertSame(array(), $this->storedSlides(), "the slide was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Button-2</strong> - ", $page, 'the refusal has to name the button');
	}

	public function testThePostScriptClassMaySaveAButtonAddressThatRunsScript()
	{
		$page = $this->create(e_UC_MAINADMIN, 'javascript:void(0)');

		self::assertCount(1, $this->storedSlides(), "the slide was refused:\n".$page);
	}

	public function testAButtonAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'https://e107.org/');

		self::assertCount(1, $this->storedSlides(), "the slide was refused:\n".$page);
	}

	public function testAButtonPostedAsTextIsNotUnserialized()
	{
		$slide = $this->slide(self::TITLE, 'https://e107.org/', '');
		$slide['hero_button1'] = 'a:1:{s:3:"url";O:15:"HeroButtonProbe":0:{}}';

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/hero/admin_config.php', 'mode=main&action=create',
			$slide + array('etrigger_submit' => 'create'), 'class HeroButtonProbe { public function __wakeup() { echo "@@HERO-BUTTON-WOKE"; } } ');

		self::assertStringNotContainsString('@@HERO-BUTTON-WOKE', $page);
	}

	/**
	 * The edit form is opened first, and what it shows is posted back under a new title.
	 */
	public function testAStoredButtonAddressThatRunsScriptPassesUnchanged()
	{
		$id = $this->seedSlide('javascript:void(0)');
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/hero/admin_config.php', 'mode=main&action=edit&id='.$id));

		self::assertSame('javascript:void(0)', $shown['hero_button1[url]'], 'the form has to show the stored address');

		$page = $this->update(e_UC_NOBODY, $id, $shown['hero_button1[url]'], self::TITLE.' renamed');
		$stored = $this->storedSlides();

		self::assertSame(array(self::TITLE.' renamed'), array_keys($stored), "the unchanged address was refused:\n".$page);
		self::assertSame('javascript:void(0)', e107::unserialize($stored[self::TITLE.' renamed']['hero_button1'])['url']);
	}

	public function testAnEditCannotGiveAButtonAnAddressThatRunsScript()
	{
		$id = $this->seedSlide('https://e107.org/');

		$page = $this->update(e_UC_NOBODY, $id, 'javascript:void(0)', self::TITLE.' renamed');

		self::assertSame(array(self::TITLE), array_keys($this->storedSlides()), "the address was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Button-1</strong> - ", $page, 'the refusal has to name the button');
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param string $address the second button's
	 * @return string the page
	 */
	private function create($scriptClass, $address)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_plugins/hero/admin_config.php', 'mode=main&action=create',
			$this->slide(self::TITLE, 'https://e107.org/', $address) + array('etrigger_submit' => 'create'));
	}

	/**
	 * @param int $scriptClass
	 * @param int $id
	 * @param string $address the first button's
	 * @param string $title
	 * @return string the page
	 */
	private function update($scriptClass, $id, $address, $title)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_plugins/hero/admin_config.php', 'mode=main&action=edit&id='.$id,
			$this->slide($title, $address, '') + array('etrigger_submit' => 'update'));
	}

	/**
	 * @param string $title
	 * @param string $first the first button's address
	 * @param string $second the second button's address
	 * @return array the form a slide posts
	 */
	private function slide($title, $first, $second)
	{
		return array(
			'hero_title'       => $title,
			'hero_description' => '',
			'hero_bullets'     => array(array('icon' => '', 'icon_style' => '', 'text' => '', 'animation' => '', 'animation_delay' => '')),
			'hero_button1'     => array('icon' => '', 'label' => 'One', 'url' => $first, 'class' => 'primary'),
			'hero_button2'     => array('icon' => '', 'label' => 'Two', 'url' => $second, 'class' => 'primary'),
			'hero_order'       => 1,
			'hero_class'       => e_UC_PUBLIC,
		);
	}

	/**
	 * @param string $address the first button's
	 * @return int
	 */
	private function seedSlide($address)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('hero')->insertGetId(array(
			'hero_title'       => self::TITLE,
			'hero_description' => '',
			'hero_bg'          => '',
			'hero_media'       => '',
			'hero_bullets'     => e107::serialize(array(), 'json'),
			'hero_button1'     => e107::serialize(array('icon' => '', 'label' => 'One', 'url' => $address, 'class' => 'primary'), 'json'),
			'hero_button2'     => e107::serialize(array('icon' => '', 'label' => '', 'url' => '', 'class' => ''), 'json'),
			'hero_order'       => 1,
			'hero_class'       => e_UC_PUBLIC,
		));

		self::assertGreaterThan(0, $id, 'the slide was not seeded');

		return $id;
	}

	/**
	 * @return array the slides this test saved, by title
	 */
	private function storedSlides()
	{
		$slides = array();

		foreach(e107::getDb()->createQueryBuilder()->select('*')->from('hero')->whereLike('hero_title', self::TITLE.'%')->fetchAll() as $row)
		{
			$slides[$row['hero_title']] = $row;
		}

		return $slides;
	}
}
