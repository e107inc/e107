<?php

namespace Helper;

/**
 * Skips a test whose subject only some database engines have.
 */
trait DatabaseDriverRequirement
{
	/**
	 * Skips the test unless the site's database runs on one of $drivers, for a test of behaviour only those engines have.
	 *
	 * @param string|string[] $drivers e107 driver names, e.g. 'mysql'
	 * @param string $reason what the test exercises that other engines lack
	 * @return void
	 */
	public function requireDatabaseDriver($drivers, $reason)
	{
		$drivers = (array) $drivers;
		$current = $this->databaseDriverName();

		if(!in_array($current, $drivers, true))
		{
			$this->markTestSkipped('Needs the '.implode(' or ', $drivers).' database driver, not '.$current.': '.$reason);
		}
	}

	/**
	 * @return string the e107 driver name of the database the site runs on
	 */
	abstract protected function databaseDriverName();
}
