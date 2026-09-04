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
class workspaceCleanupTest extends \Test\Unit
{
	/** Fixture names an acceptance Cest declares that name no file of their own at the app root. */
	private static $notFilesAtTheAppRoot = array(
		'e107_tests_p84_menu',           // a menus table row
		'e107_tests_upload_csrf.txt',    // under e_IMPORT, inside the site-path directory the list sweeps whole
		'e107_tests_security_level.txt', // under e_IMPORT, inside the site-path directory the list sweeps whole
	);

	public function testEveryCestFixtureNameIsSwept()
	{
		$swept = $this->sweptNames();
		$missing = array();

		foreach($this->cestFixtureNames() as $name => $declaredBy)
		{
			if(!in_array($name, $swept, true) && !in_array($name, self::$notFilesAtTheAppRoot, true))
			{
				$missing[] = $name . ' (' . implode(', ', $declaredBy) . ')';
			}
		}

		self::assertSame(array(), $missing,
			'every docroot fixture an acceptance Cest names must be listed in '
			. 'Extension\\WorkspaceCleanup::$artifacts, or a run that dies leaves it behind');
	}

	/**
	 * @return string[] what Extension\WorkspaceCleanup removes, each as listed and by its own name
	 */
	private function sweptNames()
	{
		$artifacts = new ReflectionProperty('Extension\\WorkspaceCleanup', 'artifacts');
		$artifacts->setAccessible(true);
		$listed = $artifacts->getValue();

		return array_merge($listed, array_map('basename', $listed));
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
				if(is_string($value) && strpos($value, 'e107_tests') !== false)
				{
					$names[$value][] = $reflection->getShortName();
				}
			}
		}

		self::assertNotEmpty($names, 'no acceptance Cest was found to take fixture names from');

		return $names;
	}
}
