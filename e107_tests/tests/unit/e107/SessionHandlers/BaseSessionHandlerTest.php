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

abstract class BaseSessionHandlerTest extends \Codeception\Test\Unit
{
	/**
	 * Different implementation for PHP 5.6 and below because of a PHP bug in {@link SessionHandler}
	 * @link https://wiki.php.net/rfc/session.user.return-value
	 * @var bool Turn on when testing on PHP 5.6 and below in native mode
	 */
	protected $quirksMode = false;

	public function testSessionLifecycle()
	{
		$savePath = ini_get('session.save_path');
		if (!$savePath)
		{
			$savePath = '/tmp';
			@session_write_close();
			@ini_set('session.save_path', $savePath);
		}
		$sessionId = 'TEST-SESSION-HANDLER';
		$sessionData = 'Not actually session data';
		$sessionData2 = 'Still not actually session data';
		$sessionHandler = SessionHandlerFactory::make(
			preg_replace('/Test$/', '', get_class($this))
		);

		// XXX: Can't test in PhpStorm because the report printer writes early to stdout, leading to
		//      "Headers already sent"
		if (headers_sent()) $this->markTestSkipped("Can't test session handler because headers already sent");

		$sessionHandler->activate();
		$sessionHandler->open($savePath, $sessionId);
		$sessionHandler->destroy($sessionId);
		$sessionHandler->close();

		$validateFunction = $this->quirksMode ? 'assertFalse' : 'assertTrue';

		$this->{$validateFunction}($sessionHandler->open($savePath, $sessionId));
		$this->assertEquals('', $sessionHandler->read($sessionId));
		$this->{$validateFunction}($sessionHandler->write($sessionId, $sessionData));
		$this->assertEquals($sessionData, $sessionHandler->read($sessionId));
		$this->{$validateFunction}($sessionHandler->write($sessionId, $sessionData2));
		$this->assertEquals($sessionData2, $sessionHandler->read($sessionId));
		$this->{$validateFunction}($sessionHandler->destroy($sessionId));
		$this->assertEquals('', $sessionHandler->read($sessionId));
		$this->{$validateFunction}((boolean)$sessionHandler->gc(ini_get('session.gc_maxlifetime')));
		$this->{$validateFunction}($sessionHandler->close());
	}
}