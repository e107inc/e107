<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Result;

use InvalidArgumentException;
use PDO;

/**
 * BufferedResult against statements pdo_sqlite really produces, which is the driver it exists for: an in-memory
 * database, so every lane runs it.
 */
class BufferedResultTest extends \Test\Unit
{
	/** @var PDO */
	private $pdo;

	protected function _before()
	{
		require_once(e_HANDLER.'Database/Result/BufferedResult.php');

		if(!extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('pdo_sqlite is not loaded');
		}

		$this->pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
		$this->pdo->exec('CREATE TABLE a (id INTEGER PRIMARY KEY, name TEXT)');
		$this->pdo->exec('CREATE TABLE b (id INTEGER PRIMARY KEY, a_id INTEGER, label TEXT)');
		$this->pdo->exec("INSERT INTO a VALUES (1, 'one'), (2, 'two'), (3, NULL)");
		$this->pdo->exec("INSERT INTO b VALUES (10, 1, 'x'), (20, 2, 'y')");
	}

	public function testTheRowCountOfASelectIsKnownBeforeAnyRowIsRead()
	{
		$statement = $this->pdo->query('SELECT * FROM a');
		$this->assertSame(0, $statement->rowCount(), 'pdo_sqlite counts no SELECT rows, which is why the buffer exists');

		$result = BufferedResult::fromStatement($this->pdo->query('SELECT * FROM a'));

		$this->assertSame(3, $result->rowCount());
		$this->assertSame(3, count($result));
		$this->assertSame(2, $result->columnCount());
	}

	public function testAnEmptyResultSetStillKnowsItsColumns()
	{
		$result = BufferedResult::fromStatement($this->pdo->query('SELECT id, name FROM a WHERE id > 99'));

		$this->assertSame(0, $result->rowCount());
		$this->assertSame(2, $result->columnCount());
		$this->assertFalse($result->fetch(PDO::FETCH_ASSOC));
	}

	public function testAStatementWithoutAResultSetReportsTheRowsItAffected()
	{
		$result = BufferedResult::fromStatement($this->pdo->query("UPDATE a SET name = 'n' WHERE id < 3"));

		$this->assertSame(0, $result->columnCount());
		$this->assertSame(2, $result->rowCount());
		$this->assertFalse($result->fetch());
	}

	public function testFetchModesShapeEachRowAsPdoDoes()
	{
		$result = BufferedResult::fromStatement($this->pdo->query('SELECT id, name FROM a ORDER BY id'));

		$this->assertSame(array('id' => '1', 'name' => 'one'), $result->fetch(PDO::FETCH_ASSOC));
		$this->assertSame(array('2', 'two'), $result->fetch(PDO::FETCH_NUM));
		$this->assertSame(array('id' => '3', 0 => '3', 'name' => null, 1 => null), $result->fetch(PDO::FETCH_BOTH));
		$this->assertFalse($result->fetch(PDO::FETCH_ASSOC));
	}

	public function testADuplicatedColumnNameReadsBackTheWayPdoReturnsIt()
	{
		$sql = 'SELECT a.id, b.id, b.label FROM a JOIN b ON b.a_id = a.id ORDER BY a.id';
		$native = $this->pdo->query($sql);
		$result = BufferedResult::fromStatement($this->pdo->query($sql));

		$this->assertSame($native->fetch(PDO::FETCH_ASSOC), $result->fetch(PDO::FETCH_ASSOC));
		$this->assertSame($native->fetch(PDO::FETCH_NUM), $result->fetch(PDO::FETCH_NUM));
		$this->assertSame(array('id' => '20', 'label' => 'y'), $this->assocOf($sql, 1));
	}

	public function testFetchColumnAndFetchAllContinueFromTheCursor()
	{
		$result = BufferedResult::fromStatement($this->pdo->query('SELECT name, id FROM a ORDER BY id'));

		$this->assertSame('one', $result->fetchColumn());
		$this->assertSame('2', $result->fetchColumn(1));
		$this->assertSame(array(array(null, '3')), $result->fetchAll(PDO::FETCH_NUM));
		$this->assertFalse($result->fetchColumn());
		$this->assertSame(array(), $result->fetchAll(PDO::FETCH_NUM));
	}

	public function testTheStatementIsReleasedSoTheNextWriteIsNotBlocked()
	{
		$file = codecept_output_dir().'BufferedResultTest.sqlite';
		@unlink($file);

		$reader = new PDO('sqlite:'.$file, null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$writer = new PDO('sqlite:'.$file, null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 0));
		$reader->exec('CREATE TABLE t (v INTEGER)');
		$reader->exec('INSERT INTO t VALUES (1), (2)');

		$result = BufferedResult::fromStatement($reader->query('SELECT v FROM t'));
		$this->assertSame(1, $writer->exec('DELETE FROM t WHERE v = 1'));
		$this->assertSame(2, $result->rowCount());

		$reader = $writer = null;
		unlink($file);
	}

	public function testAnUnsupportedFetchModeIsRefused()
	{
		$result = BufferedResult::fromStatement($this->pdo->query('SELECT id FROM a'));

		$this->expectException(InvalidArgumentException::class);
		$result->fetch(PDO::FETCH_OBJ);
	}

	public function testIteratingYieldsTheRemainingRows()
	{
		$result = BufferedResult::fromStatement($this->pdo->query('SELECT id FROM a ORDER BY id'));
		$result->fetch();

		$ids = array();
		foreach($result as $row)
		{
			$ids[] = $row['id'];
		}

		$this->assertSame(array('2', '3'), $ids);
	}

	/**
	 * @param string $sql
	 * @param int $index
	 * @return array the FETCH_ASSOC shape of one row, buffered
	 */
	private function assocOf($sql, $index)
	{
		$rows = BufferedResult::fromStatement($this->pdo->query($sql))->fetchAll(PDO::FETCH_ASSOC);

		return $rows[$index];
	}
}
