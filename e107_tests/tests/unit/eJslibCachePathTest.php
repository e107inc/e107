<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression test for issue #5629: e_jslib_cache_path() in
 * e107_web/js/e_jslib.php must work under both the legacy
 * $CACHE_DIRECTORY globals format and the v2.4+ array-return format
 * of e107_config.php. Under the array-return format, the include's
 * return value was previously discarded and the function silently
 * returned '' — disabling the JS cache on every page load.
 *
 * e_jslib_cache_path() uses a hard-coded include('../../e107_config.php'),
 * so we exercise it from a scratch directory laid out to look like
 *     <scratch>/e107_config.php
 *     <scratch>/e107_web/js/e_jslib.php (copy of the file under test)
 * and run a tiny PHP probe via proc_open() that requires the file and
 * calls the function. This avoids touching the real e107_config.php
 * in the project root.
 */

class eJslibCachePathTest extends \Codeception\Test\Unit
{
	/** @var string */
	private $scratchRoot;

	/** @var string */
	private $eJslibSource;

	protected function _before()
	{
		$this->eJslibSource = realpath(APP_PATH . '/e107_web/js/e_jslib.php');
		$this->assertNotFalse(
			$this->eJslibSource,
			'e107_web/js/e_jslib.php must exist under APP_PATH'
		);

		$this->scratchRoot = sys_get_temp_dir() . '/e107-5629-' . uniqid('', true);
		mkdir($this->scratchRoot . '/e107_web/js', 0755, true);
		copy($this->eJslibSource, $this->scratchRoot . '/e107_web/js/e_jslib.php');
	}

	protected function _after()
	{
		if (!empty($this->scratchRoot) && is_dir($this->scratchRoot))
		{
			$this->rrmdir($this->scratchRoot);
		}
	}

	public function testReturnsCachePathFromV24ArrayConfigWithExplicitCacheKey()
	{
		file_put_contents(
			$this->scratchRoot . '/e107_config.php',
			"<?php\nreturn [\n"
			. "    'database' => [],\n"
			. "    'paths' => [\n"
			. "        'CACHE_DIRECTORY' => 'e107_system/abc123/cache/',\n"
			. "    ],\n"
			. "    'other' => [],\n"
			. "];\n"
		);

		$result = $this->probeCachePath();

		$this->assertSame(
			'../e107_system/abc123/cache/content/',
			$result,
			'e_jslib_cache_path() must honor an explicit CACHE_DIRECTORY key '
			. 'in the array-return config format when present. See issue #5629.'
		);
	}

	public function testDerivesCachePathFromV24ArrayConfigLowercaseSystemKey()
	{
		// This is what install.php actually writes for a v2.4 install:
		// lowercase 'system' under paths, site_path under other, no explicit cache.
		file_put_contents(
			$this->scratchRoot . '/e107_config.php',
			"<?php\nreturn [\n"
			. "    'database' => [],\n"
			. "    'paths' => [\n"
			. "        'system' => 'e107_system/',\n"
			. "    ],\n"
			. "    'other' => ['site_path' => 'abc123'],\n"
			. "];\n"
		);

		$result = $this->probeCachePath();

		$this->assertSame(
			'../e107_system/abc123/cache/content/',
			$result,
			'e_jslib_cache_path() must derive cache path from paths.system '
			. '(or SYSTEM_DIRECTORY) plus other.site_path when no explicit '
			. 'cache entry is present. This is the realistic v2.4 install '
			. 'output. See issue #5629.'
		);
	}

	public function testReturnsCachePathFromLegacyGlobalsConfigWithExplicitCacheDir()
	{
		file_put_contents(
			$this->scratchRoot . '/e107_config.php',
			"<?php\n\$CACHE_DIRECTORY = 'e107_system/legacy/cache/';\n"
		);

		$result = $this->probeCachePath();

		$this->assertSame(
			'../e107_system/legacy/cache/content/',
			$result,
			'e_jslib_cache_path() must continue to honor the legacy '
			. '$CACHE_DIRECTORY global as a regression guard.'
		);
	}

	public function testDerivesCachePathFromLegacySystemDirectoryAndSitePath()
	{
		// install.php in legacy mode writes SYSTEM_DIRECTORY and an
		// $E107_CONFIG['site_path'], but no explicit CACHE_DIRECTORY.
		file_put_contents(
			$this->scratchRoot . '/e107_config.php',
			"<?php\n\$SYSTEM_DIRECTORY = 'e107_system/';\n"
			. "\$E107_CONFIG = ['site_path' => 'legacysite'];\n"
		);

		$result = $this->probeCachePath();

		$this->assertSame(
			'../e107_system/legacysite/cache/content/',
			$result,
			'e_jslib_cache_path() must derive the cache path from '
			. '$SYSTEM_DIRECTORY plus $E107_CONFIG[site_path] under legacy '
			. 'config, matching what e107::defaultDirs() produces at runtime.'
		);
	}

