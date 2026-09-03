<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Ip;

/**
 * The file is what every request searches, so what render() writes has to
 * come back through open() as the same table, record by record, and a
 * file that is not such a table has to be refused rather than searched.
 */
class RangeFileTest extends \Test\Unit
{
	const WHITELIST = 100;
	const MANUAL = -1;
	const FLOOD = -2;

	/** @var string[] */
	private $scratch = array();

	protected function _before()
	{
		require_once(e_HANDLER.'Ip/Address.php');
		require_once(e_HANDLER.'Ip/Range.php');
		require_once(e_HANDLER.'Ip/RangeLookup.php');
		require_once(e_HANDLER.'Ip/RangeSet.php');
		require_once(e_HANDLER.'Ip/RangeFile.php');
	}

	protected function _after()
	{
		foreach($this->scratch as $file)
		{
			if(file_exists($file))
			{
				unlink($file);
			}
		}
	}

	/**
	 * @param string $content
	 * @return string path of a scratch file holding $content
	 */
	private function fileWith($content)
	{
		$file = sys_get_temp_dir().'/e107_rangefile_'.uniqid('', true).'.php';
		$this->scratch[] = $file;
		file_put_contents($file, $content);

		return $file;
	}

	/**
	 * @param array[] $rows each: text, id, type, expires
	 * @return RangeSet compiled
	 */
	private function compiled(array $rows)
	{
		$set = new RangeSet();

		foreach($rows as $row)
		{
			$set->add(Range::fromString($row[0]), $row[1], $row[2], $row[3], $row[0]);
		}

		$set->compile();

		return $set;
	}

	/**
	 * @param RangeLookup $table
	 * @param string $address
	 * @return array[] the rows covering the address, in lookup order
	 */
	private function rowsAt(RangeLookup $table, $address)
	{
		$segment = $table->find(Address::toHex($address));
		$rows = array();

		if($segment >= 0)
		{
			foreach($table->hits($segment) as $entry)
			{
				$rows[] = $table->entry($entry);
			}
		}

		return $rows;
	}

	/**
	 * Every segment, hit and entry survives the trip to disk and back, for
	 * every address the in-memory table answers, including the ones at the
	 * ends of the space and a stored value at the column's full width.
	 */
	public function testRoundTripsEveryLookup()
	{
		$long = '2001:db8::1-'.str_repeat('2001:0db8:0000:0000:0000:0000:0000:', 1).'ffff';
		$set = $this->compiled(array(
			array('10.1.1.5', 3, self::FLOOD, 1234567890),
			array('10.1.1.0/24', 2, self::MANUAL, 0),
			array('10.1.0.0/16', 1, self::WHITELIST, 0),
			array('ffff::/16', 8, self::MANUAL, 0),
			array('::', 9, self::MANUAL, 0),
			array($long, 10, self::MANUAL, 0),
		));
		$set->addOther();
		$set->addOther();

		$table = RangeFile::open($this->fileWith(RangeFile::render($set)));

		self::assertInstanceOf(RangeFile::class, $table);
		self::assertSame($set->entryCount(), $table->entryCount());
		self::assertSame(2, $table->others());

		foreach(array('10.1.1.5', '10.1.1.4', '10.1.1.6', '10.1.200.1', '10.2.0.0', '::', '::1', 'ffff::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'fffe::1', '2001:db8::7', '2001:db8::1:0') as $address)
		{
			self::assertSame($this->rowsAt($set, $address), $this->rowsAt($table, $address), $address.' has to read back as compiled');
		}

		$rows = $this->rowsAt($table, '10.1.1.5');
		self::assertSame(array('id' => 1, 'type' => self::WHITELIST, 'expires' => 0, 'ip' => '10.1.0.0/16'), $rows[0]);
		self::assertSame(array('id' => 3, 'type' => self::FLOOD, 'expires' => 1234567890, 'ip' => '10.1.1.5'), $rows[1]);
		self::assertSame($long, $this->rowsAt($table, '2001:db8::7')[0]['ip'], 'a stored value keeps its full text');
	}

	/**
	 * An empty list is a file too, and one that answers no address.
	 */
	public function testAnEmptyTableCoversNothing()
	{
		$set = new RangeSet();
		$set->compile();
		$table = RangeFile::open($this->fileWith(RangeFile::render($set)));

		self::assertNotNull($table);
		self::assertSame(-1, $table->find(Address::toHex('10.1.1.5')));
		self::assertSame(0, $table->entryCount());
	}

	/**
	 * The file older versions wrote under a similar name, a later version,
	 * nothing at all, and a table shorter than its header says are all
	 * refused, so the caller falls back to an empty table and regenerates
	 * rather than searching a table with rows missing, which would read as
	 * nobody banned, or dying.
	 */
	public function testRefusesWhatItDidNotWrite()
	{
		$set = $this->compiled(array(array('10.1.1.0/24', 2, self::MANUAL, 0)));
		$good = RangeFile::render($set);

		self::assertNull(RangeFile::open(sys_get_temp_dir().'/e107_rangefile_missing_'.uniqid('', true).'.php'));
		self::assertNull(RangeFile::open($this->fileWith("<?php\n; die();\n0000:0000:0000:0000:0000:ffff:0a01:01 -1 0\n")));
		self::assertNull(RangeFile::open($this->fileWith('')));
		self::assertNull(RangeFile::open($this->fileWith(substr($good, 0, 20))));
		self::assertNull(RangeFile::open($this->fileWith(str_replace('banranges 1 ', 'banranges 2 ', $good))));

		$headerOnly = strlen(RangeFile::GUARD) + RangeFile::HEADER_WIDTH;
		self::assertNull(RangeFile::open($this->fileWith(substr($good, 0, $headerOnly + 40))), 'a file cut inside the segment records is refused');
		self::assertNull(RangeFile::open($this->fileWith((string) substr($good, 0, strlen($good) - 1))), 'a file one byte short is refused');
		self::assertNull(RangeFile::open($this->fileWith($good."\n")), 'a file one byte long is refused');
		self::assertNotNull(RangeFile::open($this->fileWith($good)), 'the whole file opens');
	}

	/**
	 * The file is inert when a web server serves it as PHP, which is the
	 * reason it keeps the .php extension of the file it replaces.
	 */
	public function testStartsWithAnExitGuard()
	{
		$set = new RangeSet();
		$set->compile();

		self::assertStringStartsWith("<?php exit; ?>\n", RangeFile::render($set));
	}
}
