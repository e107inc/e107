<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

use db_verify;
use e107;
use e107\Database\Platform\MysqlPlatform;
use e107\Database\Platform\PlatformInterface;
use e107\Database\Schema\Column;
use e107\Database\Schema\Index;
use e107\Database\Schema\SchemaBuilder;
use e107_db_debug;
use PDO;

/**
 * Code shared verbatim by the two ConnectionInterface backends ({@see e_db_pdo} and
 * {@see e_db_mysql}).
 *
 * Before this trait existed, these methods were maintained as duplicated
 * copies in both classes and drifted apart over time. Every method here is
 * driver-agnostic: anything it needs from the driver goes through methods
 * the backends implement themselves (e.g. {@see ConnectionInterface::fetch()},
 * db_Query(), _escape()). A trait rather than a base class so the legacy
 * `class db extends ...` BC shims remain untouched.
 *
 * The e_db_parityTest reflection suite keeps the two backends' public
 * surfaces aligned; this trait is what makes most of that surface a single
 * implementation.
 *
 * Deprecated members here carry documentation pointer stubs only; the
 * canonical docblocks, the decision guide and the backwards-compatibility
 * commitment (avoid deprecated methods in new code and migrate call sites
 * when refactoring, while the methods themselves remain supported and
 * tested, with no removal planned) live at {@see ConnectionInterface}.
 */
trait ConnectionTrait
{
	/** @var PlatformInterface|null lazily created SQL dialect object */
	private     $platform = null;

	private     $pdoBind        = false;

	/*
	 * Shared connection state, kept here in a single copy for both backends.
	 * Driver-specific members (the $mySQLaccess handle and friends) remain in
	 * the backend classes.
	 */
	public      $mySQLPrefix;

	/** @var \PDOStatement|\mysqli_result|resource|int|bool result handle or row count of the last query */
	public      $mySQLresult;
	protected   $mySQLerror = false;			// Error reporting mode - TRUE shows messages

	protected   $mySQLlastErrNum = 0;		// Number of last error - now protected, use getLastErrorNumber()
	protected   $mySQLlastErrText = '';		// Text of last error - now protected, use getLastErrorText()

	protected   $mySQLcurTable;
	public      $mySQLlanguage;
	public      $tabset;
	public      $mySQLtableList = array(); // list of all Db tables.

	public      $mySQLtableListLanguage = array(); // Db table list for the currently selected language

	public      $mySQLcharset;

	public      $total_results = false;			// Total number of results

	/** @var e107_db_debug */
	private     $dbg;

	private     $debugMode      = false;

