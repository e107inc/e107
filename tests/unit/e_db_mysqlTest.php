<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_db_mysqlTest extends \Codeception\Test\Unit
	{

	/*	public function testGetPDO()
		{

		}

		public function testGetMode()
		{

		}

		public function testDb_Connect()
		{

		}*/

		/**
		 * TODO
		 */
		public function testConnect()
		{
			try
			{
				$class = $this->make('e_db_mysql');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_db_mysql object");
			}

			if(is_object($class))
			{
				$this->assertTrue(true);
			}
		}
/*
		public function testGetServerInfo()
		{

		}

		public function testDatabase()
		{

		}

		public function testGetConfig()
		{

		}

		public function testDb_Mark_Time()
		{

		}

		public function testDb_Show_Performance()
		{

		}

		public function testDb_Write_log()
		{

		}

		public function testDb_Query()
		{

		}

		public function testRetrieve()
		{

		}

		public function testSelect()
		{

		}

		public function testDb_Select()
		{

		}

		public function testInsert()
		{

		}

		public function testLastInsertId()
		{

		}

		public function testFoundRows()
		{

		}

		public function testRowCount()
		{

		}

		public function testDb_Insert()
		{

		}

		public function testReplace()
		{

		}

		public function testDb_Replace()
		{

		}

		public function testUpdate()
		{

		}

		public function testDb_Update()
		{

		}

		public function test_getTypes()
		{

		}

		public function test_getFieldValue()
		{

		}

		public function testDb_UpdateArray()
		{

		}

		public function testTruncate()
		{

		}

		public function testFetch()
		{

		}

		public function testDb_Fetch()
		{

		}

		public function testCount()
		{

		}

		public function testDb_Count()
		{

		}

		public function testClose()
		{

		}

		public function testDb_Close()
		{

		}

		public function testDelete()
		{

		}

		public function testDb_Delete()
		{

		}

		public function testDb_Rows()
		{

		}

		public function testDb_SetErrorReporting()
		{

		}

		public function testGen()
		{

		}

		public function testDb_Select_gen()
		{

		}

		public function testMl_check()
		{

		}

		public function testDb_IsLang()
		{

		}

		public function testDb_getList()
		{

		}

		public function testRows()
		{

		}

		public function testMax()
		{

		}

		public function testSelectTree()
		{

		}

		public function testDb_QueryCount()
		{

		}

		public function testDb_Query_all()
		{

		}

		public function testDb_FieldList()
		{

		}

		public function testDb_Field()
		{

		}

		public function testColumnCount()
		{

		}

		public function testField()
		{

		}

		public function testEscape()
		{

		}

		public function testDb_Table_exists()
		{

		}

		public function testIsTable()
		{

		}

		public function testIsEmpty()
		{

		}

		public function testDb_ResetTableList()
		{

		}

		public function testDb_TableList()
		{

		}

		public function testTables()
		{

		}

		public function testDb_CopyRow()
		{

		}

		public function testDb_CopyTable()
		{

		}

		public function testBackup()
		{

		}

		public function testDbError()
		{

		}

		public function testGetLastErrorNumber()
		{

		}

		public function testGetLastErrorText()
		{

		}

		public function testResetLastError()
		{

		}

		public function testGetLastQuery()
		{

		}

		public function testDb_Set_Charset()
		{

		}

		public function testGetFieldDefs()
		{

		}*/
	}
