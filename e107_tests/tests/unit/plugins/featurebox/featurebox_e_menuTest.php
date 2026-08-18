<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers the Menu Manager configuration form in {@see featurebox_menu::config()}.
 */
class featurebox_e_menuTest extends \Test\Unit
{
	public function _before()
	{
		require_once(e_PLUGIN . 'featurebox/e_menu.php');
	}

	/**
	 * An empty field set costs the placement its whole parameters screen, because
	 * Menu Manager stops offering the free-text box the moment e_menu.php exists.
	 */
	public function testTheFormIsOfferedWhetherOrNotThePluginIsInstalled()
	{
		$menu = new featurebox_menu();
		$fields = $menu->config('featurebox');

		$this->assertArrayHasKey('category', $fields);
		$this->assertArrayHasKey('notablestyle', $fields);
		$this->assertTrue(in_array($fields['category']['type'], array('dropdown', 'text'), true));
		$this->assertSame('dropdown', $fields['notablestyle']['type']);
	}

	public function testTheCategoryDropdownNeverOffersTheSystemCategory()
	{
		$menu = new featurebox_menu();
		$fields = $menu->config('featurebox');

		if($fields['category']['type'] !== 'dropdown')
		{
			$this->markTestSkipped('featurebox is not installed in this environment.');
		}

		$this->assertArrayNotHasKey('unassigned', $fields['category']['writeParms']['optArray']);
	}
}
