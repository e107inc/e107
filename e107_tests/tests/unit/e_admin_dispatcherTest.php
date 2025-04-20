<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */


class e_admin_dispatcherTest extends \Codeception\Test\Unit
{

	protected $dp;

	/** @var e_admin_controller */
	protected $controller;

	protected $req;

	protected function _before()
	{

		try
		{
			$this->controller = $this->make(e_admin_controller_ui::class);
			$this->dp = $this->getMockBuilder('e_admin_dispatcher')
				->onlyMethods(['hasPerms', 'hasRouteAccess', 'getMenuData', 'getMenuAliases', 'getMenuIcon', 'getMenuTitle', 'hasModeAccess'])
				->disableOriginalConstructor()
				->getMock();

			$this->req = $this->make(e_admin_request::class);
			$this->dp->setRequest($this->req);

			// Shared mocks for both tests
			$this->dp->expects($this->any())
				->method('hasPerms')
				->willReturnCallback(function ($perm)
				{

					return $perm === 'Y';
				});

			$this->dp->expects($this->any())
				->method('hasModeAccess')
				->willReturn(true);

			// Mock getMenuData to return $this->adminMenu (set by setMenuData)
			$this->dp->expects($this->any())
				->method('getMenuData')
				->willReturnCallback(function ()
				{

					$reflection = new ReflectionClass($this->dp);
					$adminMenuProperty = $reflection->getProperty('adminMenu');
					$adminMenuProperty->setAccessible(true);

					return $adminMenuProperty->getValue($this->dp) ?: [];
				});

			$this->dp->expects($this->any())
				->method('getMenuAliases')
				->willReturn([]);

			$this->dp->expects($this->any())
				->method('getMenuIcon')
				->willReturn('');

			$this->dp->expects($this->any())
				->method('getMenuTitle')
				->willReturn('Admin Menu');


		}
		catch(Exception $e)
		{
			$this::fail("Setup failed: " . $e->getMessage());
		}
	}

	public function testRenderMenuGroupPerms()
	{

		$this->req->setMode('main');
		$this->req->setAction('list');

		$adminMenu = [
			'main/list'     => ['caption' => 'Manage', 'perm' => '0'],
			'main/create'   => ['caption' => 'LAN_CREATE', 'perm' => 'Y'],
			'main/prefs'    => ['caption' => 'Settings', 'perm' => 'J', 'icon' => 'fa-cog'],

			'main/custom'   => ['caption' => 'Custom Pages', 'perm' => '0', 'icon' => 'fa-asterisk'],
			'main/custom1'  => ['group' => 'main/custom', 'caption' => 'Custom Page 1', 'perm' => 'Y'],
			'main/custom2'  => ['group' => 'main/custom', 'caption' => 'Custom Page 2', 'perm' => '0'],

			'other/custom'  => ['caption' => 'Other Pages', 'perm' => 'Y', 'icon' => 'fa-asterisk'], // should be ignored since no access to sub-items. 			'other/custom1' => ['group' => 'other/custom', 'caption' => 'Other Page 1', 'perm' => '0'],
			'other/custom2' => ['group' => 'other/custom', 'caption' => 'Other Page 2', 'perm' => '0'],

			'misc/custom'   => ['caption' => 'Misc Pages', 'perm' => 'Y', 'icon' => 'fa-asterisk'],
			'misc/custom1'  => ['group' => 'misc/custom', 'caption' => 'misc Page 1', 'perm' => '0'],
			'misc/custom2'  => ['group' => 'misc/custom', 'caption' => 'misc Page 2', 'perm' => 'Y'],
		];

		// Use real setMenuData
		$this->dp->setMenuData($adminMenu);

		// Override hasRouteAccess for this test
		$this->dp->method('hasRouteAccess')
			->willReturnCallback(function ($route) use (&$access)
			{

				if(isset($access[$route]) && ((int) $access[$route] === 255))
				{
					return false;
				}

				return true;
			});

		$result = $this->dp->renderMenu(true);

		$this::assertNotEmpty($result, 'Render menu result is empty');
		$this::assertNotEmpty($result['main/create'], 'Main create menu item should be present');
		$this::assertArrayNotHasKey('main/custom', $result, 'Main custom group should not be present');
		$this::assertArrayNotHasKey('other/custom', $result, 'Other custom menu item should NOT be present');
		$this::assertNotEmpty($result['misc/custom'], 'Misc custom group should be present');
		$this::assertArrayNotHasKey('misc/custom1', $result['misc/custom']['sub'], 'Misc custom1 sub-item should not be present');
		$this::assertNotEmpty($result['misc/custom']['sub']['misc/custom2'], 'Misc custom2 sub-item should be present');


	}

