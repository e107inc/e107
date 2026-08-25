<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - DB Verify Class
 *
 * $URL: /cvs_backup/e107_0.8/e107_admin/db_verify.php,v $
 * $Revision: 12255 $
 * $Id: 2011-06-07 17:16:42 -0700 (Tue, 07 Jun 2011) $
 * $Author: e107coders $
 *
*/

use e107\Database\Exception\QueryException;
use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Declared\EngineCharsetResolverInterface;
use e107\Database\Schema\Declared\Materialiser;
use e107\Database\Schema\Declared\SqlFileCatalogue;
use e107\Database\Schema\Diff\SchemaDiffer;
use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Plan\Change\AddColumn;
use e107\Database\Schema\Plan\Change\AddIndex;
use e107\Database\Schema\Plan\Change\ConvertTable;
use e107\Database\Schema\Plan\Change\CreateTable;
use e107\Database\Schema\Plan\Change\DropIndex;
use e107\Database\Schema\Plan\Change\ModifyColumn;
use e107\Database\Schema\Plan\ChangeInterface;
use e107\Database\Schema\Plan\FixPlan;
use e107\Database\Schema\Plan\PlanBuilder;
use e107\Database\SqlFragment;

if(!defined('e107_INIT'))
{
	exit;
}

e107::includeLan(e_LANGUAGEDIR . e_LANGUAGE . '/admin/lan_db_verify.php');


/**
 *
 */
class db_verify implements EngineCharsetResolverInterface
{

	var    $backUrl       = "";
	public $sqlFileTables = array();
	const MOST_PREFERRED_STORAGE_ENGINE = "InnoDB";
	const MOST_PREFERRED_CHARSET        = "utf8mb4";
	public $availableStorageEngines = array(self::MOST_PREFERRED_STORAGE_ENGINE);

	var     $sqlLanguageTables = array();
	var     $results           = array();
	var     $indices           = array(); // array(0) - Issue?
	var     $fixList           = array();
	private $currentTable      = null;
	private $internalError     = false;

	/**
	 * Aliases for preferred storage engines when provided the key
	 *
	 * @var string[][]
	 */
	private $storageEnginePreferenceMap = [
		"MyISAM" => [self::MOST_PREFERRED_STORAGE_ENGINE, "Aria", "Maria", "MyISAM"],
		"Aria"   => ["Aria", "Maria", "MyISAM"],
		"InnoDB" => ["InnoDB", "XtraDB"],
		"XtraDB" => ["XtraDB", "InnoDB"],
	];

	/**
	 * Engines to fall back through for a table that declares a FULLTEXT index
	 * when nothing in its own preference order can carry one here.
	 *
	 * Kept apart from the preference map on purpose. Widening that map would
	 * also change what a table without a FULLTEXT index gets, and an engine
	 * nobody can supply is meant to be refused rather than silently swapped.
	 *
	 * @var string[]
	 */
	private $fulltextStorageEngineFallback = ["Aria", "Maria", "MyISAM"];

	/** @var array|null Memoised server capability probe; see getServerCapabilities() */
	private $serverCapabilities = null;

	/** @var e_search_fulltext_indexer|null */
	private $fulltextIndexer = null;

	/** @var array Derived index definitions keyed by table then index name */
	private $derivedIndexDefinitions = array();

	/** @var SqlFileCatalogue|null the one parser for *_sql.php text */
	private $sqlFileCatalogue = null;

	/** @var SchemaReader|null reads both sides of every comparison */
	private $schemaReader = null;

	/** @var Materialiser|null builds a declared body as a scratch table */
	private $materialiser = null;

	/** @var SchemaDiffer|null */
	private $schemaDiffer = null;

	/** @var PlanBuilder|null */
	private $planBuilder = null;

	/** @var bool whether leaked scratch tables have been swept for this instance */
	private $scratchSwept = false;

	/** @var TableDiff[] keyed by table name, lan_<language>_ prefixed where applicable */
	private $tableDiffs = array();

	/** @var array table name => ['engine' => string, 'charset' => string] */
	private $intendedByTable = array();

	/** @var FixPlan|null every change {@see compileResults()} has planned */
	private $fixPlan = null;

	/** @var TableSchema[] materialised declarations, keyed by file, table, engine and charset */
	private $expectedSchemas = array();

	/** @var TableSchema[] the same declarations without their derived indexes, keyed alike */
	private $declaredSchemas = array();

	/** @var array logical table name => names of the derived indexes the declaration covers */
	private $disownedIndexes = array();

	var $fieldTypes = array('time', 'timestamp', 'datetime', 'year', 'tinyblob', 'blob',
		'mediumblob', 'longblob', 'tinytext', 'mediumtext', 'longtext', 'text', 'date', 'json');

	var $fieldTypeNum = array('bit', 'tinyint', 'smallint', 'mediumint', 'integer', 'int', 'bigint',
		'real', 'double', 'float', 'decimal', 'numeric', 'varchar', 'char', 'binary', 'varbinary', 'enum', 'set');

	const STATUS_TABLE_OK                       = 0x0;
	const STATUS_TABLE_MISSING                  = 0x1 << 1;
	const STATUS_TABLE_MISMATCH_STORAGE_ENGINE  = 0x1 << 2;
	const STATUS_TABLE_MISMATCH_DEFAULT_CHARSET = 0x1 << 3;

	var $modes = array(
		'missing_table'   => 'create',
		'mismatch'        => 'alter',
		'missing_field'   => 'insert',
		'missing_index'   => 'index',
		'mismatch_index'  => '', // TODO
		'redundant_index' => 'indexdrop',
	);

	var $errors = array();

	const cachetag = 'Dbverify';

	/**
	 * Setup
	 */
	/**
	 * @param bool $init false to skip {@see init()}, which reads preferences and
	 *                   therefore needs the core tables to exist. The installer
	 *                   consults this class while creating those tables, so it
	 *                   asks for an object that can answer engine and character
	 *                   set questions and nothing else.
	 */
	function __construct($init = true)
	{


		$this->backUrl = e_SELF;

		if($init)
		{
			$this->init();
		}
	}

	/**
	 * @return bool
	 */
	public function clearCache()
	{

		return e107::getCache()->clear(self::cachetag, true);

	}

	/**
	 * Get the FULLTEXT indexer instance
	 * @return e_search_fulltext_indexer
	 */
	protected function getFulltextIndexer()
	{
		if($this->fulltextIndexer === null)
		{
			require_once(e_HANDLER . 'e_search_fulltext_indexer_class.php');
			$this->fulltextIndexer = new e_search_fulltext_indexer();
		}

		return $this->fulltextIndexer;
	}

	/**
	 * Get derived FULLTEXT indexes for a table from e_search configurations
	 *
	 * Also stores the definitions in $derivedIndexDefinitions for use by getFixQuery().
	 *
	 * @param string $tableName Table name without prefix
	 * @return array Index definitions compatible with getIndex() output
	 */
	protected function getSearchFieldIndexes($tableName)
	{
		$indexes = $this->getFulltextIndexer()->getIndexesForTable($tableName);

		// Store definitions for use by getFixQuery()
		if(!empty($indexes))
		{
			if(!isset($this->derivedIndexDefinitions[$tableName]))
			{
				$this->derivedIndexDefinitions[$tableName] = array();
			}
			$this->derivedIndexDefinitions[$tableName] = array_merge(
				$this->derivedIndexDefinitions[$tableName],
				$indexes
			);
		}

		return $indexes;
	}

	/**
	 * @return array
	 */
	private function load()
	{

		$mes = e107::getMessage();
		$pref = e107::getPref();

		$ret = array();

		$core_data = file_get_contents(e_CORE . 'sql/core_sql.php');
		$ret['core'] = $this->getSqlFileTables($core_data);


		if(!empty($pref['e_sql_list']))
		{
			foreach($pref['e_sql_list'] as $path => $file)
			{
				$filename = e_PLUGIN . $path . '/' . $file . '.php';
				if(is_readable($filename))
				{
					$id = str_replace('_sql', '', $file);
					$data = file_get_contents($filename);
					$this->currentTable = $id;
					$ret[$id] = $this->getSqlFileTables($data);
					unset($data);
				}
				else
				{
					$message = str_replace("[x]", $filename, DBVLAN_22);
					$mes->add($message, E_MESSAGE_WARNING);
				}
			}
		}

		return $ret;

	}


	/**
	 * Permissive field validation
	 *
	 * @deprecated v2.4.0 Every rule here cancels out once both sides are read from one server through {@see SchemaReader}.
	 */
	private function diffStructurePermissive($expected, $actual)
	{

		$expected['default'] = isset($expected['default']) ? $expected['default'] : '';
		$actual['default'] = isset($actual['default']) ? $actual['default'] : '';

		if($expected['type'] === 'JSON' && $actual['type'] !== 'JSON') // Fix for JSON alias MySQL 5.7+
		{
			$expected['type'] = 'LONGTEXT';
		}

		// Permit actual text types that default to null even when
		// expected does not explicitly default to null
		if(
			0 === strcasecmp($expected['type'], $actual['type']) &&
			1 === preg_match('/[A-Z]*TEXT/i', $expected['type']) &&
			0 === strcasecmp($actual['default'], "DEFAULT NULL")
		)
		{
			$expected['default'] = $actual['default'];
		}

		// Loosely typed default value for numeric types
		if(1 === preg_match('/([A-Z]*INT|NUMERIC|DEC|FIXED|FLOAT|REAL|DOUBLE)/i', $expected['type']))
		{
			$expected['default'] = preg_replace("/DEFAULT '(\d*\.?\d*)'/i", 'DEFAULT $1', $expected['default']);
			$actual['default'] = preg_replace("/DEFAULT '(\d*\.?\d*)'/i", 'DEFAULT $1', $actual['default']);
		}

		/**
		 * Display width specification for integer data types was deprecated in MySQL 8.0.17
		 *
		 * @see https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-19.html
		 */
		if(1 === preg_match('/([A-Z]*INT)/i', $expected['type']))
		{
			$expected['value'] = '';
			$actual['value'] = '';
		}

		// Correct difference on CREATE TABLE statement between MariaDB and MySQL
		if(1 === preg_match('/(DATE|DATETIME|TIMESTAMP|TIME|YEAR)/i', $expected['default']))
		{
			$expected['default'] = preg_replace("/CURRENT_TIMESTAMP\(\)/i", 'CURRENT_TIMESTAMP', $expected['default']);
			$actual['default'] = preg_replace("/CURRENT_TIMESTAMP\(\)/i", 'CURRENT_TIMESTAMP', $actual['default']);
		}

		return array_diff_assoc($expected, $actual);
	}

	/**
	 * Main Routine for checking and rendering results.
	 */
	function verify()
	{

		if(!empty($_POST['runfix']))
		{
			$this->runFix($_POST['fix']);
		}

		if(!empty($_POST['verify_table']))
		{
			$this->runComparison($_POST['verify_table']);
		}
		else
		{
			$this->renderTableSelect();
		}

	}


	/**
	 * @param $fileArray
	 * @return void
	 */
	function runComparison($fileArray)
	{

		$mes = e107::getMessage();

		foreach($fileArray as $tab)
		{
			$this->compare($tab);
			foreach($this->sqlLanguageTables as $lng => $lantab)
			{
				$this->compare($tab, $lng);
			}
		}

		if($this->internalError === true)
		{
			$mes->add(defset('DBVLAN_RESULT_INCOMPLETE', 'Some tables could not be checked, so this result is incomplete. Enable debug mode for the reason.'), E_MESSAGE_WARNING);
		}

		if($cnt = $this->errors())
		{
			$message = str_replace("[x]", $cnt, DBVLAN_26); // Found [x] issues.
			$mes->add($message, E_MESSAGE_WARNING);
			$this->renderResults($fileArray);
		}
		else
		{
			if($this->internalError === false)
			{
				$mes->addSuccess(DBLAN_111);
				$mes->addSuccess("<a class='btn btn-primary' href='" . $this->backUrl . "'>" . LAN_BACK . "</a>");
			}


			//$debug = "<pre>".print_r($this->results,TRUE)."</pre>";
			//$mes->add($debug,E_MESSAGE_DEBUG);	
			//$text .= "<div class='buttons-bar center'>".$frm->admin_button('back', DBVLAN_17, 'back')."</div>";
			echo $mes->render();
			//	$ns->tablerender("Okay",$mes->render().$text);
		}

	}

