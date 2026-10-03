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
use e107\Database\SqlLexer;
use InvalidArgumentException;

class MysqlDdlParserTest extends \Test\Unit
{
	/** @var MysqlDdlParser */
	private $parser;

	protected function _before()
	{
		require_once(e_HANDLER.'e_db_interface.php');
		require_once(e_HANDLER.'Database/Schema/Definition/MysqlDdlParser.php');
		require_once(e_HANDLER.'Database/Schema/Definition/MysqlDdlWriter.php');
		$this->parser = new MysqlDdlParser();
	}

	public function testATypicalE107DeclarationParsesIntoColumnsKeysAndOptions()
	{
		$table = $this->parser->parseCreateTable("CREATE TABLE news (
			news_id int(10) unsigned NOT NULL auto_increment,
			news_title varchar(255) NOT NULL default '',
			news_body longtext NOT NULL,
			news_class varchar(255) NOT NULL default '0',
			PRIMARY KEY  (news_id),
			KEY news_class (news_class),
			FULLTEXT (news_title)
		) ENGINE=InnoDB;");

		$this->assertSame('news', $table->getName());
		$this->assertSame(array('news_id', 'news_title', 'news_body', 'news_class'), array_keys($table->getColumns()));
		$this->assertSame(array('engine' => 'InnoDB'), $table->getOptions());

		$id = $table->getColumn('news_id');
		$this->assertSame('int', $id->getType());
		$this->assertSame('10', $id->getLength());
		$this->assertTrue($id->isUnsigned());
		$this->assertFalse($id->isNullable());
		$this->assertTrue($id->isAutoIncrement());
		$this->assertSame(ColumnDefinition::DEFAULT_NONE, $id->getDefaultKind());
		$this->assertSame($id, $table->getAutoIncrementColumn());

		$title = $table->getColumn('news_title');
		$this->assertSame(ColumnDefinition::DEFAULT_LITERAL, $title->getDefaultKind());
		$this->assertSame('', $title->getDefault());

		$this->assertSame(array('PRIMARY', 'news_class', 'news_title'), array_keys($table->getIndexes()));
		$this->assertSame(IndexDefinition::KIND_FULLTEXT, $table->getIndex('news_title')->getKind());
		$this->assertSame(array('news_id'), $table->getPrimaryKey()->getColumnNames());
	}

	public function testUnnamedKeysTakeMysqlsNames()
	{
		$table = $this->parser->parseTableBody('t', 'a int, b int, c int, KEY (a), KEY (a, b), UNIQUE (b), KEY a_3 (c), FULLTEXT (c)');

		$this->assertSame(array('a', 'a_2', 'a_3', 'b', 'c'), $this->sorted(array_keys($table->getIndexes())));
		$this->assertSame(array('a', 'b'), $table->getIndex('a_2')->getColumnNames());
		$this->assertSame(IndexDefinition::KIND_UNIQUE, $table->getIndex('b')->getKind());
	}

	public function testAKeyNamedOnlyByItsConstraintTakesTheConstraintsName()
	{
		$table = $this->parser->parseTableBody('t', 'a int NOT NULL, b int, c int, CONSTRAINT pk PRIMARY KEY (a), CONSTRAINT uq_b UNIQUE (b), CONSTRAINT uq_c UNIQUE KEY c_key (c), UNIQUE (b), CONSTRAINT UNIQUE (c)');

		$this->assertSame(array('PRIMARY', 'b', 'c', 'c_key', 'uq_b'), $this->sorted(array_keys($table->getIndexes())));
		$this->assertSame(array('b'), $table->getIndex('uq_b')->getColumnNames());
	}

	public function testInlineKeysBecomeIndexes()
	{
		$table = $this->parser->parseTableBody('t', 'id int NOT NULL PRIMARY KEY, code varchar(10) UNIQUE');

		$this->assertSame(array('id'), $table->getPrimaryKey()->getColumnNames());
		$this->assertSame(IndexDefinition::KIND_UNIQUE, $table->getIndex('code')->getKind());
		$this->assertSame(array('other'), $this->parser->parseTableBody('t', 'other int KEY')->getPrimaryKey()->getColumnNames(), 'a bare KEY on a column means PRIMARY KEY');

		$this->expectException(InvalidArgumentException::class);
		$this->parser->parseTableBody('t', 'a int PRIMARY KEY, b int PRIMARY KEY');
	}

