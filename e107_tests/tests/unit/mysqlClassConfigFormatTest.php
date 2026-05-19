<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression test for issue #5630:
 *
 * mysql_class.php has two top-level branches gated by MYSQL_LIGHT and
 * E107_INSTALL. Both used to assume the *legacy* e107_config.php format
 * which sets $mySQL* file-scope globals as a side effect of inclusion.
 *
 * The v2.4+ e107_config.php instead `return`s an array
 *     ['database' => ['server'=>..., 'user'=>..., 'password'=>...,
 *                     'db'=>..., 'prefix'=>...], 'paths'=>[...], 'other'=>[...]]
 * so the legacy globals never come into scope. Before the fix, the
 * MYSQL_LIGHT branch emitted `Undefined variable $mySQLprefix` and defined
 * MPREFIX as null; the E107_INSTALL branch did the same and then handed
 * a compact() of all-null values to e107::initInstallSql() / db_Connect.
 *
 * Because mysql_class.php executes config-loading logic at file scope,
 * the only way to exercise it cleanly per branch is to spawn a php
 * subprocess that defines MYSQL_LIGHT or E107_INSTALL and then includes
 * the handler against an array-format config.
 */
class mysqlClassConfigFormatTest extends \Codeception\Test\Unit
{
	/** @var string */
	private $tmpDir;

	/** @var string Absolute path to the repository root (containing class2.php). */
	private $repoRoot;

	protected function _before()
	{
		$this->repoRoot = realpath(__DIR__ . '/../../..');
		$this->assertNotFalse($this->repoRoot, 'Could not locate repo root from test file');
		$this->assertFileExists($this->repoRoot . '/e107_handlers/mysql_class.php');

		$this->tmpDir = sys_get_temp_dir() . '/e107-5630-' . uniqid('', true);
		$this->assertTrue(mkdir($this->tmpDir, 0777, true));
	}

	protected function _after()
	{
		if ($this->tmpDir && is_dir($this->tmpDir))
		{
			$this->rrmdir($this->tmpDir);
		}
	}

	/**
	 * MYSQL_LIGHT branch with v2.4 array-format e107_config.php must not
	 * emit "Undefined variable" warnings and must set MPREFIX from the
	 * array config rather than to null.
	 */
	public function testMysqlLightAcceptsArrayFormatConfig()
	{
		$this->writeArrayConfig($this->tmpDir);

		// Probe: stub `class db` (the alias loaded by mysql_class.php) so
		// we don't need a real MySQL server, then run the file-scope
		// branch and inspect what it captured. mysql_class.php declares
		// `class e_db_mysql implements e_db` so we must preload the
		// interface before the include.
		$probe = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/e107_handlers/e_db_interface.php';

// Stub the `db` alias that mysql_class.php uses ("new db") so we can
// observe db_Connect() arguments without a real connection. The stub
// must precede the require so the `if(!class_exists('db'))` guard at
// the foot of mysql_class.php does not re-declare it.
class db {
	public static $args = null;
	public function db_Connect($s, $u, $p, $d, $newLink = false, $prefix = null) {
		self::$args = compact('s', 'u', 'p', 'd');
		return true;
	}
}

define('MYSQL_LIGHT', true);
require __DIR__ . '/e107_handlers/mysql_class.php';

echo "MPREFIX=" . (defined('MPREFIX') ? var_export(MPREFIX, true) : 'UNDEFINED') . "\n";
echo "ARGS=" . json_encode(db::$args) . "\n";
PHP;

		// Lay out a minimal docroot inside tmpDir so the require path
		// `e107_handlers/mysql_class.php` resolves to the worktree copy.
		symlink(
			$this->repoRoot . '/e107_handlers',
			$this->tmpDir . '/e107_handlers'
		);
		file_put_contents($this->tmpDir . '/probe.php', $probe);

		$result = $this->runPhp($this->tmpDir, 'probe.php');

		$this->assertStringNotContainsString(
			'Undefined variable',
			$result['stderr'] . "\n" . $result['stdout'],
			"mysql_class.php should not emit 'Undefined variable' warnings under array-format config (issue #5630)"
		);
		$this->assertStringNotContainsString(
			'Undefined variable',
			$result['stdout'],
			"No 'Undefined variable' notices should appear in stdout either"
		);
		$this->assertStringContainsString(
			"MPREFIX='e107_'",
			$result['stdout'],
			"MPREFIX should be populated from \$config['database']['prefix'], not null. Output was:\n" . $result['stdout'] . "\n--- stderr ---\n" . $result['stderr']
		);

		// Verify db_Connect received populated args from the array config.
		preg_match('/^ARGS=(.*)$/m', $result['stdout'], $m);
		$this->assertNotEmpty($m, 'Probe did not print ARGS line. Output: ' . $result['stdout']);
		$args = json_decode($m[1], true);
		$this->assertIsArray($args, 'db_Connect was never invoked or ARGS line malformed');
		$this->assertSame('localhost',  $args['s']);
		$this->assertSame('testuser',   $args['u']);
		$this->assertSame('testpass',   $args['p']);
		$this->assertSame('testdb',     $args['d']);
	}

