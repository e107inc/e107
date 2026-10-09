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
 * What the database session handler costs every request that has a session: PHP reads, writes and closes on each one, and destroys on each sign-out.
 */
class e_session_dbTest extends \Test\Unit
{
	const ID = 'sessdbtest0123456789abcdefABCDEF';

	const EXPIRED = 'sessdbtestexpired';

	const PROBE_ID = 'sessdbtestgcprobe0123456789';

	/** A child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable. */
	const BUFFERED = '-d output_buffering=On';

	/** @var SessionDbHandlerLeavingTheSessionOpen */
	private $handler;

	protected function _before()
	{
		require_once(__DIR__ . '/fixtures/SessionDbHandlerLeavingTheSessionOpen.php');
		$this->handler = new SessionDbHandlerLeavingTheSessionOpen();
		$this->forget();
	}

	protected function _after()
	{
		$this->forget();
	}

	/**
	 * @return e_db the handler's own connection, so that it is open, and has sent its setup statements, before anything is counted
	 */
	private function db()
	{
		return e107::getDb('session');
	}

	private function forget()
	{
		$this->db()->createQueryBuilder()->delete('session')
			->whereIn('session_id', array(e_session_db::storageKey(self::ID), self::ID, strtoupper(self::ID), self::EXPIRED, e_session_db::storageKey(self::PROBE_ID)))
			->execute();
	}

