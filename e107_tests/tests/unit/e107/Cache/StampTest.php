<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Cache;

/**
 * The file shape every stamp in the cache shares: readable back as what was
 * written, silent if a web server ever serves it, and never a path outside
 * its directory whatever the name says.
 */
class StampTest extends \Test\Unit
{
	const NAME = 'unitStamp';

	/** @var Stamp */
	private $stamps;

	protected function _before()
	{
		require_once(e_HANDLER.'Cache/Stamp.php');

		$this->stamps = new Stamp(e_CACHE);
		$this->stamps->clear(self::NAME);
	}

	protected function _after()
	{
		$this->stamps->clear(self::NAME);
	}

	public function testARecordRoundTrips()
	{
		$data = array('time' => 1700000000, 'via' => 'http', 'ip' => '203.0.113.5');

		self::assertTrue($this->stamps->write(self::NAME, $data));
		self::assertSame($data, $this->stamps->read(self::NAME));
		self::assertStringStartsWith(Stamp::PREFIX, file_get_contents(e_CACHE.self::NAME.'.php'));
	}

	public function testNothingReadsAsNull()
	{
		self::assertNull($this->stamps->read(self::NAME));
	}

	public function testGarbageReadsAsNull()
	{
		$file = e_CACHE.self::NAME.'.php';

		file_put_contents($file, '{"time":1}');
		self::assertNull($this->stamps->read(self::NAME), 'a file without the exit line is not a stamp');

		file_put_contents($file, Stamp::PREFIX.'{"time":');
		self::assertNull($this->stamps->read(self::NAME), 'a stamp that does not parse is nothing');

		file_put_contents($file, Stamp::PREFIX.'"text"');
		self::assertNull($this->stamps->read(self::NAME), 'a stamp holds a record, not a scalar');
	}

	public function testTheNameIsKeptToWordCharacters()
	{
		$this->stamps->write('../'.self::NAME.'-x', array('ok' => 1));

		self::assertFileExists(e_CACHE.self::NAME.'x.php');
		self::assertSame(array('ok' => 1), $this->stamps->read('../'.self::NAME.'-x'));

		$this->stamps->clear('../'.self::NAME.'-x');
		self::assertFileDoesNotExist(e_CACHE.self::NAME.'x.php');
	}

	public function testClearForgetsAndIsQuietWhenThereIsNothing()
	{
		$this->stamps->write(self::NAME, array('ok' => 1));
		$this->stamps->clear(self::NAME);
		self::assertNull($this->stamps->read(self::NAME));

		$this->stamps->clear(self::NAME);
		self::assertNull($this->stamps->read(self::NAME));
	}
}