	//	$this->sqlTables = $this->sqlTableList();

	//	print_a($this->tables);
	// $this->renderTableSelect();

	//	print_a($field);
	//	print_a($match[2]);
	// echo "<pre>".$sql_data."</pre>";

	/**
	 * Check core tables and installed plugin tables
	 *
	 * @param $exclude - array of plugins to exclude.
	 */
	function compareAll($exclude = null)
	{

		if(is_array($exclude))
		{
			foreach($exclude as $val)
			{
				unset($this->sqlFileTables[$val]);
			}
		}

		$dtables = array_keys($this->sqlFileTables);

		foreach($dtables as $tb)
		{
			$this->compare($tb);
		}

		if(!empty($this->sqlLanguageTables)) // language tables. 
		{
			foreach($this->sqlLanguageTables as $lng => $lantab)
			{
				foreach($dtables as $tb)
				{
					$this->compare($tb, $lng);
				}
			}
		}

	}


	/**
	 * Compare every table one schema file declares against the live database.
	 *
	 * @param string $selection 'core' or a plugin folder, keying {@see $sqlFileTables}.
	 * @param string $language language whose lan_<language>_ tables to compare; a language table that does not exist is not an error.
	 * @return false|void false when the selection declares nothing to compare.
	 */
	public function compare($selection, $language = '')
	{

		$this->currentTable = $selection;

		if(!isset($this->sqlFileTables[$selection])) // doesn't have an SQL file.
		{
			// e107::getMessage()->addDebug("No SQL File for ".$selection);
			return false;
		}

		if(!is_array($this->sqlFileTables[$selection]) || empty($this->sqlFileTables[$selection]['tables']))
		{
			//$this->internalError = true;
			e107::getMessage()->addDebug("Couldn't read table data for " . $selection);

			return false;
		}

		$declared = array();
		$physical = array();

		foreach($this->sqlFileTables[$selection]['tables'] as $key => $tbl)
		{
			$table = $this->declaredTable($selection, $key);

			if($table === null)
			{
				continue;
			}

			$declared[$key] = $table;
			$physical[$key] = MPREFIX . ($language ? 'lan_' . $language . '_' : '') . $table->getName();
		}

		try
		{
			$live = $this->schemaReader()->readMany($physical);
		}
		catch(Exception $e)
		{
			e107::getMessage()->addDebug('Could not read the live schema for ' . $selection . ': ' . $e->getMessage());
			$this->internalError = true;

			return false;
		}

		foreach($declared as $key => $table)
		{
			$actual = isset($live[$physical[$key]]) ? $live[$physical[$key]] : null;

			if($actual === null && $language)
			{
				continue;
			}

			$logical = $language ? 'lan_' . $language . '_' . $table->getName() : $table->getName();

			try
			{
				$compared = $this->diffFor($table, $selection, $logical, $actual);
			}
			catch(Exception $e)
			{
				e107::getMessage()->addDebug('Could not verify ' . $logical . ': ' . $e->getMessage());
				$this->internalError = true;
				$this->projectUnverifiable($logical, $selection);

				continue;
			}

			$this->intendedByTable[$logical] = $compared['intended'];
			$this->tableDiffs[$logical] = $compared['diff'];
			$this->projectDiff($compared['diff']);
		}

	}


	/**
	 * @param string $sqlFile key of {@see $sqlFileTables}.
	 * @param int|string $key ordinal within that file.
	 * @return DeclaredTable|null null when the file has no such declaration.
	 */
	private function declaredTable($sqlFile, $key)
	{

		$file = $this->sqlFileTables[$sqlFile];

		if(!isset($file['tables'][$key]) || !isset($file['data'][$key]) || trim((string) $file['tables'][$key]) === '')
		{
			return null;
		}

		return new DeclaredTable(
			$sqlFile,
			$file['tables'][$key],
			$file['data'][$key],
			isset($file['engine'][$key]) ? $file['engine'][$key] : null,
			isset($file['charset'][$key]) ? $file['charset'][$key] : null
		);
	}


	/**
	 * The engine and character set this server should build a declared table with, derived indexes included.
	 *
	 * @param DeclaredTable $table
	 * @param TableSchema|null $live the table as it stands, when it exists; one already at the preferred character set keeps it. Untyped because PHP 5.6 and 8.4 disagree on how to spell a nullable parameter.
	 * @return array ['engine' => string, 'charset' => string]
	 * @throws InvalidArgumentException when $live is neither
	 */
	public function resolve(DeclaredTable $table, $live = null)
	{

		if($live !== null && !$live instanceof TableSchema)
		{
			throw new InvalidArgumentException('db_verify::resolve() takes the live table as a TableSchema or null, ' . gettype($live) . ' given.');
		}

		$fields = $this->getFields($table->getBody());
		$indexes = $this->getIndex($table->getBody());

		$derived = $this->getSearchFieldIndexes($table->getName());

		if(!empty($derived))
		{
			$indexes = array_merge($indexes, $derived);
		}

		return $this->intendedEngineAndCharset(
			$fields,
			$indexes,
			$table->getDeclaredEngine(),
			$table->getDeclaredCharset(),
			($live === null) ? null : $this->liveKeyProof($live)
		);
	}


	/**
	 * What a live table proves about the keys this server accepts: the character set its indexed character columns stand at, the widest such key in characters, and the engine holding it.
	 *
	 * @param TableSchema $live
	 * @return array|null ['engine' => string, 'charset' => string, 'widestIndexChars' => int]; null when the table has no indexed character column, or they do not all share one character set.
	 */
	private function liveKeyProof(TableSchema $live)
	{

		$columns = $live->getColumns();
		$charset = null;
		$widest = 0;

		foreach($live->getIndexes() as $index)
		{
			if($index->getKind() === IndexSchema::KIND_FULLTEXT || $index->getKind() === IndexSchema::KIND_SPATIAL)
			{
				continue;
			}

			$chars = 0;

			foreach($index->getParts() as $part)
			{
				$name = $part->getColumnName();

				if(!isset($columns[$name]) || !preg_match('/^(?:var)?char\((\d+)\)/i', $columns[$name]->getColumnType(), $m))
				{
					continue;
				}

				$columnCharset = ($columns[$name]->getCharset() === null) ? $live->getCharset() : $columns[$name]->getCharset();

				if($charset === null)
				{
					$charset = $columnCharset;
				}
				elseif($charset !== $columnCharset)
				{
					return null;
				}

				$subPart = $part->getSubPart();
				$chars += ($subPart !== null && (int) $subPart > 0) ? min((int) $subPart, (int) $m[1]) : (int) $m[1];
			}

			$widest = max($widest, $chars);
		}

		if($charset === null)
		{
			return null;
		}

		return array('engine' => $live->getEngine(), 'charset' => $charset, 'widestIndexChars' => $widest);
	}


	/**
	 * Settle the engine and character set of every declared table against the database as it stands, without comparing or building anything.
	 *
	 * @return array table => character set, as {@see getIntendedCharsets()} returns it
	 * @throws Exception when the live schema cannot be read
	 */
	public function resolveAll()
	{

		foreach($this->sqlFileTables as $sqlFile => $file)
		{
			if(!is_array($file) || empty($file['tables']))
			{
				continue;
			}

			$declared = array();
			$physical = array();

			foreach($file['tables'] as $key => $tbl)
			{
				$table = $this->declaredTable($sqlFile, $key);

				if($table === null)
				{
					continue;
				}

				$declared[$key] = $table;
				$physical[$key] = MPREFIX . $table->getName();
			}

			if(empty($declared))
			{
				continue;
			}

			$live = $this->schemaReader()->readMany($physical);

			foreach($declared as $key => $table)
			{
				$actual = isset($live[$physical[$key]]) ? $live[$physical[$key]] : null;
				$this->intendedByTable[$table->getName()] = $this->resolve($table, $actual);
			}
		}

		return $this->getIntendedCharsets();
	}


	/**
	 * The engine and character set one declared table was compared under, and the difference from the live shape given.
	 *
	 * @param DeclaredTable $table
	 * @param string $sqlFile 'core' or the plugin folder that declared it.
	 * @param string $logical table name as it is reported, lan_<language>_ prefix included.
	 * @param TableSchema|null $actual the live shape, or null when the table is absent.
	 * @return array ['intended' => ['engine' => string, 'charset' => string], 'diff' => TableDiff]
	 * @throws Exception when the server will not build the declared body.
	 */
	private function diffFor(DeclaredTable $table, $sqlFile, $logical, $actual)
	{

		$intended = $this->resolve($table, $actual);
		$expected = $this->expectedSchema($table, $intended);

		$this->disownedIndexes[$logical] = array_map('strval', array_keys($this->derivedIndexPartition($table, $intended)['redundant']));

		return array(
			'intended' => $intended,
			'diff'     => $this->schemaDiffer()->diff($sqlFile, $expected, $actual, $logical, $this->disownedIndexes[$logical]),
		);
	}


	/**
	 * The declared shape of a table, as this server builds it, carrying every e_search FULLTEXT index the declaration does not already cover.
	 *
	 * Memoised on the table and the engine and character set given.
	 *
	 * @param DeclaredTable $table
	 * @param array $intended ['engine' => string, 'charset' => string], neither half null.
	 * @return TableSchema
	 * @throws QueryException when the server refuses the declared body.
	 */
	private function expectedSchema(DeclaredTable $table, array $intended)
	{

		$key = $this->schemaKey($table, $intended);

		if(!isset($this->expectedSchemas[$key]))
		{
			$surviving = $this->derivedIndexPartition($table, $intended)['surviving'];

			$this->expectedSchemas[$key] = empty($surviving)
				? $this->declaredSchema($table, $intended)
				: $this->materialiser()->materialise(
					$this->withDerivedIndexes($table, $surviving),
					isset($intended['engine']) ? $intended['engine'] : null,
					isset($intended['charset']) ? $intended['charset'] : null
				);
		}

		return $this->expectedSchemas[$key];
	}


	/**
	 * The declared body alone, with no derived index appended, memoised as {@see expectedSchema()} is.
	 *
	 * @param DeclaredTable $table
	 * @param array $intended ['engine' => string, 'charset' => string]
	 * @return TableSchema
	 * @throws QueryException when the server refuses the declared body.
	 */
	private function declaredSchema(DeclaredTable $table, array $intended)
	{

		$key = $this->schemaKey($table, $intended);

		if(!isset($this->declaredSchemas[$key]))
		{
			$this->declaredSchemas[$key] = $this->materialiser()->materialise(
				$table,
				isset($intended['engine']) ? $intended['engine'] : null,
				isset($intended['charset']) ? $intended['charset'] : null
			);
		}

		return $this->declaredSchemas[$key];
	}


	/**
	 * Memoisation key of one declaration built with one engine and character set.
	 *
	 * @param DeclaredTable $table
	 * @param array $intended ['engine' => string, 'charset' => string]
	 * @return string
	 */
	private function schemaKey(DeclaredTable $table, array $intended)
	{

		$engine = isset($intended['engine']) ? $intended['engine'] : null;
		$charset = isset($intended['charset']) ? $intended['charset'] : null;

		return $table->getSqlFile() . "\0" . $table->getName() . "\0" . $engine . "\0" . $charset;
	}


	/**
	 * The table's e_search FULLTEXT indexes sorted into the ones worth building and the ones the declaration has made redundant, both keyed by index name.
	 *
	 * Redundant means the declared body already carries a FULLTEXT index over the same columns in the same order, whatever it is called.
	 *
	 * @param DeclaredTable $table
	 * @param array $intended ['engine' => string, 'charset' => string]
	 * @return array ['surviving' => array, 'redundant' => array]
	 * @throws QueryException when the server refuses the declared body.
	 */
	private function derivedIndexPartition(DeclaredTable $table, array $intended)
	{

		$partition = array('surviving' => array(), 'redundant' => array());
		$derived = $this->getSearchFieldIndexes($table->getName());

		if(empty($derived))
		{
			return $partition;
		}

		$declared = $this->declaredSchema($table, $intended);

		foreach($derived as $key => $definition)
		{
			$name = empty($definition['field']) ? $key : (string) $definition['field'];
			$slot = ($this->declaredIndexCovering($declared, $definition) === null) ? 'surviving' : 'redundant';

			$partition[$slot][$name] = $definition;
		}

		return $partition;
	}


