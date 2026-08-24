<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Declared\Materialiser;
use e107\Database\Schema\Introspect\SchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Plan\Change\AddColumn;
use e107\Database\Schema\Plan\Change\AddIndex;
use e107\Database\Schema\Plan\Change\CreateTable;
use e107\Database\Schema\Plan\Change\ModifyColumn;
use e107\Database\Schema\Plan\ChangeInterface;
use e107\Database\SqlFragment;

/**
 * The one property the rendering correction rests on: restating a live table with the definitions the server itself
 * wrote for the same declaration changes nothing, so SHOW CREATE TABLE comes back byte-identical.
 *
 * @see \e107\Database\Schema\Declared\Materialiser the capture
 * @see \e107\Database\Schema\Plan\Change\AbstractChange::captured() the fail-closed guard
 */
class SchemaRenderRoundTripTest extends \Test\Unit
{
	use \Helper\SchemaIntent;

	/** Unprefixed name of the live table each case builds, restates, and drops. */
	const LIVE_TABLE = 'dbvroundtrip';

	/** @var \e107\Database\ConnectionInterface */
	private $db;

	/** @var Materialiser */
	private $materialiser;

	protected function _before()
	{
		$this->db = e107::getDb();
		$this->materialiser = new Materialiser($this->db, new SchemaReader($this->db), MPREFIX);

		$this->dropLiveTable();
	}

	protected function _after()
	{
		$this->dropLiveTable();
		$this->materialiser->sweep();
	}

	// --- one shape at a time ----------------------------------------------

	public function testAnUnsignedAutoIncrementColumnKeepsItsRange()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp(
			"widget_id int(10) unsigned NOT NULL auto_increment,
			 PRIMARY KEY (widget_id)"
		);

		$captured = $expected->getColumn('widget_id')->getDdl();

		$this->assertStringContainsStringIgnoringCase('unsigned', $captured, 'UNSIGNED is part of the type and the server states it.');
		$this->assertStringContainsStringIgnoringCase('auto_increment', $captured);

		$before = $this->showCreateLiveTable();
		$mutilated = str_ireplace(' unsigned', '', $captured);

		$this->assertNotEquals($captured, $mutilated, 'The control only means something if it actually changes the definition.');
		$this->assertNotFalse($this->db->execute('ALTER TABLE '.$this->quotedLiveTable().' MODIFY '.$mutilated));