	/**
	 * @param string $key
	 * @param string $data
	 * @param int $expires
	 */
	private function haveRow($key, $data, $expires)
	{
		$this->db()->createQueryBuilder()->insert('session')->values(array(
			'session_id'      => $key,
			'session_expires' => $expires,
			'session_user'    => 0,
			'session_data'    => base64_encode($data),
		))->execute();
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	private function rowExists($key)
	{
		return $this->db()->createQueryBuilder()->select('session_id')->from('session')->where('session_id', $key)->count() > 0;
	}

	/**
	 * @param callable $call
	 * @return array what $call returned, and how many statements it sent
	 */
	private function counted($call)
	{
		$db = $this->db();
		$before = $db->queryCount();
		$result = call_user_func($call);

		return array($result, $db->queryCount() - $before);
	}

	public function testAnUnknownIdIsLookedUpWithOneStatement()
	{
		$handler = $this->handler;

		list($data, $statements) = $this->counted(function() use ($handler) { return $handler->read(e_session_dbTest::ID); });

		$this->assertSame('', $data);
		$this->assertSame(1, $statements, 'the hashed and the legacy key are one lookup');
	}

	public function testTheHashedRowIsPreferredOverALegacyOne()
	{
		$this->haveRow(e_session_db::storageKey(self::ID), 'hashed', time() + 600);
		$this->haveRow(self::ID, 'legacy', time() + 600);

		$this->assertSame('hashed', $this->handler->read(self::ID));
		$this->assertTrue($this->rowExists(self::ID), 'a legacy row behind a hashed one is left where it is');
	}

	public function testALegacyRowIsReadAndMovedToItsHashedKey()
	{
		$this->haveRow(self::ID, 'legacy', time() + 600);

		$this->assertSame('legacy', $this->handler->read(self::ID));
		$this->assertTrue($this->rowExists(e_session_db::storageKey(self::ID)));
		$this->assertFalse($this->rowExists(self::ID));
	}

	public function testALegacyRowInAnotherLetterCaseIsReadAsTheTableMatchesIt()
	{
		$this->haveRow(strtoupper(self::ID), 'legacy', time() + 600);
		$tableMatches = $this->rowExists(self::ID);

		$this->assertSame($tableMatches ? 'legacy' : '', $this->handler->read(self::ID));
	}

	public function testAnExpiredRowIsNotRead()
	{
		$this->haveRow(e_session_db::storageKey(self::ID), 'stale', time() - 10);
		$this->haveRow(self::ID, 'stale too', time() - 10);

		$this->assertSame('', $this->handler->read(self::ID));
	}

	public function testDestroyRemovesBothKeysWithOneStatement()
	{
		$this->haveRow(e_session_db::storageKey(self::ID), 'hashed', time() + 600);
		$this->haveRow(self::ID, 'legacy', time() + 600);
		$handler = $this->handler;

		list($done, $statements) = $this->counted(function() use ($handler) { return $handler->destroy(e_session_dbTest::ID); });

		$this->assertTrue($done);
		$this->assertSame(1, $statements);
		$this->assertFalse($this->rowExists(e_session_db::storageKey(self::ID)));
		$this->assertFalse($this->rowExists(self::ID));
	}

	public function testCloseLeavesCollectionToPhpWhereItCollects()
	{
		$this->haveRow(self::EXPIRED, 'expired', time() - 10);

		$this->assertSame(0, $this->closeInChild('1', true), 'closing a session sends nothing while PHP collects');
		$this->assertTrue($this->rowExists(self::EXPIRED));
	}

	public function testCloseCollectsOnOneDrawInAHundredWherePhpDoesNot()
	{
		$this->haveRow(self::EXPIRED, 'expired', time() - 10);

		$this->assertSame(1, $this->closeInChild('0', true));
		$this->assertFalse($this->rowExists(self::EXPIRED), 'a host that holds gc_probability at 0 still has its table collected');
	}

	public function testCloseSendsNothingOnTheOtherDrawsWherePhpDoesNot()
	{
		$this->haveRow(self::EXPIRED, 'expired', time() - 10);

		$this->assertSame(0, $this->closeInChild('0', false));
		$this->assertTrue($this->rowExists(self::EXPIRED));
	}

	public function testTheCollectorRemovesExpiredRowsAlone()
	{
		$this->haveRow(self::EXPIRED, 'expired', time() - 10);
		$this->haveRow(e_session_db::storageKey(self::ID), 'live', time() + 600);

		$this->handler->gc(3600);

		$this->assertFalse($this->rowExists(self::EXPIRED));
		$this->assertTrue($this->rowExists(e_session_db::storageKey(self::ID)));
	}

	/**
	 * PHP calls the handler's collector as a session starts, at the odds session.gc_probability and session.gc_divisor give; here they are one in one.
	 */
	public function testPhpCollectsThroughTheHandlerAsASessionStarts()
	{
		$this->haveRow(self::EXPIRED, 'expired', time() - 10);

		$php = "session_write_close(); ";
		$php .= "ini_set('session.gc_probability', '1'); ini_set('session.gc_divisor', '1'); ";
		$php .= "session_set_save_handler(new e_session_db(), true); ";
		$php .= "session_id('".self::PROBE_ID."'); ";
		$php .= "fwrite(STDERR, session_start() ? '@@started@@' : '@@refused@@'); ";
		$php .= "session_write_close(); ";

		list($output) = $this->runInBootedCli($php, self::BUFFERED);

		$this->assertStringContainsString('@@started@@', implode("\n", $output));
		$this->assertFalse($this->rowExists(self::EXPIRED), 'the session start ran the collector');
	}

	/**
	 * Closes a handler in a child whose session.gc_probability is $probability, the generator seeded so that close()'s mt_rand(1, 100) draws 1 or does not.
	 *
	 * @param string $probability
	 * @param bool $drawsOne
	 * @return int statements the close sent
	 */
	private function closeInChild($probability, $drawsOne)
	{
		$php = "session_write_close(); ini_set('session.gc_probability', '".$probability."'); ";
		$php .= "\$handler = new e_session_db(); \$handler->read('".self::PROBE_ID."'); ";
		$php .= "\$db = e107::getDb('session'); \$before = \$db->queryCount(); ";
		$php .= "\$seed = 0; do { mt_srand(++\$seed); } while((1 === mt_rand(1, 100)) !== ".var_export($drawsOne, true)."); mt_srand(\$seed); ";
		$php .= "\$handler->close(); ";
		$php .= "fwrite(STDERR, '@@'.ini_get('session.gc_probability').':'.(\$db->queryCount() - \$before).'@@'); ";

		list($output) = $this->runInBootedCli($php, self::BUFFERED);
		$printed = implode("\n", $output);

		$this->assertSame(1, preg_match('/@@(\d+):(\d+)@@/', $printed, $matches), $printed);
		$this->assertSame($probability, $matches[1], 'the child closes at the probability asked for');

		return (int) $matches[2];
	}
}