	/**
	 * @param TableSchema $declared the declared body, materialised.
	 * @param array $definition one entry of {@see getSearchFieldIndexes()}.
	 * @return string|null Name of the declared FULLTEXT index covering the same columns, null when the declaration has none.
	 */
	private function declaredIndexCovering(TableSchema $declared, array $definition)
	{

		$type = empty($definition['type']) ? '' : strtoupper((string) $definition['type']);

		if($type !== IndexSchema::KIND_FULLTEXT)
		{
			return null;
		}

		$columns = self::indexColumnList(isset($definition['keyname']) ? $definition['keyname'] : '');

		if(empty($columns))
		{
			return null;
		}

		foreach($declared->getIndexes() as $name => $index)
		{
			if($index->getKind() === IndexSchema::KIND_FULLTEXT && $index->getColumnNames() === $columns)
			{
				return $name;
			}
		}

		return null;
	}


	/**
	 * The column names of a legacy index definition's `keyname`, in order.
	 *
	 * @param string $keyname one column, or several separated by commas.
	 * @return string[]
	 */
	private static function indexColumnList($keyname)
	{

		$columns = array();

		foreach(explode(',', (string) $keyname) as $column)
		{
			$column = trim($column);

			if($column !== '')
			{
				$columns[] = $column;
			}
		}

		return $columns;
	}


	/**
	 * The declaration with the given e_search FULLTEXT indexes appended to the body.
	 *
	 * A definition whose index name or column list falls outside `[A-Za-z0-9_,]` is dropped rather than appended.
	 *
	 * @param DeclaredTable $table
	 * @param array $derived definitions to append, from {@see derivedIndexPartition()}.
	 * @return DeclaredTable the same table when there is nothing to append.
	 */
	private function withDerivedIndexes(DeclaredTable $table, array $derived)
	{

		if(empty($derived))
		{
			return $table;
		}

		$known = array('FULLTEXT', 'UNIQUE', 'SPATIAL', 'INDEX', 'KEY');
		$extra = array();

		foreach($derived as $def)
		{
			$field = isset($def['field']) ? (string) $def['field'] : '';
			$keyname = isset($def['keyname']) ? (string) $def['keyname'] : '';
			$type = empty($def['type']) ? 'INDEX' : strtoupper((string) $def['type']);

			if(!preg_match('/^[A-Za-z0-9_]+$/D', $field) || !preg_match('/^[A-Za-z0-9_,]+$/D', $keyname))
			{
				continue;
			}

			if(!in_array($type, $known, true))
			{
				continue;
			}

			$extra[] = $type . ' `' . $field . '` (' . $keyname . ')';
		}

		if(empty($extra))
		{
			return $table;
		}

		$body = rtrim(rtrim($table->getBody()), ',') . ",\n  " . implode(",\n  ", $extra);

		return new DeclaredTable(
			$table->getSqlFile(),
			$table->getName(),
			$body,
			$table->getDeclaredEngine(),
			$table->getDeclaredCharset()
		);
	}


	/**
	 * Project one {@see TableDiff} into the legacy $results, $indices and $errors arrays the admin screen, {@see errors()} and {@see compileResults()} read.
	 *
	 * All three gain an entry for every compared table, sound ones included.
	 *
	 * @param TableDiff $diff
	 * @return void
	 */
	private function projectDiff(TableDiff $diff)
	{

		$table = $diff->getTableName();
		$file = $diff->getSqlFile();

		$entry = array('_status' => self::STATUS_TABLE_OK, '_file' => $file);
		$status = self::STATUS_TABLE_OK;

		if($diff->isMissing())
		{
			$status |= self::STATUS_TABLE_MISSING;
		}

		$engineChange = $diff->getEngineChange();

		if($engineChange !== null)
		{
			$status |= self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE;
			$entry['_valid_' . self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE] = $engineChange['expected'];
			$entry['_invalid_' . self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE] = $engineChange['actual'];
		}

		$charsetChange = $diff->getCharsetChange();

		if($charsetChange !== null)
		{
			$status |= self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET;
			$entry['_valid_' . self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET] = $charsetChange['expected'];
			$entry['_invalid_' . self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET] = $charsetChange['actual'];
		}

		$entry['_status'] = $status;

		$this->errors[$table] = $entry;
		$this->results[$table] = $this->projectColumns($diff);
		$this->indices[$table] = $this->projectIndexes($diff);
	}


	/**
	 * A table nothing could be said about: recorded as sound, in all three legacy arrays.
	 *
	 * @param string $table
	 * @param string $file
	 * @return void
	 */
	private function projectUnverifiable($table, $file)
	{

		unset($this->tableDiffs[$table]);

		$this->errors[$table] = array('_status' => self::STATUS_TABLE_OK, '_file' => $file);
		$this->results[$table] = array();
		$this->indices[$table] = array();
	}


	/**
	 * Every declared column of a table, keyed by name, in the legacy $results shape.
	 *
	 * A live column nothing declares is not reported.
	 *
	 * @param TableDiff $diff
	 * @return array
	 */
	private function projectColumns(TableDiff $diff)
	{

		$expected = $diff->getExpectedTable();

		if($diff->isMissing() || !$expected instanceof TableSchema)
		{
			return array();
		}

		$file = $diff->getSqlFile();
		$missing = $diff->getMissingColumns();
		$modified = $diff->getModifiedColumns();
		$fields = array();

		foreach($expected->getColumns() as $name => $column)
		{
			if(isset($missing[$name]))
			{
				$fields[$name] = array(
					'_status' => 'missing_field',
					'_valid'  => $this->legacyField($column),
					'_file'   => $file,
				);

				continue;
			}

			if(!isset($modified[$name]))
			{
				$fields[$name] = array('_status' => 'ok');

				continue;
			}

			$valid = $this->legacyField($modified[$name]->getExpected());
			$invalid = $this->legacyField($modified[$name]->getActual());

			$fields[$name] = array(
				'_status'  => 'mismatch',
				'_diff'    => $this->legacyDiff($valid, $invalid, $modified[$name]),
				'_valid'   => $valid,
				'_invalid' => $invalid,
				'_file'    => $file,
			);
		}

		return $fields;
	}


	/**
	 * Every declared index of a table in the legacy $indices shape, followed by the live indexes the declaration has made redundant, all keyed by the name the server gives them.
	 *
	 * @param TableDiff $diff
	 * @return array
	 */
	private function projectIndexes(TableDiff $diff)
	{

		$expected = $diff->getExpectedTable();

		if($diff->isMissing() || !$expected instanceof TableSchema)
		{
			return array();
		}

		$file = $diff->getSqlFile();
		$missing = $diff->getMissingIndexes();
		$modified = $diff->getModifiedIndexes();
		$indices = array();

		foreach($expected->getIndexes() as $name => $index)
		{
			if(isset($missing[$name]))
			{
				$indices[$name] = array(
					'_status' => 'missing_index',
					'_valid'  => $this->legacyIndex($index),
					'_file'   => $file,
				);

				continue;
			}

			if(!isset($modified[$name]))
			{
				$indices[$name] = array('_status' => 'ok');

				continue;
			}

			$valid = $this->legacyIndex($modified[$name]->getExpected());
			$invalid = $this->legacyIndex($modified[$name]->getActual());

			$indices[$name] = array(
				'_status'  => 'mismatch',
				'_diff'    => $this->legacyDiff($valid, $invalid, $modified[$name]),
				'_valid'   => $valid,
				'_invalid' => $invalid,
				'_file'    => $file,
			);
		}

		foreach($diff->getRedundantIndexes() as $name => $index)
		{
			$indices[$name] = array(
				'_status'     => 'redundant_index',
				'_invalid'    => $this->legacyIndex($index),
				'_valid'      => array(),
				'_duplicates' => $this->declaredDuplicateOf($expected, $index),
				'_file'       => $file,
			);
		}

		return $indices;
	}


	/**
	 * The name of the declared index a redundant one duplicates.
	 *
	 * @param TableSchema $expected the declared shape.
	 * @param IndexSchema $index the live index being reported.
	 * @return string empty when the declaration turns out to carry no such index.
	 */
	private function declaredDuplicateOf(TableSchema $expected, IndexSchema $index)
	{

		foreach($expected->getIndexes() as $name => $declared)
		{
			if($declared->getKind() === $index->getKind() && $declared->getColumnNames() === $index->getColumnNames())
			{
				return $name;
			}
		}

		return '';
	}


	/**
	 * Which keys of a legacy pair differ, for the admin screen.
	 *
	 * $objectDiff is consulted when the two arrays are identical, the legacy shape carrying no character set, collation or comment of its own.
	 *
	 * @param array $valid
	 * @param array $invalid
	 * @param \e107\Database\Schema\Diff\ColumnDiff|\e107\Database\Schema\Diff\IndexDiff $objectDiff
	 * @return array differing key => expected value.
	 */
	private function legacyDiff(array $valid, array $invalid, $objectDiff)
	{

		$changed = array_diff_assoc($valid, $invalid);

		if(!empty($changed))
		{
			return $changed;
		}

		$expected = $objectDiff->getExpected()->toArray();

		foreach($objectDiff->getChangedFields() as $field)
		{
			$changed[$field] = isset($expected[$field]) ? $expected[$field] : null;
		}

		return $changed;
	}


	/**
	 * A {@see ColumnSchema} in the five-key array {@see toMysql()} renders and {@see renderNotes()} feeds it.
	 *
	 * For rendering only: nothing compares these, and no fix is built from them.
	 *
	 * @param ColumnSchema $column
	 * @return array{type:string, value:string, attributes:string, null:string, default:string}
	 */
	private function legacyField(ColumnSchema $column)
	{

		$type = trim($column->getColumnType());
		$value = '';
		$attributes = '';

		if(preg_match('/^([A-Za-z0-9_]+)\s*(?:\((.*)\))?\s*(.*)$/s', $type, $m))
		{
			$type = $m[1];
			$value = isset($m[2]) ? $m[2] : '';
			$attributes = isset($m[3]) ? trim($m[3]) : '';
		}

		$default = ($column->getDefault() === null) ? '' : 'DEFAULT ' . $column->getDefault();
		$extra = strtoupper($column->getExtra());

		return array(
			'type'       => strtoupper($type),
			'value'      => $value,
			'attributes' => strtoupper($attributes),
			'null'       => $column->isNullable() ? 'NULL' : 'NOT NULL',
			'default'    => trim($default . ' ' . $extra),
		);
	}


	/**
	 * An {@see IndexSchema} in the three-key array {@see getIndex()} returns and {@see toMysql()} renders.
	 *
	 * For rendering only: a primary key states no name, and an indexed expression is spelled `(expression)`.
	 *
	 * @param IndexSchema $index
	 * @return array{type:string, keyname:string, field:string}
	 */
	private function legacyIndex(IndexSchema $index)
	{

		$kind = $index->getKind();
		$columns = array();

		foreach($index->getColumnNames() as $column)
		{
			$columns[] = ($column === null) ? '(expression)' : $column;
		}

		return array(
			'type'    => ($kind === IndexSchema::KIND_INDEX) ? '' : $kind,
			'keyname' => implode(',', $columns),
			'field'   => ($kind === IndexSchema::KIND_PRIMARY) ? '' : $index->getName(),
		);
	}


	/**
	 * @return SchemaReader
	 */
	private function schemaReader()
	{

		if($this->schemaReader === null)
		{
			$this->schemaReader = new SchemaReader(e107::getDb());
		}

		return $this->schemaReader;
	}


