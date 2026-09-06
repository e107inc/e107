<?php

namespace Test;

/**
 * The base class every unit test in this suite extends.
 *
 * It exists so that cross-cell compatibility is inherited rather than opted
 * into. The matrix runs the same suite against two PHPUnit generations --
 * Codeception 5.x with PHPUnit 10+ on PHP 8.1 and later, Codeception 4.x with
 * PHPUnit 5.7 / 6.x on the PHP 5.6 and 7.0 cells -- and the assertion names
 * those generations agree on do not cover the whole suite. \Helper\PhpUnitCompat
 * bridges the gap, but a trait has to be named in every class that wants it,
 * and a test author has no way to notice the omission until a legacy cell
 * fails on a machine they are not looking at.
 *
 * Extending this class instead means a new test written the same way as its
 * neighbours is correct by default. unitTestConventionsTest fails the suite
 * if a test extends \Codeception\Test\Unit directly, so the omission is
 * reported on the first run rather than on the first legacy cell.
 *
 * Keep this class in PHP 5.6 syntax: it is part of the shipping-adjacent test
 * tree that the downgrade pipeline walks, and anything modern here would just
 * be rewritten by the next rector-downgrade run.
 */
class Unit extends \Codeception\Test\Unit
{
	use \Helper\PhpUnitCompat;

	/**
	 * Copies a fixture tree, e.g. a theme out of tests/_data into e_THEME.
	 *
	 * @param string $src
	 * @param string $dst
	 * @return bool false when there is nothing to copy or the destination is already there
	 */
	protected function copydir($src, $dst)
	{
		if(!is_dir($src) || is_dir($dst))
		{
			return false;
		}

		mkdir($dst);

		foreach(scandir($src) as $file)
		{
			if($file === '.' || $file === '..')
			{
				continue;
			}

			if(is_dir($src.DIRECTORY_SEPARATOR.$file))
			{
				$this->copydir($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
				continue;
			}

			copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
		}

		return true;
	}

	/**
	 * Runs $php in a subprocess that has booted class2.php in CLI mode.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param array $e107 what $_E107 holds when class2.php boots
	 * @param int $timeout seconds
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInBootedCli($php, $ini = '', $e107 = array('cli' => true), $timeout = 60)
	{
		$boot = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$boot .= "\$_E107 = ".var_export($e107, true)."; ";
		$boot .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";

		$output = array();
		$status = 0;
		exec(sprintf('timeout %d php %s -r %s 2>&1', $timeout, $ini, escapeshellarg($boot.$php)), $output, $status);

		self::assertNotSame(124, $status, 'the subprocess wedged, so nothing was measured');

		return array($output, $status);
	}
}