		$this->assertNotEquals(
			$before,
			$this->showCreateLiveTable(),
			'Restating the column without UNSIGNED must be visible, or the byte-identical assertion above would hold for a broken renderer too.'
		);
	}

	public function testAnEmptyStringDefaultSurvivesTheRoundTrip()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp("widget_name varchar(255) NOT NULL default ''");

		$this->assertStringContainsString("DEFAULT ''", $expected->getColumn('widget_name')->getDdl());

		$this->assertContains(
			$expected->getColumn('widget_name')->getDefault(),
			array('', "''"),
			'The vendors disagree about how information_schema reports an empty default, and this system never has to care.'
		);
	}

	public function testEnumMembersKeepTheirCase()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp("widget_flag enum('Yes','No') NOT NULL default 'Yes'");

		$captured = $expected->getColumn('widget_flag')->getDdl();

		$this->assertStringContainsString("enum('Yes','No')", $captured, 'ENUM members are user data; a folded `enum(\'yes\',\'no\')` would rewrite every row.');
		$this->assertStringContainsString("DEFAULT 'Yes'", $captured);
		$this->assertSame("enum('Yes','No')", $expected->getColumn('widget_flag')->getColumnType());
	}

	public function testAnExpressionDefaultIsNotQuoted()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp(
			'widget_changed timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
		);

		$captured = $expected->getColumn('widget_changed')->getDdl();

		$this->assertMatchesRegularExpression('/DEFAULT current_timestamp(\(\))?/i', $captured);
		$this->assertMatchesRegularExpression('/ON UPDATE current_timestamp(\(\))?/i', $captured);
		$this->assertStringNotContainsString(
			"DEFAULT '",
			$captured,
			'MariaDB spells it current_timestamp() and MySQL CURRENT_TIMESTAMP. Quoting either turns a clock into a string.'
		);
	}

	public function testAnExplicitCharacterSetAndCollationSurviveTheRoundTrip()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp(
			'widget_code varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL'
		);

		$captured = $expected->getColumn('widget_code')->getDdl();

		$this->assertStringContainsString('CHARACTER SET latin1', $captured);
		$this->assertStringContainsString('COLLATE latin1_general_ci', $captured);
		$this->assertSame('latin1', $expected->getColumn('widget_code')->getCharset(), 'The table is utf8mb4, so this column keeps its own charset; the reader only nulls the ones that match the table default.');
	}

	public function testACommentContainingACommaAndAnApostropheSurvivesTheRoundTrip()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp(
			"widget_code varchar(20) NOT NULL default '' COMMENT 'it''s, a comma'"
		);

		$captured = $expected->getColumn('widget_code')->getDdl();

		$this->assertStringContainsString("COMMENT 'it''s, a comma'", $captured);
		$this->assertStringNotContainsString("\n", $captured, 'One definition, one line, comma and all.');
		$this->assertSame("it's, a comma", $expected->getColumn('widget_code')->getComment());
	}

	public function testATextColumnAndADecimalSurviveTheRoundTrip()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp(
			"widget_body text NOT NULL,
			 widget_price decimal(10,2) NOT NULL default '0.00'"
		);

		$this->assertStringContainsString('text NOT NULL', $expected->getColumn('widget_body')->getDdl());
		$this->assertSame('text', $expected->getColumn('widget_body')->getColumnType(), 'A text column materialised at the target charset is not widened; CONVERT TO CHARACTER SET is what widens one.');

		$price = $expected->getColumn('widget_price')->getDdl();

		$this->assertStringContainsString('decimal(10,2)', $price);
		$this->assertMatchesRegularExpression(
			"/DEFAULT '?0\\.00'?/",
			$price,
			'MySQL quotes a decimal default and MariaDB does not. Both spellings arrive here already correct for the server that wrote them.'
		);
	}

	public function testAWholeTableOfAwkwardShapesRestatesToItself()
	{
		$expected = $this->assertRestatingEveryColumnIsANoOp($this->awkwardBody());

		$names = array_keys($expected->getIndexes());

		sort($names);

		$this->assertCount(7, $expected->getColumns());
		$this->assertSame(array('PRIMARY', 'widget_body', 'widget_flag', 'widget_name'), $names);

		$before = $this->showCreateLiveTable();

		foreach($expected->getIndexes() as $name => $index)
		{
			if($index->getKind() === 'PRIMARY')
			{
				continue;
			}

			$this->assertNotFalse($this->db->execute('ALTER TABLE '.$this->quotedLiveTable().' DROP INDEX `'.$name.'`'));

			$sql = $this->render(new AddIndex('core', self::LIVE_TABLE, $index));

			$this->assertStringContainsString($index->getDdl(), $sql);
			$this->assertNotFalse($this->db->execute($sql), 'The server should accept its own definition of '.$name.' back: '.$sql);
		}

		$this->assertSame($before, $this->showCreateLiveTable(), 'A dropped index rebuilt from the captured key clause is the same index, prefix lengths and all.');
	}

	// --- the two standing guarantees --------------------------------------

	public function testARenderedCreateTableNeverCarriesAnAutoIncrementCounter()
	{
		$expected = $this->materialise($this->awkwardBody());
		$sql = $this->render(new CreateTable('core', self::LIVE_TABLE, $expected));

		$this->assertDoesNotMatchRegularExpression('/AUTO_INCREMENT\s*=\s*\d/i', $sql, 'The table counter is stripped on capture.');
		$this->assertStringContainsStringIgnoringCase('AUTO_INCREMENT', $sql, 'The column keyword is a schema fact and stays, so the assertion above is about the counter alone.');

		$this->assertNotFalse($this->db->execute($sql), 'The rendered statement is one the server has already accepted once: '.$sql);
		$this->assertNotFalse($this->db->execute(
			'INSERT INTO '.$this->quotedLiveTable().' (widget_name, widget_body) VALUES (\'a\',\'a\'),(\'b\',\'b\'),(\'c\',\'c\')'
		), 'Every NOT NULL column without a default is given one, so this holds under strict mode too.');

		$this->assertMatchesRegularExpression(
			'/AUTO_INCREMENT\s*=\s*\d/i',
			$this->showCreateLiveTable(),
			'This server does state a counter for a table with rows, so the strip is a real removal rather than a vacuous assertion.'
		);
	}

	public function testAChangeWithNoCapturedDefinitionThrowsRatherThanRenderingAnything()
	{
		$this->createLiveTable($this->awkwardBody());

		$live = (new SchemaReader($this->db))->read(MPREFIX.self::LIVE_TABLE);

		$this->assertInstanceOf(TableSchema::class, $live);
		$this->assertNull($live->getColumn('widget_id')->getDdl(), 'A live read carries no DDL, whoever built the table.');
		$this->assertNull($live->getCreateBody());

		$this->assertRendersNothing(new ModifyColumn('core', self::LIVE_TABLE, $live->getColumn('widget_id')));
		$this->assertRendersNothing(new AddColumn('core', self::LIVE_TABLE, $live->getColumn('widget_name')));
		$this->assertRendersNothing(new AddIndex('core', self::LIVE_TABLE, $live->getIndex('widget_name')));
		$this->assertRendersNothing(new CreateTable('core', self::LIVE_TABLE, $live));
	}

	// --- the property -----------------------------------------------------

	/**
	 * @param string $body declared CREATE TABLE body, spelled as a *_sql.php file spells it.
	 * @return TableSchema the materialised schema, for a case to assert on.
	 */
	private function assertRestatingEveryColumnIsANoOp($body)
	{
		$expected = $this->materialise($body);

		$this->createLiveTable($body);

		$this->assertSame(
			$this->render(new CreateTable('core', self::LIVE_TABLE, $expected)),
			$this->showCreateLiveTable(),
			'The premise, and a round trip in its own right: the scratch table and the live table were built from the same '
			.'declaration, so the CREATE TABLE rendered from the capture is the one the server states for the live table.'
		);

		$before = $this->showCreateLiveTable();

		$this->assertNotEmpty($expected->getColumns(), 'A body with no columns would make the loop below vacuous.');

		foreach($expected->getColumns() as $name => $column)
		{
			$sql = $this->render(new ModifyColumn('core', self::LIVE_TABLE, $column));

			$this->assertStringContainsString($column->getDdl(), $sql, 'The change splices the server\'s own definition instead of rendering one of its own.');
			$this->assertNotFalse($this->db->execute($sql), 'The server should accept its own definition of '.$name.' back: '.$sql);
		}

		$this->assertSame(
			$before,
			$this->showCreateLiveTable(),
			'Restating every column as the server states it must change nothing at all.'
		);

		return $expected;
	}

	/**
	 * @param ChangeInterface $change one built from a schema nobody materialised.
	 * @return void
	 */
	private function assertRendersNothing(ChangeInterface $change)
	{
		$rendered = null;
		$thrown = null;

		try
		{
			$rendered = $change->toSql($this->db->schema());
		}
		catch(RuntimeException $e)
		{
			$thrown = $e;
		}

		$this->assertNull($rendered, get_class($change).' must not render anything at all from a schema with no captured DDL.');
		$this->assertInstanceOf('RuntimeException', $thrown, get_class($change).' must throw rather than guess.');
		$this->assertStringContainsString('captured', $thrown->getMessage());
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * A declaration carrying every shape the rendering correction exists for, in one table.
	 *
	 * @return string
	 */
	private function awkwardBody()
	{
		return "widget_id int(10) unsigned NOT NULL auto_increment,
			widget_name varchar(255) NOT NULL default '',
			widget_flag enum('Yes','No') NOT NULL default 'Yes',
			widget_changed timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			widget_code varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'it''s, a comma',
			widget_body text NOT NULL,
			widget_price decimal(10,2) NOT NULL default '0.00',
			PRIMARY KEY (widget_id),
			UNIQUE KEY widget_name (widget_name(20)),
			KEY widget_flag (widget_name(10),widget_flag),
			FULLTEXT KEY widget_body (widget_body)";
	}

	/**
	 * @param string $body declared CREATE TABLE body.
	 * @return DeclaredTable
	 */
	private function declared($body)
	{
		return new DeclaredTable('core', self::LIVE_TABLE, $body);
	}

	/**
	 * The engine and character set both tables of a case are built with.
	 *
	 * @param string $body declared CREATE TABLE body.
	 * @return array ['engine' => string, 'charset' => string]
	 */
	private function intentFor($body)
	{
		return $this->schemaIntentFor($this->declared($body));
	}

	/**
	 * @param string $body declared CREATE TABLE body.
	 * @return TableSchema carrying the server's own DDL for every part of it.
	 */
	private function materialise($body)
	{
		$intent = $this->intentFor($body);

		return $this->materialiser->materialise($this->declared($body), $intent['engine'], $intent['charset']);
	}

	/**
	 * Builds the live table from the declared body itself, never from the capture, so the two arrive by different routes.
	 *
	 * @param string $body
	 * @return void
	 */
	private function createLiveTable($body)
	{
		$this->dropLiveTable();

		$ddl = $this->db->schema()->buildCreateTablePhysicalRaw(
			self::LIVE_TABLE,
			SqlFragment::raw($body),
			$this->intentFor($body)
		);

		$this->assertNotFalse($this->db->execute($ddl), 'The declared body should build a live table: '.$ddl);
	}

	/**
	 * @return void
	 */
	private function dropLiveTable()
	{
		$this->db->execute('DROP TABLE IF EXISTS '.$this->quotedLiveTable());
	}

	/**
	 * @return string the server's own CREATE TABLE statement for the live table.
	 */
	private function showCreateLiveTable()
	{
		$create = $this->db->schema()->getCreateTablePhysical(self::LIVE_TABLE);

		$this->assertIsString($create, 'The live table should exist and state its own definition.');

		return $create;
	}

	/**
	 * @param ChangeInterface $change
	 * @return string
	 */
	private function render(ChangeInterface $change)
	{
		return $change->toSql($this->db->schema());
	}

	/**
	 * @return string backtick-quoted physical name of the live table.
	 */
	private function quotedLiveTable()
	{
		return '`'.MPREFIX.self::LIVE_TABLE.'`';
	}
}