	/**
	 * The materialiser, with any scratch table a killed run left behind swept away the first time it is asked for.
	 *
	 * @return Materialiser
	 */
	private function materialiser()
	{

		if($this->materialiser === null)
		{
			$this->materialiser = new Materialiser(e107::getDb(), $this->schemaReader(), MPREFIX);
		}

		if($this->scratchSwept === false)
		{
			$this->scratchSwept = true;

			try
			{
				$this->materialiser->sweep();
			}
			catch(QueryException $e)
			{
				e107::getMessage()->addDebug('Could not sweep leaked scratch tables: ' . $e->getMessage());
			}
		}

		return $this->materialiser;
	}


	/**
	 * @return SchemaDiffer
	 */
	private function schemaDiffer()
	{

		if($this->schemaDiffer === null)
		{
			$this->schemaDiffer = new SchemaDiffer();
		}

		return $this->schemaDiffer;
	}


	/**
	 * @return PlanBuilder
	 */
	private function planBuilder()
	{

		if($this->planBuilder === null)
		{
			$this->planBuilder = new PlanBuilder();
		}

		return $this->planBuilder;
	}


	/**
	 * The statements that take one live table's character columns through binary and back to utf8mb4, each built on the server's own column definition so that nothing but the type and the character set changes.
	 *
	 * A char or varchar becomes a varbinary wide enough for every byte it can hold at its present character set, so nothing is cut; a column under a FULLTEXT index cannot become binary, so it is converted in the second step alone.
	 *
	 * @param string $physical table name, prefix included
	 * @param string[] $columns the columns to convert; one the server does not define, or that is not a char or text type, is skipped
	 * @return array ['binary' => string[], 'restore' => string[]]
	 * @throws InvalidArgumentException when the table name is not one the schema builder will quote
	 */
	public function utf8ConversionStatements($physical, array $columns)
	{

		$logical = (MPREFIX !== '' && strpos($physical, MPREFIX) === 0) ? (string) substr($physical, strlen(MPREFIX)) : $physical;
		$create = e107::getDb()->schema()->getCreateTablePhysical($logical);
		$statement = is_string($create) ? Materialiser::splitCreateStatement($create) : null;

		if($statement === null)
		{
			return array('binary' => array(), 'restore' => array());
		}

		$definitions = Materialiser::definitionsByName($statement['body']);
		$fulltext = array();
		$bytesPerChar = array();
		$sql = e107::getDb();

		if($sql->execute('SELECT c.COLUMN_NAME, cs.MAXLEN FROM information_schema.COLUMNS c JOIN information_schema.CHARACTER_SETS cs ON cs.CHARACTER_SET_NAME = c.CHARACTER_SET_NAME WHERE c.TABLE_SCHEMA = DATABASE() AND c.TABLE_NAME = :table', array('table' => $physical)))
		{
			while($row = $sql->fetch())
			{
				$bytesPerChar[$row['COLUMN_NAME']] = (int) $row['MAXLEN'];
			}
		}

		foreach($definitions['indexes'] as $index)
		{
			if(preg_match('/^FULLTEXT KEY `[^`]*` \((.*)\)/i', $index, $m))
			{
				foreach(explode(',', $m[1]) as $part)
				{
					$fulltext[trim($part, ' `')] = true;
				}
			}
		}

		$binary = array();
		$restore = array();
		$prefix = 'ALTER TABLE `' . str_replace('`', '``', $physical) . '` MODIFY ';

		foreach($columns as $column)
		{
			if(!isset($definitions['columns'][$column], $bytesPerChar[$column])
				|| !preg_match('/^(`[^`]+`\s+)((?:var)?char\((\d+)\)|(?:tiny|medium|long)?text)((?:\s+CHARACTER SET \w+)?(?:\s+COLLATE \w+)?)(.*)$/is', $definitions['columns'][$column], $m))
			{
				continue;
			}

			if(!isset($fulltext[$column]))
			{
				$binaryType = ($m[3] !== '')
					? 'varbinary(' . ((int) $m[3] * $bytesPerChar[$column]) . ')'
					: preg_replace('/text$/i', 'blob', $m[2]);

				$binary[] = $prefix . $m[1] . $binaryType . $m[5] . ';';
			}

			$restore[] = $prefix . $m[1] . $m[2] . ' CHARACTER SET utf8mb4' . $m[5] . ';';
		}

		return array('binary' => $binary, 'restore' => $restore);
	}


	/**
	 * The character set each compared table should carry, keyed by table name as reported, `lan_` prefix included; filled by {@see compare()} and {@see compareAll()}.
	 *
	 * @return array table => character set
	 */
	public function getIntendedCharsets()
	{

		$charsets = array();

		foreach($this->intendedByTable as $table => $intended)
		{
			$charsets[$table] = $intended['charset'];
		}

		return $charsets;
	}


	/**
	 * The difference {@see compare()} found for every table it looked at.
	 *
	 * @return TableDiff[] keyed by table name.
	 */
	public function getTableDiffs()
	{

		return $this->tableDiffs;
	}


	/**
	 * Every change {@see compileResults()} has planned, in application order.
	 *
	 * @return FixPlan empty until compileResults() has run.
	 */
	public function getFixPlan()
	{

		if($this->fixPlan === null)
		{
			$this->fixPlan = new FixPlan();
		}

		return $this->fixPlan;
	}


	/**
	 * @param string $type fields|indices
	 * @return array
	 */
	public function getResults($type = 'fields')
	{

		if($type === 'indices')
		{
			return $this->indices;
		}

		return $this->results;

	}

	/**
	 * Whether this server refuses the declared body outright, by building it as a scratch table.
	 *
	 * Never throws.
	 *
	 * @param mixed $sqlFileData the text between the outer parentheses of a CREATE TABLE.
	 * @return bool false for anything that is not a body to try, and for a check that could not be made.
	 */
	public function hasSyntaxIssue($sqlFileData)
	{

		if(!is_string($sqlFileData) || trim($sqlFileData) === '')
		{
			return false;
		}

		try
		{
			$table = new DeclaredTable('core', 'dbvsyntaxcheck', $sqlFileData);
			$intended = $this->resolve($table);

			$this->materialiser()->materialise($table, $intended['engine'], $intended['charset']);
		}
		catch(QueryException $e)
		{
			return true;
		}
		catch(Exception $e)
		{
			return false;
		}

		return false;
	}

	/**
	 * @param string $tbl       table name without prefix.
	 * @param string $selection 'core' OR plugin-folder name.
	 * @param array  $sqlData   ie. array('field'=>getFields($data), 'index'=>getFields($data));
	 * @param array  $fileData  ie. array('field'=>getFields($data), 'index'=>getFields($data));
	 * @deprecated v2.4.0 {@see compare()} projects a {@see TableDiff} into the same arrays.
	 * @todo Check for additional fields in SQL that should be removed.
	 * @todo Add support for MYSQL 5 table layout .eg. journal_id INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	 */
	public function prepareResults($tbl, $selection, $sqlData, $fileData)
	{

		if($this->hasSyntaxIssue($fileData))
		{
			return false;
		}

		// Check field and index data
		foreach(['field', 'index'] as $type)
		{
			$results = 'results';

			if($type === 'index')
			{
				$results = 'indices';
			}

			if(!isset($this->errors[$tbl]))
			{
				$this->errors[$tbl] = [];
			}
			if(!isset($this->errors[$tbl]['_status']))
			{
				$this->errors[$tbl]['_status'] = self::STATUS_TABLE_OK;
			}
			$this->errors[$tbl]['_file'] = $selection;

			foreach($fileData[$type] as $key => $value)
			{
				$this->{$results}[$tbl][$key]['_status'] = 'ok';

				if(!isset($sqlData[$type][$key]) || !is_array($sqlData[$type][$key]))
				{
					$this->{$results}[$tbl][$key]['_status'] = "missing_$type"; // type status
					$this->{$results}[$tbl][$key]['_valid'] = $value;
					$this->{$results}[$tbl][$key]['_file'] = $selection;
				}
				elseif(count($diff = $this->diffStructurePermissive($value, $sqlData[$type][$key])))
				{
					$this->{$results}[$tbl][$key]['_status'] = 'mismatch';
					$this->{$results}[$tbl][$key]['_diff'] = $diff;
					$this->{$results}[$tbl][$key]['_valid'] = $value;
					$this->{$results}[$tbl][$key]['_invalid'] = $sqlData[$type][$key];
					$this->{$results}[$tbl][$key]['_file'] = $selection;
				}


			}

			if($fileData['engine'] !== $sqlData['engine'])
			{
				$this->errors[$tbl]['_status'] |= self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE;
				$this->errors[$tbl]['_valid_' . self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE] = $fileData['engine'];
				$this->errors[$tbl]['_invalid_' . self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE] = $sqlData['engine'];
			}
			if($fileData['charset'] !== $sqlData['charset'])
			{
				$this->errors[$tbl]['_status'] |= self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET;
				$this->errors[$tbl]['_valid_' . self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET] = $fileData['charset'];
				$this->errors[$tbl]['_invalid_' . self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET] = $sqlData['charset'];
			}

		}

		return null;

	}


	/**
	 * Compile Results into a complete list of Fixes that could be run without the need of a form selection.
	 *
	 * Idempotent: calling it twice does not queue anything twice.
	 *
	 * @return void
	 */
	function compileResults()
	{

		$this->fixList = array();
		$plan = new FixPlan();

		foreach($this->tableDiffs as $table => $diff)
		{
			if(!$diff->hasDrift())
			{
				continue;
			}

			$intended = isset($this->intendedByTable[$table]) ? $this->intendedByTable[$table] : array();

			try
			{
				$tablePlan = $this->planBuilder()->build(
					$diff,
					isset($intended['engine']) ? $intended['engine'] : null,
					isset($intended['charset']) ? $intended['charset'] : null
				);
			}
			catch(Exception $e)
			{
				e107::getMessage()->addDebug('Could not plan a repair for ' . $table . ': ' . $e->getMessage());
				$this->internalError = true;

				continue;
			}

			$plan = $plan->merge($tablePlan);

			foreach($tablePlan->getChanges() as $change)
			{
				$slot = $this->changeSlot($change, $diff);
				$field = $slot['field'];
				$mode = $slot['modes'][0];

				if(!isset($this->fixList[$change->getSqlFile()][$change->getTable()][$field]))
				{
					$this->fixList[$change->getSqlFile()][$change->getTable()][$field] = array();
				}

				if(!in_array($mode, $this->fixList[$change->getSqlFile()][$change->getTable()][$field], true))
				{
					$this->fixList[$change->getSqlFile()][$change->getTable()][$field][] = $mode;
				}
			}
		}

		$this->fixPlan = $plan;
	}


	/**
	 * Where one planned change is filed in $fixList, and which of the legacy modes a request has to name to select it back out again.
	 *
	 * @param ChangeInterface $change
	 * @param TableDiff $diff the difference the change was planned from.
	 * @return array{field:string, modes:string[]} The first mode is the one the change is filed under; the rest are spellings the admin form may post for the same repair.
	 */
	private function changeSlot(ChangeInterface $change, TableDiff $diff)
	{

		if($change instanceof CreateTable)
		{
			return array('field' => 'all', 'modes' => array('create'));
		}

		if($change instanceof ConvertTable)
		{
			return array('field' => 'all', 'modes' => array('convert'));
		}

		if($change instanceof AddColumn)
		{
			return array('field' => $change->getColumn()->getName(), 'modes' => array('insert'));
		}

		if($change instanceof ModifyColumn)
		{
			$name = $change->getColumn()->getName();
			$modified = $diff->getModifiedColumns();

			if(isset($modified[$name]))
			{
				return array('field' => $name, 'modes' => array('alter'));
			}

			return array('field' => 'all', 'modes' => array('convert'));
		}

		if($change instanceof DropIndex)
		{
			return array('field' => $change->getIndex()->getName(), 'modes' => array('indexdrop', 'index', 'alter'));
		}

		if($change instanceof AddIndex)
		{
			return array('field' => $change->getIndex()->getName(), 'modes' => array('index', 'indexdrop', 'alter'));
		}

		return array('field' => 'all', 'modes' => array('alter'));
	}

