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
	 * Settings of the one {@see \e107::getParser()} a unit process shares, which every test after this one inherits.
	 *
	 * @var string[]
	 */
	private static $parserSettings = array('staticUrl', 'modRewriteMedia', 'fontawesome', 'bootstrap', 'multibyte',
		'thumbWidth', 'thumbHeight', 'thumbCrop');

	/**
	 * Copies a fixture tree, e.g. a theme out of tests/_data into e_THEME, journaled so the run takes it back out.
	 *
	 * @param string $src
	 * @param string $dst absolute, inside APP_PATH
	 * @return bool false when there is nothing to copy or the destination is already there
	 */
	protected function copydir($src, $dst)
	{
		if(!is_dir($src) || is_dir($dst))
		{
			return false;
		}

		\Helper\AppFileRegistry::didWrite($this->appRelativePath($dst));
		self::copyTree($src, $dst);

		return true;
	}

	private static function copyTree($src, $dst)
	{
		mkdir($dst);

		foreach(scandir($src) as $file)
		{
			if($file === '.' || $file === '..')
			{
				continue;
			}

			if(is_dir($src.DIRECTORY_SEPARATOR.$file))
			{
				self::copyTree($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
				continue;
			}

			copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
		}
	}

	/**
	 * Writes $contents into the app through the deployer, journaled so the run takes it back out.
	 *
	 * @param string $path relative to the app root, or the absolute or ./ form e107's path constants give
	 * @param string $contents
	 * @return void
	 */
	protected function writeAppFile($path, $contents)
	{
		$this->getModule('\Helper\Unit')->writeAppFile($this->appPath($path), $contents);
	}

	/**
	 * Removes a file from the app through the deployer; one the run did not write is backed up first and comes back when the test ends.
	 *
	 * @param string $path relative to the app root, or the absolute or ./ form e107's path constants give
	 * @return void
	 */
	protected function deleteAppFile($path)
	{
		$this->getModule('\Helper\Unit')->deleteAppFile($this->appPath($path));
	}

	/**
	 * @param string $path relative to the app root, or the absolute or ./ form e107's path constants give
	 * @return string the same path relative to the app root
	 */
	private function appPath($path)
	{
		return strpos($path, '/') === 0 || strpos($path, './') === 0 ? $this->appRelativePath($path) : $path;
	}

	/**
	 * @param string $path under APP_PATH, absolute or relative to the working directory, which class2.php makes the app root; the last segment need not exist yet
	 * @return string the same path relative to the app root
	 */
	private function appRelativePath($path)
	{
		$root = realpath(APP_PATH);
		$parent = realpath(dirname(rtrim($path, '/')));

		if($root === false || $parent === false || strpos($parent.'/', $root.'/') !== 0)
		{
			self::fail("$path is not inside the app root ".APP_PATH);
		}

		return ltrim(substr($parent, strlen($root)).'/'.basename(rtrim($path, '/')), '/');
	}

	/**
	 * Runs $php in a subprocess that has booted class2.php, in CLI mode unless $e107 says otherwise.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param array $e107 what $_E107 holds when class2.php boots
	 * @param float $timeout seconds, to the millisecond
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInBootedCli($php, $ini = '', $e107 = array('cli' => true), $timeout = 60)
	{
		$boot = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$boot .= "\$_E107 = ".var_export($e107, true)."; ";
		$boot .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";

		return $this->runInCli($boot.$php, $ini, array(), $timeout);
	}

	/**
	 * Runs $php in a subprocess of the interpreter running the suite, booting nothing; {@see Unit::runInBootedCli()} boots e107 on top of this.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param array $env environment variables to export to the child, e.g. array('HTTP_HOST' => 'example.com')
	 * @param float $timeout seconds, to the millisecond
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInCli($php, $ini = '', $env = array(), $timeout = 60)
	{
		$exports = '';
		foreach($env as $name => $value)
		{
			if(!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name))
			{
				self::fail("'$name' is not a shell identifier, so the shell would run it as a command rather than export it");
			}

			$exports .= $name.'='.escapeshellarg($value).' ';
		}

		$duration = sprintf('%.3F', $timeout);
		if((float) $duration <= 0)
		{
			self::fail("a timeout of $timeout second(s) renders as $duration, and only a duration above zero bounds the child");
		}

		$output = array();
		$status = 0;
		exec(sprintf('%stimeout %s %s %s -r %s 2>&1',
			$exports, $duration, escapeshellarg(PHP_BINARY), $ini, escapeshellarg($php)), $output, $status);

		if($status === 124)
		{
			$head = array_slice($output, 0, 20);

			self::fail(sprintf("the subprocess wedged, so nothing was measured. The first %d of its %d line(s):\n%s",
				count($head), count($output), implode("\n", $head)));
		}

		return array($output, $status);
	}

	/**
	 * What the shared parser is configured with right now, to hand back to {@see Unit::restoreParserState()} afterwards.
	 *
	 * @return array
	 */
	protected function parserState()
	{
		$parser = \e107::getParser();
		$state = array();

		foreach(self::$parserSettings as $setting)
		{
			$state[$setting] = $this->parserProperty($setting)->getValue($parser);
		}

		return $state;
	}

	/**
	 * Puts back the settings {@see Unit::parserState()} found, and the map and round-robin position the replaced static URL derived.
	 *
	 * @param array $state from {@see Unit::parserState()}
	 * @return void
	 */
	protected function restoreParserState($state)
	{
		$parser = \e107::getParser();

		foreach($state as $setting => $value)
		{
			$this->parserProperty($setting)->setValue($parser, $value);
		}

		$parser->setStaticUrl($state['staticUrl']);
	}

	/**
	 * @param string $setting a property of {@see \e_parse}, which writes these settings through casting setters and reads most of them back nowhere
	 * @return \e107\Reflection\ReflectionProperty
	 */
	private function parserProperty($setting)
	{
		return new \e107\Reflection\ReflectionProperty('e_parse', $setting);
	}
}
