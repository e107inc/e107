<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An inverted userclass ("all but VIP") is stored as a negative class id, which
 * no IN() list of the visitor's classes can ever match. The fetch therefore
 * hands every menu row to {@see e_menu::isVisible()}, which is where the class
 * is decided. See issue #6279.
 */
class menu_classTest extends \Test\Unit
{
	const AREA = 97;
	const FIXTURE_PATH = 'menu_classTest/';

	/** @var e_menu */
	protected $menu;

	/** @var int userclass the runtime user is not a member of */
	protected $absentClass = 42;

	protected function _before()
	{
		require_once(e_HANDLER.'menu_class.php');

		if(!defined('THEME_LAYOUT'))
		{
			define('THEME_LAYOUT', (string) e107::getPref('sitetheme_deflayout'));
		}

		$this->menu = new e_menu();
		$this->removeFixtureMenus();
	}

	protected function _after()
	{
		$this->removeFixtureMenus();
	}

	public function testFetchKeepsMenusOfEveryUserclass()
	{
		$this->assertNotContains(
			(string) $this->absentClass,
			explode(',', USERCLASS_LIST),
			'This test needs a userclass the runtime user is outside of.'
		);

		$this->addFixtureMenu('inverted_menu', '-'.$this->absentClass);
		$this->addFixtureMenu('restricted_menu', (string) $this->absentClass);

		$names = array();

		foreach($this->fetchArea() as $row)
		{
			$names[] = $row['menu_name'];
		}

		$this->assertContains('inverted_menu', $names, 'A menu excluded from one class must survive the fetch, or it is invisible to everybody.');
		$this->assertContains('restricted_menu', $names, 'Restricted menus are dropped by isVisible(), not by the query.');
	}

	public function testIsVisibleHonoursTheMenuClass()
	{
		$this->assertFalse($this->isVisible(1, (string) $this->absentClass), 'A menu limited to a class the user is outside of stays hidden.');
		$this->assertTrue($this->isVisible(2, '-'.$this->absentClass), 'A menu hidden from one class is shown to everybody else.');
		$this->assertTrue($this->isVisible(3, '0'), 'Class 0 is public.');
		$this->assertTrue($this->isVisible(4, 0), 'A numeric class reads the same as its string form.');
		$this->assertTrue($this->isVisible(5, ''), 'A blank class is no restriction, which is how the Menu Manager list reads it.');
	}

	public function testInitDropsMenusTheVisitorMayNotSee()
	{
		$this->addFixtureMenu('inverted_menu', '-'.$this->absentClass);
		$this->addFixtureMenu('restricted_menu', (string) $this->absentClass);
		$this->addFixtureMenu('public_menu', '0');

		$names = array();

		foreach($this->activeArea() as $row)
		{
			$names[] = $row['menu_name'];
		}

		$this->assertContains('public_menu', $names, 'A public menu is active.');
		$this->assertContains('inverted_menu', $names, 'A menu hidden from one class reaches everybody else. #6279');
		$this->assertNotContains('restricted_menu', $names, 'The fetch no longer filters by class, so init() is the only thing keeping a restricted menu off the page.');
	}

	/**
	 * @param int $id distinct per row: {@see e_menu::isVisible()} caches by menu_id
	 * @param string|int $class
	 * @return bool
	 */
	private function isVisible($id, $class)
	{
		return $this->visible(array('menu_id' => $id, 'menu_class' => $class, 'menu_pages' => ''));
	}

	private function visible($row)
	{
		$method = new ReflectionMethod('e_menu', 'isVisible');
		$method->setAccessible(true);

		return $method->invoke($this->menu, $row);
	}

	/**
	 * @return array the fixture area's rows that {@see e_menu::init()} made active
	 */
	private function activeArea()
	{
		global $_E107;

		$cli = $_E107['cli'];
		$request = varset($_SERVER['REQUEST_URI']);

		$_E107['cli'] = false;
		$_SERVER['REQUEST_URI'] = '/';
		e107::getCache()->clear_sys('menus_');

		try
		{
			$this->menu->init();
		}
		finally
		{
			$_E107['cli'] = $cli;
			$_SERVER['REQUEST_URI'] = $request;
			e107::getCache()->clear_sys('menus_');
		}

		return isset($this->menu->eMenuActive[self::AREA]) ? $this->menu->eMenuActive[self::AREA] : array();
	}

	/**
	 * @return array the fixture area's rows as {@see e_menu::init()} receives them
	 */
	private function fetchArea()
	{
		$method = new ReflectionMethod('e_menu', 'getDataLegacy');
		$method->setAccessible(true);

		e107::getCache()->clear_sys('menus_');
		$data = $method->invoke($this->menu);
		e107::getCache()->clear_sys('menus_');

		return isset($data[self::AREA]) ? $data[self::AREA] : array();
	}

	private function addFixtureMenu($name, $class)
	{
		e107::getDb()->createQueryBuilder()->insert('menus')->values(array(
			'menu_name'     => $name,
			'menu_location' => self::AREA,
			'menu_order'    => 1,
			'menu_class'    => $class,
			'menu_pages'    => '',
			'menu_path'     => self::FIXTURE_PATH,
			'menu_layout'   => $this->layoutField(),
			'menu_parms'    => '',
		))->execute();
	}

	private function removeFixtureMenus()
	{
		e107::getDb()->createQueryBuilder()->delete('menus')->where('menu_path', self::FIXTURE_PATH)->execute();
	}

	/**
	 * @return string the menu_layout value {@see e_menu::getDataLegacy()} queries for
	 */
	private function layoutField()
	{
		return THEME_LAYOUT != e107::getPref('sitetheme_deflayout') ? THEME_LAYOUT : '';
	}
}
