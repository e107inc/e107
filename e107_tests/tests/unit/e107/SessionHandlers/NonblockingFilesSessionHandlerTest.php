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
 * Session files without the lock: the files PHP's own module keeps, each replaced whole on a write.
 */
class NonblockingFilesSessionHandlerTest extends \Test\Unit
{
	/** A child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable. */
	const BUFFERED = '-d output_buffering=On';

	const ID = 'nonblockingtest0123456789abcdef';

	const SITE = 'nonblockingsite';

	/** @var string */
	private $dir;

	/** @var string|null */
	private $methodWas;

	/** @var bool */
	private $methodChanged = false;

	protected function _before()
	{
		$this->dir = sys_get_temp_dir().'/e107-nonblocking-session-'.getmypid().'-'.mt_rand();
		mkdir($this->dir, 0700);
	}

	protected function _after()
	{
		if($this->methodChanged)
		{
			$config = \e107::getConfig();
			null === $this->methodWas ? $config->remove('session_save_method') : $config->set('session_save_method', $this->methodWas);
			$config->save(false, true, false);
		}

		$this->removeTree($this->dir);
	}

	/**
	 * @param string $path
	 * @return void
	 */
	private function removeTree($path)
	{
		foreach((array) glob($path.'/*') as $entry)
		{
			is_dir($entry) ? $this->removeTree($entry) : unlink($entry);
		}

		rmdir($path);
	}

	/**
	 * @param string $savePath
	 * @return NonblockingFilesSessionHandler
	 */
	private function handler($savePath = null)
	{
		return new NonblockingFilesSessionHandler(null === $savePath ? $this->dir : $savePath, self::SITE);
	}

	public function testAWriteLeavesTheDataWholeAndReadableOnlyByItsOwner()
	{
		$this->assertTrue($this->handler()->write(self::ID, 'a|s:1:"b";'));

		$file = $this->dir.'/sess_'.self::ID;
		$this->assertSame('a|s:1:"b";', file_get_contents($file));
		$this->assertSame(0600, fileperms($file) & 0777);
	}

	public function testTheModeTheSavePathNamesIsTheModeFilesAreWrittenWith()
	{
		$this->handler('0;0640;'.$this->dir)->write(self::ID, 'x|i:1;');

		$this->assertSame(0640, fileperms($this->dir.'/sess_'.self::ID) & 0777);
	}

	public function testASessionPhpsOwnModuleWroteIsRead()
	{
		$native = "ini_set('session.use_cookies', '0'); session_save_path(".var_export($this->dir, true)."); ";
		$native .= "session_id('".self::ID."'); session_start(); \$_SESSION['written_by'] = 'module'; session_write_close();";
		$this->runInCli($native);

		$this->assertSame('written_by|s:6:"module";', $this->handler()->read(self::ID));
	}

	public function testASessionNobodyWroteReadsAsEmpty()
	{
		$this->assertSame('', $this->handler()->read(self::ID));
	}

	public function testAnIdThatIsNotASessionIdTouchesNoFile()
	{
		$handler = $this->handler();

		$this->assertFalse($handler->read('../escape'));
		$this->assertFalse($handler->write('../escape', 'x'));
		$this->assertFalse(file_exists(dirname($this->dir).'/sess_escape'));
	}

	public function testDestroyRemovesTheSession()
	{
		$handler = $this->handler();
		$handler->write(self::ID, 'x|i:1;');

		$this->assertTrue($handler->destroy(self::ID));
		$this->assertFalse(file_exists($this->dir.'/sess_'.self::ID));
	}

	public function testTheCollectorRemovesStaleSessionsAndNothingElse()
	{
		$handler = $this->handler();
		$handler->write('stale', 'x|i:1;');
		$handler->write('fresh', 'x|i:1;');
		touch($this->dir.'/sess_stale', time() - 7200);
		touch($this->dir.'/unrelated', time() - 7200);

		$this->assertSame(1, $handler->gc(3600));
		$this->assertFalse(file_exists($this->dir.'/sess_stale'));
		$this->assertTrue(file_exists($this->dir.'/sess_fresh'));
		$this->assertTrue(file_exists($this->dir.'/unrelated'));
	}