	/**
	 * Returns the number of errors
	 */
	public function errors()
	{

		$badTableCount = 0;
		foreach($this->errors as $tableName => $tableMetadata)
		{
			if(!empty($tableMetadata['_status']))
			{
				$badTableCount++;
				continue;
			}
			foreach($this->results[$tableName] as $fieldMetadata)
			{
				if(isset($fieldMetadata['_status']) && $fieldMetadata['_status'] != 'ok')
				{
					$badTableCount++;
					continue 2;
				}
			}
			foreach($this->indices[$tableName] as $indexMetadata)
			{
				if(isset($indexMetadata['_status']) && $indexMetadata['_status'] != 'ok')
				{
					$badTableCount++;
					continue 2;
				}
			}
		}

		return $badTableCount;
	}

	public function getErrors()
	{

		return $this->errors;
	}

	/**
	 * @param $fileArray
	 * @return void
	 */
	function renderResults($fileArray = array())
	{

		$frm = e107::getForm();
		$ns = e107::getRender();
		$mes = e107::getMessage();

		$text = "
		<form method='post' action='" . e_SELF . "?" . e_QUERY . "'>
			<fieldset id='core-db-verify-results'>
				<legend id='core-db-verify-results-legend'>" . DBVLAN_16 . "</legend>

				<table class='table adminlist'>
					<colgroup>
						<col style='width: 25%'></col>
						<col style='width: 25%'></col>
						<col style='width: 10%'></col>
						<col style='width: 30%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th>" . DBVLAN_4 . "</th>
							<th>" . DBVLAN_5 . "</th>
							<th class='center'>" . DBVLAN_6 . "</th>
							<th>" . DBVLAN_7 . "</th>
							<th class='center last'>" . DBVLAN_19 . "</th>
						</tr>
					</thead>
					<tbody>
		";

		$info = array(
			self::STATUS_TABLE_MISSING                  => DBVLAN_13,
			self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE  => DBVLAN_17,
			self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET => DBVLAN_18,
			'mismatch'                                  => DBVLAN_8,
			'missing_field'                             => DBVLAN_11,
			'ok'                                        => defset('ADMIN_TRUE_ICON', 'true'),
			'missing_index'                             => DBVLAN_25,
			'redundant_index'                           => defset('DBVLAN_INDEX_REDUNDANT', 'Redundant index'),
		);


		foreach($this->results as $tabs => $field)
		{
			$tableStatus = $this->errors[$tabs]['_status'];
			if($tableStatus != self::STATUS_TABLE_OK) // Missing Table
			{
				$errors = [];
				$parser = e107::getParser();
				foreach([
					self::STATUS_TABLE_MISSING,
					self::STATUS_TABLE_MISMATCH_STORAGE_ENGINE,
					self::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET
				] as $statusFlag)
				{
					if($tableStatus & $statusFlag)
					{
						$errors[] = $parser->lanVars(
							$info[$statusFlag],
							[
								'x' => $this->errors[$tabs]['_valid_' . $statusFlag],
								'y' => $this->errors[$tabs]['_invalid_' . $statusFlag],
							]
						);
					}
				}

				$fixMode = $tableStatus & self::STATUS_TABLE_MISSING ? 'create' : 'convert';

				$text .= "
					<tr>
						<td>" . $this->renderTableName($tabs) . "</td>
						<td><em>" . DBVLAN_28 . "</em></td>
						<td class='center middle error'>" . DBVLAN_27 . "</td>
						<td>" . implode("<br />", $errors) . "</td>
						<td class='center middle autocheck e-pointer'>" . $this->fixForm($this->errors[$tabs]['_file'], $tabs, 'all', '', $fixMode) . "</td>
					</tr>
					";
			}
			foreach($field as $k => $f)
			{
				if($f['_status'] == 'ok')
				{
					continue;
				}

				$fstat = $info[$f['_status']];

				$text .= "
					<tr>
						<td>" . $this->renderTableName($tabs) . "</td>
						<td>" . $k . "&nbsp;</td>
						<td class='center middle error'>" . $fstat . "</td>
						<td>" . $this->renderNotes($f) . "&nbsp;</td>
						<td class='center middle autocheck e-pointer'>" . $this->fixForm($f['_file'], $tabs, $k, $f['_valid'], $this->modes[$f['_status']]) . "</td>
					</tr>
					";
			}
		}


		// Indices


		if(count($this->indices))
		{
			foreach($this->indices as $tabs => $field)
			{

				if($this->errors[$tabs] != 'ok')
				{
					foreach($field as $k => $f)
					{
						if($f['_status'] == 'ok')
						{
							continue;
						}

						$fstat = $info[$f['_status']];

						$text .= "
						<tr>
							<td>" . $this->renderTableName($tabs) . "</td>
							<td>" . $k . "&nbsp;</td>
							<td class='center middle error'>" . $fstat . "</td>
							<td>" . $this->renderNotes($f, 'index') . "&nbsp;</td>
							<td class='center middle autocheck e-pointer'>" . $this->fixForm($f['_file'], $tabs, $k, $f['_valid'], $this->modes[$f['_status']]) . "</td>
						</tr>
						";
					}
				}

			}
		}


		$text .= "
					</tbody>
				</table>
				<br/>
		";
		$text .= "
			<div class='buttons-bar right'>
				" . $frm->admin_button('runfix', DBVLAN_21, 'execute', '', array('id' => false)) . "
				" . $frm->admin_button('check_all', 'jstarget:fix', 'action', LAN_CHECKALL, array('id' => false)) . "
				" . $frm->admin_button('uncheck_all', 'jstarget:fix', 'action', LAN_UNCHECKALL, array('id' => false));

		foreach($fileArray as $tab)
		{
			$text .= $frm->hidden('verify_table[]', $tab);
		}

		$text .= "
			</div>
			
			</fieldset>
			</form>
		";


		$ns->tablerender(DBVLAN_23 . SEP . DBVLAN_16, $mes->render() . $text);

	}

	/**
	 * @param $tabs
	 * @return mixed|string
	 */
	function renderTableName($tabs)
	{

		if(strpos($tabs, "lan_") === 0)
		{
			list($tmp, $lang, $table) = explode("_", $tabs, 3);

			return $table . " (" . ucfirst($lang) . ")";
		}

		return $tabs;
	}


	/**
	 * @param $file
	 * @param $table
	 * @param $field
	 * @param $newvalue
	 * @param $mode
	 * @param $after
	 * @return string
	 */
	function fixForm($file, $table, $field, $newvalue, $mode, $after = '')
	{

		$frm = e107::getForm();
		$text = $frm->checkbox("fix[$file][$table][$field][]", $mode, false, array('id' => false));

		return $text;
	}


	/**
	 * The Notes cell of one row: what is there now, and what belongs there, or for a redundant index which declared index covers it.
	 *
	 * @param array $data one entry of $results or $indices.
	 * @param string $mode 'field' or 'index'.
	 * @return string
	 */
	function renderNotes($data, $mode = 'field')
	{

		// return "<pre>".print_r($data,TRUE)."</pre>";

		$v = $data['_valid'];
		$i = !empty($data['_invalid']) ? $data['_invalid'] : array();

		$valid = $this->toMysql($v, $mode);
		$invalid = $this->toMysql($i, $mode);

		$text = "";
		if($invalid)
		{
			$text .= "<strong>" . DBVLAN_9 . "</strong>
				<div class='indent'>" . $invalid . "</div>";
		}

		if(isset($data['_status']) && $data['_status'] === 'redundant_index')
		{
			$note = defset('DBVLAN_INDEX_REDUNDANT_NOTE', 'Duplicates the FULLTEXT index [x]; the schema declares that one, so this one can be removed.');

			return $text . "<div class='indent'>"
				. str_replace('[x]', '<code>' . $data['_duplicates'] . '</code>', $note)
				. "</div>";
		}

		$text .= "<strong>" . DBVLAN_10 . "</strong>
			<div class='indent'>" . $valid . "</div>";

		return $text;
	}


	/**
	 * @param $data
	 * @param $mode
	 * @return string|void
	 */
	public function toMysql($data, $mode = 'field')
	{

		if(!$data)
		{
			return;
		}

		if($mode === 'index')
		{
			// print_a($data);
			if($data['type'])
			{
				// field = index name (in backticks), keyname = column name(s) (in parentheses)
				// This matches MySQL syntax: FULLTEXT `index_name` (column_name)
				$field = (!empty($data['field']) ? " `" . $data['field'] . "`" : "");

				return $data['type'] . $field . " (" . $data['keyname'] . ");";
			}
			else
			{
				return "INDEX `" . $data['field']  . "` (" . $data['keyname'] . ");";
			}

		}

		if(!in_array(strtolower($data['type']), $this->fieldTypes))
		{
			$ret = $data['type'] . "(" . $data['value'] . ") " . $data['attributes'] . " " . $data['null'] . " " . $data['default'];

			return trim($ret);
		}
		else
		{
			$ret = $data['type'];
			$ret .= !empty($data['attributes']) ? " " . $data['attributes'] : '';
			$ret .= !empty($data['null']) ? " " . $data['null'] : '';
			$ret .= !empty($data['default']) ? " " . $data['default'] : '';

			return $ret;
		}

	}

	// returns the previous Field

	/**
	 * @param $array
	 * @param $cur
	 * @return int|mixed|string
	 */
	function getPrevious($array, $cur)
	{

		$fkeys = array_keys($array);
		$cur_key = array_search($cur, $fkeys);

		return @$fkeys[$cur_key - 1];
	}

	/**
	 * Resolve the base table name for a language table.
	 *
	 * Language tables are stored as lan_<language>_<base> (e.g. lan_dutch_news).
	 * SQL-file definitions and derived indexes are registered under the base
	 * name, so both getId() and getFixQuery() must normalise to it before a
	 * lookup.
	 *
	 * @param string $table table name, possibly lan_<language>_ prefixed
	 * @return string base table name
	 */
	private function getBaseTableName($table)
	{

		if(strpos($table, "lan_") === 0) // language table adjustment.
		{
			$parts = explode("_", $table, 3);
			if(count($parts) === 3)
			{
				return $parts[2];
			}
		}

		return $table;
	}

	/**
	 * get the key ID for the current table which is being Fixed.
	 */
	function getId($tabl, $cur)
	{

		if(empty($tabl))
		{
			return null;
		}

		$key = array_flip($tabl);

		$cur = $this->getBaseTableName($cur); // language table adjustment.

		if(isset($key[$cur]))
		{
			return $key[$cur];
		}

	}


