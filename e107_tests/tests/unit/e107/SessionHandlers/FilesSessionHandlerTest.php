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
class FilesSessionHandlerTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

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