	public function testTheCollectorRemovesAnAccountFileOnlyOnceItsSessionIsGoneAndItIsStale()
	{
		$handler = $this->handler();
		$handler->write('alive', 'x|i:1;');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_1', 'gone');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_2', 'alive');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_3', 'gone');
		file_put_contents($this->dir.'/e107_othersite_4', 'gone');
		touch($this->dir.'/e107_'.self::SITE.'_1', time() - 7200);
		touch($this->dir.'/e107_'.self::SITE.'_2', time() - 7200);
		touch($this->dir.'/e107_othersite_4', time() - 7200);

		$handler->gc(3600);

		$this->assertFalse(file_exists($this->dir.'/e107_'.self::SITE.'_1'), 'stale, and its session is gone');
		$this->assertTrue(file_exists($this->dir.'/e107_'.self::SITE.'_2'), 'its session is still there');
		$this->assertTrue(file_exists($this->dir.'/e107_'.self::SITE.'_3'), 'claimed too recently to be stale');
		$this->assertTrue(file_exists($this->dir.'/e107_othersite_4'), 'another site sharing the directory owns it');
	}

	public function testAClaimEndsTheSessionTheAccountClaimedBefore()
	{
		$handler = $this->handler();
		$handler->write('earlier', 'x|i:1;');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_77', 'earlier');

		$this->assertTrue($handler->claim(77, self::ID));
		$this->assertFalse(file_exists($this->dir.'/sess_earlier'));
		$this->assertSame(self::ID, file_get_contents($this->dir.'/e107_'.self::SITE.'_77'));
		$this->assertSame(0600, fileperms($this->dir.'/e107_'.self::SITE.'_77') & 0777, 'it names a live session, so only its owner may read it');
	}

	public function testAClaimEndsNothingOfAnotherAccountOrSite()
	{
		$handler = $this->handler();
		$handler->write('theirs', 'x|i:1;');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_78', 'theirs');
		file_put_contents($this->dir.'/e107_othersite_77', 'theirs');

		$this->assertFalse($handler->claim(77, self::ID));
		$this->assertTrue(file_exists($this->dir.'/sess_theirs'));
	}

	public function testAClaimByTheSessionTheAccountFileAlreadyNamesEndsNothing()
	{
		$handler = $this->handler();
		$handler->write(self::ID, 'x|i:1;');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_77', self::ID);

		$this->assertFalse($handler->claim(77, self::ID));
		$this->assertTrue(file_exists($this->dir.'/sess_'.self::ID));
	}

	public function testAnAccountFileNamingSomethingThatIsNotASessionIdEndsNothing()
	{
		$handler = $this->handler();
		file_put_contents($this->dir.'/escape', 'x');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_77', '../escape');

		$this->assertFalse($handler->claim(77, self::ID));
		$this->assertTrue(file_exists($this->dir.'/escape'));
	}

	public function testAClaimForNoAccountOrAMalformedIdIsRefused()
	{
		$handler = $this->handler();

		$this->assertFalse($handler->claim(0, self::ID));
		$this->assertFalse($handler->claim(77, '../escape'));
		$this->assertSame(array(), glob($this->dir.'/e107_*'));
	}

	/**
	 * A request still running when another signs the session out, or ends it for a newer sign-in, must not put it back.
	 */
	public function testASessionEndedWhileARequestRanIsNotWrittenBack()
	{
		$this->handler()->write(self::ID, 'token|s:3:"7.x";');
		$running = $this->handler();
		$running->read(self::ID);

		$this->handler()->destroy(self::ID);

		$this->assertTrue($running->write(self::ID, 'token|s:3:"7.x";page|s:5:"later";'));
		$this->assertFalse(file_exists($this->dir.'/sess_'.self::ID));
	}

	/**
	 * The evicted browser's next request starts the id afresh as a guest; a slow request from before the end must still not put the signed-in session back over it.
	 */
	public function testAnEndedSessionStaysEndedWhenItsBrowserComesBackDuringASlowRequest()
	{
		$this->handler()->write(self::ID, 'token|s:3:"7.x";');
		$slow = $this->handler();
		$slow->read(self::ID);

		$this->handler()->destroy(self::ID);

		$next = $this->handler();
		$next->read(self::ID);
		$this->assertTrue($next->write(self::ID, 'guest|b:1;'));

		$this->assertTrue($slow->write(self::ID, 'token|s:3:"7.x";page|s:5:"later";'));
		$this->assertSame('guest|b:1;', file_get_contents($this->dir.'/sess_'.self::ID));

		$after = $this->handler();
		$after->read(self::ID);
		$this->assertTrue($after->write(self::ID, 'guest|b:1;form|s:1:"x";'));
		$this->assertSame('guest|b:1;form|s:1:"x";', file_get_contents($this->dir.'/sess_'.self::ID), 'a request after the end may write as usual');
	}

	/**
	 * Guards an end made by another process; it cannot show the stale status cache the store clears, since the child's launch stats other paths first.
	 */
	public function testASessionAnotherProcessEndedIsNotWrittenBack()
	{
		$this->handler()->write(self::ID, 'token|s:3:"7.x";');
		$running = $this->handler();
		$running->read(self::ID);

		$this->runInBootedCli("\$store = new \\e107\\SessionHandlers\\NonblockingFilesSessionHandler(".var_export($this->dir, true).", '".self::SITE."'); \$store->destroy('".self::ID."');");

		$this->assertTrue($running->write(self::ID, 'token|s:3:"7.x";page|s:5:"later";'));
		$this->assertFalse(file_exists($this->dir.'/sess_'.self::ID));
	}

	public function testASessionNoRequestHadReadIsWritten()
	{
		$handler = $this->handler();
		$handler->read(self::ID);

		$this->assertTrue($handler->write(self::ID, 'x|i:1;'));
		$this->assertTrue(file_exists($this->dir.'/sess_'.self::ID));
	}

	/**
	 * PHP's files module refuses a session file another account created in a shared directory, since its contents are unserialised as this one's.
	 */
	public function testASessionFileAnotherAccountOwnsIsNotRead()
	{
		$file = $this->dir.'/sess_'.self::ID;
		file_put_contents($file, 'planted|s:3:"yes";');
		$this->ownedByAnotherAccount($file);

		$this->assertSame('', $this->handler()->read(self::ID));
	}

	public function testASessionFileThatIsALinkIsNotRead()
	{
		file_put_contents($this->dir.'/elsewhere', 'planted|s:3:"yes";');
		symlink($this->dir.'/elsewhere', $this->dir.'/sess_'.self::ID);

		$this->assertSame('', $this->handler()->read(self::ID));
	}

	public function testAnAccountFileAnotherAccountOwnsIsNotTrusted()
	{
		$handler = $this->handler();
		$handler->write('earlier', 'x|i:1;');
		file_put_contents($this->dir.'/e107_'.self::SITE.'_77', 'earlier');
		$this->ownedByAnotherAccount($this->dir.'/e107_'.self::SITE.'_77');

		$this->assertFalse($handler->claim(77, self::ID));
		$this->assertTrue(file_exists($this->dir.'/sess_earlier'));
	}

	public function testAnAccountFileThatIsALinkIsNeitherTrustedNorWrittenThrough()
	{
		$handler = $this->handler();
		$handler->write('earlier', 'x|i:1;');
		file_put_contents($this->dir.'/target', 'earlier');
		symlink($this->dir.'/target', $this->dir.'/e107_'.self::SITE.'_77');

		$this->assertFalse($handler->claim(77, self::ID));
		$this->assertTrue(file_exists($this->dir.'/sess_earlier'));
		$this->assertSame('earlier', file_get_contents($this->dir.'/target'));
	}

	/**
	 * @param string $file
	 * @return void
	 */
	private function ownedByAnotherAccount($file)
	{
		if(!function_exists('posix_geteuid') || 0 !== posix_geteuid() || !@chown($file, 65534))
		{
			$this->markTestSkipped('handing a file to another account needs root');
		}

		clearstatcache();
	}

	public function testADepthInTheSavePathPutsTheSessionWherePhpsModuleLooks()
	{
		mkdir($this->dir.'/n', 0700);

		$this->handler('1;'.$this->dir)->write(self::ID, 'x|i:1;');

		$this->assertTrue(file_exists($this->dir.'/n/sess_'.self::ID));
	}

	public function testOpeningADirectoryThatCannotBeWrittenFails()
	{
		$this->assertFalse($this->handler($this->dir.'/missing')->open($this->dir.'/missing', 'PHPSESSID'));
	}

	/**
	 * Where open_basedir leaves the session directory out, PHP's own module still reaches it and this store cannot, so the site keeps working sessions through plain files.
	 */
	public function testTheNonblockingSaveMethodFallsBackToFilesItCannotReach()
	{
		$config = \e107::getConfig();
		$this->methodWas = $config->get('session_save_method');
		$this->methodChanged = true;
		$config->set('session_save_method', 'nonblocking')->save(false, true, false);

		$outside = '/var/tmp/e107-nonblocking-basedir-'.getmypid().'-'.mt_rand();
		mkdir($outside, 0700);

		try
		{
			$php = "fwrite(STDERR, '@@'.ini_get('session.save_handler').':'.session_status().':'.(e107::getRegistry('core/e107/sole_session') ? 'claims' : 'cannot claim').'@@');";
			list($output) = $this->runInBootedCli($php, self::BUFFERED
				.' -d open_basedir='.escapeshellarg(implode(PATH_SEPARATOR, array(APP_PATH, sys_get_temp_dir(), '/dev/urandom')))
				.' -d session.save_path='.escapeshellarg($outside));
		}
		finally
		{
			$this->removeTree($outside);
		}

		$printed = implode("\n", $output);
		$this->assertStringContainsString('@@user:'.PHP_SESSION_ACTIVE.':cannot claim@@', $printed);
		$this->assertStringNotContainsString('open_basedir restriction', $printed);
	}

	/**
	 * PHP's files module holds an exclusive lock on the session file from session_start() to the close; this save method holds none.
	 */
	public function testTheNonblockingSaveMethodLeavesAnOpenSessionUnlocked()
	{
		$config = \e107::getConfig();
		$this->methodWas = $config->get('session_save_method');
		$this->methodChanged = true;
		$config->set('session_save_method', 'nonblocking')->save(false, true, false);

		$file = $this->dir.'/sess_'.self::ID;
		file_put_contents($file, 'before|s:6:"opened";');

		$php = "session_write_close(); ini_set('session.use_cookies', '0'); ";
		$php .= "session_id('".self::ID."'); session_start(); ";
		$php .= "\$other = fopen(".var_export($file, true).", 'c'); \$free = flock(\$other, LOCK_EX | LOCK_NB); ";
		$php .= "fwrite(STDERR, '@@'.e107::getSession()->getSaveMethod().':'.(\$free ? 'unlocked' : 'locked').'@@'); ";
		$php .= "if(\$free) { flock(\$other, LOCK_UN); } fclose(\$other); session_write_close();";
		list($output) = $this->runInBootedCli($php, self::BUFFERED.' -d session.save_path='.escapeshellarg($this->dir));

		$this->assertStringContainsString('@@nonblocking:unlocked@@', implode("\n", $output));
	}
}
