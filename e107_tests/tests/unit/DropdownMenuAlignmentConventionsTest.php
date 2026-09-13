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
class DropdownMenuAlignmentConventionsTest extends \Test\Unit
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

		foreach(\Test\Tree::appPhpFiles() as $path)
		{
			$found = 0;

			foreach($this->classLists($path) as $classes)
			{
				if(!in_array('dropdown-menu', $classes, true) || !array_intersect(self::$spellings, $classes))
				{
					continue;
				}

				$found++;
				self::$menus[substr($path, strlen(e_ROOT)).' #'.$found] = $classes;
			}
		}

		return self::$menus;
	}

	/**
	 * The classes of every class attribute one file writes, an attribute a concatenation cuts short counted for the classes it names before the cut.
	 *
	 * @param string $path
	 * @return array[] one array of class names per attribute, in source order
	 */
	private function classLists($path)
	{
		$markup = array(T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE);
		$lists = array();

		foreach(token_get_all(file_get_contents($path)) as $token)
		{
			if(!is_array($token) || !in_array($token[0], $markup, true))
			{
				continue;
			}

			$text = $token[0] === T_CONSTANT_ENCAPSED_STRING ? $this->literal($token[1]) : $token[1];

			if(!preg_match_all('/\bclass\s*=\s*(["\'])([^"\']*)(?:\1|$)/', $text, $matches))
			{
				continue;
			}

			foreach($matches[2] as $list)
			{
				$lists[] = preg_split('/\s+/', trim($list), -1, PREG_SPLIT_NO_EMPTY);
			}
		}

		return $lists;
	}

	/**
	 * @param string $token the source text of one quoted string, its quotes included
	 * @return string what PHP would hold in memory for it
	 */
	private function literal($token)
	{
		$quote = substr($token, 0, 1);
		$escaped = $quote === '"' ? array('\\"', '\\\\') : array("\\'", '\\\\');

		return str_replace($escaped, array($quote, '\\'), (string) substr($token, 1, -1));
	}
}
