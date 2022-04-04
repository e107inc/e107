<?php
/**
 * Created by PhpStorm.
 * Date: 2/8/2019
 * Time: 12:13 PM
 */


	/**
	 * Legacy e107 database methods
	 * Trait e_db_legacy
	 */
	trait e_db_legacy
	{

		/**
		 * @param $table
		 * @param $fields
		 * @param $arg
		 * @param $mode
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return false|int
		 */
		public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Select() is deprecated.</b> Use $sql->select() or $sql->retrieve() instead.', E_USER_DEPRECATED);
			return $this->select($table, $fields, $arg, $mode !== 'default', $debug, $log_type, $log_remark);
		}


		/**
		 * @param $tableName
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return bool|int|PDOStatement
		 */
		public function db_Insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Insert() is deprecated.</b> Use $sql->insert() instead.', E_USER_DEPRECATED);

			return $this->insert($tableName, $arg, $debug, $log_type, $log_remark);
		}

		/**
		 * @param $tableName
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return bool|int|PDOStatement
		 */
		function db_Update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Update() is deprecated.</b> Use $sql->update() instead.', E_USER_DEPRECATED);

			return $this->update($tableName, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @return void
		 */
		public function db_Close()
		{
			$this->close();
		}


		/**
		 * @param $type
		 * @return array|bool
		 */
		public function db_Fetch($type = null)
		{
			trigger_error('<b>$sql->db_Fetch() is deprecated.</b> Use $sql->fetch() instead.', E_USER_DEPRECATED);

			return $this->fetch($type);
		}


		/**
		 * @param $table
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return false|int
		 */
		public function db_Delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Delete() is deprecated.</b> Use $sql->delete() instead.', E_USER_DEPRECATED);

			return $this->delete($table, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @param $table
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return bool|int|PDOStatement
		 */
		function db_Replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Replace() is deprecated.</b> Use $sql->replace() instead.', E_USER_DEPRECATED);

			return $this->replace($table, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @param $table
		 * @param $fields
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return false|int
		 */
		function db_Count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Count is deprecated.</b> Use $sql->count() instead.', E_USER_DEPRECATED);
			return $this->count($table, $fields, $arg, $debug, $log_type, $log_remark);
		}


		/**
		 * @return int
		 */
		function db_Rows()
		{
			return $this->rowCount();
		}


		/**
		 * @param $query
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return bool|int
		 */
		public function db_Select_gen($query, $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_Select_gen() is deprecated.</b> Use $sql->gen() instead.', E_USER_DEPRECATED);
			return $this->gen($query, $debug, $log_type, $log_remark);
		}


		/**
		 * @param $table
		 * @param $language
		 * @return bool
		 */
		public function db_Table_exists($table, $language='')
		{
			return $this->isTable($table, $language);
		}


		/**
		 * @param $mode
		 * @return array|array[]
		 */
		public function db_TableList($mode='all')
		{
			return $this->tables($mode);
		}


		/**
		 * @param $table
		 * @param $fieldid
		 * @param $key
		 * @param $retinfo
		 * @return array|bool
		 */
		function db_Field($table, $fieldid = "", $key = "", $retinfo = false)
		{
			return $this->field($table, $fieldid, $key, $retinfo);
		}


		/**
		 * @param $fields
		 * @param $amount
		 * @param $maximum
		 * @param $ordermode
		 * @return array
		 */
		function db_getList($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false)
		{
			return $this->rows($fields, $amount, $maximum, $ordermode);
		}


		/**
		 * @param $table
		 * @param $multiple
		 * @return array|false|string
		 */
		function db_IsLang($table, $multiple=false)
		{
			trigger_error('<b>$sql->db_IsLang() is deprecated.</b> Use $sql->hasLanguage() instead.', E_USER_DEPRECATED);

			return $this->hasLanguage($table, $multiple);
		}


		/**
		 * @param $mySQLserver
		 * @param $mySQLuser
		 * @param $mySQLpassword
		 * @param $mySQLdefaultdb
		 * @param $newLink
		 * @param $mySQLPrefix
		 * @return bool|string
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
		 * @param $table
		 * @param $vars
		 * @param $arg
		 * @param $debug
		 * @param $log_type
		 * @param $log_remark
		 * @return bool|int|PDOStatement
		 */
		public function db_UpdateArray($table, $vars=array(), $arg='', $debug = false, $log_type = '', $log_remark = '')
		{
			trigger_error('<b>$sql->db_UpdateArray() is deprecated.</b> Use $sql->update() with "WHERE" instead.', E_USER_DEPRECATED);

			$vars['WHERE'] = str_replace('WHERE', '', $arg);

			return $this->update($table,$vars,$debug,$log_type,$log_remark);
		}

		/**
		 * @deprecated
		 * @param        $table
		 * @param string $fields
		 * @param string $args
		 * @return mixed
		 */
		public function db_CopyRow($table, $fields = '*', $args='')
		{
			trigger_error('<b>$sql->db_CopyRow() is deprecated.</b>Use $sql->copyRow() instead.', E_USER_DEPRECATED); // NO LAN

			return $this->copyRow($table,$fields,$args);
		}

		/**
		 * @param $oldtable
		 * @param $newtable
		 * @param $drop
		 * @param $data
		 * @return bool
		 */
		public function db_CopyTable($oldtable, $newtable, $drop = false, $data = false)
		{
			return $this->copyTable($oldtable, $newtable, $drop, $data);
		}


		/**
		 * @param $table
		 * @param $prefix
		 * @param $retinfo
		 * @return array|bool
		 */
		public function db_FieldList($table, $prefix = '', $retinfo = FALSE)
		{
			return $this->fields($table, $prefix, $retinfo);
		}

		/**
		 * @return void
		 */
		public function db_ResetTableList()
		{
			return $this->resetTableList();

		}

		/**
		 * @return int
		 */
		public function db_QueryCount()
		{
			return $this->queryCount();
		}

		/**
		 * @param $log_type
		 * @param $log_remark
		 * @param $log_query
		 * @return void
		 */
		public function db_Write_log($log_type = '', $log_remark = '', $log_query = '')
		{
			$this->log($log_type, $log_remark, $log_query);
		}

		/**
		 * @param $mode
		 * @return void
		 */
		public function db_SetErrorReporting($mode)
		{
			$this->setErrorReporting($mode);
		}


		/**
		 * @param $sMarker
		 * @return bool|true|null
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