<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/** The classes core's markup names on a dropdown menu it means to right-align (#6328). */
class DropdownMenuAlignmentConventionsTest extends \Codeception\Test\Unit
{
	/** Bootstrap 3 and 4 align a menu by the first, Bootstrap 5 by the second, and no bundled version reads the other's. */
	private static $required = array('dropdown-menu-right', 'dropdown-menu-end');

	/** Every spelling that says a menu is meant to open rightwards, the two that never aligned one included, so a menu left on any of them is caught rather than skipped. */
	private static $spellings = array('pull-right', 'float-right', 'float-end', 'dropdown-menu-right', 'dropdown-menu-end');

	const RULE = 'A dropdown menu core right-aligns carries class="dropdown-menu dropdown-menu-right dropdown-menu-end". pull-right is not part of it: Bootstrap 4 and 5 do not define it, and on Bootstrap 3 it adds float:right !important, which a collapsed navbar cannot undo.';

	/** @var array|null */
	private static $menus = null;

	public function testEveryRightAlignedDropdownMenuNamesEveryFramework()
	{
		foreach($this->menus() as $where => $classes)
		{
			foreach(self::$required as $class)
			{
				$this->assertContains($class, $classes,
					$where.' right-aligns a dropdown menu without naming '.$class.'. '.self::RULE);
			}
		}
	}

	/** The scan is the whole of the rule's reach, so one that reads nothing asserts nothing. */
	public function testTheScanFindsTheRightAlignedMenusCoreShips()
	{
		$this->assertGreaterThan(5, count($this->menus()),
			'the scan found almost no right-aligned dropdown menu, so the assertion above holds nothing down');
	}

	/**
	 * Every class list in core's PHP sources that puts an alignment class on a dropdown menu; one right-aligned by its container alone is invisible here and has to be found by eye.
	 *
	 * @return array 'path #ordinal within the file' => string[] the classes on that element
	 */
	private function menus()
	{
		if(self::$menus !== null)
		{
			return self::$menus;
		}

		self::$menus = array();

		foreach(\Test\Markup::classListsIn(\Test\Tree::appPhpFiles()) as $where => $classes)
		{
			if(in_array('dropdown-menu', $classes, true) && array_intersect(self::$spellings, $classes))
			{
				self::$menus[$where] = $classes;
			}
		}

		return self::$menus;
	}
}
