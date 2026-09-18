<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

use e107\Cache\Stamp;

/**
 * The coalescing that turns a bot's thousand hits into one record with a
 * count. The cron refusal record and the password-reset alert both read
 * through this, so the window, the detail and the garbage handling are
 * pinned once here.
 */
class IncidentTest extends \Test\Unit
{
	const ID = 'unitIncident';

	/** @var Stamp */
	private $stamps;

	/** @var Incident */
	private $incidents;

	protected function _before()
	{
		require_once(e_HANDLER.'Cache/Stamp.php');
		require_once(e_HANDLER.'Admin/Incident.php');

		$this->stamps = new Stamp(e_CACHE);
		$this->incidents = new Incident($this->stamps);
		$this->incidents->clear(self::ID);
	}

	protected function _after()
	{
		$this->incidents->clear(self::ID);
	}

	public function testAFirstEventStartsARun()
	{
		self::assertNull($this->incidents->last(self::ID), 'nothing is recorded before the first event');

		$before = time();
		$stored = $this->incidents->record(self::ID, array('ip' => '203.0.113.5', 'via' => 'http'));

		self::assertSame(1, $stored['count']);
		self::assertSame($stored['first'], $stored['last']);
		self::assertGreaterThanOrEqual($before, $stored['first']);
		self::assertSame('203.0.113.5', $stored['ip']);
		self::assertSame('http', $stored['via']);

		self::assertSame($stored, $this->incidents->last(self::ID), 'what was stored is what is read back');
	}

	public function testAnEventInsideTheWindowContinuesTheRun()
	{
		$first = $this->incidents->record(self::ID, array('ip' => '203.0.113.5'));
		$second = $this->incidents->record(self::ID, array('ip' => '203.0.113.6'));

		self::assertSame(2, $second['count']);
		self::assertSame($first['first'], $second['first'], 'the run keeps the time it began');
		self::assertGreaterThanOrEqual($second['first'], $second['last']);
		self::assertSame('203.0.113.6', $second['ip'], 'the newest detail wins');
	}

	public function testAnEventAfterTheWindowStartsANewRun()
	{
		$this->incidents->record(self::ID, array('ip' => '203.0.113.5'));

		$stale = $this->incidents->last(self::ID);
		$stale['first'] = time() - Incident::WINDOW - 1;
		$this->stamps->write(self::ID, $stale);

		$fresh = $this->incidents->record(self::ID, array('ip' => '203.0.113.5'));

		self::assertSame(1, $fresh['count']);
		self::assertGreaterThan($stale['first'], $fresh['first']);
	}

	public function testAZeroWindowNeverContinuesARun()
	{
		$this->incidents->record(self::ID, array(), 0);
		$second = $this->incidents->record(self::ID, array(), 0);

		self::assertSame(1, $second['count']);
	}

	public function testTheCountsCannotBeOverriddenByTheDetail()
	{
		$stored = $this->incidents->record(self::ID, array('count' => 99, 'first' => 1, 'nested' => array('x')));

		self::assertSame(1, $stored['count']);
		self::assertGreaterThan(1, $stored['first']);
		self::assertArrayNotHasKey('nested', $stored, 'only scalars are stored');
	}

	public function testGarbageReadsAsNothing()
	{
		$this->stamps->write(self::ID, array('first' => 1));
		self::assertNull($this->incidents->last(self::ID), 'a record without its counts is no record');

		file_put_contents(e_CACHE.self::ID.'.php', 'not a stamp');
		self::assertNull($this->incidents->last(self::ID));

		$this->stamps->write(self::ID, array('first' => '7', 'last' => '9', 'count' => '0', 'ip' => 'x', 'deep' => array()));
		self::assertSame(array('first' => 7, 'last' => 9, 'count' => 1, 'ip' => 'x'), $this->incidents->last(self::ID),
			'the counts are integers and at least one event is counted');

		$this->incidents->clear(self::ID);
		self::assertNull($this->incidents->last(self::ID));
	}

	public function testTheRecordIsAStampInTheCacheDirectory()
	{
		$this->incidents->record(self::ID);

		$file = e_CACHE.self::ID.'.php';
		self::assertFileExists($file);
		self::assertStringStartsWith(Stamp::PREFIX, file_get_contents($file), 'a stamp served as PHP prints nothing');
	}
}
