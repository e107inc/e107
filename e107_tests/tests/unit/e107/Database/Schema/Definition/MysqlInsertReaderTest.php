<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Definition;

use e107\Database\Exception\UnsupportedException;
use InvalidArgumentException;

class MysqlInsertReaderTest extends \Test\Unit
{
	/** @var MysqlInsertReader */
	private $reader;

	protected function _before()
	{
		require_once(e_HANDLER.'Database/Schema/Definition/MysqlInsertReader.php');
		require_once(e_HANDLER.'Database/Schema/Definition/MysqlDdlParser.php');

		$this->reader = new MysqlInsertReader();
	}

	public function testRowsAreReadFromMysqlLiterals()
	{
		$table = (new MysqlDdlParser())->parseTableBody('t', 'a varchar(20), b int, c text, d varbinary(5), e blob, f tinyint(1)');
		$insert = $this->reader->read("INSERT INTO `t` VALUES ('Côte d\\'Ivoire', -5, NULL, 0x6869, _binary 'x', TRUE), ('a;b', 1.50, '', X'41', b'1000010', FALSE)", array('t' => $table));

		$this->assertSame('t', $insert['table']);
		$this->assertSame(array(), $insert['columns']);
		$this->assertFalse($insert['ignore']);
		$this->assertSame(array(
			array("Côte d'Ivoire", '-5', null, 'hi', 'x', '1'),
			array('a;b', '1.50', '', 'A', 'B', '0'),
		), $insert['rows']);
	}

	public function testAHexadecimalOrBitLiteralIsReadForTheColumnItGoesTo()
	{
		$tables = array('t' => (new MysqlDdlParser())->parseTableBody('t', 'Name varchar(5), hits int, flags bit(8)'));

		$this->assertSame(array(array('A', '65', '5')), $this->reader->read("INSERT INTO t VALUES (0x41, 0x41, b'101')", $tables)['rows']);
		$this->assertSame(array(array('65', 'A', '65')), $this->reader->read("INSERT INTO t (HITS, name, flags) VALUES (0x41, b'1000001', X'41')", $tables)['rows']);
		$this->assertSame(array(array('', '-65')), $this->reader->readAll("INSERT INTO t (name, hits) VALUES (b'', -0x41);", $tables)[0]['rows']);
	}

	public function testAHexadecimalLiteralForNoKnownColumnIsRefused()
	{
		$this->expectException(UnsupportedException::class);

		$this->reader->read('INSERT INTO t VALUES (0x41)');
	}

	public function testAQuotedHexadecimalStringForANumericColumnIsRefused()
	{
		$this->expectException(UnsupportedException::class);

		$this->reader->read("INSERT INTO t VALUES (X'41')", array('t' => (new MysqlDdlParser())->parseTableBody('t', 'hits int')));
	}

	public function testAdjacentStringsAreOneValue()
	{
		$this->assertSame(array(array('abc', 'x')), $this->reader->read("INSERT INTO t VALUES ('a' \"b\" 'c', N'x')")['rows']);
	}

	public function testANamedColumnListAndIgnoreAreKept()
	{
		$insert = $this->reader->read('INSERT IGNORE INTO t (`a`, b) VALUE (1, 2)');

		$this->assertSame(array('a', 'b'), $insert['columns']);
		$this->assertTrue($insert['ignore']);
		$this->assertSame(array(array('1', '2')), $insert['rows']);
	}

	public function testEveryInsertOfAScriptIsRead()
	{
		$script = "<?php header('location:../index.php'); exit; ?>\n"
			."CREATE TABLE t (a int);\n"
			."INSERT INTO t VALUES (1);\n"
			."-- a comment; with a semicolon\n"
			."INSERT INTO t VALUES ('two');\n";

		$inserts = $this->reader->readAll($script);

		$this->assertCount(2, $inserts);
		$this->assertSame(array(array('two')), $inserts[1]['rows']);
	}

	public function testRowsAreKeyedByTheColumnsTheInsertNamesOrElseTheDeclaredOnes()
	{
		$declared = array('a', 'b');

		$this->assertSame(array(array('b' => '1', 'a' => '2')), $this->reader->rowsByColumn($this->reader->read('INSERT INTO t (b, a) VALUES (1, 2)'), $declared));
		$this->assertSame(array(array('a' => '1', 'b' => '2'), array('a' => '3', 'b' => null)), $this->reader->rowsByColumn($this->reader->read('INSERT INTO t VALUES (1, 2), (3, NULL)'), $declared));
		$this->assertFalse($this->reader->rowsByColumn($this->reader->read('INSERT INTO t VALUES (1, 2, 3)'), $declared));
	}

	public function testARowWithTheWrongNumberOfValuesIsRefused()
	{
		$this->expectException(InvalidArgumentException::class);

		$this->reader->read('INSERT INTO t (a, b) VALUES (1)');
	}

	public function testAnExpressionIsRefused()
	{
		$this->expectException(InvalidArgumentException::class);

		$this->reader->read('INSERT INTO t VALUES (NOW())');
	}
}