	/*
	 * Backend contract: the driver-specific methods this trait calls.
	 * Declared abstract so the dependency is explicit to readers and tooling,
	 * and signature-checked when the trait is composed (PHP 8+), instead of
	 * resolving invisibly at runtime. Signatures mirror the backends;
	 * e_db_parityTest keeps the two backends' copies identical.
	 */
	abstract public function gen($query, $debug = false, $log_type = '', $log_remark = '');
	abstract public function fetch($type = null);
	abstract public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = false, $log_type = '', $log_remark = '');
	abstract public function lastInsertId();
	abstract public function getFieldDefs($tableName);
	abstract public function db_Query($query, $rli = null, $qry_from = '', $debug = false, $log_type = '', $log_remark = '');
	abstract public function rowCount($result = null);
	abstract public function isTable($table, $language = '');
	abstract public function dbError($from);
	abstract public function fields($table, $prefix = '', $retinfo = false);
	abstract public function execute($sql, $params = array());

	abstract protected function _escape($data);
	abstract protected function _getTableList($language = '');
	abstract protected function _getMySQLaccess();

	/**
	 * Get system config
	 * @return \e_core_pref
	 */
	public function getConfig()
	{
		return e107::getConfig('core', false);
	}

	/**
	 * @param $bool
	 * @return void
	 */
	function debugMode($bool)
	{
		$this->debugMode = (bool) $bool;
	}

	/**
	 * @return mixed
	 */
	function getMode()
	{
		 $this->gen('SELECT @@sql_mode');
		 $row = $this->fetch();
		 return $row['@@sql_mode'];
	}

	/**
	* @return void
	* @param bool $mode
	* @desc Enter description here...
	* @access private
	*/
	function setErrorReporting($mode)
	{
		$this->mySQLerror = $mode;
	}

	/**
	*
	* @param string $sMarker
	* @desc Enter description here...
	 * @return null|true
	*/
	public function markTime($sMarker)
	{
		if($this->debugMode !== true)
		{
			return null;
		}

		$this->dbg->Mark_Time($sMarker);

		return true;
	}

	/**
	 * Resolve a logical e107 table name to its physical name: the database
	 * prefix is attached and, on multi-language sites, the table is routed to
	 * a language's lan_* table when one exists.
	 *
	 * @param string $table table name with or without a leading '#'
	 * @param string|null $language null: route for the connection's current
	 *                    language, honouring the multilanguage preference (the
	 *                    default); a language name, e.g. 'Spanish': route to
	 *                    that language's lan_* table when it exists, regardless
	 *                    of the current language or the multilanguage preference
	 * @return string|false physical table name (unquoted), or false when the
	 *                      name is not a valid identifier
	 */
	public function resolveTableName($table, $language = null)
	{
		$table = ltrim((string) $table, '#');

		if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
		{
			return false;
		}

		if($language !== null)
		{
			if($this->isTable($table, $language))
			{
				return $this->mySQLPrefix.'lan_'.strtolower($language.'_'.$table);
			}

			return $this->mySQLPrefix.$table;
		}

		return $this->mySQLPrefix.$this->hasLanguage($table);
	}

	/**
	 * Resolve a logical e107 table name to its physical name applying the
	 * database prefix only, never the multi-language lan_* routing that
	 * {@see ConnectionInterface::resolveTableName()} performs.
	 *
	 * Schema-maintenance tooling (db_verify, db_table_admin) addresses the
	 * literal table it parsed from a schema file and does its own language-table
	 * handling; routing such DDL would silently retarget it on multi-language
	 * sites. This resolver gives those callers the prefixed physical name with
	 * the same fail-closed identifier grammar.
	 *
	 * @param string $table table name with or without a leading '#'
	 * @return string|false physical table name (unquoted, prefix only), or false
	 *                      when the name is not a valid identifier
	 */
	public function resolvePhysicalTableName($table)
	{
		$table = ltrim((string) $table, '#');

		if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
		{
			return false;
		}

		return $this->mySQLPrefix.$table;
	}

	/**
	 * Documented at {@see ConnectionInterface::executeAllLanguages()}.
	 *
	 * @param string $sql
	 * @param array $parameters
	 * @return int|false statements executed (>= 1), or false when any leg failed
	 */
	public function executeAllLanguages($sql, $parameters = array())
	{
		$legs = array(false); // false: prefix-only resolution, the base tables

		$tables = $this->_markerTables($sql);

		if(!empty($tables) && ($variants = $this->hasLanguage($tables, true)))
		{
			foreach(array_keys($variants) as $language)
			{
				$legs[] = $language;
			}
		}

		$failedText = null;
		$failedNumber = null;

		foreach($legs as $language)
		{
			if($this->execute($this->_substituteTableNames($sql, $language), $parameters) === false
				&& $failedText === null)
			{
				$failedText = $this->getLastErrorText();
				$failedNumber = $this->getLastErrorNumber();
			}
		}

		if($failedText !== null)
		{
			// later successful legs reset the error state; resurface the first failure
			$this->mySQLlastErrText = $failedText;
			$this->mySQLlastErrNum = $failedNumber;

			return false;
		}

		return count($legs);
	}

	/**
	 * Validate and backtick-quote an SQL identifier (`column` or `table.column`).
	 * Fails closed: anything outside the {@see IdentifierFilter::identifier()} grammar returns false.
	 *
	 * @param string $identifier
	 * @return string|false
	 */
	public function quoteIdentifier($identifier)
	{
		if(!class_exists(IdentifierFilter::class))
		{
			require_once(__DIR__.'/IdentifierFilter.php');
		}

		return IdentifierFilter::identifier($identifier);
	}

	/**
	 * Create a fluent query builder bound to this connection; the full
	 * contract is documented at {@see ConnectionInterface::createQueryBuilder()}.
	 *
	 * @return QueryBuilder
	 */
	public function createQueryBuilder()
	{
		if(!class_exists(QueryBuilder::class))
		{
			// QueryBuilder hard-depends on SqlFragment (typed fragments) and ExpressionBuilder
			// (its expression helper, extracted to its own file); load them too so
			// the builder stays usable where the class autoloader is not active.
			if(!class_exists(SqlFragment::class))
			{
				require_once(__DIR__.'/SqlFragment.php');
			}

			if(!class_exists(ExpressionBuilder::class))
			{
				require_once(__DIR__.'/ExpressionBuilder.php');
			}

			require_once(__DIR__.'/QueryBuilder.php');
		}

		return new QueryBuilder($this);
	}

	/**
	 * Create a schema/DDL builder bound to this connection; the full contract is
	 * documented at {@see ConnectionInterface::createSchemaBuilder()}.
	 *
	 * @return SchemaBuilder
	 */
	public function createSchemaBuilder()
	{
		if(!class_exists(SchemaBuilder::class))
		{
			// SchemaBuilder accepts vouched SqlFragment fragments and structured
			// Column/Index value objects; load them too so the builder
			// stays usable where the class autoloader is not active.
			if(!class_exists(SqlFragment::class))
			{
				require_once(__DIR__.'/SqlFragment.php');
			}

			require_once(__DIR__.'/Schema/Column.php');
			require_once(__DIR__.'/Schema/Index.php');
			require_once(__DIR__.'/Schema/SchemaBuilder.php');
		}

		return new SchemaBuilder($this);
	}

	/**
	 * Shorthand for {@see ConnectionInterface::createSchemaBuilder()}.
	 *
	 * @return SchemaBuilder
	 */
	public function schema()
	{
		return $this->createSchemaBuilder();
	}

	/**
	 * SQL dialect of this connection, consulted by the query builder.
	 *
	 * @return PlatformInterface
	 */
	public function getPlatform()
	{
		if($this->platform === null)
		{
			if(!class_exists(MysqlPlatform::class))
			{
				require_once(__DIR__.'/Platform/MysqlPlatform.php');
			}

			$this->platform = new MysqlPlatform();
		}

		return $this->platform;
	}

	/**
	 * Quote-aware scan for '#table' markers: string literals, backticked
	 * identifiers and comments are consumed first, so a '#' inside them is
	 * never treated as a marker. Group 1 captures `#table`, group 2 bare #table.
	 *
	 * @var string
	 */
	private static $markerScan = '/\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|`#([A-Za-z0-9_]+)`|`[^`]*`|\/\*[\s\S]*?\*\/|--[^\r\n]*|#([A-Za-z0-9_]+)/';

	/**
	 * Replace `#table` (and bare #table) markers with physical table names via
	 * the {@see ConnectionTrait::$markerScan} scan.
	 *
	 * @param string $sql
	 * @param string|false|null $language null: route for the current language;
	 *                          false: attach the prefix only, no language
	 *                          routing; a language name: route to that
	 *                          language's lan_* tables where they exist
	 * @return string
	 */
	private function _substituteTableNames($sql, $language = null)
	{
		return preg_replace_callback(
			self::$markerScan,
			function ($matches) use ($language)
			{
				if(!empty($matches[1])) // `#table`
				{
					return '`'.$this->_resolveMarker($matches[1], $language).'`';
				}

				if(isset($matches[2]) && $matches[2] !== '') // bare #table
				{
					return $this->_resolveMarker($matches[2], $language);
				}

				return $matches[0];
			},
			$sql
		);
	}

	/**
	 * Resolve one marker table name for {@see ConnectionTrait::_substituteTableNames()}.
	 *
	 * @param string $table
	 * @param string|false|null $language see {@see ConnectionTrait::_substituteTableNames()}
	 * @return string|false
	 */
	private function _resolveMarker($table, $language)
	{
		if($language === false)
		{
			return $this->resolvePhysicalTableName($table);
		}

		return $this->resolveTableName($table, $language);
	}

	/**
	 * Collect the logical table names referenced by '#table' markers.
	 *
	 * @param string $sql
	 * @return string[] unique logical names, in order of first appearance
	 */
	private function _markerTables($sql)
	{
		$tables = array();

		if(!preg_match_all(self::$markerScan, $sql, $matches, PREG_SET_ORDER))
		{
			return $tables;
		}

		foreach($matches as $match)
		{
			if(!empty($match[1]))
			{
				$tables[$match[1]] = true;
			}
			elseif(isset($match[2]) && $match[2] !== '')
			{
				$tables[$match[2]] = true;
			}
		}

		return array_keys($tables);
	}

	/**
	 * Pick the bind type for an execute() parameter given as a plain value.
	 *
	 * @param mixed $value
	 * @return int ConnectionInterface::PARAM_*
	 */
	private function _detectParamType($value)
	{
		if($value === null)
		{
			return ConnectionInterface::PARAM_NULL;
		}

		if(is_int($value))
		{
			return ConnectionInterface::PARAM_INT;
		}

		if(is_bool($value))
		{
			return ConnectionInterface::PARAM_BOOL;
		}

		return ConnectionInterface::PARAM_STR;
	}

	/**
	 * Validate a caller-supplied identifier before it is placed in SQL, using
	 * the same grammar as {@see IdentifierFilter::identifier()}. The name is
	 * returned unquoted and unchanged, so the emitted SQL stays byte-identical
	 * for valid input; anything outside the grammar fails closed.
	 *
	 * The grammar is inlined rather than delegated to IdentifierFilter so the
	 * check stays self-contained no matter how this trait was loaded.
	 *
	 * @param string $name
	 * @param bool $allowDot true to also accept the `table.column` form
	 * @return string|false the trimmed name, or false on violation
	 */
	private function _safeIdentifier($name, $allowDot = false)
	{
		if(!is_string($name) && !is_numeric($name))
		{
			return false;
		}

		$name = trim((string) $name);

		$pattern = $allowDot ? '/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/D' : '/^[A-Za-z0-9_]+$/D';

		return preg_match($pattern, $name) ? $name : false;
	}

	/**
	 * Documented at {@see ConnectionInterface::escape()}.
	 *
	 * @deprecated v2.4.0 Bind values instead; see {@see ConnectionInterface::escape()}.
	 * @return string
	 */
	function escape($data, $strip = true)
	{
		$this->_notifyDeprecated('escape', 'Bind values instead of escaping them: the query builder ($sql->createQueryBuilder()) binds every value, and $sql->execute($query, $params) binds :named parameters in raw SQL.');

		return $this->_escape($data);
	}

	/**
	 * Emit one E_USER_DEPRECATED notice per deprecated method per call site
	 * per request. The class2.php error handler feeds these into the
	 * E107_DBG_DEPRECATED debug panel via {@see e107_db_debug::logDeprecated()}.
	 *
	 * @param string $method Deprecated method name, without parentheses
	 * @param string $advice One sentence naming the current replacement API
	 * @return void
	 */
	private function _notifyDeprecated($method, $advice)
	{
		static $notified = array();

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

		// Internal routing (retrieve() driving select(), a v1 shim delegating
		// to its replacement, the CRUD methods running through db_Query()) is
		// not a call site to warn about; only application code is.
		$callerFile = isset($trace[1]['file']) ? basename($trace[1]['file']) : '';
		if(in_array($callerFile, array('ConnectionTrait.php', 'e_db_pdo_class.php', 'mysql_class.php', 'e_db_legacy_trait.php'), true))
		{
			return;
		}

		$site = (isset($trace[1]['file']) ? $trace[1]['file'] : '?').':'.(isset($trace[1]['line']) ? $trace[1]['line'] : '?');

		if(isset($notified[$method.'|'.$site]))
		{
			return;
		}

		$notified[$method.'|'.$site] = true;
		trigger_error('<b>$sql->'.$method.'() is deprecated.</b> '.$advice.' Called from '.$site, E_USER_DEPRECATED); // NO LAN
	}

	/**
	 * Set the database language
	 * @param string $lang French, German etc.
	 */
	public function setLanguage($lang)
	{
		$this->mySQLlanguage = $lang;
	}

	/**
	 * Get the current database language. eg. English, French etc.
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->mySQLlanguage;
	}

	/**
	 * preg_replace_callback() callback that substitutes a matched table
	 * reference with its prefixed, language-routed physical name.
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function ml_check($matches)
	{
		$table = $this->hasLanguage($matches[1]);
		if($this->tabset == false)
		{
			$this->mySQLcurTable = $table;
			$this->tabset = true;
		}

		return " ".$this->mySQLPrefix.$table.substr($matches[0],-1);
	}

	/**
	 * Return the total number of results on the last query regardless of the LIMIT value when SELECT SQL_CALC_FOUND_ROWS is used.
	 * @return bool
	 */
	public function foundRows()
	{
		return $this->total_results;
	}

	/**
	 * Truncate a table
	 * @param string $table - table name without e107 prefix; fails closed on
	 *                        anything outside the [A-Za-z0-9_] identifier grammar
	 */
	function truncate($table=null)
	{
		if($table == null){ return null; }

		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		return $this->gen("TRUNCATE TABLE ".$this->mySQLPrefix.$table);
	}

	/**
	 * Check if a database table is empty or not.
	 * @param $table table name without the prefix; fails closed on anything
	 *               outside the [A-Za-z0-9_] identifier grammar
	 * @return bool
	 */
	function isEmpty($table=null)
	{
		if(empty($table) || ($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		$result = $this->gen("SELECT NULL FROM ".$this->mySQLPrefix.$table." LIMIT 1");

		if($result === 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Return a sorted list of parent/child tree with an optional where clause.
	 * @param string $table Name of table (without the prefix)
	 * @param string $parent Name of the parent field
	 * @param string $pid  Name of the primary id
	 * @param string $where (Optional ) where condition. Caller-supplied SQL:
	 *               never place user input here; bind it with {@see ConnectionInterface::execute()} instead.
	 * @param string $order Name of the order field.
	 * @todo Add extra params to each procedure so we only need 2 of them site-wide.
	 * @return boolean | int with the addition of  _treesort and _depth fields in the results.
	 */
	public function selectTree($table, $parent, $pid, $order, $where=null)
	{

		if(empty($table) || empty($parent) || empty($pid))
		{
			$this->mySQLlastErrText = "missing variables in sql->categories()";
			return false;
		}

		// table and column names fail closed outside the identifier grammar
		if(($table = $this->_safeIdentifier($table)) === false
			|| ($parent = $this->_safeIdentifier($parent, true)) === false
			|| ($pid = $this->_safeIdentifier($pid, true)) === false
			|| ($order = $this->_safeIdentifier($order, true)) === false)
		{
			$this->mySQLlastErrText = "invalid identifier in sql->selectTree()";
			return false;
		}

		$sql = "DROP FUNCTION IF EXISTS `getDepth` ;";

		$this->gen($sql);

		$sql = "
		CREATE FUNCTION `getDepth` (project_id INT) RETURNS int
		BEGIN
		    DECLARE depth INT;
		    SET depth=1;

		    WHILE project_id > 0 DO

		        SELECT IFNULL(".$parent.",-1)
		        INTO project_id
		        FROM ( SELECT ".$parent." FROM `#".$table."` WHERE ".$pid." = project_id) AS t;

		        IF project_id > 0 THEN
		            SET depth = depth + 1;
		        END IF;

		    END WHILE;

		    RETURN depth;

		END
		;
		";


		$this->gen($sql);

		$sql = "DROP FUNCTION IF EXISTS `getTreeSort`;";

		$this->gen($sql);

        $sql = "
        CREATE FUNCTION getTreeSort(incid INT)
        RETURNS CHAR(255)
        BEGIN
                SET @parentstr = CONVERT(incid, CHAR);
                SET @parent = -1;
                label1: WHILE @parent != 0 DO
                        SET @parent = (SELECT ".$parent." FROM `#".$table."` WHERE ".$pid." =incid);
                        SET @order = (SELECT ".$order." FROM `#".$table."` WHERE ".$pid." =incid);
                        SET @parentstr = CONCAT(if(@parent = 0,'',@parent), LPAD(@order,4,0), @parentstr);
                        SET incid = @parent;
                END WHILE label1;

                RETURN @parentstr;
        END
   ;

        ";


        $this->gen($sql);

        $qry =  "SELECT SQL_CALC_FOUND_ROWS *, getTreeSort(".$pid.") as _treesort, getDepth(".$pid.") as _depth FROM `#".$table."` ";

		if($where !== null)
		{
			$qry .= " WHERE ".$where;
		}


		$qry .= " ORDER BY _treesort";


		return $this->gen($qry);


	}

	/**
	 * @param $table
	 * @return array  field name => key name
	 */
	private function _getUnique($table)
	{

		$unique = array();

		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return $unique;
		}

		$result = $this->retrieve("SHOW INDEXES FROM #".$table, true);
		foreach($result as $row)
		{
			$notUnique = (int) $row['Non_unique'];

			if(!$notUnique)
			{
				$field = $row['Column_name'];
				$unique[$field] = $row['Key_name'];
			}

		}

		return $unique;
	}

	/**
	 *
	 */
	public function resetTableList()
	{
		$this->mySQLtableList = array();
		$this->mySQLtableListLanguage = array();
	}

	/**
	 * Duplicate a Table Row in a table.
	 *
	 * @param string $table table name without the prefix; fails closed
	 *               outside the [A-Za-z0-9_] identifier grammar
	 * @param string $fields '*' or a comma-separated list of column names;
	 *               every name fails closed outside the identifier grammar
	 * @param string $args WHERE clause. Caller-supplied SQL: never place user
	 *               input here; bind it with {@see ConnectionInterface::execute()} instead.
	 * @return int|false the copied row's id, or false on failure
	 */
	function copyRow($table, $fields = '*', $args='')
	{
		if(!$table || !$args )
		{
			return false;
		}

		if(($table = $this->_safeIdentifier($table)) === false)
		{
			$this->mySQLlastErrText = "copyRow \$table failed identifier validation";
			return false;
		}

		if($fields !== '*')
		{
			foreach(explode(',', $fields) as $fieldName)
			{
				if($this->_safeIdentifier($fieldName, true) === false)
				{
					$this->mySQLlastErrText = "copyRow \$fields failed identifier validation";
					return false;
				}
			}
		}

		for ($retries = 0; $retries < 3; $retries ++) {
			list($fieldList, $fieldList2) = $this->generateCopyRowFieldLists($table, $fields);

			if (empty($fieldList)) {
				$this->mySQLlastErrText = "copyRow \$fields list was empty";
				return false;
			}

			$beforeLastInsertId = $this->lastInsertId();
			$query = "INSERT INTO " . $this->mySQLPrefix . $table .
				"(" . $fieldList . ") SELECT " .
				$fieldList2 .
				" FROM " . $this->mySQLPrefix . $table .
				" WHERE " . $args;
			$id = $this->gen($query);
			$lastInsertId = $this->lastInsertId();
			if ($beforeLastInsertId !== $lastInsertId) break;
		}

		return ($id && $lastInsertId) ? $lastInsertId : false;
	}

	/**
	 * Determine before and after fields for a table
	 * @param $table string Table name, without the prefix
	 * @param $fields string Field list in query format (i.e. separated by commas) or all of them ("*")
	 * @return array Index 0 is before and index 1 is after
	 */
	private function generateCopyRowFieldLists($table, $fields)
	{
		if ($fields !== '*') return array($fields, $fields);

		$fieldList = $this->fields($table);
		$unique = $this->_getUnique($table);

		$flds = array();
		// randomize fields that must be unique.
		foreach ($fieldList as $fld) {
			if (isset($unique[$fld])) {
				$flds[] = $unique[$fld] === 'PRIMARY' ? 0 :
					"'rand-" . e107::getUserSession()->generateRandomString('***********') . "'";
				continue;
			}

			$flds[] = $fld;
		}

		$fieldList = implode(",", $fieldList);
		$fieldList2 = implode(",", $flds);
		return array($fieldList, $fieldList2);
	}

	/**
	 * @param string $oldtable
	 * @param string $newtable
	 * @param bool $drop
	 * @param bool $data
	 * @return bool|int
	 */
	public function copyTable($oldtable, $newtable, $drop = false, $data = false)
	{
		if(($oldtable = $this->_safeIdentifier($oldtable)) === false || ($newtable = $this->_safeIdentifier($newtable)) === false)
		{
			return false;
		}

		$old = $this->mySQLPrefix.strtolower($oldtable);
		$new = $this->mySQLPrefix.strtolower($newtable);

		if ($drop)
		{
			$this->gen("DROP TABLE IF EXISTS {$new}");
		}

		//Get $old table structure
		$this->gen('SET SQL_QUOTE_SHOW_CREATE = 1');

		$qry = "SHOW CREATE TABLE {$old}";
		if ($this->gen($qry))
		{
			$row = $this->fetch('num');
			$qry = $row[1];
			//        $qry = str_replace($old, $new, $qry);
			$qry = preg_replace("#CREATE\sTABLE\s`?".$old."`?\s#", "CREATE TABLE {$new} ", $qry, 1); // More selective search
		}
		else
		{
			return false;
		}

		if(!$this->isTable($newtable))
		{
			$result = $this->db_Query($qry);
		}

		if ($data) //We need to copy the data too
		{
			$qry = "INSERT INTO {$new} SELECT * FROM {$old}";
			$result = $this->gen($qry);
		}
		return $result;
	}

	/**
	 * Drop/delete table and all it's data
	 * @param string $table name without the prefix; fails closed on anything
	 *               outside the [A-Za-z0-9_] identifier grammar
	 * @return bool|int
	 */
	public function dropTable($table)
	{
		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		$name = $this->mySQLPrefix.strtolower($table);
		return $this->gen("DROP TABLE IF EXISTS ".$name);
	}

	/**
	 * @return int
	 */
	function getLastErrorNumber()
	{
		return $this->mySQLlastErrNum;		// Number of last error
	}

	/**
	 * @return string
	 */
	function getLastErrorText()
	{
		return $this->mySQLlastErrText;		// Text of last error (empty string if no error)
	}

	/**
	 * @return void
	 */
	function resetLastError()
	{
		$this->mySQLlastErrNum = 0;
		$this->mySQLlastErrText = '';
	}

	/**
	 * @return mixed
	 */
	public function getCharset()
	{
		require_once(e_HANDLER."db_verify_class.php");
		return (new db_verify())->getIntendedCharset($this->mySQLcharset);
	}

	/**
	 * Documented at {@see ConnectionInterface::retrieve()}.
	 *
	 * @deprecated v2.4.0 Prefer the query builder; see {@see ConnectionInterface::retrieve()}.
	 * @return mixed
	 */
	public function retrieve($table=null, $fields = null, $where=null, $multi = false, $indexField = null, $debug = false)
	{
		$this->_notifyDeprecated('retrieve', 'Use the query builder: $sql->createQueryBuilder()->select(...)->from(\'table\')->where(...)->fetchAll(), ->fetchRow() or ->fetchOne().');

		// fetch mode
		if(empty($table))
		{

			if(!$multi)
			{
				 return $this->fetch();
			}

			$ret = array();

			while($row = $this->fetch())
			{
				if(null !== $indexField)
				{
					 $ret[$row[$indexField]] = $row;
				}
				else
				{
					 $ret[] = $row;
				}
			}
			return $ret;
		}

		// detect mode
		$mode = 'one';
		if($table && !$where && is_bool($fields))
		{
			// table is the query, fields used for multi
			if($fields)
			{
				 $mode = 'multi';
			}
			else
			{
				 $mode = 'single';
			}

			$fields = null;
		}
		elseif($fields && '*' !== $fields && strpos($fields, ',') === false && $where)
		{
			$mode = 'single';
		}

		if($multi)
		{
			$mode = 'multi';
		}

		// detect query type
		$select = true;
		$noWhere = false;
		if(!$fields && !$where)
		{
			// gen()
			$select = false;
			if($mode == 'one' && !preg_match('/[,*]+[\s\S]*FROM/im',$table)) // if a comma or astericks is found before "FROM" then leave it in 'one' row mode.
			{
			    $mode = 'single';
			}
		}
		// auto detect noWhere - if where string starts with upper case LATIN word
		elseif(!$where || preg_match('/^[A-Z]+\S.*$/', trim($where)))
		{
			// FIXME - move auto detect to select()?
			$noWhere = true;
		}


		// execute & fetch
		switch ($mode)
		{
			case 'single': // single field value returned.
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					$this->mySQLcurTable = $table;
					return null;
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return null;
				}
				$rows = $this->fetch();
				return array_shift($rows);
			break;

			case 'one': // one row returned.
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					return array();
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return array();
				}
				return $this->fetch();
			break;

			case 'multi':
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					return array();
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return array();
				}
				$ret = array();
				while($row = $this->fetch())
				{
					if(null !== $indexField) $ret[$row[$indexField]] = $row;
					else $ret[] = $row;
				}
				return $ret;
			break;

		}

		return null;
	}

	/**
	 * Documented at {@see ConnectionInterface::rows()}.
	 *
	 * @return array
	 */
	function rows($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false)
	{
		$list = array();
		$counter = 1;
		while ($row = $this->fetch())
		{
			foreach($row as $key => $value)
			{
				if (is_string($key))
				{
					if (strtoupper($fields) == 'ALL' || in_array ($key, $fields))
					{
						if(!$ordermode)
						{
							$list[$counter][$key] = $value;
						}
						else
						{
							$list[$row[$ordermode]][$key] = $value;
						}
					}
				}
			}
			if ($amount && $amount == $counter || ($maximum && $counter > $maximum))
			{
				break;
			}
			$counter++;
		}
		return $list;
	}

	/**
	 * Documented at {@see ConnectionInterface::max()}.
	 *
	 * @deprecated v2.4.0 Prefer the query builder; see {@see ConnectionInterface::max()}.
	 * @return mixed
	 */
	public function max($table, $field, $where='')
	{
		$this->_notifyDeprecated('max', 'Use the query builder: $sql->createQueryBuilder()->selectAggregate(\'MAX\', \'field\')->from(\'table\')->fetchOne().');

		if(($table = $this->_safeIdentifier($table)) === false || ($field = $this->_safeIdentifier($field, true)) === false)
		{
			return null;
		}

		$qry = "SELECT MAX(".$field.") FROM ".$this->mySQLPrefix.$table;

		if(!empty($where))
		{
			$qry .= " WHERE ".$where;
		}

		return $this->retrieve($qry);

	}

	/**
	 *	Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $fieldid - Numeric offset or field/key name
	 *	@param string $key - PRIMARY|INDEX|UNIQUE - type of key when searching for key name
	 *	@param boolean $retinfo = false - just returns true|false. TRUE - returns all field info
	 *	@return array|boolean - false on error, field information on success
	 */
	function field($table,$fieldid="",$key="", $retinfo = false)
	{

		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		$convert = array("PRIMARY"=>"PRI","INDEX"=>"MUL","UNIQUE"=>"UNI");
		$key = (isset($convert[$key])) ? $convert[$key] : "OFF";

		$this->_getMySQLaccess();

        $result = $this->gen("SHOW COLUMNS FROM ".$this->mySQLPrefix.$table);
        if ($result && ($this->rowCount() > 0))
		{
			$c=0;
			while ($row = $this->fetch())
			{
				if(is_numeric($fieldid))
				{
					if($c == $fieldid)
					{
						if ($retinfo) return $row;
						return $row['Field']; // field number matches.
					}
				}
				else
				{	// Check for match of key name - and allow that key might not be used
					if(($fieldid == $row['Field']) && (($key == "OFF") || ($key == $row['Key'])))
					{
						if ($retinfo) return $row;
						return true;
					}
				}
				$c++;
			}
		}
		return false;
	}

	/**
	 *	Determines if a table index (key) exist.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $keyname - Name of the key to
	 *  @param array $fields - OPTIONAL list of fieldnames, the index (key) must contain
	 *	@param boolean $retinfo = false - just returns true|false. TRUE - returns all key info
	 *	@return array|boolean - false on error, key information on success
	 */
	function index($table, $keyname, $fields=null, $retinfo = false)
	{

		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		$this->_getMySQLaccess();

		if (!empty($fields) && !is_array($fields))
		{
			$fields = explode(',', str_replace(' ', '', $fields));
		}
		elseif(empty($fields))
		{
			$fields = array();
		}

		$check_field = count($fields) > 0;

		$info = array();
		$result = $this->gen("SHOW INDEX FROM ".$this->mySQLPrefix.$table);
		if ($result && ($this->rowCount() > 0))
		{
			$c=0;
			while ($row = $this->fetch())
			{
				// Check for match of key name - and allow that key might not be used
				if($keyname == $row['Key_name'])
				{
					// a key can contain severeal fields which are returned as 1 row per field
					if (!$check_field)
					{   // Check only for keyname
						$info[] = $row;
					}
					elseif ($check_field && in_array($row['Column_name'], $fields))
					{   // Check also for fieldnames
						$info[] = $row;
					}
					$c++;
				}
			}

			if (count($info) > 0)
			{
				// Kex does not consist of all keys
				if ($check_field && $c != count($fields)) return false;
				// Return full information
				if ($retinfo) return $info;
				// Return only if index was found
				return true;
			}
		}
		return false;
	}

	/**
	* Check for the existence of a matching language table when multi-language tables are active.
	* @param string|array $table Name of table, without the prefix. or an array of table names.
	* @access private
	* @return array|false|string the name of the language table (eg. lan_french_news) or an array of all matching language tables. (with mprefix)
	*/
	public function hasLanguage($table, $multiple=false)
	{
		//When running a multi-language site with english included. English must be the main site language.
		// WARNING!!! false is critical important - if missed, expect dead loop (prefs are calling db handler as well when loading)
		// Temporary solution, better one is needed
		$core_pref = $this->getConfig();
		//if ((!$this->mySQLlanguage || !$pref['multilanguage'] || $this->mySQLlanguage=='English') && $multiple==false)
		if ((!$this->mySQLlanguage || !$core_pref->get('multilanguage') || !$core_pref->get('sitelanguage') /*|| $this->mySQLlanguage==$core_pref->get('sitelanguage')*/) && $multiple==false)
		{
		  	return $table;
		}

		$this->_getMySQLaccess();

		if($multiple == false)
		{
			$mltable = "lan_".strtolower($this->mySQLlanguage.'_'.$table);
			return ($this->isTable($table,$this->mySQLlanguage) ? $mltable : $table);
		}
		else // return an array of all matching language tables. eg [french]->e107_lan_news
		{
			if(!is_array($table))
			{
				$table = array($table);
			}

			if(!$this->mySQLtableList)
			{
				$this->mySQLtableList = $this->_getTableList();
			}

			$lanlist = array();

			foreach($this->mySQLtableList as $tab)
			{

 				if(strpos($tab,"lan_") === 0)
				{
					list($tmp,$lng,$tableName) = explode("_",$tab,3);

                    foreach($table as $t)
					{
						if($tableName == $t)
						{
							$lanlist[$lng][$this->mySQLPrefix.$t] = $this->mySQLPrefix.$tab; // prefix needed.
						}

					}
			  	}
			}

			if(empty($lanlist))
			{
				return false;
			}
			else
			{
				return $lanlist;
			}


		}
	// -------------------------


	}

	/**
	 * Documented at {@see ConnectionInterface::insert()}.
	 *
	 * @return int|bool Last insert ID or false on error. When using '_DUPLICATE_KEY_UPDATE' return ID, true on update, 0 on no change and false on error.
	 * @deprecated v2.4.0 Prefer the query builder; see {@see ConnectionInterface::insert()}.
	 */
	function insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('insert', 'Use the query builder: $sql->createQueryBuilder()->insert(\'table\')->values($row)->execute().');

		$table = $this->hasLanguage($tableName);
		$this->mySQLcurTable = $table;
		$REPLACE = false; // kill any PHP notices
		$DUPEKEY_UPDATE = false;
		$IGNORE = '';

		if(is_array($arg))
		{
			if(isset($arg['WHERE'])) // use same array for update and insert.
			{
				unset($arg['WHERE']);
			}

			if(isset($arg['_REPLACE']))
			{
				$REPLACE = TRUE;
				unset($arg['_REPLACE']);
			}

			if(isset($arg['_DUPLICATE_KEY_UPDATE']))
			{
				$DUPEKEY_UPDATE = true;
				unset($arg['_DUPLICATE_KEY_UPDATE']);
			}

			if(isset($arg['_IGNORE']))
			{
				$IGNORE = ' IGNORE';
				unset($arg['_IGNORE']);
			}

			if(!isset($arg['_FIELD_TYPES']) && !isset($arg['data']))
			{
		   	//Convert data if not using 'new' format
				$_tmp = array();
				$_tmp['data'] = $arg;
				$arg = $_tmp;
				unset($_tmp);
			}

			if(!isset($arg['data'])) { return false; }


			// See if we need to auto-add field types array
			if(!isset($arg['_FIELD_TYPES']))
			{
				$fieldDefs = $this->getFieldDefs($tableName);
				if (is_array($fieldDefs)) $arg = array_merge($arg, $fieldDefs);
			}

			$argUpdate = $arg;  // used when DUPLICATE_KEY_UPDATE is active;


			// Handle 'NOT NULL' fields without a default value
			if (isset($arg['_NOTNULL']))
			{
				foreach ($arg['_NOTNULL'] as $f => $v)
				{
					if (!isset($arg['data'][$f]))
					{
						$arg['data'][$f] = $v;
					}
				}
			}


			$fieldTypes = $this->_getTypes($arg);
			$keyList= '`'.implode('`,`', array_keys($arg['data'])).'`';
			$tmp = array();
			$bind = array();

			foreach($arg['data'] as $fk => $fv)
			{
				$tmp[] = ':'.$fk;
				$fieldType = isset($fieldTypes[$fk]) ? $fieldTypes[$fk] : null;
				$bind[$fk] = array('value'=>$this->_getPDOValue($fieldType,$fv), 'type'=> $this->_getPDOType($fieldType,$this->_getPDOValue($fieldType,$fv)));
			}

			$valList= implode(', ', $tmp);


			unset($tmp);



			if($REPLACE === false)
			{
				$query = "INSERT".$IGNORE." INTO ".$this->mySQLPrefix."{$table} ({$keyList}) VALUES ({$valList})";

				if($DUPEKEY_UPDATE === true)
				{
					$query .= " ON DUPLICATE KEY UPDATE ";
					$query .= $this->_prepareUpdateArg($tableName, $argUpdate);
				}

			}
			else
			{
				$query = "REPLACE INTO ".$this->mySQLPrefix."{$table} ({$keyList}) VALUES ({$valList})";
			}


			$query = array(
				'PREPARE' => $query,
				'BIND'  => $bind,
			);



		}
		else
		{
			$query = 'INSERT INTO '.$this->mySQLPrefix."{$table} VALUES ({$arg})";
		}

		$this->_getMySQLaccess();

		$this->mySQLresult = $this->db_Query($query, NULL, 'db_Insert', $debug, $log_type, $log_remark);

		if($DUPEKEY_UPDATE === true)
		{
			$result = false; // ie. there was an error.

			if($this->mySQLresult === 1 ) // insert.
			{
				$result = $this->lastInsertId();
			}
			elseif($this->mySQLresult === 2 || $this->mySQLresult === true) // updated
			{
				$result = true;
				// reset auto-increment to prevent gaps.
				$this->db_Query("ALTER TABLE ".$this->mySQLPrefix.$table."  AUTO_INCREMENT=1", NULL, 'db_Insert', $debug, $log_type, $log_remark);
			}
			elseif($this->mySQLresult === 0) // updated (no change)
			{
				$result = 0;
			}

			$this->dbError('db_Insert');
			return $result;
		}


		if ($this->mySQLresult)
		{
			if(true === $REPLACE)
			{
				$tmp = $this->mySQLresult ;
				$this->dbError('db_Replace');
				// $tmp == -1 (error), $tmp == 0 (not modified), $tmp == 1 (added), greater (replaced)
				if ($tmp == -1) { return false; } // mysql_affected_rows error
				return $tmp;
			}

		//	$tmp = ($this->pdo) ? $this->mySQLaccess->lastInsertId() : mysql_insert_id($this->mySQLaccess);

			$tmp = $this->lastInsertId();

			$this->dbError('db_Insert');
			return ($tmp) ? $tmp : TRUE; // return true even if table doesn't have auto-increment.
		}
		else
		{
		//	$this->dbError("db_Insert ({$query})");
			return false;
		}
	}

	/**
	 * Documented at {@see ConnectionInterface::replace()}.
	 *
	 * @return int Last insert ID or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see ConnectionInterface::replace()}.
	 */
	function replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('replace', 'Use the query builder: $sql->createQueryBuilder()->replace(\'table\')->values($row)->execute().');

		$arg['_REPLACE'] = TRUE;
		return $this->insert($table, $arg, $debug, $log_type, $log_remark);
	}

	/**
	 * @param $tableName
	 * @param $arg
	 * @return false|mixed|string
	 */
	private function _prepareUpdateArg($tableName, $arg)
	{
		$this->pdoBind = array();
		if (is_array($arg))  // Remove the need for a separate db_UpdateArray() function.
	  	{

			if(!isset($arg['_FIELD_TYPES']) && !isset($arg['data']))
		   	{
			   	//Convert data if not using 'new' format
		   		$_tmp = array();
		   		if(isset($arg['WHERE']))
		   		{
		   			$_tmp['WHERE'] = $arg['WHERE'];
		   			unset($arg['WHERE']);
		   		}
		   		$_tmp['data'] = $arg;
		   		$arg = $_tmp;
		   		unset($_tmp);
		   	}

	   		if(!isset($arg['data'])) { return false; }

			// See if we need to auto-add field types array
			if(!isset($arg['_FIELD_TYPES']))
			{
				$fieldDefs = $this->getFieldDefs($tableName);
				if (is_array($fieldDefs)) $arg = array_merge($arg, $fieldDefs);
			}

			$fieldTypes = $this->_getTypes($arg);


			$new_data = '';
			//$this->pdoBind = array(); // moved up to the beginning of the method to make sure it is initialized properly
			foreach ($arg['data'] as $fn => $fv)
			{
				$new_data .= ($new_data ? ', ' : '');
				$ftype =  isset($fieldTypes[$fn]) ? $fieldTypes[$fn] : 'str';

				$new_data .= ($ftype !='cmd') ? "`{$fn}`= :". $fn : "`{$fn}`=".$this->_getFieldValue($fn, $fv, $fieldTypes);

				if($fv === '_NULL_')
				{
					$ftype = 'null';
				}

				if($ftype != 'cmd')
				{
					$this->pdoBind[$fn] = array('value'=>$this->_getPDOValue($ftype,$fv), 'type'=> $this->_getPDOType($ftype,$this->_getPDOValue($ftype,$fv)));
				}
			}

			$arg = $new_data .(isset($arg['WHERE']) ? ' WHERE '. $arg['WHERE'] : '');

		}

		return $arg;

	}

	/**
	 * Documented at {@see ConnectionInterface::update()}.
	 *
	 * @return int|false number of affected rows, or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see ConnectionInterface::update()}.
	 */
	function update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('update', 'Use the query builder: $sql->createQueryBuilder()->update(\'table\')->set(\'col\', $value)->where(...)->execute().');

		$table = $this->hasLanguage($tableName);
		$this->mySQLcurTable = $table;

		$this->_getMySQLaccess();

		$arg = $this->_prepareUpdateArg($tableName, $arg);

		$query = 'UPDATE '.$this->mySQLPrefix.$table.' SET '.$arg;

		if(!empty($this->pdoBind))
		{
			$query = array(
					'PREPARE' => $query,
					'BIND'  => $this->pdoBind,
			);
		}

		$result = $this->mySQLresult = $this->db_Query($query, NULL, 'db_Update', $debug, $log_type, $log_remark);

		if ($result !==false)
		{

			if(is_object($result) || $result === true)
			{
					// make sure to return the number of records affected, instead of an object
				$result = $this->rowCount();
			}


			$this->dbError('db_Update');
			if ($result === -1) { return false; }	// Error return from mysql_affected_rows
			return $result;
		}
		else
		{
			$this->dbError('db_Update ('.print_r($query, true).')');
			return false;
		}
	}

	/**
	 * @param $arg
	 * @return array|mixed
	 */
	private function _getTypes(&$arg)
	{
		if(isset($arg['_FIELD_TYPES']))
		{
			if(!isset($arg['_FIELD_TYPES']['_DEFAULT']))
			{
				$arg['_FIELD_TYPES']['_DEFAULT'] = 'string';
			}
			$fieldTypes = $arg['_FIELD_TYPES'];
			unset($arg['_FIELD_TYPES']);
		}
		else
		{
			$fieldTypes = array();
			$fieldTypes['_DEFAULT'] = 'string';
		}
		return $fieldTypes;
	}

	/**
	* @param string|array $fieldValue
	 * @desc Return new field value in proper format<br />
	*
	* @access private
	*@return array|float|int|string
	*/
	private function _getFieldValue($fieldKey, $fieldValue, &$fieldTypes)
	{
		if($fieldValue === '_NULL_') { return 'NULL';}
		$type = (isset($fieldTypes[$fieldKey]) ? $fieldTypes[$fieldKey] : $fieldTypes['_DEFAULT']);

		switch ($type)
		{
			case 'int':
			case 'integer':
				return (int) $fieldValue;
			break;

			case 'cmd':
				return $fieldValue;
			break;

			case 'safestr':
				return "'{$fieldValue}'";
			break;

			case 'str':
			case 'string':
				//return "'{$fieldValue}'";
				return "'".$this->_escape($fieldValue)."'";
			break;

			case 'float':
				// fix - convert localized float numbers
				// $larr = localeconv();
				// $search = array($larr['decimal_point'], $larr['mon_decimal_point'], $larr['thousands_sep'], $larr['mon_thousands_sep'], $larr['currency_symbol'], $larr['int_curr_symbol']);
				// $replace = array('.', '.', '', '', '', '');

				// return str_replace($search, $replace, floatval($fieldValue));

				return e107::getParser()->toNumber($fieldValue);
			break;

			case 'null':
				//return ($fieldValue && $fieldValue !== 'NULL' ? "'{$fieldValue}'" : 'NULL');
				return ($fieldValue && $fieldValue !== 'NULL' ? "'".$this->_escape($fieldValue)."'" : 'NULL');
				break;

			case 'array':
				if(is_array($fieldValue))
				{
					return "'".e107::serialize($fieldValue, true)."'";
				}
				return "'". (string) $fieldValue."'";
			break;

			case 'todb': // using as default causes serious BC issues.
				if($fieldValue == '') { return "''"; }
				return "'".e107::getParser()->toDB($fieldValue)."'";
			break;

			case 'escape':
			default:
				return "'".$this->_escape($fieldValue)."'";
			break;
	  	}
	}

	/**
	 * Return a value for use in PDO bindValue() - based on field-type.
	 * @param $type
	 * @param $fieldValue
	 * @return int|string|null
	 */
	private function _getPDOValue($type, $fieldValue)
	{


		if(is_string($fieldValue) && ($fieldValue === '_NULL_'))
		{
			$type = 'null';
		}

		switch($type)
		{
			case "int":
			case "integer":
				return (int) $fieldValue;
				break;



			case 'float':
				// fix - convert localized float numbers
				// $larr = localeconv();
				// $search = array($larr['decimal_point'], $larr['mon_decimal_point'], $larr['thousands_sep'], $larr['mon_thousands_sep'], $larr['currency_symbol'], $larr['int_curr_symbol']);
				// $replace = array('.', '.', '', '', '', '');

				// return str_replace($search, $replace, floatval($fieldValue));
				return e107::getParser()->toNumber($fieldValue);
			break;

			case 'null':
			    return (
                    is_string($fieldValue) && (
                        ($fieldValue !== '_NULL_') && ($fieldValue !== '')
                    )
                ) ? $fieldValue : null;
				break;

			case 'array':
				if(is_array($fieldValue))
				{
					return e107::serialize($fieldValue);
				}
				return $fieldValue;
			break;

			case 'todb': // using as default causes serious BC issues.
				if($fieldValue == '') { return ''; }
				return e107::getParser()->toDB($fieldValue);
			break;

				case 'cmd':
			case 'safestr':
			case 'str':
			case 'string':
			case 'escape':
			default:

				return $fieldValue;
				break;

		}


	}

	/**
	 * Convert FIELD_TYPE to a bind type for the prepared-statement contract.
	 * @param $type
	 * @param null $value
	 * @return int ConnectionInterface::PARAM_* constant (value-identical to PDO::PARAM_*)
	 */
	private function _getPDOType($type, $value = null)
	{
		switch($type)
		{
			case "int":
			case "integer":
				return ConnectionInterface::PARAM_INT;
				break;

			case 'null':
				return ($value === null) ? ConnectionInterface::PARAM_NULL : ConnectionInterface::PARAM_STR;
				break;

			case 'cmd':
			case 'safestr':
			case 'str':
			case 'string':
			case 'escape':
			case 'array':
			case 'todb':
			case 'float':
				return ConnectionInterface::PARAM_STR;
				break;

		}

		// e107::getMessage()->addDebug("MySQL Missing Field-Type: ".$type);
		return ConnectionInterface::PARAM_STR;
	}

	/**
	 * Apply the e107 field-type STORAGE transform to a value, returning exactly
	 * what the deprecated array-form {@see ConnectionInterface::insert()}/{@see ConnectionInterface::update()}
	 * would bind for that token ('int', 'float', 'array', 'todb', 'null', 'str',
	 * 'safestr', 'escape', 'cmd', ...). This is the public face of the single
	 * transform body {@see ConnectionTrait::_getPDOValue()}, shared with the
	 * query builder ({@see QueryBuilder::setTyped()},
	 * {@see QueryBuilder::valuesTyped()}) so builder writes are byte-identical to
	 * the legacy CRUD path.
	 *
	 * @param string $type Field-type token.
	 * @param mixed $fieldValue
	 * @return mixed transformed value ready for bindValue()
	 */
	public function applyFieldType($type, $fieldValue)
	{
		return $this->_getPDOValue($type, $fieldValue);
	}

	/**
	 * The bind type ({@see ConnectionInterface}::PARAM_*) for a field-type token, matching the
	 * legacy bind tuple. Pass the ALREADY-transformed value (the result of
	 * {@see ConnectionInterface::applyFieldType()}), exactly as the array-form CRUD does.
	 *
	 * @param string $type Field-type token.
	 * @param mixed $value Transformed value; consulted only for the 'null' type.
	 * @return int ConnectionInterface::PARAM_* constant
	 */
	public function fieldTypeBind($type, $value = null)
	{
		return $this->_getPDOType($type, $value);
	}
}
