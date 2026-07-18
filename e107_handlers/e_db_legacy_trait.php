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
			return $this->isTable($table, $language);
		}


		/**
		 * @param string $mode
		 * @return array|array[]
		 * @deprecated v2.0.0 Renamed; use {@see \e107\Database\ConnectionInterface::tables()}.
		 */
		public function db_TableList($mode='all')
		{
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
			return $this->fields($table, $prefix, $retinfo);
		}

		/**
		 * @return void
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionInterface::resetTableList()}.
		 */
		public function db_ResetTableList()
		{
			return $this->resetTableList();

		}

		/**
		 * @return int
		 * @deprecated v2.2.0 Renamed; use {@see e_db_pdo::queryCount()}.
		 */
		public function db_QueryCount()
		{
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
			$this->log($log_type, $log_remark, $log_query);
		}

		/**
		 * @param bool $mode
		 * @return void
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::setErrorReporting()}.
		 */
		public function db_SetErrorReporting($mode)
		{
			$this->setErrorReporting($mode);
		}


		/**
		 * @param string $sMarker
		 * @return bool|true|null
		 * @deprecated v2.2.0 Renamed; use {@see \e107\Database\ConnectionTrait::markTime()}.
		 */
		public function db_Mark_Time($sMarker)
		{
			return $this->markTime($sMarker);
		}

		/**
		 * @deprecated 2.1.9 Used only to provide $mySQLaccess to other instances of e_db_mysql scattered around
		 * @return PDO
		 */
		public function get_mySQLaccess()
		{
			return $this->mySQLaccess;
		}

	}