	/**
	 * E107_INSTALL branch with v2.4 array-format e107_config.php must
	 * also work: initInstallSql() must receive populated values and
	 * MPREFIX must be set correctly.
	 */
	public function testE107InstallAcceptsArrayFormatConfig()
	{
		$this->writeArrayConfig($this->tmpDir);

		$probe = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/e107_handlers/e_db_interface.php';

// Stub the `db` alias before mysql_class.php (which has a
// `class_exists('db')`-guarded fallback declaration).
class db {
	public static $args = null;
	public function db_Connect($s, $u, $p, $d, $newLink = false, $prefix = null) {
		self::$args = compact('s', 'u', 'p', 'd');
		return true;
	}
}

// Load the real e107 class so initInstallSql() works.
define('e107_INIT', true);
require __DIR__ . '/e107_handlers/core_functions.php';
require __DIR__ . '/e107_handlers/e107_class.php';

define('E107_INSTALL', true);
require __DIR__ . '/e107_handlers/mysql_class.php';

echo "MPREFIX=" . (defined('MPREFIX') ? var_export(MPREFIX, true) : 'UNDEFINED') . "\n";
echo "ARGS=" . json_encode(db::$args) . "\n";
echo "CFG_PREFIX=" . var_export(e107::getMySQLConfig('prefix'), true) . "\n";
echo "CFG_SERVER=" . var_export(e107::getMySQLConfig('server'), true) . "\n";
echo "CFG_USER=" . var_export(e107::getMySQLConfig('user'), true) . "\n";
echo "CFG_DB=" . var_export(e107::getMySQLConfig('db'), true) . "\n";
PHP;

		symlink(
			$this->repoRoot . '/e107_handlers',
			$this->tmpDir . '/e107_handlers'
		);
		file_put_contents($this->tmpDir . '/probe.php', $probe);

		$result = $this->runPhp($this->tmpDir, 'probe.php');

		$combined = $result['stderr'] . "\n" . $result['stdout'];
		$this->assertStringNotContainsString(
			'Undefined variable',
			$combined,
			"mysql_class.php E107_INSTALL branch should not emit 'Undefined variable' warnings under array-format config (issue #5630). Output:\n" . $combined
		);
		$this->assertStringContainsString(
			"MPREFIX='e107_'",
			$result['stdout'],
			"MPREFIX should be populated. Output:\n" . $combined
		);

		preg_match('/^ARGS=(.*)$/m', $result['stdout'], $m);
		$this->assertNotEmpty($m, 'Probe did not print ARGS line. Output: ' . $combined);
		$args = json_decode($m[1], true);
		$this->assertIsArray($args, 'db_Connect was never invoked');
		$this->assertSame('localhost', $args['s']);
		$this->assertSame('testuser',  $args['u']);
		$this->assertSame('testpass',  $args['p']);
		$this->assertSame('testdb',    $args['d']);

		// initInstallSql() should have stored the config under the
		// canonical mySQL* keys via setMySQLConfig()'s shape normalisation.
		$this->assertStringContainsString("CFG_PREFIX='e107_'",   $result['stdout']);
		$this->assertStringContainsString("CFG_SERVER='localhost'", $result['stdout']);
		$this->assertStringContainsString("CFG_USER='testuser'",  $result['stdout']);
		// 'db' key in array format is stored under mySQLdb (initCore aliases
		// it to defaultdb but initInstallSql does not).
		$this->assertStringContainsString("CFG_DB='testdb'", $result['stdout']);
	}