	public function testDefaultsOfEveryKind()
	{
		$table = $this->parser->parseTableBody('t', "a int DEFAULT 0, b int DEFAULT -5, c varchar(5) DEFAULT 'it\\'s', d datetime DEFAULT NULL, e timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP, f bit(1) DEFAULT b'1', g double DEFAULT 1.5e2");

		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '0'), $this->defaultOf($table, 'a'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '-5'), $this->defaultOf($table, 'b'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, "it's"), $this->defaultOf($table, 'c'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_NULL, null), $this->defaultOf($table, 'd'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_EXPRESSION, 'CURRENT_TIMESTAMP'), $this->defaultOf($table, 'e'));
		$this->assertSame('CURRENT_TIMESTAMP', $table->getColumn('e')->getOnUpdate());
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '1'), $this->defaultOf($table, 'f'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '1.5e2'), $this->defaultOf($table, 'g'));
	}

	public function testTypeDetailsCharsetCollationAndMembers()
	{
		$table = $this->parser->parseTableBody('t', "s enum('a','b c','d''e') NOT NULL default 'a', p decimal(10,2), b varchar(20) BINARY, c text CHARACTER SET latin1 COLLATE latin1_bin COMMENT 'note', z tinyint(3) unsigned zerofill");

		$this->assertSame(array('a', 'b c', "d'e"), $table->getColumn('s')->getMembers());
		$this->assertSame('10,2', $table->getColumn('p')->getLength());
		$this->assertSame('binary', $table->getColumn('b')->getCollation());
		$this->assertSame('latin1', $table->getColumn('c')->getCharset());
		$this->assertSame('latin1_bin', $table->getColumn('c')->getCollation());
		$this->assertSame('note', $table->getColumn('c')->getComment());
		$this->assertTrue($table->getColumn('z')->isZerofill());
	}

	public function testKeyPartsCarryPrefixLengthsAndDirections()
	{
		$index = $this->parser->parseIndex('KEY `k` (`a`(191), b DESC) USING BTREE');

		$this->assertSame('k', $index->getName());
		$this->assertSame(array(
			array('column' => 'a', 'length' => 191, 'direction' => 'ASC'),
			array('column' => 'b', 'length' => null, 'direction' => 'DESC'),
		), $index->getParts());
	}

	public function testTableOptionsAreNormalised()
	{
		$this->assertSame(
			array('engine' => 'MyISAM', 'auto_increment' => '58', 'charset' => 'utf8', 'collate' => 'utf8_bin', 'comment' => 'x y'),
			$this->parser->parseTableOptions("TYPE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARACTER SET = utf8 COLLATE utf8_bin COMMENT='x y';")
		);
	}

	public function testAColumnFragmentParsesWithoutItsName()
	{
		$column = $this->parser->parseColumn('user_twitter', "VARCHAR(255) NOT NULL DEFAULT ''");

		$this->assertSame('user_twitter', $column->getName());
		$this->assertSame('varchar', $column->getType());
		$this->assertSame('255', $column->getLength());
		$this->assertSame(array('id', 'int'), array($this->parser->parseNamedColumn('`id` INT( 11 ) unsigned')->getName(), $this->parser->parseNamedColumn('id INT')->getType()));
	}

	public function testConstructsTheModelCannotHoldAreRefusedNotDropped()
	{
		foreach(array(
			'a int, b int, FOREIGN KEY (a) REFERENCES x(id)',
			'a int, CONSTRAINT chk CHECK (a > 0)',
			'a int GENERATED ALWAYS AS (1) VIRTUAL',
		) as $body)
		{
			try
			{
				$this->parser->parseTableBody('t', $body);
				$this->fail('Accepted: '.$body);
			}
			catch(UnsupportedException $e)
			{
				$this->assertNotEmpty($e->getMessage());
			}
		}
	}

	public function testTypeSynonymsAreReadAsTheTypesTheyStandFor()
	{
		$table = $this->parser->parseTableBody('t', 'a int4, b int8, c middleint, d float8, e nvarchar(5), f national char(3), g nchar varchar(4), h character varying(5), i double precision, j long varbinary, k long varchar, l long');

		$types = array();
		foreach($table->getColumns() as $name => $column)
		{
			$types[$name] = $column->getType();
		}

		$this->assertSame(array('a' => 'int', 'b' => 'bigint', 'c' => 'mediumint', 'd' => 'double', 'e' => 'varchar', 'f' => 'char', 'g' => 'varchar', 'h' => 'varchar', 'i' => 'double', 'j' => 'mediumblob', 'k' => 'mediumtext', 'l' => 'mediumtext'), $types);
	}

	public function testSerialIsAnUnsignedAutoIncrementBigintWithAUniqueKey()
	{
		foreach(array('id SERIAL', 'id bigint unsigned SERIAL DEFAULT VALUE') as $body)
		{
			$table = $this->parser->parseTableBody('t', $body);
			$id = $table->getColumn('id');

			$this->assertSame(array('bigint', true, false, true), array($id->getType(), $id->isUnsigned(), $id->isNullable(), $id->isAutoIncrement()), $body);
			$this->assertSame(IndexDefinition::KIND_UNIQUE, $table->getIndex('id')->getKind(), $body);
		}
	}

	public function testAHexadecimalOrBitDefaultIsReadForTheColumnsType()
	{
		$table = $this->parser->parseTableBody('t', "a varchar(5) DEFAULT 0x41, b int DEFAULT 0x41, c varchar(5) DEFAULT X'4142', d bit(8) DEFAULT b'101', e char(1) DEFAULT b'1000001', f int DEFAULT -0x10");

		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, 'A'), $this->defaultOf($table, 'a'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '65'), $this->defaultOf($table, 'b'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, 'AB'), $this->defaultOf($table, 'c'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '5'), $this->defaultOf($table, 'd'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, 'A'), $this->defaultOf($table, 'e'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '-16'), $this->defaultOf($table, 'f'));
		$this->assertSame(array(ColumnDefinition::DEFAULT_LITERAL, '5'), $this->defaultOf($this->parser->parseTableBody('t', "g bit(8) DEFAULT X'05'"), 'g'));

		$this->expectException(UnsupportedException::class);
		$this->parser->parseTableBody('t', "h int DEFAULT X'41'");
	}

	public function testADecimalDefaultInAStringColumnIsTheTextMysqlStores()
	{
		$table = $this->parser->parseTableBody('t', "a varchar(9) DEFAULT 007, b varchar(9) DEFAULT .5, c varchar(9) DEFAULT 1.50, d varchar(9) DEFAULT -007, e varchar(9) DEFAULT -0, f varchar(9) DEFAULT 5., g int DEFAULT 007");

		$this->assertSame(array('7', '0.5', '1.50', '-7', '0', '5', '007'), array_map(function(ColumnDefinition $column)
		{
			return $column->getDefault();
		}, array_values($table->getColumns())));

		$this->expectException(UnsupportedException::class);
		$this->parser->parseTableBody('t', 'h varchar(9) DEFAULT 1e5');
	}

	public function testLegalMysqlTheModelCanHoldIsRead()
	{
		$table = $this->parser->parseTableBody('t', "userId int NOT NULL, a varchar(5) ASCII DEFAULT _utf8mb4'x', b varchar(5) UNICODE DEFAULT N'y', c char(4) BYTE, d varchar(5) DEFAULT 'a' \"b\", e text, f tinyint(1) DEFAULT TRUE, PRIMARY KEY pk (userId), KEY (USERID, a), FULLTEXT KEY ft (e) WITH PARSER ngram");

		$this->assertSame(array('userId', 'a'), $table->getIndex('userid')->getColumnNames(), 'a key names its column in any case');
		$this->assertSame('userId', $table->getIndex('userid')->getName(), 'and takes its name as the column is declared');
		$this->assertSame(array('userId'), $table->getPrimaryKey()->getColumnNames());
		$this->assertSame(array('latin1', 'ucs2'), array($table->getColumn('a')->getCharset(), $table->getColumn('b')->getCharset()));
		$this->assertSame(array('x', 'y', 'ab', '1'), array($table->getColumn('a')->getDefault(), $table->getColumn('b')->getDefault(), $table->getColumn('d')->getDefault(), $table->getColumn('f')->getDefault()));
		$this->assertSame('binary', $table->getColumn('c')->getType(), 'CHAR BYTE is BINARY');
		$this->assertSame(IndexDefinition::KIND_FULLTEXT, $table->getIndex('ft')->getKind());
		$this->assertSame(array('engine' => 'InnoDB', 'data directory' => '/d', 'index directory' => '/i'), $this->parser->parseTableOptions("ENGINE=InnoDB DATA DIRECTORY='/d' INDEX DIRECTORY = '/i'"));
	}

	public function testNamesMatchWhateverTheirCase()
	{
		$table = $this->parser->parseTableBody('t', 'Item_Id int, KEY Item_Key (item_id)');

		$this->assertSame('Item_Id', $table->getColumn('ITEM_ID')->getName());
		$this->assertSame('Item_Key', $table->getIndex('item_key')->getName());

		$this->expectException(InvalidArgumentException::class);
		$this->parser->parseTableBody('t', 'a int, A int');
	}

	public function testABinaryCharacterSetMakesATextColumnBinary()
	{
		$table = $this->parser->parseTableBody('t', "a varchar(5) CHARACTER SET binary, b text CHARSET binary, c enum('x') CHARACTER SET binary");

		$this->assertSame(array('varbinary', null), array($table->getColumn('a')->getType(), $table->getColumn('a')->getCharset()));
		$this->assertSame('blob', $table->getColumn('b')->getType());
		$this->assertSame(array('enum', 'binary'), array($table->getColumn('c')->getType(), $table->getColumn('c')->getCollation()));
		$this->assertSame('blob', $this->parser->parseCreateTable('CREATE TABLE t (a text) DEFAULT CHARSET=binary')->getColumn('a')->getType());
	}

	public function testATableCollationReachesEveryTextColumnThatNamesNoneOfItsOwn()
	{
		$table = $this->parser->parseCreateTable('CREATE TABLE t (a varchar(5), b varchar(5) CHARACTER SET latin1, c varchar(5) COLLATE utf8mb4_general_ci, d int, e json) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin');

		$this->assertSame('utf8mb4_bin', $table->getColumn('a')->getCollation());
		$this->assertNull($table->getColumn('b')->getCollation(), 'a character set of its own brings its own default collation');
		$this->assertSame('utf8mb4_general_ci', $table->getColumn('c')->getCollation());
		$this->assertNull($table->getColumn('d')->getCollation());
		$this->assertNull($table->getColumn('e')->getCollation());
		$this->assertSame('utf8mb4_bin', $this->parser->parseTableBody('t', 'a text', array('collate' => 'utf8mb4_bin'))->getColumn('a')->getCollation());
	}

	public function testMalformedTextIsRefused()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->parser->parseTableBody('t', 'a int SOMETHING');
	}

	/**
	 * Every table e107 declares, in core and in every bundled plugin, parses; and the writer writes back DDL that
	 * parses to the same model, so the writer is a faithful DSL writer.
	 */
	public function testEveryShippedDeclarationParsesAndRoundTripsThroughTheWriter()
	{
		$writer = new MysqlDdlWriter();
		$count = 0;

		foreach($this->shippedDeclarations() as $label => $statement)
		{
			$table = $this->parser->parseCreateTable($statement);
			$again = $this->parser->parseTableBody($table->getName(), $writer->writeBody($table), $table->getOptions());

			$this->assertSame($table->toArray(), $again->toArray(), $label);
			$count++;
		}

		$this->assertGreaterThan(50, $count);
	}

	/**
	 * @return string[] label => CREATE TABLE statement
	 */
	private function shippedDeclarations()
	{
		$files = array_merge(array(e_CORE.'sql/core_sql.php', e_CORE.'sql/extended_country.php'), glob(e_PLUGIN.'*/*_sql.php'));
		$statements = array();

		foreach($files as $file)
		{
			$text = preg_replace('#^<\?php.*?\?>#s', '', file_get_contents($file));

			foreach(SqlLexer::mysql()->splitStatements($text) as $i => $statement)
			{
				$tokens = SqlLexer::mysql()->significantTokens($statement);

				if(!empty($tokens) && strtoupper($tokens[0]['text']) === 'CREATE')
				{
					$statements[basename($file).'#'.$i] = $statement;
				}
			}
		}

		return $statements;
	}

	/**
	 * @param TableDefinition $table
	 * @param string $column
	 * @return array
	 */
	private function defaultOf(TableDefinition $table, $column)
	{
		$definition = $table->getColumn($column);

		return array($definition->getDefaultKind(), $definition->getDefault());
	}

	/**
	 * @param string[] $names
	 * @return string[]
	 */
	private function sorted(array $names)
	{
		sort($names);

		return $names;
	}
}