	public function testReturnsEmptyStringWhenConfigHasNoUsablePaths()
	{
		file_put_contents(
			$this->scratchRoot . '/e107_config.php',
			"<?php\nreturn [\n"
			. "    'database' => [],\n"
			. "    'paths'    => [],\n"
			. "    'other'    => [],\n"
			. "];\n"
		);

		$result = $this->probeCachePath();

		$this->assertSame(
			'',
			$result,
			'e_jslib_cache_path() must safely return an empty string when '
			. 'no path or system entry is derivable, without emitting PHP-8 '
			. 'undefined-variable warnings.'
		);
	}

	/**
	 * Spawn a php-cli subprocess that requires e_jslib.php from the
	 * scratch layout and prints the result of e_jslib_cache_path().
	 *
	 * e_jslib.php executes its top-level code (including require_once
	 * '../../class2.php') unconditionally, so we run it under a guard
	 * that defines e_NOCACHE before the require and short-circuits at
	 * the function-only stage. Simpler: include the file and rely on
	 * the fact that the function definitions come after the exit; we
	 * include into a probe that re-defines exit-equivalents.
	 *
	 * Cleanest approach used here: extract just the function we need
	 * via a probe that opcache_compile-style includes the file with
	 * the bootstrap short-circuited. We achieve that by requiring the
	 * file inside a function that the probe defines and never calls;
	 * the function declarations at the bottom of the file are picked
	 * up regardless.
	 *
	 * Actually simpler still: e_jslib_cache_path() is just a regular
	 * function. We can re-extract its text and require it directly,
	 * but that would defeat the regression-test purpose (we want to
	 * exercise the real bytes). Instead, the probe sets $_SERVER and
	 * uses a stream wrapper to neuter the require_once. The probe is
	 * generated below.
	 */
	private function probeCachePath()
	{
		$probe = <<<'PROBE'
<?php
// Pretend we are e107_web/js/e_jslib.php so the hard-coded
// include('../../e107_config.php') resolves under the scratch tree.
chdir(__DIR__ . '/e107_web/js');

// Suppress the bootstrap. e_jslib.php runs top-level code that
// requires ../../class2.php, which doesn't exist in our scratch
// layout. We snapshot the file, strip everything before the first
// "function " token, and eval just the function definitions.
$source = file_get_contents(__DIR__ . '/e107_web/js/e_jslib.php');
$marker = strpos($source, 'function e_jslib_cache_out');
if ($marker === false)
{
    fwrite(STDERR, "PROBE_ERROR: marker not found\n");
    exit(2);
}
// Replace everything before the first function with an open tag.
$functionsOnly = "<?php\n" . substr($source, $marker);
eval('?>' . $functionsOnly);

echo e_jslib_cache_path();
PROBE;

		$probePath = $this->scratchRoot . '/probe.php';
		file_put_contents($probePath, $probe);

		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		// -d error_reporting=E_ALL and display_errors=1 so PHP-8
		// undefined-variable warnings surface as test failures.
		$cmd = [
			PHP_BINARY,
			'-d', 'error_reporting=E_ALL',
			'-d', 'display_errors=stderr',
			'-d', 'log_errors=0',
			$probePath,
		];

		$process = proc_open($cmd, $descriptors, $pipes, $this->scratchRoot);
		$this->assertIsResource($process, 'proc_open() must return a resource');

		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exitCode = proc_close($process);

		$this->assertSame(
			0,
			$exitCode,
			"Probe subprocess exited with $exitCode.\nSTDOUT: $stdout\nSTDERR: $stderr"
		);
		$this->assertSame(
			'',
			$stderr,
			"Probe subprocess emitted PHP warnings/errors:\n$stderr"
		);

		return $stdout;
	}

	private function rrmdir($dir)
	{
		if (!is_dir($dir)) return;
		$entries = scandir($dir);
		foreach ($entries as $entry)
		{
			if ($entry === '.' || $entry === '..') continue;
			$path = $dir . '/' . $entry;
			if (is_dir($path) && !is_link($path))
			{
				$this->rrmdir($path);
			}
			else
			{
				@unlink($path);
			}
		}
		@rmdir($dir);
	}
}
