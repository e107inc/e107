<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;


use e107\Factories\SessionHandlerFactory;

class NonblockingFilesSessionHandlerTest extends BaseSessionHandlerTest
{
	public function testGcDeletesExpiredSessionFiles()
	{
		$tmpdir = sys_get_temp_dir() . "/e107TestDir";
		@chmod($tmpdir, 0777);
		self::delTree($tmpdir);
		$sessionName = 'fake-old-session';
		$sessionData = 'Make me disappear';
		$sessionHandler = SessionHandlerFactory::make(NonblockingFilesSessionHandler::class);
		$sessionHandler->open($tmpdir, $sessionName);
		$sessionHandler->write($sessionName, $sessionData);
		$sessionHandler->close();
		$sessionHandler->open($tmpdir, $sessionName);
		$this->assertEquals($sessionData, $sessionHandler->read($sessionName));
		$sessionHandler->close();
		touch($tmpdir . "/" . NonblockingFilesSessionHandler::SESSION_FILE_PREFIX . $sessionName, time() - 172800);

		$sessionHandler->gc(86400);

		$sessionHandler->open($tmpdir, $sessionName);
		$this->assertEquals('', $sessionHandler->read($sessionName));
		$sessionHandler->close();
	}

	public function testOpenCreatesSessionFolder()
	{
		$tmpdir = sys_get_temp_dir() . "/e107TestDir";
		@chmod($tmpdir, 0777);
		self::delTree($tmpdir);
		$nestDir = "$tmpdir/a/b/c/d/e/f/g/";

		$this->assertFalse(is_dir($nestDir));

		$sessionHandler = SessionHandlerFactory::make(NonblockingFilesSessionHandler::class);
		$sessionHandler->open($nestDir, 'anything');
		$sessionHandler->close();

		$this->assertTrue(is_dir($nestDir));
		self::delTree($tmpdir);
	}

	public function testOpenThrowsExceptionIfSavePathIsNotWritable()
	{
		$this->expectException(\RuntimeException::class);

		$tmpfile = tmpfile();
		$tmpfilePath = stream_get_meta_data($tmpfile)['uri'];
		$sessionHandler = SessionHandlerFactory::make(NonblockingFilesSessionHandler::class);

		$sessionHandler->open($tmpfilePath . "/invalid", 'anything');
	}

	public function testReadReturnsFalseIfSessionIdIsInvalid()
	{
		$sessionHandler = SessionHandlerFactory::make(NonblockingFilesSessionHandler::class);
		$this->assertFalse($sessionHandler->read('../../path/traversal'));
	}

	/**
	 * @link https://www.php.net/manual/en/function.rmdir.php#110489
	 */
	private static function delTree($dir)
	{
		if (!file_exists($dir)) return true;
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file)
		{
			(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
}
