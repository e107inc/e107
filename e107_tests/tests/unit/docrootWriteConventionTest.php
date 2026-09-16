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
 * Every write into the app root goes through a deployer helper, so it lands where the app is served from and {@see \Helper\AppFileRegistry} takes it back out.
 */
class docrootWriteConventionTest extends \Codeception\Test\Unit
{
	private static $suites = array('tests/acceptance', 'tests/unit');

	/**
	 * APP_PATH and every constant e107 defines for a directory git tracks. e107_system and e107_media are the
	 * installer's output, which E107Preparer rolls back at the suite's edges, and the _ABS twins are URLs.
	 */
	private static $roots = array(
		'APP_PATH', 'e_ROOT', 'e_BASE', 'e_DOCROOT',
		'e_ADMIN', 'e_CORE', 'e_DOCS', 'e_FILE', 'e_HANDLER', 'e_HELP', 'e_IMAGE', 'e_LANGUAGEDIR',
		'e_PLUGIN', 'e_PLUGIN_DIR', 'e_THEME', 'e_WEB', 'e_WEB_CSS', 'e_WEB_IMAGE', 'e_WEB_JS',
	);

	/** Anything that could put a file in the docroot, or take one out. */
	private static $writers = array(
		'file_put_contents', 'fopen', 'copy', 'rename', 'mkdir', 'touch',
		'symlink', 'link', 'unlink', 'rmdir',
	);

	public function testNoTestWritesIntoTheDocrootDirectly()
	{
		$offenders = array();

		foreach(self::$suites as $suite)
		{
			foreach(\Test\Tree::phpFiles($suite) as $file)
			{
				foreach(self::docrootWrites(file_get_contents($file)) as $line => $call)
				{
					$offenders[] = \Test\Tree::relativePath($file).':'.$line.' '.$call.'()';
				}
			}
		}

		sort($offenders);

		self::assertSame(array(), $offenders,
			"These tests reach the tracked app tree with a filesystem call of their own.\n"
			. "Use writeAppFile(), deleteAppFile() or removeAppPath() instead: the\n"
			. "deployer puts the fixture wherever the app is served from, and\n"
			. "\\Helper\\AppFileRegistry takes it back out when the test ends."
		);
	}

	public function testTheScanReadsCodeAndNotStringsOrComments()
	{
		$source = <<<'PHP'
<?php
class Sample
{
	public function host($I)
	{
		file_put_contents(APP_PATH.'/a.php', '');
		\copy(__FILE__, APP_PATH.'/b.php');
		rename(e_IMAGE.'c.png', e_THEME.'c.png');
		$I->copy(APP_PATH.'/d.php');
		$this->mkdir(APP_PATH);
		$text = 'unlink(APP_PATH)';
		// rmdir(APP_PATH.'/e');
		/* touch(APP_PATH.'/f') */
		file_put_contents("{$this->dir}/g.php", APP_PATH);
		file_put_contents(e_CACHE.'h.php', e_VERSION);
		return <<<PROBE
<?php
rename(APP_PATH.'/i', APP_PATH.'/j');
PROBE;
	}
}
PHP;

		self::assertSame(array(6 => 'file_put_contents', 7 => 'copy', 8 => 'rename', 14 => 'file_put_contents'), self::docrootWrites($source));
	}

	/**
	 * @param string $source
	 * @return array line number to function name, one per statement that names a root and calls a writer
	 */
	private static function docrootWrites($source)
	{
		$found = array();
		$tokens = token_get_all($source);
		$count = count($tokens);
		$startedAt = null;
		$namesRoot = false;
		$writer = null;
		$interpolating = 0;

		for($i = 0; $i < $count; $i++)
		{
			$token = $tokens[$i];

			if(is_array($token))
			{
				if(in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
				{
					continue;
				}

				if($startedAt === null)
				{
					$startedAt = $token[2];
				}

				if($token[0] === T_CURLY_OPEN || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES)
				{
					$interpolating++;
				}
				elseif($token[0] === T_STRING && in_array($token[1], self::$roots, true))
				{
					$namesRoot = true;
				}
				elseif($writer === null && \Test\Tree::isFunctionCall($tokens, $i, self::$writers))
				{
					$writer = ltrim($token[1], '\\');
				}

				continue;
			}

			if($token === '}' && $interpolating > 0)
			{
				$interpolating--;
				continue;
			}

			if($token !== ';' && $token !== '{' && $token !== '}')
			{
				continue;
			}

			if($namesRoot && $writer !== null)
			{
				$found[$startedAt] = $writer;
			}

			$startedAt = null;
			$namesRoot = false;
			$writer = null;
		}

		return $found;
	}

}
