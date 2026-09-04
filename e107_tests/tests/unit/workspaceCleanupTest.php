<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2024 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers the list of names Extension\WorkspaceCleanup sweeps out of the app
 * root. Every docroot fixture an acceptance Cest names as a class constant has
 * to be on it: a Cest killed before its own cleanup otherwise leaves the file
 * in the worktree, where the next run's plugin and theme scans find it.
 */
class workspaceCleanupTest extends \Codeception\Test\Unit
{
	/** Values carrying the suite's prefix that name no file at the app root, as value to why and to the name the sweep takes in its place, where it takes one. */
	private static $notAppRootFixtures = [
		'e107_tests_p84_menu'           => ['a menu name in the database', ''],
		'e107_tests_p84_themecopy'      => ['a theme directory', 'e107_themes/e107_tests_p84_themecopy'],
		'e107_tests_upload_csrf.txt'    => ['written into e_IMPORT, inside the e107_system hash the sweep takes whole', ''],
		'e107_tests_security_level.txt' => ['written into e_IMPORT, inside the e107_system hash the sweep takes whole', ''],
	];

	public function testEveryCestFixtureNameIsSwept()
	{
		$swept = $this->sweptNames();
		$missing = array();

		foreach($this->cestFixtureNames() as $name => $declaredBy)
		{
			if(!in_array($name, $swept, true))
			{
				$missing[] = $name . ' (' . implode(', ', $declaredBy) . ')';
			}
		}

		self::assertSame(array(), $missing,
			'every docroot fixture an acceptance Cest names must be listed in '
			. 'Extension\\WorkspaceCleanup::$artifacts, or a run that dies leaves it behind');
	}

	public function testEveryNameSweptInPlaceOfAFixtureIsOnTheList()
	{
		$swept = $this->sweptNames();
		$missing = array();

		foreach(self::$notAppRootFixtures as $name => $exemption)
		{
			if($exemption[1] !== '' && !in_array($exemption[1], $swept, true))
			{
				$missing[] = $exemption[1] . ' (in place of ' . $name . ')';
			}
		}

		self::assertSame(array(), $missing,
			'a fixture exempted because the sweep takes it under another name must have that '
			. 'name in Extension\\WorkspaceCleanup::$artifacts, or nothing sweeps it at all');
	}

	/**
	 * @return string[] what Extension\WorkspaceCleanup removes from the app root
	 */
	private function sweptNames()
	{
		$artifacts = new ReflectionProperty('Extension\\WorkspaceCleanup', 'artifacts');
		$artifacts->setAccessible(true);

		return $artifacts->getValue();
	}

	/**
	 * @return array docroot-relative fixture name, recognised by the suite's own
	 *               prefix wherever it falls in the name, to the Cests that name it
	 */
	private function cestFixtureNames()
	{
		foreach(glob(codecept_root_dir() . 'tests/acceptance/*Cest.php') as $file)
		{
			require_once $file;
		}

		$names = array();

		foreach(get_declared_classes() as $class)
		{
			$reflection = new ReflectionClass($class);
			$file = (string) $reflection->getFileName();

			if(strpos($file, '/acceptance/') === false)
			{
				continue;
			}

			foreach($reflection->getConstants() as $value)
			{
				if(is_string($value) && strpos($value, 'e107_tests') !== false
					&& !isset(self::$notAppRootFixtures[$value]))
				{
					$names[$value][] = $reflection->getShortName();
				}
			}
		}

		self::assertNotEmpty($names, 'no acceptance Cest was found to take fixture names from');

		return $names;
	}
}
