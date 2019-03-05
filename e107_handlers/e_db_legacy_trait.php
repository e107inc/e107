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

		public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->select($table, $fields, $arg, $mode !== 'default', $debug, $log_type, $log_remark);
		}


		public function db_Insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->insert($tableName, $arg, $debug, $log_type, $log_remark);
		}

		function db_Update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->update($tableName, $arg, $debug, $log_type, $log_remark);
		}


		public function db_Close()
		{
			$this->close();
		}


		public function db_Fetch($type = null)
		{
			if (defined('e_LEGACY_MODE') && !is_int($type))
			{
				return $this->fetch('both');
		    }

			return $this->fetch($type);
		}


		public function db_Delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->delete($table, $arg, $debug, $log_type, $log_remark);
		}


		function db_Replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->replace($table, $arg, $debug, $log_type, $log_remark);
		}


		function db_Count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->count($table, $fields, $arg, $debug, $log_type, $log_remark);
		}


		function db_Rows()
		{
			return $this->rowCount();
		}



		public function db_Select_gen($query, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->gen($query, $debug, $log_type, $log_remark);
		}


		public function db_Table_exists($table,$language='')
		{
			return $this->isTable($table, $language);
		}


		public function db_TableList($mode='all')
		{
			return $this->tables($mode);
		}


		function db_Field($table, $fieldid = "", $key = "", $retinfo = false)
		{
			return $this->field($table, $fieldid, $key, $retinfo);
		}


		function db_getList($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false)
		{
			return $this->rows($fields, $amount, $maximum, $ordermode);
		}


		function db_IsLang($table, $multiple=false)
		{
			return $this->hasLanguage($table, $multiple);
		}


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

		public function db_UpdateArray($table, $vars=array(), $arg='', $debug = false, $log_type = '', $log_remark = '')
		{
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
			return $this->copyRow($table,$fields,$args);
		}

		public function db_CopyTable($oldtable, $newtable, $drop = false, $data = false)
		{
			return $this->copyTable($oldtable, $newtable, $drop, $data);
		}


		public function db_FieldList($table, $prefix = '', $retinfo = FALSE)
		{
			return $this->fields($table, $prefix, $retinfo);
		}

		public function db_ResetTableList()
		{
			return $this->resetTableList();

		}

		public function db_QueryCount()
		{
			return $this->queryCount();
		}

		public function db_Write_log($log_type = '', $log_remark = '', $log_query = '')
		{
			$this->log($log_type, $log_remark, $log_query);
		}

		public function db_SetErrorReporting($mode)
		{
			$this->setErrorReporting($mode);
		}


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