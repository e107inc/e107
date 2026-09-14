<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/** The classes core's markup names on an element it means to float right (#6425). */
class RightFloatConventionsTest extends \Codeception\Test\Unit
{
	/** Bootstrap 3 declares the first, 4 the second and 5 the third, each as float:right !important, and none of them reads another's. */
	private static $spellings = array('pull-right', 'float-right', 'float-end');

	const RULE = 'An element core floats right carries class="pull-right float-right float-end", all three, so that it still floats whichever bundled Bootstrap the theme loads. A list naming fewer is either a float the other frameworks lose or a float nothing wanted, and the fix is to complete the list or to drop the spelling.';

	/** @var array|null */
	private static $floats = null;

	public function testEveryRightFloatedElementNamesEveryFramework()
	{
		foreach($this->floats() as $where => $classes)
		{
			foreach(self::$spellings as $class)
			{
				$this->assertContains($class, $classes,
					$where.' floats an element right without naming '.$class.'. '.self::RULE);
			}
		}
	}

	/** The scan is the whole of the rule's reach, so one that reads nothing asserts nothing. */
	public function testTheScanFindsTheRightFloatsCoreShips()
	{
		$this->assertGreaterThan(20, count($this->floats()),
			'the scan found almost no right-floated element, so the assertion above holds nothing down');
	}

	/**
	 * Every class list in core's own PHP sources that names a right-float spelling; one composed at run time is invisible here and has to be found by eye.
	 *
	 * @return array 'path #ordinal within the file' => string[] the classes on that element
	 */
	private function floats()
	{
		if(self::$floats !== null)
		{
			return self::$floats;
		}

		self::$floats = array();

		foreach(\Test\Markup::classListsIn(\Test\Tree::corePhpFiles()) as $where => $classes)
		{
			if(array_intersect(self::$spellings, $classes))
			{
				self::$floats[$where] = $classes;
			}
		}

		return self::$floats;
	}
}