	/**
	 * Legacy-globals format must continue to work unchanged.
	 */
	public function testMysqlLightStillAcceptsLegacyGlobalsFormat()
	{
		$this->writeLegacyConfig($this->tmpDir);

		$probe = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/e107_handlers/e_db_interface.php';

class db {
	public static $args = null;
	public function db_Connect($s, $u, $p, $d, $newLink = false, $prefix = null) {
		self::$args = compact('s', 'u', 'p', 'd');
		return true;
	}
}

define('MYSQL_LIGHT', true);
require __DIR__ . '/e107_handlers/mysql_class.php';

echo "MPREFIX=" . (defined('MPREFIX') ? var_export(MPREFIX, true) : 'UNDEFINED') . "\n";
echo "ARGS=" . json_encode(db::$args) . "\n";
PHP;

		symlink(
			$this->repoRoot . '/e107_handlers',
			$this->tmpDir . '/e107_handlers'
		);
		file_put_contents($this->tmpDir . '/probe.php', $probe);

		$result = $this->runPhp($this->tmpDir, 'probe.php');

		$combined = $result['stderr'] . "\n" . $result['stdout'];
		$this->assertStringNotContainsString(
			'Undefined variable',
			$combined,
			"Legacy-format config must still load without warnings. Output:\n" . $combined
		);
		$this->assertStringContainsString("MPREFIX='legacy_'", $result['stdout']);

		preg_match('/^ARGS=(.*)$/m', $result['stdout'], $m);
		$this->assertNotEmpty($m);
		$args = json_decode($m[1], true);
		$this->assertSame('legacyhost',  $args['s']);
		$this->assertSame('legacyuser',  $args['u']);
		$this->assertSame('legacypass',  $args['p']);
		$this->assertSame('legacydb',    $args['d']);
	}

	private function writeArrayConfig(string $dir): void
	{
		$contents = <<<'PHP'
<?php
return [
	'database' => [
		'server'   => 'localhost',
		'user'     => 'testuser',
		'password' => 'testpass',
		'db'       => 'testdb',
		'prefix'   => 'e107_',
		'charset'  => 'utf8',
	],
	'paths' => [],
	'other' => [],
];
PHP;
		file_put_contents($dir . '/e107_config.php', $contents);
	}

	private function writeLegacyConfig(string $dir): void
	{
		$contents = <<<'PHP'
<?php
$mySQLserver    = 'legacyhost';
$mySQLuser      = 'legacyuser';
$mySQLpassword  = 'legacypass';
$mySQLdefaultdb = 'legacydb';
$mySQLprefix    = 'legacy_';
PHP;
		file_put_contents($dir . '/e107_config.php', $contents);
	}

	/**
	 * @return array{stdout: string, stderr: string, exit: int}
	 */
	private function runPhp(string $cwd, string $script): array
	{
		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script);
		$proc = proc_open($cmd, $descriptors, $pipes, $cwd, null);
		if (!is_resource($proc))
		{
			$this->fail('Could not spawn php subprocess');
		}
		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$exit = proc_close($proc);

		return [
			'stdout' => $stdout,
			'stderr' => $stderr,
			'exit'   => $exit,
		];
	}

	private function rrmdir(string $dir): void
	{
		if (!is_dir($dir)) return;
		$items = scandir($dir);
		foreach ($items as $item)
		{
			if ($item === '.' || $item === '..') continue;
			$path = $dir . '/' . $item;
			if (is_link($path))
			{
				unlink($path);
			}
			elseif (is_dir($path))
			{
				$this->rrmdir($path);
			}
			else
			{
				unlink($path);
			}
		}
		rmdir($dir);
	}
}
