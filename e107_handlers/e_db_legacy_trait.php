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
	 * v1-era database API, retained for backwards compatibility with old
	 * plugins and themes. Every method is a deprecated shim over the current
	 * API: the query builder
	 * ({@see \e107\Database\ConnectionInterface::createQueryBuilder()}) for
	 * CRUD, {@see \e107\Database\ConnectionInterface::execute()} for raw SQL,
	 * and like-for-like renames for the rest. Do not add call sites.
	 *
	 * Avoid these methods in new code and migrate existing call sites when
	 * refactoring; they nevertheless remain supported and tested, with no
	 * removal planned, because plugins and themes dating back to e107 v1
	 * depend on them.
	 *
	 * The deprecation warnings the shims emit are E_USER_DEPRECATED notices:
	 * raised once per call site per request, surfaced only in the debug
	 * panel when the E107_DBG_DEPRECATED flag (debug bit 16384) is on, and
	 * never shown to site visitors.
	 */
	trait e_db_legacy
	{

		/**
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param string $mode
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Select', 'Use the query builder: $sql->createQueryBuilder()->select(...)->from(\'table\')->where(...)->fetchAll().');
			return $this->select($table, $fields, $arg, $mode !== 'default', $debug, $log_type, $log_remark);
		}


		/**
		 * @param string       $tableName
		 * @param array|string $arg
		 * @param bool         $debug
		 * @param string       $log_type
		 * @param string       $log_remark
		 * @return bool|int|PDOStatement
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		public function db_Insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Insert', 'Use the query builder: $sql->createQueryBuilder()->insert(\'table\')->values($row)->execute().');

			return $this->insert($tableName, $arg, $debug, $log_type, $log_remark);
		}

		/**
		 * @param string       $tableName
		 * @param array|string $arg
		 * @param bool         $debug
		 * @param string       $log_type
		 * @param string       $log_remark
		 * @return bool|int|PDOStatement
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		function db_Update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Update', 'Use the query builder: $sql->createQueryBuilder()->update(\'table\')->set(\'col\', $value)->where(...)->execute().');

			return $this->update($tableName, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @return void
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::close()}.
		 */
		public function db_Close()
		{
			$this->_notifyDeprecated('db_Close', 'Use $sql->close() instead.');

			$this->close();
		}


		/**
		 * @param string|null $type assoc|num|both
		 * @return array|bool
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::fetch()}.
		 */
		public function db_Fetch($type = null)
		{
			$this->_notifyDeprecated('db_Fetch', 'Use $sql->fetch() instead.');

			return $this->fetch($type);
		}


		/**
		 * @param string $table
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		public function db_Delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Delete', 'Use the query builder: $sql->createQueryBuilder()->delete(\'table\')->where(...)->execute().');

			return $this->delete($table, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @param string       $table
		 * @param array|string $arg
		 * @param bool         $debug
		 * @param string       $log_type
		 * @param string       $log_remark
		 * @return bool|int|PDOStatement
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		function db_Replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Replace', 'Use the query builder: $sql->createQueryBuilder()->replace(\'table\')->values($row)->execute().');

			return $this->replace($table, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		function db_Count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Count', 'Use the query builder: $sql->createQueryBuilder()->select(\'COUNT(*)\')->from(\'table\')->where(...)->fetchOne().');
			return $this->count($table, $fields, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @return int
		 * @deprecated v2.0.0 Renamed; use {@see e_db_pdo::rowCount()}.
		 */
		function db_Rows()
		{
			$this->_notifyDeprecated('db_Rows', 'Use $sql->rowCount() instead.');

			return $this->rowCount();
		}


		/**
		 * @param string $query
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 * @deprecated v2.0.0 Use {@see \e107\Database\ConnectionInterface::execute()} with bound
		 *             :named parameters, or the query builder
		 *             ({@see \e107\Database\ConnectionInterface::createQueryBuilder()}) for
		 *             ordinary CRUD.
		 */
		public function db_Select_gen($query, $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_Select_gen', 'Use $sql->execute($query, $params) with :named parameters; for ordinary CRUD prefer the query builder ($sql->createQueryBuilder()).');
			return $this->gen($query, $debug, $log_type, $log_remark);
		}


		/**
		 * @param string $table
		 * @param string $language
		 * @return bool
		 * @deprecated v2.0.0 Renamed; use {@see e_db_pdo::isTable()}.
		 */
		public function db_Table_exists($table, $language='')
		{
			$this->_notifyDeprecated('db_Table_exists', 'Use $sql->isTable() instead.');

			return $this->isTable($table, $language);
		}


		/**
		 * @param string $mode
		 * @return array|array[]
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::tables()}.
		 */
		public function db_TableList($mode='all')
		{
			$this->_notifyDeprecated('db_TableList', 'Use $sql->tables() instead.');

			return $this->tables($mode);
		}


		/**
		 * @param string     $table
		 * @param int|string $fieldid
		 * @param string     $key
		 * @param bool       $retinfo
		 * @return array|bool
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::field()}.
		 */
		function db_Field($table, $fieldid = "", $key = "", $retinfo = false)
		{
			$this->_notifyDeprecated('db_Field', 'Use $sql->field() instead.');

			return $this->field($table, $fieldid, $key, $retinfo);
		}


		/**
		 * @param string      $fields
		 * @param bool|int    $amount
		 * @param bool|int    $maximum
		 * @param bool|string $ordermode
		 * @return array
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::rows()}.
		 */
		function db_getList($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false)
		{
			$this->_notifyDeprecated('db_getList', 'Use $sql->rows() instead.');

			return $this->rows($fields, $amount, $maximum, $ordermode);
		}


		/**
		 * @param string $table
		 * @param bool   $multiple
		 * @return array|false|string
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::hasLanguage()}.
		 */
		function db_IsLang($table, $multiple=false)
		{
			$this->_notifyDeprecated('db_IsLang', 'Use $sql->hasLanguage() instead.');

			return $this->hasLanguage($table, $multiple);
		}


		/**
		 * @param string $mySQLserver
		 * @param string $mySQLuser
		 * @param string $mySQLpassword
		 * @param string $mySQLdefaultdb
		 * @param bool   $newLink
		 * @param string $mySQLPrefix
		 * @return bool|string
		 * @deprecated v2.0.0 Use {@see \e107\Database\ConnectionInterface::connect()} and
		 *             {@see \e107\Database\ConnectionInterface::database()}.
		 */
		public function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $newLink = false, $mySQLPrefix = MPREFIX)
		{
			$this->_notifyDeprecated('db_Connect', 'Use $sql->connect() and $sql->database() instead.');

			if(!$this->connect($mySQLserver, $mySQLuser, $mySQLpassword, $newLink))
			{
				return 'e1';
			}

			if (!$this->database($mySQLdefaultdb,$mySQLPrefix))
			{
				return 'e2';
			}

			return true;
		}

		/**
		 * @param string $table
		 * @param array  $vars
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int|PDOStatement
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see \e107\Database\ConnectionInterface::createQueryBuilder()} and the
		 *             guide at {@see \e107\Database\ConnectionInterface}.
		 */
		public function db_UpdateArray($table, $vars=array(), $arg='', $debug = false, $log_type = '', $log_remark = '')
		{
			$this->_notifyDeprecated('db_UpdateArray', 'Use the query builder: $sql->createQueryBuilder()->update(\'table\')->set(\'col\', $value)->where(...)->execute().');

			$vars['WHERE'] = str_replace('WHERE', '', $arg);

			return $this->update($table,$vars,$debug,$log_type,$log_remark);
		}

		/**
		 * @param string $table
		 * @param string $fields
		 * @param string $args
		 * @return mixed
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::copyRow()}.
		 */
		public function db_CopyRow($table, $fields = '*', $args='')
		{
			$this->_notifyDeprecated('db_CopyRow', 'Use $sql->copyRow() instead.');

			return $this->copyRow($table,$fields,$args);
		}

		/**
		 * @param string $oldtable
		 * @param string $newtable
		 * @param bool   $drop
		 * @param bool   $data
		 * @return bool
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionInterface::copyTable()}.
		 */
		public function db_CopyTable($oldtable, $newtable, $drop = false, $data = false)
		{
			$this->_notifyDeprecated('db_CopyTable', 'Use $sql->copyTable() instead.');

			return $this->copyTable($oldtable, $newtable, $drop, $data);
		}


		/**
		 * @param string $table
		 * @param string $prefix
		 * @param bool   $retinfo
		 * @return array|bool
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionInterface::fields()}.
		 */
		public function db_FieldList($table, $prefix = '', $retinfo = FALSE)
		{
			$this->_notifyDeprecated('db_FieldList', 'Use $sql->fields() instead.');

			return $this->fields($table, $prefix, $retinfo);
		}

		/**
		 * @return void
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionInterface::resetTableList()}.
		 */
		public function db_ResetTableList()
		{
			$this->_notifyDeprecated('db_ResetTableList', 'Use $sql->resetTableList() instead.');

			return $this->resetTableList();

		}

		/**
		 * @return int
		 * @deprecated v2.2.0 Renamed; use {@see e_db_pdo::queryCount()}.
		 */
		public function db_QueryCount()
		{
			$this->_notifyDeprecated('db_QueryCount', 'Use $sql->queryCount() instead.');

			return $this->queryCount();
		}

		/**
		 * @param string $log_type
		 * @param string $log_remark
		 * @param string $log_query
		 * @return void
		 * @deprecated v2.2.0 Renamed; use {@see e_db_pdo::log()}.
		 */
		public function db_Write_log($log_type = '', $log_remark = '', $log_query = '')
		{
			$this->_notifyDeprecated('db_Write_log', 'Use $sql->log() instead.');

			$this->log($log_type, $log_remark, $log_query);
		}

		/**
		 * @param bool $mode
		 * @return void
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::setErrorReporting()}.
		 */
		public function db_SetErrorReporting($mode)
		{
			$this->_notifyDeprecated('db_SetErrorReporting', 'Use $sql->setErrorReporting() instead.');

			$this->setErrorReporting($mode);
		}


		/**
		 * @param string $sMarker
		 * @return bool|true|null
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::markTime()}.
		 */
		public function db_Mark_Time($sMarker)
		{
			$this->_notifyDeprecated('db_Mark_Time', 'Use $sql->markTime() instead.');

			return $this->markTime($sMarker);
		}

		/**
		 * @deprecated 2.1.9 Used only to provide $mySQLaccess to other instances of e_db_mysql scattered around
		 * @return PDO
		 */
		public function get_mySQLaccess()
		{
			$this->_notifyDeprecated('get_mySQLaccess', 'Raw driver-handle access has no replacement; avoid it.');

			return $this->mySQLaccess;
		}

	}
