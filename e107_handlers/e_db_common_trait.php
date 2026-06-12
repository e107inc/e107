<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Code shared verbatim by the two e_db backends ({@see e_db_pdo} and
 * {@see e_db_mysql}).
 *
 * Before this trait existed, these methods were maintained as duplicated
 * copies in both classes and drifted apart over time. Every method here is
 * driver-agnostic: anything it needs from the driver goes through methods
 * the backends implement themselves (e.g. {@see e_db::fetch()},
 * db_Query(), _escape()). A trait rather than a base class so the legacy
 * `class db extends ...` BC shims remain untouched.
 *
 * The e_db_parityTest reflection suite keeps the two backends' public
 * surfaces aligned; this trait is what makes most of that surface a single
 * implementation.
 */
trait e_db_common
{
	/** @var e_db_platform|null lazily created SQL dialect object */
	private     $platform = null;

	/**
	 * Get system config
	 * @return e_core_pref
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
	 * the current language's lan_* table when one exists.
	 *
	 * @param string $table table name with or without a leading '#'
	 * @return string|false physical table name (unquoted), or false when the
	 *                      name is not a valid identifier
	 */
	public function resolveTableName($table)
	{
		$table = ltrim((string) $table, '#');

		if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
		{
			return false;
		}

		return $this->mySQLPrefix.$this->hasLanguage($table);
	}

	/**
	 * Validate and backtick-quote an SQL identifier (`column` or `table.column`).
	 * Fails closed: anything outside the {@see e_db_filter::identifier()} grammar returns false.
	 *
	 * @param string $identifier
	 * @return string|false
	 */
	public function quoteIdentifier($identifier)
	{
		if(!class_exists('e_db_filter'))
		{
			require_once(__DIR__.'/e_db_filter_class.php');
		}

		return e_db_filter::identifier($identifier);
	}

	/**
	 * Create a fluent query builder bound to this connection; the full
	 * contract is documented at {@see e_db::createQueryBuilder()}.
	 *
	 * @return e_db_query
	 */
	public function createQueryBuilder()
	{
		if(!class_exists('e_db_query'))
		{
			require_once(__DIR__.'/e_db_query_class.php');
		}

		return new e_db_query($this);
	}

	/**
	 * SQL dialect of this connection, consulted by the query builder.
	 *
	 * @return e_db_platform
	 */
	public function getPlatform()
	{
		if($this->platform === null)
		{
			if(!class_exists('e_db_platform_mysql'))
			{
				require_once(__DIR__.'/e_db_platform_class.php');
			}

			$this->platform = new e_db_platform_mysql();
		}

		return $this->platform;
	}

	/**
	 * Replace `#table` (and bare #table) markers with physical table names via
	 * a quote-aware scan: string literals, backticked identifiers and comments
	 * are consumed first, so a '#' inside them is never rewritten.
	 *
	 * @param string $sql
	 * @return string
	 */
	private function _substituteTableNames($sql)
	{
		return preg_replace_callback(
			'/\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|`#([A-Za-z0-9_]+)`|`[^`]*`|\/\*[\s\S]*?\*\/|--[^\r\n]*|#([A-Za-z0-9_]+)/',
			function ($matches)
			{
				if(!empty($matches[1])) // `#table`
				{
					return '`'.$this->resolveTableName($matches[1]).'`';
				}

				if(isset($matches[2]) && $matches[2] !== '') // bare #table
				{
					return $this->resolveTableName($matches[2]);
				}

				return $matches[0];
			},
			$sql
		);
	}

	/**
	 * Pick the bind type for an execute() parameter given as a plain value.
	 *
	 * @param mixed $value
	 * @return int e_db::PARAM_*
	 */
	private function _detectParamType($value)
	{
		if($value === null)
		{
			return e_db::PARAM_NULL;
		}

		if(is_int($value))
		{
			return e_db::PARAM_INT;
		}

		if(is_bool($value))
		{
			return e_db::PARAM_BOOL;
		}

		return e_db::PARAM_STR;
	}

	/**
	 * Escape special characters in a string for use in an SQL statement,
	 * with the same semantics as mysqli_real_escape_string().
	 * The result is only safe when enclosed in quotes in the SQL statement.
	 *
	 * @deprecated v2.4.0 Bind values with {@see e_db::execute()} instead.
	 * @param string $data
	 * @param bool $strip Unused; retained for backwards compatibility
	 * @return string
	 */
	function escape($data, $strip = true)
	{
		$this->_notifyEscapeDeprecated();

		return $this->_escape($data);
	}

	/**
	 * Emit one E_USER_DEPRECATED notice per escape() call site per request.
	 * The class2.php error handler feeds these into the E107_DBG_DEPRECATED
	 * debug panel via {@see e107_db_debug::logDeprecated()}.
	 *
	 * @return void
	 */
	private function _notifyEscapeDeprecated()
	{
		static $notified = array();

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
		$site = (isset($trace[1]['file']) ? $trace[1]['file'] : '?').':'.(isset($trace[1]['line']) ? $trace[1]['line'] : '?');

		if(isset($notified[$site]))
		{
			return;
		}

		$notified[$site] = true;
		trigger_error('<b>$sql->escape() is deprecated.</b> Bind values with $sql->execute($sql, $params) instead. Called from '.$site, E_USER_DEPRECATED); // NO LAN
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
	 * @param $matches
	 * @return string
	 */
	function ml_check($matches)
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
	 * @param string $table - table name without e107 prefix
	 */
	function truncate($table=null)
	{
		if($table == null){ return null; }
		return $this->gen("TRUNCATE TABLE ".$this->mySQLPrefix.$table);
	}

	/**
	 * Check if a database table is empty or not.
	 * @param $table
	 * @return bool
	 */
	function isEmpty($table=null)
	{
		if(empty($table))
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
	 * @param string $where (Optional ) where condition.
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
	 */
	function copyRow($table, $fields = '*', $args='')
	{
		if(!$table || !$args )
		{
			return false;
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

		$fieldList = $this->db_FieldList($table);
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
	 * @param string $table name without the prefix
	 * @return bool|int
	 */
	public function dropTable($table)
	{
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
}
