<?php

use PHPUnit\Framework\TestCase;

/**
 * The confirm-your-identity guard on e107's sensitive admin forms is written
 * three times over as
 *
 *     if (!$_POST['ac'] == md5(ADMINPWCHANGE))
 *
 * which PHP parses as ((!$_POST['ac']) == md5(ADMINPWCHANGE)): a boolean
 * compared with a 32 character string, false for every non-empty value of ac.
 * The behaviour of each of the three call sites is asserted end to end in
 * AdminConfirmTokenCest. This is the sweep that says there are three of them
 * and not four, and it is the only assertion here that would notice a fourth
 * being written tomorrow.
 *
 * The shape, not the variable: `!$x == $y` is a negation compared against
 * something, which is never what the author meant and which the parser will
 * never complain about.
 *
 * @see e107_admin/users.php  users_admin_ui::AddSubmitTrigger()
 * @see e107_admin/plugin.php  plugin_ui::pluginProcessUpload()
 * @see e107_handlers/theme_handler.php  themeHandler::themeUpload()
 */
class AdminConfirmTokenTest extends TestCase
{
	/**
	 * A negation on the left of a comparison. The right side is not
	 * constrained: whatever it is, the left side has already collapsed to a
	 * boolean and the comparison no longer says what it reads as.
	 *
	 * Covers a bare variable, array subscripts, property access, static
	 * property access and a method call, with or without a space after the
	 * negation: !$x, !$x['k'], !$this->prop, !$obj->prop['k'], !$obj->call()
	 * and !$x::$prop. It does not cover a negated function call with no
	 * receiver (!func() == ...) or a comparison split over two lines.
	 */
	const NEGATED_COMPARISON =
		'/!\s*\$[A-Za-z_][A-Za-z0-9_]*(?:\[[^\]]*\]|(?:->|::)\$?[A-Za-z_][A-Za-z0-9_]*(?:\([^()]*\))?)*\s*===?\s/';

	/**
	 * Lines the sweep matches that are not admin guards.
	 *
	 * e107_handlers/shortcode_handler.php:1413 reads
	 * "if (!$this->nowrap == $code)" and is the same defect in a different
	 * place: with $this->nowrap holding some other shortcode's name the
	 * comparison answers false where the author meant true, so a wrapper is
	 * dropped. It is a rendering defect and not an authorisation one, and
	 * correcting it changes rendered HTML, so it is recorded here rather than
	 * fixed inside a security change.
	 */
	private static $knownExceptions = array(
		'e107_handlers/shortcode_handler.php:1413',
	);

	/**
	 * Application source. e107_tests is excluded because a test that quotes the
	 * defect in a docblock is not the defect, and vendor because it is not ours
	 * to fix.
	 */
	private static $roots = array(
		'e107_admin',
		'e107_core',
		'e107_handlers',
		'e107_plugins',
		'e107_themes',
	);

	public function testNoAdminGuardComparesAgainstANegation()
	{
		$hits = array();

		foreach($this->sweep() as $location => $line)
		{
			if(!in_array($location, self::$knownExceptions, true))
			{
				$hits[] = $location.': '.$line;
			}
		}

		$this->assertSame(array(), $hits,
			"A comparison against a negation always answers something other than what it reads as. "
			."Found:\n  ".implode("\n  ", $hits));
	}

	/**
	 * The exception list is only honest while every entry on it still names a
	 * line the sweep would otherwise report. An entry that has been fixed, or
	 * moved, has to leave the list rather than quietly excuse a future hit at
	 * the same coordinates.
	 */
	public function testEveryKnownExceptionIsStillThere()
	{
		$found = $this->sweep();

		foreach(self::$knownExceptions as $location)
		{
			$this->assertArrayHasKey($location, $found,
				'The sweep no longer matches '.$location.', so that entry must come off '
				.'AdminConfirmTokenTest::$knownExceptions.');
		}
	}

	/**
	 * @return array location => trimmed source line
	 */
	private function sweep()
	{
		$hits = array();

		foreach($this->sourceFiles() as $path)
		{
			$lines = file($path);

			foreach($lines as $number => $line)
			{
				if(preg_match(self::NEGATED_COMPARISON, $line))
				{
					$relative = substr($path, strlen(APP_PATH) + 1);
					$hits[$relative.':'.($number + 1)] = trim($line);
				}
			}
		}

		return $hits;
	}

	/**
	 * The sweep is worth nothing if it is looking at an empty file list, which
	 * is exactly what a renamed directory or a wrong APP_PATH would give it.
	 */
	public function testTheSweepActuallyReadsTheApplicationSource()
	{
		$files = $this->sourceFiles();

		$this->assertGreaterThan(500, count($files),
			'The source sweep found '.count($files).' PHP files under '.APP_PATH
			.', which is too few for this application. It is not scanning what it thinks it is.');

		$this->assertContains(APP_PATH.'/e107_handlers/theme_handler.php', $files,
			'The source sweep did not reach e107_handlers/theme_handler.php.');
	}

	/**
	 * @return string[] absolute paths
	 */
	private function sourceFiles()
	{
		$files = array();

		foreach(glob(APP_PATH.'/*.php') as $path)
		{
			$files[] = $path;
		}

		foreach(self::$roots as $root)
		{
			$dir = APP_PATH.'/'.$root;

			if(!is_dir($dir))
			{
				continue;
			}

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

			foreach($iterator as $file)
			{
				if($file->isFile() && strtolower($file->getExtension()) === 'php')
				{
					$files[] = $file->getPathname();
				}
			}
		}

		return $files;
	}
}
