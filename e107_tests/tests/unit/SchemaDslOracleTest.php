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
use e107\Database\Schema\Declared\SqlFileCatalogue;
use e107\Database\Schema\Definition\MysqlDdlParser;
use e107\Database\Schema\Definition\MysqlDdlWriter;
use e107\Database\Schema\Introspect\SchemaReader;

/**
 * The schema DSL parser checked against the MySQL server itself: every table e107 declares, built from its verbatim
 * declaration and from that declaration parsed into the neutral model and written back, must come out of the server
 * as the same table. A parser that misread a type, default, key or name would make the two differ, and every engine
 * that renders the model instead of passing the text through would build the wrong table.
 */
class SchemaDslOracleTest extends \Test\Unit
{
	/** @var Materialiser */
	private $materialiser;

	protected function _before()
	{
		$db = e107::getDb();

		if($db->getDriver()->getName() !== 'mysql')
		{
			$this->markTestSkipped('The oracle is the MySQL server');
		}

		$this->materialiser = new Materialiser($db, new SchemaReader($db), MPREFIX);
	}

	protected function _after()
	{
		if($this->materialiser !== null)
		{
			$this->materialiser->sweep();
		}
	}

	public function testEveryShippedDeclarationBuildsTheSameTableVerbatimAndAsParsed()
	{
		require_once(e_HANDLER.'db_verify_class.php');
		$verify = new db_verify();
		$parser = new MysqlDdlParser();
		$writer = new MysqlDdlWriter();
		$catalogue = new SqlFileCatalogue();
		$compared = 0;

		$files = array_merge(array('core' => e_CORE.'sql/core_sql.php'), $this->pluginSchemaFiles());

		foreach($files as $sqlFile => $path)
		{
			foreach($catalogue->parse(file_get_contents($path), $sqlFile) as $declared)
			{
				$resolved = $verify->resolve($declared);
				$model = $parser->parseTableBody($declared->getName(), $declared->getBody());
				$rewritten = new DeclaredTable($sqlFile, $declared->getName(), $writer->writeBody($model), $declared->getDeclaredEngine(), $declared->getDeclaredCharset());

				$verbatim = $this->materialiser->materialise($declared, $resolved['engine'], $resolved['charset']);
				$parsed = $this->materialiser->materialise($rewritten, $resolved['engine'], $resolved['charset']);

				$this->assertTrue($verbatim->equals($parsed), $sqlFile.'/'.$declared->getName().": verbatim\n".print_r($verbatim->toArray(), true)."\nparsed\n".print_r($parsed->toArray(), true));
				$compared++;
			}
		}

		$this->assertGreaterThan(50, $compared);
	}

	public function testLegalMysqlBeyondTheShippedDeclarationsBuildsTheSameTableVerbatimAndAsParsed()
	{
		$parser = new MysqlDdlParser();
		$writer = new MysqlDdlWriter();

		foreach(array(
			'serial'    => 'id SERIAL, n varchar(5), PRIMARY KEY (id)',
			'serialdv'  => 'id bigint unsigned SERIAL DEFAULT VALUE',
			'columnpk'  => 'id bigint unsigned NOT NULL auto_increment PRIMARY KEY, b int UNIQUE KEY UNIQUE',
			'synonyms'  => 'a int1, b int2, c int3, d int4, e int8, f middleint, g float4, h float8, i double precision, j long varbinary, k long varchar, l long',
			'national'  => 'a nvarchar(5), b national char(3), c nchar varchar(4), d character varying(5), e nchar(2), f national varchar(4)',
			'hex'       => "a varchar(5) DEFAULT 0x41, b int DEFAULT 0x41, c varchar(5) DEFAULT X'4142', d bit(8) DEFAULT b'101', e char(1) DEFAULT b'1000001', f bit(8) DEFAULT X'05', g bit(1) DEFAULT b'1'",
			'numeric'   => "b int(10) unsigned zerofill DEFAULT 0x0A, c decimal(5,2) DEFAULT b'11', d float DEFAULT 0x10, e double DEFAULT 1.5e2, f tinyint DEFAULT FALSE",
			'attribute' => "userId int NOT NULL, a varchar(5) ASCII DEFAULT _utf8mb4'x', b varchar(5) UNICODE DEFAULT N'y', c char(4) BYTE, d varchar(5) DEFAULT 'a' \"b\", e text, PRIMARY KEY pk (userId), KEY (USERID, a)",
			'binary'    => "a varchar(5) CHARACTER SET binary, b text CHARSET binary, c enum('x','X') CHARACTER SET binary",
		) as $name => $body)
		{
			$declared = new DeclaredTable('core', 'oracle_'.$name, $body, 'InnoDB', 'utf8mb4');
			$rewritten = new DeclaredTable('core', 'oracle_'.$name, $writer->writeBody($parser->parseTableBody('oracle_'.$name, $body)), 'InnoDB', 'utf8mb4');

			$verbatim = $this->materialiser->materialise($declared, 'InnoDB', 'utf8mb4');
			$parsed = $this->materialiser->materialise($rewritten, 'InnoDB', 'utf8mb4');

			$this->assertTrue($verbatim->equals($parsed), $name.": verbatim\n".print_r($verbatim->toArray(), true)."\nparsed\n".print_r($parsed->toArray(), true));
		}
	}

	/**
	 * @return string[] plugin folder => path of its *_sql.php file
	 */
	private function pluginSchemaFiles()
	{
		$files = array();

		foreach(glob(e_PLUGIN.'*/*_sql.php') as $path)
		{
			$files[basename(dirname($path))] = $path;
		}

		return $files;
	}
}
