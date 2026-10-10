<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SessionHandlers;

/**
 * The files save method runs through core's own handler, and that handler reads and writes exactly what PHP's files module does, so switching to it signs nobody out.
 */
class FilesSessionHandlerTest extends \Test\Unit
{
	/** A child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable. */
	const BUFFERED = '-d output_buffering=On';

	const ID = 'filesessionhandlertest0123456789';

	/** @var string */
	private $dir;

	protected function _before()
	{
		$this->dir = sys_get_temp_dir().'/e107-files-session-'.getmypid().'-'.mt_rand();
		mkdir($this->dir, 0700);
	}

	protected function _after()
	{
		foreach(glob($this->dir.'/*') as $file)
		{
			unlink($file);
		}

		rmdir($this->dir);
	}

	public function testTheFilesSaveMethodIsServedByCoresOwnHandler()
	{
		list($output) = $this->runInBootedCli(
			"fwrite(STDERR, '@@'.e107::getSession()->getSaveMethod().':'.ini_get('session.save_handler').'@@');",
			self::BUFFERED
		);
		$printed = implode("\n", $output);

		$this->assertSame(1, preg_match('/@@([^:@]*):([^@]*)@@/', $printed, $matches), $printed);
		$this->assertSame('files', $matches[1], 'the fixture site stores its sessions in files');
		$this->assertSame('user', $matches[2], 'a handler object, not the bare module, serves them');
	}

	/**
	 * Through the policy e_session publishes for the files save method, with PHP's own module running underneath.
	 */
	public function testAClaimEndsTheSessionTheAccountClaimedBefore()
	{
		$php = "session_write_close(); ini_set('session.use_cookies', '0'); e107::getConfig()->set('disallowMultiLogin', 1); ";
		$php .= "\$sole = e107::getRegistry('core/e107/sole_session'); ";
		$php .= "session_id('earlier'); session_start(); \$sole->claim(77); \$_SESSION['claimed'] = 1; session_write_close(); ";
		$php .= "session_id('".self::ID."'); session_start(); ";
		$php .= "fwrite(STDERR, '@@'.var_export(\$sole->claim(77), true).'@@'); ";
		$php .= "session_destroy();";
		list($output) = $this->runInBootedCli($php, self::BUFFERED.' -d session.save_path='.escapeshellarg($this->dir));

		$this->assertStringContainsString('@@true@@', implode("\n", $output));
		$this->assertFalse(file_exists($this->dir.'/sess_earlier'));
		$this->assertSame(self::ID, $this->accountFileOf(77));
	}

	/**
	 * Sign-in regenerates the id before it claims; on PHP 5.6 the module then reports another session removed without removing it.
	 */
	public function testAClaimAfterTheIdIsRegeneratedStillEndsTheEarlierSession()
	{
		$php = "session_write_close(); ini_set('session.use_cookies', '0'); e107::getConfig()->set('disallowMultiLogin', 1); ";
		$php .= "\$sole = e107::getRegistry('core/e107/sole_session'); ";
		$php .= "session_id('earlier'); session_start(); \$sole->claim(77); \$_SESSION['claimed'] = 1; session_write_close(); ";
		$php .= "session_id('".self::ID."'); session_start(); session_regenerate_id(true); ";
		$php .= "fwrite(STDERR, '@@'.var_export(\$sole->claim(77), true).'@@'); ";
		$php .= "session_destroy();";
		list($output) = $this->runInBootedCli($php, self::BUFFERED.' -d session.save_path='.escapeshellarg($this->dir));

		$this->assertStringContainsString('@@true@@', implode("\n", $output));
		$this->assertFalse(file_exists($this->dir.'/sess_earlier'));
	}