	/**
	 * @param string $mode        index|alter|insert|drop|create|indexdrop
	 * @param string $table       eg. submitnews
	 * @param string $field       eg. submitnews_id
	 * @param string $sqlFileData (after CREATE)  eg. dblog_id int(10) unsigned NOT NULL auto_increment, ..... KEY....
	 * @param string $engine      MyISAM|InnoDB
	 * @param string $charset     MySQL/MariaDB text character set
	 * @return string SQL query
	 */
	function getFixQuery(
		$mode,
		$table,
		$field,
		$sqlFileData,
		$engine = self::MOST_PREFERRED_STORAGE_ENGINE,
		$charset = self::MOST_PREFERRED_CHARSET
	)
	{

		// $table and $field become SQL identifiers below and cannot be bound. This
		// public method no longer trusts its caller to have allowlisted them: reject
		// anything outside the identifier grammar fail-closed (the same guard runFix()
		// already applies to the POST keys it forwards here), so the method is safe by
		// construction regardless of who calls it.
		if(!preg_match('/^[A-Za-z0-9_]+$/D', (string) $table))
		{
			return "";
		}

		// $field is embedded as a bare `identifier` by the alter/insert/drop/indexdrop
		// clauses; the index/create modes take it only as an array key.
		$fieldIsIdentifier = in_array($mode, array('alter', 'insert', 'drop', 'indexdrop'), true);
		if($fieldIsIdentifier && !preg_match('/^[A-Za-z0-9_]+$/D', (string) $field))
		{
			return "";
		}

		// SchemaBuilder owns the DDL envelope from here: it resolves and quotes the
		// physical table name (prefix only, no language routing - db_verify handles
		// language tables itself) and assembles the ALTER/CREATE statement. The
		// clause/body text is developer-controlled schema text (from toMysql()/the
		// sql file), passed as a vouched SqlFragment fragment so the emitted SQL stays
		// byte-identical to the legacy hand-assembled string.
		$schema = e107::getDb()->schema();

		if(strpos($mode, 'index') === 0)
		{
			$fdata  = $this->getIndex($sqlFileData);

			// Check if this is a derived index (e.g., from e_search configurations)
			if(!isset($fdata[$field]))
			{
				// Derived indexes are registered under the base table name, so a
				// language table (lan_<language>_*) must resolve to it first.
				$baseTable = $this->getBaseTableName($table);

				// Load derived indexes on-demand (needed when runFix() is called before compare())
				if(!isset($this->derivedIndexDefinitions[$baseTable]))
				{
					$this->getSearchFieldIndexes($baseTable);
				}

				if(isset($this->derivedIndexDefinitions[$baseTable][$field]))
				{
					$fdata[$field] = $this->derivedIndexDefinitions[$baseTable][$field];
				}
			}

			$newval = $this->toMysql($fdata[$field], 'index');
		}
		elseif($mode == 'alter' || $mode === 'insert' || $mode === 'index')
		{
			$fdata = $this->getFields($sqlFileData);
			$newval = $this->toMysql($fdata[$field]);
		}

		$query = "";

		switch($mode)
		{
			case 'alter':
				$query = $schema->tablePhysical($table)
					->addRaw(SqlFragment::raw("CHANGE `$field` `$field` $newval"))
					->getSQL();
				break;

			case 'insert':
				$after = ($aft = $this->getPrevious($fdata, $field)) ? " AFTER {$aft}" : "";
				$query = $schema->tablePhysical($table)
					->addRaw(SqlFragment::raw("ADD `$field` $newval{$after}"))
					->getSQL();
				break;

			case 'drop':
				$query = $schema->tablePhysical($table)
					->addRaw(SqlFragment::raw("DROP `$field`"))
					->getSQL();
				break;

			case 'index':
				$newval = str_replace("PRIMARY", "PRIMARY KEY", $newval);
				$query = $schema->tablePhysical($table)
					->addRaw(SqlFragment::raw("ADD " . $newval))
					->getSQL();
				break;

			case 'indexdrop':
				$query = $schema->tablePhysical($table)
					->addRaw(SqlFragment::raw("DROP INDEX `$field`"))
					->getSQL();
				break;

			case 'create':
				$query = $schema->buildCreateTablePhysicalRaw(
					$table,
					SqlFragment::raw($sqlFileData),
					SqlFragment::raw(" ENGINE=" . $engine . " DEFAULT CHARACTER SET=" . $charset . ";")
				);
				break;

			case 'convert':
				$showCreateTable = $this->getSqlData($table);
				$currentSchema = $this->getSqlFileTables($showCreateTable);
				if($engine != $currentSchema['engine'][0])
				{
					$query .= $schema->tablePhysical($table)
						->addRaw(SqlFragment::raw("ENGINE=" . $engine . ";"))
						->getSQL();
				}
				if($charset != $currentSchema['charset'][0])
				{
					$query .= $schema->tablePhysical($table)
						->addRaw(SqlFragment::raw("CONVERT TO CHARACTER SET " . $charset . ";"))
						->getSQL();
				}
				break;
		}


		return $query;
	}


	/**
	 * Fix tables
	 * FixArray eg. [core][table][field] = alter|create|index| etc.
	 *
	 * A named table is compared again here, so a request applies only what is still outstanding.
	 *
	 * @param array|string $fixArray [sqlFile][table][field][] = mode, or anything that is not an array to apply the whole plan.
	 * @return void
	 */
	function runFix($fixArray = '')
	{

		$log = e107::getLog();

		if(!is_array($fixArray))
		{
			$this->settle($this->applyPlan($this->getFixPlan()));
			$log->flushMessages("Database Table(s) Modified");

			return;
		}

		foreach($fixArray as $j => $file)
		{
			if(!is_array($file) || !isset($this->sqlFileTables[$j]['tables']))
			{
				continue;
			}

			foreach($file as $table => $val)
			{
				$id = $this->getId($this->sqlFileTables[$j]['tables'], $table);

				// $table is an attacker-controllable POST array key: reject anything that
				// is not a known table identifier before it reaches the SQL string.
				if($id === null || !is_array($val) || !preg_match('/^[A-Za-z0-9_]+$/D', (string) $table))
				{
					continue;
				}

				$this->settle($this->applyPlan($this->requestedPlan($j, $id, $table, $val)));
			}    //
		}

		$log->flushMessages("Database Table(s) Modified");

	}


	/**
	 * The changes one form submission asked for, planned afresh against the database as it stands now.
	 *
	 * @param string $sqlFile key of {@see $sqlFileTables}.
	 * @param int|string $id ordinal of the table within that file.
	 * @param string $table table name as the form named it, lan_<language>_ prefix included.
	 * @param array $requested field => list of modes.
	 * @return FixPlan empty when the table cannot be compared, or when nothing requested is still outstanding.
	 */
	private function requestedPlan($sqlFile, $id, $table, array $requested)
	{

		$declared = $this->declaredTable($sqlFile, $id);

		if($declared === null)
		{
			return new FixPlan();
		}

		foreach(array_keys($requested) as $field)
		{
			if(!preg_match('/^[A-Za-z0-9_]+$/D', (string) $field))
			{
				unset($requested[$field]);
			}
		}

		if(empty($requested))
		{
			return new FixPlan();
		}

		try
		{
			$actual = $this->schemaReader()->read(MPREFIX . $table);
			$compared = $this->diffFor($declared, $sqlFile, $table, $actual);
			$diff = $compared['diff'];
			$plan = $this->planBuilder()->build($diff, $compared['intended']['engine'], $compared['intended']['charset']);
		}
		catch(Exception $e)
		{
			e107::getLog()->addWarning('Could not plan a repair for `' . $table . '`: ' . $e->getMessage());
			$this->internalError = true;

			return new FixPlan();
		}

		$matched = array();

		foreach($plan->getChanges() as $change)
		{
			$slot = $this->changeSlot($change, $diff);

			if(!isset($requested[$slot['field']]))
			{
				continue;
			}

			$modes = is_array($requested[$slot['field']]) ? $requested[$slot['field']] : array($requested[$slot['field']]);

			if(count(array_intersect($modes, $slot['modes'])) > 0)
			{
				$matched[] = $change;
			}
		}

		return new FixPlan($matched);
	}


	/**
	 * Run a plan, in order, and report what each table's changes did.
	 *
	 * @param FixPlan $plan
	 * @return array table => ['applied' => int, 'failed' => int]
	 */
	private function applyPlan(FixPlan $plan)
	{

		$log = e107::getLog();
		$sql = e107::getDb();
		$schema = $sql->schema();
		$outcome = array();

		foreach($plan->getChanges() as $change)
		{
			$table = $change->getTable();

			if(!isset($outcome[$table]))
			{
				$outcome[$table] = array('applied' => 0, 'failed' => 0);
			}

			try
			{
				$statements = new FixPlan(array($change));
				$statements = $statements->toSqlStatements($schema);
			}
			catch(Exception $e)
			{
				$log->addWarning('Could not build the SQL for ' . $change->describe() . ' on `' . $table . '`: ' . $e->getMessage());
				$outcome[$table]['failed']++;

				continue;
			}

			$mode = $change->mayLoseData() ? $this->enterStrictMode($sql) : null;

			try
			{
				foreach($statements as $query)
				{
					if(trim((string) $query) === '')
					{
						$log->addDebug('No statement for ' . $change->describe() . ' on `' . $table . '`, nothing to run.');

						continue;
					}

					if($sql->execute($query) !== false)
					{
						$log->addDebug(defset('LAN_UPDATED', 'Updated') . '  [' . $query . ']');
						$outcome[$table]['applied']++;
					}
					else
					{
						$log->addWarning(defset('LAN_UPDATED_FAILED', 'Update Failed') . '  [' . $query . ']');
						$log->addWarning($sql->getLastErrorText()); // PDO compatible.
						$outcome[$table]['failed']++;
					}
				}
			}
			finally
			{
				if($mode !== null)
				{
					$sql->execute('SET SESSION sql_mode = :mode', array('mode' => $mode));
				}
			}
		}

		return $outcome;
	}


	/**
	 * Make the server refuse a statement that would rewrite data to fit, instead of doing so with a warning.
	 *
	 * Under e107's usual `NO_ENGINE_SUBSTITUTION` a `CONVERT TO CHARACTER SET` replaces every character the target
	 * cannot hold with `?`; under `STRICT_TRANS_TABLES` the same statement fails and the table is left as it was.
	 *
	 * @param e_db $sql
	 * @return string the session sql_mode to put back
	 */
	private function enterStrictMode($sql)
	{

		$mode = (string) $sql->getMode();
		$sql->execute("SET SESSION sql_mode = CONCAT(@@sql_mode, ',STRICT_TRANS_TABLES')");

		return $mode;
	}


	/**
	 * Drop a repaired table from every outstanding list, the {@see FixPlan} included, so that a later report or fix on this instance no longer sees it.
	 *
	 * @param array $outcome as {@see applyPlan()} returns.
	 * @return void
	 */
	private function settle(array $outcome)
	{

		$settled = array();

		foreach($outcome as $table => $counts)
		{
			if($counts['failed'] > 0)
			{
				continue;
			}

			$settled[] = $table;

			unset($this->errors[$table], $this->tableDiffs[$table]);

			foreach(array_keys($this->fixList) as $file)
			{
				unset($this->fixList[$file][$table]);
			}
		}

		if(!empty($settled) && $this->fixPlan instanceof FixPlan)
		{
			$this->fixPlan = $this->fixPlan->exceptTables($settled);
		}
	}


	/**
	 * Every table a `*_sql.php` file declares, in the legacy four parallel arrays, parsed by {@see SqlFileCatalogue}.
	 *
	 * A file declaring the same table twice yields one entry, the last declaration; a statement naming no table is refused.
	 *
	 * @param string $sql_data contents of a `*_sql.php` file, or a SHOW CREATE TABLE statement with a semicolon appended.
	 * @return array|false ['tables'=>[], 'data'=>[], 'engine'=>[], 'charset'=>[]] keyed by the same ordinals; false when there is nothing to read or the text cannot be parsed.
	 */
	function getSqlFileTables($sql_data)
	{

		if(!$sql_data)
		{
			e107::getMessage()->addError("No SQL Data found in file");

			return false;
		}

		$sqlFile = ($this->currentTable === null || $this->currentTable === '') ? 'core' : $this->currentTable;

		try
		{
			$declared = $this->sqlFileCatalogue()->parse($sql_data, $sqlFile);
		}
		catch(Exception $e)
		{
			e107::getMessage()->addError("Unable to parse " . $sqlFile . "_sql.php file data: " . $e->getMessage());

			return false;
		}

		$ret = array('tables' => array(), 'data' => array(), 'engine' => array(), 'charset' => array());

		foreach(array_values($declared) as $ordinal => $table)
		{
			$ret['tables'][$ordinal] = $table->getName();
			$ret['data'][$ordinal] = $table->getBody();
			$ret['engine'][$ordinal] = $table->getDeclaredEngine();
			$ret['charset'][$ordinal] = $table->getDeclaredCharset();
		}

		if(empty($ret['tables']))
		{
			e107::getMessage()->addDebug("Unable to parse " . $this->currentTable . "_sql.php file data. Possibly missing a ';' at the end?");
		}

		return $ret;
	}


	/**
	 * @return SqlFileCatalogue
	 */
	private function sqlFileCatalogue()
	{

		if($this->sqlFileCatalogue === null)
		{
			$this->sqlFileCatalogue = new SqlFileCatalogue();
		}

		return $this->sqlFileCatalogue;
	}