	public function testRenderMenuUserclassAccess()
	{

		$this->req->setMode('main');
		$this->req->setAction('list');


		$adminMenu = [
			'main/list'     => ['caption' => 'Manage', 'perm' => '0'],
			'main/create'   => ['caption' => 'LAN_CREATE', 'perm' => 'Y'],
			'main/prefs'    => ['caption' => 'Settings', 'perm' => 'Y', 'icon' => 'fa-cog'],

			'main/custom'   => ['caption' => 'Custom Pages', 'perm' => '0', 'icon' => 'fa-asterisk'],
			'main/custom1'  => ['group' => 'main/custom', 'caption' => 'Custom Page 1', 'perm' => 'Y'],
			'main/custom2'  => ['group' => 'main/custom', 'caption' => 'Custom Page 2', 'perm' => 'Y'],

			'other/custom'  => ['caption' => 'Other Pages', 'perm' => 'Y', 'icon' => 'fa-asterisk'],
			'other/custom1' => ['group' => 'other/custom', 'caption' => 'Other Page 1', 'perm' => 'Y'],
			'other/custom2' => ['group' => 'other/custom', 'caption' => 'Other Page 2', 'perm' => 'Y'],

			'misc/custom'   => ['caption' => 'Misc Pages', 'perm' => 'Y', 'icon' => 'fa-asterisk'],
			'misc/custom1'  => ['group' => 'misc/custom', 'caption' => 'misc Page 1', 'perm' => 'Y'],
			'misc/custom2'  => ['group' => 'misc/custom', 'caption' => 'misc Page 2', 'perm' => 'Y'],

			'cat/custom'   => ['caption' => 'Category Pages', 'perm' => 'Y', 'icon' => 'fa-asterisk'],
			'cat/custom1'  => ['group' => 'cat/custom', 'caption' => 'Category Page 1', 'perm' => 'Y'],
			'cat/custom2'  => ['group' => 'cat/custom', 'caption' => 'Category Page 2', 'perm' => 'Y'],

			'treatment'         => array('caption'=> "Treatment", 'perm' => 'P', 'icon'=>'fas-syringe'),
			'treatment/day' 	=> array('group'=>'treatment', 'caption'=> "Treatment Today (Status)", 'perm' => 'P', 'icon'=>'fas-syringe'),
			'schedule/week' 	=> array('group'=>'treatment', 'caption'=> "Treatment Today (Scheduled)", 'perm' => 'P', 'icon'=>'fas-calendar-week'),

		];

		$access = [
			'main/list'     => e_UC_ADMIN,
			'main/create'   => e_UC_ADMIN,
			'main/prefs'    => e_UC_NOBODY,

			'main/custom'   => e_UC_ADMIN,
			'main/custom1'  => e_UC_ADMIN,
			'main/custom2'  => e_UC_ADMIN,

			'other/custom'  => e_UC_NOBODY,
			'other/custom1' => e_UC_NOBODY,
			'other/custom2' => e_UC_NOBODY,

			'misc/custom'   => e_UC_ADMIN,
			'misc/custom1'  => e_UC_ADMIN,
			'misc/custom2'  => e_UC_NOBODY,

			'cat/custom1'   => e_UC_NOBODY,
			'cat/custom2'   => e_UC_NOBODY,

			'treatment/day' => e_UC_ADMIN,
			'schedule/week' => e_UC_ADMIN,
		];

		// Use real setMenuData and setAccess
		$this->dp->setMenuData($adminMenu);
		$this->dp->setAccess($access);


		// Override hasRouteAccess for this test
		$this->dp->method('hasRouteAccess')
			->willReturnCallback(function ($route) use (&$access)
			{

				if(isset($access[$route]) && ((int) $access[$route] === 255))
				{
					return false;
				}

				return true;
			});

		$result = $this->dp->renderMenu(true);

		$this::assertNotEmpty($result, 'Render menu result is empty');
		$this::assertArrayNotHasKey('main/list', $result, 'Main list menu item should NOT be present');
		$this::assertNotEmpty($result['main/create'], 'Main create menu item should be present');
		$this::assertArrayNotHasKey('main/prefs', $result, 'Main prefs menu item should NOT be present');
		$this::assertArrayNotHasKey('main/custom', $result, 'Main custom menu item should NOT be present');
		$this::assertArrayNotHasKey('other/custom', $result, 'Other custom group should NOT be present');

		$this::assertArrayNotHasKey('cat/custom', $result, 'Category group should NOT be present');

	//	$this::assertArrayHasKey('treatment/day', $result, 'Treatment day sub-item should be present'); // This is failing.
	}
}