	/**
	 * PHP's own module reaches its directory whatever open_basedir says; this class's file calls do not, and PHP's own rules decide which directories they reach.
	 */
	public function testAStoreWhoseDirectoryIsOutsideOpenBasedirCannotClaim()
	{
		$outside = '/var/tmp/e107-files-basedir-'.getmypid().'-'.mt_rand();
		mkdir($outside, 0700);
		mkdir($this->dir.'/real', 0700);
		symlink($outside, $this->dir.'/leads-out');
		symlink($this->dir.'/real', $this->dir.'/leads-in');

		try
		{
			$this->assertSame('false', $this->canClaimUnder($this->dir, APP_PATH), 'a directory open_basedir leaves out');
			$this->assertSame('true', $this->canClaimUnder($this->dir, APP_PATH.PATH_SEPARATOR.$this->dir), 'a directory open_basedir names');
			$this->assertSame('true', $this->canClaimUnder($this->dir, null), 'no open_basedir');
			$this->assertSame('false', $this->canClaimUnder($this->dir.'/leads-out', APP_PATH.PATH_SEPARATOR.$this->dir), 'a link inside the allowed tree that leads out of it');
			$this->assertSame('true', $this->canClaimUnder($this->dir.'/real', APP_PATH.PATH_SEPARATOR.$this->dir.'/leads-in'), 'an allowed entry that is a link to the directory');
		}
		finally
		{
			unlink($this->dir.'/leads-out');
			unlink($this->dir.'/leads-in');
			rmdir($this->dir.'/real');
			rmdir($outside);
		}
	}

	/**
	 * @param string $directory
	 * @param string|null $openBasedir
	 * @return string what canClaim() answered in a child running under that open_basedir
	 */
	private function canClaimUnder($directory, $openBasedir)
	{
		$php = "require ".var_export(APP_PATH.'/e107_handlers/SessionHandlers/SoleSessionStoreInterface.php', true)."; ";
		$php .= "require ".var_export(APP_PATH.'/e107_handlers/SessionHandlers/FilesSessionHandler.php', true)."; ";
		$php .= "\$store = new \\e107\\SessionHandlers\\FilesSessionHandler(".var_export($directory, true).", 'basedir'); ";
		$php .= "echo '@@'.var_export(\$store->canClaim(), true).'@@';";

		list($output) = $this->runInCli($php, null === $openBasedir ? '' : '-d open_basedir='.escapeshellarg($openBasedir));
		$printed = implode("\n", $output);

		$this->assertSame(1, preg_match('/@@(true|false)@@/', $printed, $matches), $printed);

		return $matches[1];
	}

	/**
	 * PHP's module removes a session only while one is running; the earlier session then stays named, so a later claim can still end it.
	 */
	public function testAClaimWithNoSessionRunningEndsNothingAndForgetsNothing()
	{
		file_put_contents($this->dir.'/sess_earlier', 'x|i:1;');
		file_put_contents($this->dir.'/e107_filestest_77', 'earlier');

		$php = "session_write_close(); \$store = new \\e107\\SessionHandlers\\FilesSessionHandler(".var_export($this->dir, true).", 'filestest'); ";
		$php .= "fwrite(STDERR, '@@'.var_export(\$store->claim(77, '".self::ID."'), true).'@@');";
		list($output) = $this->runInBootedCli($php, self::BUFFERED);

		$this->assertStringContainsString('@@false@@', implode("\n", $output));
		$this->assertTrue(file_exists($this->dir.'/sess_earlier'));
		$this->assertSame('earlier', file_get_contents($this->dir.'/e107_filestest_77'));
	}

	/**
	 * @param int $userId
	 * @return string|null the session the account's file in the test's directory names
	 */
	private function accountFileOf($userId)
	{
		$files = glob($this->dir.'/e107_*_'.$userId);

		$this->assertCount(1, $files, 'one account file for the account');

		return file_get_contents($files[0]);
	}

	/**
	 * Guards the upgrade: green before core had its own handler as well as after.
	 */
	public function testASessionPhpsOwnModuleWroteIsReadAndRewrittenInItsFormat()
	{
		$native = "ini_set('session.use_cookies', '0'); session_save_path(".var_export($this->dir, true)."); ";
		$native .= "session_id('".self::ID."'); session_start(); \$_SESSION['written_by'] = 'module'; session_write_close();";
		$this->runInCli($native);

		$core = "session_write_close(); ini_set('session.use_cookies', '0'); session_save_path(".var_export($this->dir, true)."); ";
		$core .= "session_id('".self::ID."'); session_start(); ";
		$core .= "fwrite(STDERR, '@@'.(isset(\$_SESSION['written_by']) ? \$_SESSION['written_by'] : 'nothing').'@@'); ";
		$core .= "\$_SESSION['written_by'] = 'core'; session_write_close();";
		list($output) = $this->runInBootedCli($core, self::BUFFERED);

		$this->assertStringContainsString('@@module@@', implode("\n", $output));
		$this->assertSame('written_by|s:4:"core";', file_get_contents($this->dir.'/sess_'.self::ID));
	}
}