	/**
	 * @param $data
	 * @param $print
	 * @return array
	 */
	function getFields($data, $print = false)
	{

		// Clean $data and add ` ` arond field-names - prevents issues when field == field-type. 
		$tmp = explode("\n", $data);
		$newline = array();

		foreach($tmp as $line)
		{
			$line = trim($line);

			if(strpos($line, "PRIMARY") === 0 || strpos($line, "KEY") === 0 || strpos($line, "INDEX") === 0 || strpos($line, "FULLTEXT") === 0 || strpos($line, "FOREIGN") === 0)
			{
				$newline[] = '';  // Add a placeholder to preserve the structure
				continue;
			}

			$newline[] = preg_replace('/^([^`\s][0-9a-zA-Z\$_]*)/', "`$1`", $line);
		}

		$data = implode("\n", $newline);
		// --------------------

		$mes = e107::getMessage();

		//	$regex = "/`?([\w]*)`?\s*?(".implode("|",$this->fieldTypes)."|".implode("|",$this->fieldTypeNum).")\s?(?:\([\s]?([0-9,]*)[\s]?\))?[\s]?(unsigned)?[\s]?.*?(?:(NOT NULL|NULL))?[\s]*(auto_increment|default .*)?[\s]?(?:PRIMARY KEY)?[\s]*?,?\s*?\n/im";
		$regex = "/^\s*?`?([\w]*)`?\s*?(" . implode("|", $this->fieldTypes) . "|" . implode("|", $this->fieldTypeNum) . ")\s?(?:\([\s]?([0-9,]*)[\s]?\))?[\s]?(unsigned)?[\s]?.*?(?:(NOT NULL|NULL))?[\s]*(auto_increment|default|AUTO_INCREMENT|DEFAULT [\w'\s.\(:\)-]*)?[\s]?(comment [\w\s'.-]*)?[\s]?(?:PRIMARY KEY|FULLTEXT)?[\s]*?,?\s*?\n/im";

		preg_match_all($regex, $data, $m);

		$ret = array();

		if($print)
		{
			var_dump($regex, $m);
		}

		foreach($m[1] as $k => $val)
		{
			$ret[$val] = array(
				'type'       => trim(strtoupper($m[2][$k])),
				'value'      => $m[3][$k],
				'attributes' => strtoupper($m[4][$k]),
				'null'       => strtoupper($m[5][$k]),
				'default'    => strtoupper($m[6][$k])
			);
		}

		return $ret;
	}


	/**
	 * Helper method to clean column names
	 *
	 * @param string $col The raw column name to clean
	 * @return string The cleaned column name
	 */
	private function cleanColumn($col)
	{

		$col = trim($col);
		$col = str_replace('(', ' (', $col);
		$tmp = explode(' ', $col);

		return str_replace('`', '', $tmp[0]);
	}

	/**
	 * Parse index definitions from a string
	 *
	 * @param string $data  The raw index definition string
	 * @param bool   $print Optional flag to print results (not used here)
	 * @return array Parsed index information
	 */
	public function getIndex($data, $print = false)
	{

		// Regular expression to match index definitions
		$regex = "/(?P<type>PRIMARY|UNIQUE|FULLTEXT|FOREIGN|KEY|INDEX)[\s]*?(?P<key_type>INDEX|KEY)?[\s]*(?:`?(?P<field>[\w]*)`?)?[\s]*?\((?P<columns>[^)]+)\)[\s]*,?/i";
		preg_match_all($regex, $data, $m);

		$ret = [];

		// Process each matched index
		foreach($m['type'] as $k => $val)
		{
			$type = trim(strtoupper($m['type'][$k])); // Index type (e.g., PRIMARY, UNIQUE)
			$field = trim($m['field'][$k]); // Index name before parentheses
			$columnsRaw = trim($m['columns'][$k]); // Content inside parentheses

			// Split columns and clean each one using the helper method
			$columnsArray = array_map(array($this, 'cleanColumn'), explode(',', $columnsRaw));
			$keyname = implode(',', $columnsArray); // Comma-separated column names

			// Determine the index name: use 'field' if provided, otherwise first column
			$i = $field ?: $columnsArray[0];
			if($type === 'PRIMARY')
			{
				$i = $columnsArray[0]; // Primary key uses the column name
			}

			// Normalize KEY/INDEX to empty type for regular indexes
			if($type === 'KEY' || $type === 'INDEX')
			{
				$type = '';
			}

			// Build the result array for this index
			$ret[$i] = array(
				'type'    => $type,
				'keyname' => $keyname,
				'field'   => $i,
			);
		}

		return $ret;
	}


	/**
	 * @param $tbl
	 * @param $language
	 * @return false|string
	 */
	function getSqlData($tbl, $language = '')
	{

		$mes = e107::getMessage();
		$prefix = MPREFIX;

		if($language)
		{
			if(!in_array($tbl, $this->sqlLanguageTables[$language]))
			{
				return false;
			}

			$prefix .= "lan_" . $language . "_";
			// $mes->addDebug("<h2>Retrieving Language Table Data: ".$prefix . $tbl."</h2>"); 				
		}


		$sql = e107::getDb();

		if(!$sql->isTable($tbl))
		{
			$mes->addDebug('Missing table on db-verify: ' . $tbl);

			return false;
		}


		//	mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
		$qry = 'SHOW CREATE TABLE `' . $prefix . $tbl . "`";


		//	$z = mysql_query($qry);
		// SHOW CREATE TABLE introspection has no builder equivalent; $tbl is verified by isTable()
		// above and the table name is an identifier (not a bindable value), so run via the sanctioned
		// bound execute(). execute() returns the same rowCount() and exposes the same result set as
		// gen(), so both the if($z) guard and the fetch('num') below are unchanged.
		$z = $sql->execute($qry);
		if($z)
		{
			//	$row = mysql_fetch_row($z);
			$row = $sql->fetch('num');

			//return $row[1];

			return stripslashes($row[1]) . ';'; // backticks needed.
			// return str_replace("`", "", stripslashes($row[1])).';';
		}
		else
		{
			$mes->addDebug('Failed: ' . $qry);
			$this->internalError = true;

			return false;
		}

	}

	/**
	 * @return array
	 */
	function getSqlLanguages()
	{

		$sql = e107::getDb();
		$list = $sql->tables('lan');

		$array = array();

		foreach($list as $tb)
		{
			list($tmp, $lang, $table) = explode("_", $tb, 3);
			$array[$lang][] = $table;
		}

		return $array;

	}


	/**
	 * @return void
	 */
	function renderTableSelect()
	{

		$frm = e107::getForm();
		$ns = e107::getRender();
		$mes = e107::getMessage();


		$text = "
		<form method='post' action='" . e_SELF . (e_QUERY ? '?' . e_QUERY : '') . "' id='core-db-verify-sql-tables-form'>
			<fieldset id='core-db-verify-sql-tables'>
				<legend>" . DBVLAN_14 . "</legend>
				<table class='table table-striped' >
					<colgroup>
						<col style='width: 33%'></col>
						<col style='width: 33%'></col>
						<col style='width: 33%'></col>
					</colgroup>
					<thead>
						<tr>
							<th class='first form-inline' colspan='3'><label for='check-all-verify-jstarget-verify-table'>" . $frm->checkbox_toggle('check-all-verify', 'verify_table') . " " . LAN_CHECKALL . ' | ' . LAN_UNCHECKALL . "</label></th>
						</tr>
					</thead>
					<tbody>
		";

		$c = 0;
		$plg = e107::getPlug();

		foreach(array_keys($this->sqlFileTables) as $t => $x)
		{
			if($x !== 'core')
			{
				$plg->load($x);
				if(!$plg->getId()) // no data.
				{
					$plg->load($x . '_menu');// try menu folder.
				}

				$icon = $plg->getIcon();
				$name = $plg->getName();

			}
			else
			{
				$icon = defset('E_16_E107');
				$name = LAN_CORE;
			}
			$text .= ($c === 0) ? "<tr>\n" : '';
			$text .= "<td title='" . $x . "'>" . $frm->checkbox('verify_table[' . $t . ']', $x, false, array('label' => $icon . ' ' . $name)) . "</td>";
			$text .= ($c === 2) ? "</tr>\n" : '';

			$c++;

			if($c > 2)
			{
				$c = 0;
			}

		}

		while(($c % 3) !== 0)
		{
			$text .= "<td>&nbsp;</td>\n";
			$text .= (($c + 1) % 3 == 0) ? "</tr>" : "";
			$c++;
		}

		/*	if($c !== 2)
			{
				$add = (3 - $c) + 1;

				$text .= "<td>".$c."</td>";

				$text .= "</tr>";
			}*/

		$text .= "
					</tbody>
					</table>
						<div class='buttons-bar center'>
							" . $frm->admin_button('db_verify', DBVLAN_15) . "
							" . $frm->admin_button('db_tools_back', LAN_BACK, 'back') . "
						</div>
					</fieldset>
				</form>
		";

		$ns->tablerender(DBVLAN_23 . SEP . DBVLAN_16, $mes->render() . $text);
	}

	/**
	 * Get the available storage engines on this MySQL server
	 *
	 * This method is not memoized and should not be called repeatedly.
	 *
	 * @return string[] An unordered list of the storage engines supported by the current MySQL server
	 */
	public function getAvailableStorageEngines()
	{

		$db = e107::getDb();
		$db->execute("SHOW ENGINES;");
		$output = [];
		while($row = $db->fetch())
		{
			$output[] = $row['Engine'];
		}

		return $output;
	}

	/**
	 * Get the most compatible MySQL storage engine on this server for the provided storage engine
	 *
	 * @param string|null $maybeStorageEngine The requested storage engine
	 * @return string|false The MySQL storage engine that should actually be used. false if no match found.
	 */
	public function getIntendedStorageEngine($maybeStorageEngine = null, array $requirements = array())
	{

		if($maybeStorageEngine === null)
		{
			return $this->getIntendedStorageEngine(self::MOST_PREFERRED_STORAGE_ENGINE, $requirements);
		}

		if(strtoupper($maybeStorageEngine) === 'MYISAM')
		{
			$maybeStorageEngine = 'MyISAM';
		}
		elseif(strtoupper($maybeStorageEngine) === 'INNODB')
		{
			$maybeStorageEngine = 'InnoDB';
		}

		if(!array_key_exists($maybeStorageEngine, $this->storageEnginePreferenceMap))
		{
			if(in_array($maybeStorageEngine, $this->availableStorageEngines))
			{
				return $maybeStorageEngine;
			}

			return false;
		}

		$fit = array_values(
			array_intersect($this->storageEnginePreferenceMap[$maybeStorageEngine], $this->availableStorageEngines)
		);

		if(empty($fit))
		{
			return false;
		}

		// A table that declares a FULLTEXT index cannot live on an engine that
		// has no FULLTEXT on this server, so walk past the preferred engine to
		// the first one that can carry it. MySQL gave InnoDB FULLTEXT in 5.6;
		// before that only MyISAM has it, and the whole table has to follow the
		// index.
		if(!empty($requirements['needsFulltext']))
		{
			foreach($fit as $candidate)
			{
				if($this->engineSupportsFulltext($candidate))
				{
					return $candidate;
				}
			}

			// Nothing in this table's own preference order can carry a FULLTEXT
			// index here, which on MySQL before 5.6 is every table that asks
			// InnoDB for one. Take the first engine that can.
			$capable = array_intersect($this->fulltextStorageEngineFallback, $this->availableStorageEngines);

			foreach($capable as $candidate)
			{
				if($this->engineSupportsFulltext($candidate))
				{
					return $candidate;
				}
			}
		}

		return $fit[0];
	}

	/**
	 * Whether the named storage engine can carry a FULLTEXT index on this server
	 *
	 * @param string $engine
	 * @return bool
	 */
	public function engineSupportsFulltext($engine)
	{

		$engine = strtolower((string) $engine);

		if($engine === 'innodb' || $engine === 'xtradb')
		{
			return $this->innodbSupportsFulltext();
		}

		// MyISAM has carried FULLTEXT for as long as it has existed, and Aria
		// is MyISAM's successor in MariaDB.
		return in_array($engine, array('myisam', 'aria', 'maria'), true);
	}

	/**
	 * Try to figure out what storage engine the provided one is referring to
	 *
	 * @param string $maybeStorageEngine The reported storage engine
	 * @return string The probable storage engine the input is referring to
	 * @throws UnexpectedValueException if the provided storage engine is not known as an available storage engine
	 */
	public function getCanonicalStorageEngine($maybeStorageEngine)
	{

		if(in_array($maybeStorageEngine, $this->availableStorageEngines))
		{
			return $maybeStorageEngine;
		}

		throw new UnexpectedValueException(
			"Unknown storage engine: " . var_export($maybeStorageEngine, true)
		);
	}

	/**
	 * Get the most compatible MySQL character set based on the input
	 *
	 * @param string|null $maybeCharset The requested character set. null to retrieve the default
	 * @return string The MySQL character set that should actually be used
	 */
	public function getIntendedCharset($maybeCharset = null, array $requirements = array())
	{

		$charset = empty($maybeCharset)
			? self::MOST_PREFERRED_CHARSET
			: $this->getCanonicalCharset($maybeCharset);

		return $this->narrowCharsetToIndexLimit($charset, $requirements);
	}

	/**
	 * Step a table down to a narrower character set when its widest index would
	 * not fit this server's key limit at four bytes per character.
	 *
	 * MySQL before 5.7 and MariaDB before 10.1 cap an InnoDB index at 767
	 * bytes, so a UNIQUE key over varchar(250) needs 1000 bytes and is refused
	 * with error 1071. Three-byte utf8 brings the same column to 750 and it
	 * fits.
	 *
	 * A live table whose indexed character columns already stand at the
	 * preferred character set, on this engine, with a key at least this wide,
	 * is never narrowed: the server built it, so the keys fit, whatever the
	 * probe says. Narrowing is for a table that has yet to be created.
	 *
	 * @param string $charset      the character set the table would otherwise get
	 * @param array  $requirements widestIndexChars, engine to measure against, and existingCharset when a live table proves it
	 * @return string
	 */
	private function narrowCharsetToIndexLimit($charset, array $requirements)
	{

		if($charset !== self::MOST_PREFERRED_CHARSET || empty($requirements['widestIndexChars']))
		{
			return $charset;
		}

		if(isset($requirements['existingCharset']) && $requirements['existingCharset'] === $charset)
		{
			return $charset;
		}

		// The limit depends on the engine, so the engine has to be decided
		// first; callers that adapt a table pass the engine they settled on.
		$engine = isset($requirements['engine']) ? $requirements['engine'] : self::MOST_PREFERRED_STORAGE_ENGINE;
		$limit = $this->maxIndexKeyBytes($engine);
		$chars = (int) $requirements['widestIndexChars'];

		if(($chars * 4) <= $limit)
		{
			return $charset;
		}

		// If even three bytes per character will not fit, narrowing solves
		// nothing: keep the preferred character set and let the CREATE TABLE
		// fail loudly rather than quietly building something else.
		return (($chars * 3) <= $limit) ? 'utf8' : $charset;
	}

	/**
	 * Settle the storage engine and character set a table should have on this
	 * server, from what the table itself needs.
	 *
	 * compare() and runFix() both have to reach this answer, and they have to
	 * reach the same one: compare() decides whether a table is wrong, runFix()
	 * decides what to change it to. When they disagree, compare() queues a
	 * 'convert' and getFixQuery() then finds nothing to change and hands back
	 * an empty statement. Doing the calculation in one place is what stops the
	 * two drifting apart.
	 *
	 * The order matters and is the reason this is not two independent calls:
	 * the engine decides the index key limit, and the key limit is what can
	 * narrow the character set, so the engine has to be settled first and fed
	 * back in.
	 *
	 * @param array  $fields          as returned by {@see getFields()}
	 * @param array  $indexes         as returned by {@see getIndex()}, with any derived indexes merged in
	 * @param string $declaredEngine  storage engine named by the .sql file
	 * @param string $declaredCharset character set named by the .sql file
	 * @param array|null $proof what the live table proves, as {@see liveKeyProof()} returns it; null when it does not exist
	 * @return array{engine:string|false, charset:string}
	 */
	private function intendedEngineAndCharset($fields, $indexes, $declaredEngine, $declaredCharset, $proof = null)
	{

		$requirements = $this->deriveTableRequirements($fields, $indexes);

		$engine = $this->getIntendedStorageEngine($declaredEngine, $requirements);

		$requirements['engine'] = $engine;

		if($proof !== null
			&& strcasecmp($proof['engine'], (string) $engine) === 0
			&& $proof['widestIndexChars'] >= $requirements['widestIndexChars'])
		{
			$requirements['existingCharset'] = $proof['charset'];
		}

		return array(
			'engine'  => $engine,
			'charset' => $this->getIntendedCharset($declaredCharset, $requirements),
		);
	}

	/**
	 * Derive what a table needs from its parsed fields and indexes, for
	 * {@see getIntendedStorageEngine()} and {@see getIntendedCharset()}.
	 *
	 * Only character columns are counted towards the index width, because they
	 * are the only ones whose byte cost changes with the character set.
	 *
	 * @param array $fields  as returned by {@see getFields()}
	 * @param array $indexes as returned by {@see getIndex()}
	 * @return array{needsFulltext:bool, widestIndexChars:int}
	 */
	public function deriveTableRequirements($fields, $indexes)
	{

		$needsFulltext = false;
		$widest = 0;

		if(empty($indexes) || !is_array($indexes))
		{
			return array('needsFulltext' => false, 'widestIndexChars' => 0);
		}

		foreach($indexes as $index)
		{
			if(!empty($index['type']) && strtoupper($index['type']) === 'FULLTEXT')
			{
				// A FULLTEXT index is not held to the key-length limit, so it
				// contributes the requirement but never the width.
				$needsFulltext = true;
				continue;
			}

			$chars = 0;
			$columns = isset($index['keyname']) ? explode(',', $index['keyname']) : array();

			foreach($columns as $column)
			{
				$column = trim($column);

				if(!isset($fields[$column]['type']))
				{
					continue;
				}

				$type = strtoupper($fields[$column]['type']);

				if($type !== 'VARCHAR' && $type !== 'CHAR')
				{
					continue;
				}

				$chars += (int) $fields[$column]['value'];
			}

			if($chars > $widest)
			{
				$widest = $chars;
			}
		}

		return array('needsFulltext' => $needsFulltext, 'widestIndexChars' => $widest);
	}

	/**
	 * Try to figure out what character set the provided one is referring to
	 *
	 * @param string $maybeCharset The reported character set
	 * @return string The probable character set
	 */
	public function getCanonicalCharset($maybeCharset)
	{

		if($maybeCharset == "utf8")
		{
			return "utf8mb4";
		}

		return $maybeCharset;
	}

	/**
	 * Ask the server which of the two limits that break old MySQL apply here.
	 *
	 * Memoised for the request rather than written to the db_verify file cache:
	 * these are two cheap queries, and a cached answer would outlive a server
	 * upgrade for the life of the cache entry.
	 *
	 * @return array{version:string, isMariaDb:bool, innodbLargePrefix:string|null}
	 */
	private function getServerCapabilities()
	{

		if($this->serverCapabilities !== null)
		{
			return $this->serverCapabilities;
		}

		$sql = e107::getDb();

		$version = '';
		$sql->execute('SELECT VERSION() AS server_version');
		if($row = $sql->fetch())
		{
			$version = (string) varset($row['server_version'], '');
		}

		// Absent on MySQL 8.0 and MariaDB 10.6, listed but inert on MariaDB
		// 10.3 to 10.5: {@see maxIndexKeyBytes()} weighs it by version.
		$largePrefix = null;
		$sql->execute("SHOW VARIABLES LIKE 'innodb_large_prefix'");
		if($row = $sql->fetch())
		{
			$largePrefix = strtoupper((string) varset($row['Value'], ''));
		}

		$this->serverCapabilities = array(
			'version'           => $version,
			'isMariaDb'         => stripos($version, 'mariadb') !== false,
			'innodbLargePrefix' => $largePrefix,
		);

		return $this->serverCapabilities;
	}

	/**
	 * The server version as a bare x.y.z string, without the vendor suffix
	 *
	 * @return string empty when the server did not report one
	 */
	private function getServerVersionNumber()
	{

		$caps = $this->getServerCapabilities();

		return preg_match('/^(\d+\.\d+(?:\.\d+)?)/', $caps['version'], $m) ? $m[1] : '';
	}

	/**
	 * Whether this server's InnoDB can carry a FULLTEXT index.
	 *
	 * MySQL gained InnoDB FULLTEXT in 5.6 and MariaDB in 10.0.5. Measured
	 * against mysql:5.5 (no), mysql:5.6 (yes) and mariadb:10.0 (yes).
	 *
	 * @return bool
	 */
	public function innodbSupportsFulltext()
	{

		$version = $this->getServerVersionNumber();

		if($version === '')
		{
			// An unreadable version is far more likely to mean a server this
			// code has never seen than a fifteen-year-old one, so assume the
			// capability and keep the previous behaviour.
			return true;
		}

		$caps = $this->getServerCapabilities();

		return $caps['isMariaDb']
			? version_compare($version, '10.0.5', '>=')
			: version_compare($version, '5.6', '>=');
	}

	/**
	 * The widest index in bytes that the given storage engine accepts here.
	 *
	 * @param string $engine
	 * @return int
	 */
	public function maxIndexKeyBytes($engine)
	{

		$engine = strtolower((string) $engine);

		if($engine === 'innodb' || $engine === 'xtradb')
		{
			$caps = $this->getServerCapabilities();

			return ($caps['innodbLargePrefix'] === 'OFF' && $this->innodbLargePrefixHasEffect()) ? 767 : 3072;
		}

		// MyISAM and Aria cap at 1000 on every server old enough for one of
		// them to be chosen here. Newer servers allow more; treating their
		// limit as 1000 only narrows a character set sooner, which is safe.
		return 1000;
	}

	/**
	 * Whether innodb_large_prefix still decides the InnoDB key limit here.
	 *
	 * MariaDB 10.3 to 10.5 keep the variable as a no-op that reads back empty, or OFF when a configuration sets it,
	 * while every table already gets 3072 bytes; MySQL 8.0 and MariaDB 10.6 removed it. Measured against
	 * mariadb:10.1 through 10.11 and mysql:5.7.
	 *
	 * @return bool
	 */
	private function innodbLargePrefixHasEffect()
	{

		$version = $this->getServerVersionNumber();

		if($version === '')
		{
			return false;
		}

		$caps = $this->getServerCapabilities();

		return $caps['isMariaDb']
			? version_compare($version, '10.3', '<')
			: version_compare($version, '8.0', '<');
	}

	/**
     * Initialize db_verify with table definitions and storage engine info
     *
     * @param bool $clearCache When true, clears all caches that db_verify depends on:
     *                         - MySQL table list cache (for accurate isTable() checks)
     *                         - Core preference cache (for fresh e_sql_list and e_search_list)
     *                         - db_verify's own file cache
     *                         Use this after creating/dropping tables or changing plugin preferences.
     * @return void
     */
    public function init($clearCache = false)
	{
		if($clearCache)
		{
			// Clear MySQL table list cache - isTable() uses a cached list that may be stale
			// if tables were created/dropped since the db handler was first used
			e107::getDb()->resetTableList();

			// Reload core preferences from database - load() uses e_sql_list which may have
			// changed if plugins were installed/uninstalled
			e107::getConfig('core')->clearPrefCache()->load(null, true);
		}

		$sql = e107::getDb();
		$sql->execute('SET SQL_QUOTE_SHOW_CREATE = 1');

		if(!deftrue('e_DEBUG') && ($clearCache === false) && $tmp = e107::getCache()->retrieve(self::cachetag, 15, true, true))
		{
			$cacheData = e107::unserialize($tmp);
			$this->sqlFileTables = isset($cacheData['sqlFileTables']) ? $cacheData['sqlFileTables'] : $this->load();
			$this->availableStorageEngines = isset($cacheData['availableStorageEngines']) ?
				$cacheData['availableStorageEngines'] : $this->getAvailableStorageEngines();
		}
		else
		{
			$this->sqlFileTables = $this->load();
			$this->availableStorageEngines = $this->getAvailableStorageEngines();
			$cacheData = e107::serialize([
				'sqlFileTables'           => $this->sqlFileTables,
				'availableStorageEngines' => $this->availableStorageEngines,
			], 'json');
			e107::getCache()->set(self::cachetag, $cacheData, true, true, true);
		}


		$this->sqlLanguageTables = $this->getSqlLanguages();
	}


}







