<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */



	class e_admin_controller_uiTest extends \Codeception\Test\Unit
	{

		/** @var e_admin_ui */
		protected $ui;
		protected $req;

		protected function _before()
		{
			try
			{
				$this->ui = $this->make(e_admin_ui::class);
				$this->req = $this->make(e_admin_request::class);
				$this->ui->setRequest($this->req);
			}
			catch (Exception $e)
			{
				$this::fail("Couldn't load e_admin_controller_ui object: " . $e->getMessage());
			}
		}



		public function testJoinAlias()
		{
			// Simple Join --------------
			$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended as ue on u.user_id = ue.user_extended_id";
			$this->ui->joinAlias($qry);

			$actual = $this->ui->getJoinAlias();
			$expected = array (  'user' => 'u',  'user_extended' => 'ue',);
			$this->assertEquals($expected,$actual);

			$actual = $this->ui->getJoinField();
			$expected = array (   'user_id' => 'u.user_id',   'user_extended_id' => 'ue.user_extended_id', );
			$this->assertEquals($expected,$actual);

			// Complex Join

			$qry2 = "SELECT e.*,m.mem_id, m.mem_firstname, m.mem_lastname, cl.cal_appointment_start FROM `#calls` AS e 
			LEFT JOIN `#member` as m ON 
			(
				e.calls_direction = 'Inbound' AND m.mem_status NOT LIKE '%DUP%' AND e.calls_from !='' AND 
				(
					e.calls_from = REPLACE(m.mem_phone_day, '-', '') 
					OR e.calls_from = REPLACE(m.mem_phone_night, '-', '')  
					OR e.calls_from = REPLACE(m.mem_phone_cell , '-', '') 
					OR e.calls_from = REPLACE(m.mem_phone_other1 , '-', '') 
					OR e.calls_from = REPLACE(m.mem_phone_other2 , '-', '')
				)
			
			) 
			LEFT JOIN `#member_calender` AS cl ON 
			(
				e.calls_direction = 'Outbound' AND 
				(
					cl.cal_by_phone = REPLACE(m.mem_phone_day, '-', '') 
					OR cl.cal_by_cell = REPLACE(m.mem_phone_cell , '-', '') 
				)
			
			) ";

			$this->ui->joinAlias($qry2);

			$actual = $this->ui->getJoinAlias();
			$expected = array ( 'user' => 'u',  'user_extended' => 'ue',  'calls' => 'e',  'member' => 'm', 'member_calender' => 'cl', );
			$this->assertEquals($expected,$actual);


			$actual = $this->ui->getJoinField();
			$expected = array (
			  'user_id' => 'u.user_id',
			  'user_extended_id' => 'ue.user_extended_id',
			  'calls_direction' => 'e.calls_direction',
			  'calls_from' => 'e.calls_from',
			  'mem_id' => 'm.mem_id',
			  'mem_firstname' => 'm.mem_firstname',
			  'mem_lastname' => 'm.mem_lastname',
			  'mem_status' => 'm.mem_status',
			  'mem_phone_day' => 'm.mem_phone_day',
			  'mem_phone_night' => 'm.mem_phone_night',
			  'mem_phone_cell' => 'm.mem_phone_cell',
			  'mem_phone_other1' => 'm.mem_phone_other1',
			  'mem_phone_other2' => 'm.mem_phone_other2',
			  'cal_appointment_start' => 'cl.cal_appointment_start',
			  'cal_by_phone' => 'cl.cal_by_phone',
			  'cal_by_cell' => 'cl.cal_by_cell',
			);

			self::assertEquals($expected,$actual);

		}

		public function test_ModifyListQrySearch()
		{

			$listQry = 'SELECT u.* FROM `#user`  WHERE 1 ';
			$filterOptions = '';
			$tablePath = '`#user`.';
			$tableFrom = '`#user`';
			$primaryName = 'user_id';
			$raw = false;
			$orderField = null;
			$qryAsc = null;
			$forceFrom = false;
			$qryFrom = 0;
			$forceTo = false;
			$perPage = 10;
			$qryField = null;
			$isfilter = false;
			$handleAction = 'List';

			$this->ui->setFields([
					'user_id'           => array('title'=>'User ID', '__tableField' => 'u.user_id', 'type'=>'int', 'data'=>'int'),
					'user_name' 		=> array('title' => 'Name',	'__tableField' => 'u.user_name', 'type' => 'text',	 'data'=>'safestr'), // Display name
 		            'user_login' 		=> array('title' => 'Login','__tableField' => 'u.user_login', 'type' => 'text',	 'data'=>'safestr'), // Real name (no real vetting)
 		            'user_phone' 		=> array('title' => 'Phone','__tableField' => 'u.user_phone', 'search'=>true, 'type' => 'text',	 'data'=>'safestr'), // Real name (no real vetting)


 			]);

			// Test single word search term.
			$result = $this->ui->_modifyListQrySearch($listQry, 'admin', $filterOptions, $tablePath,  $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$expected = "SELECT u.* FROM `#user`  WHERE 1  AND  ( u.user_name LIKE '%admin%' OR u.user_login LIKE '%admin%' OR u.user_phone LIKE '%admin%' )  LIMIT 0, 10";
			$this::assertSame($expected, $result);

			// Test multiple word search term.
			$result = $this->ui->_modifyListQrySearch($listQry, 'firstname lastname', $filterOptions, $tablePath,  $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$expected = "SELECT u.* FROM `#user`  WHERE 1  AND (u.user_name LIKE '%firstname%' OR u.user_login LIKE '%firstname%' OR u.user_phone LIKE '%firstname%') AND (u.user_name LIKE '%lastname%' OR u.user_login LIKE '%lastname%' OR u.user_phone LIKE '%lastname%') LIMIT 0, 10";
			$this::assertSame($expected, $result);

			// Search term in quotes.
			$expected = "SELECT u.* FROM `#user`  WHERE 1  AND  ( u.user_name LIKE '%firstname lastname%' OR u.user_login LIKE '%firstname lastname%' OR u.user_phone LIKE '%firstname lastname%' )  LIMIT 0, 10";

			// Double-quotes.
			$result = $this->ui->_modifyListQrySearch($listQry, '"firstname lastname"', $filterOptions, $tablePath,  $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$this::assertSame($expected, $result);

			// Single-quotes.
			$result = $this->ui->_modifyListQrySearch($listQry, "'firstname lastname'", $filterOptions, $tablePath,  $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$this::assertSame($expected, $result);

			// Single quote as apostophie.
			$result = $this->ui->_modifyListQrySearch($listQry, "burt's", $filterOptions, $tablePath,  $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$expected = "SELECT u.* FROM `#user`  WHERE 1  AND  ( u.user_name LIKE '%burt&#039;s%' OR u.user_login LIKE '%burt&#039;s%' OR u.user_phone LIKE '%burt&#039;s%' )  LIMIT 0, 10";
			$this::assertSame($expected, $result);

			// Raw mode.
			$result = $this->ui->_modifyListQrySearch($listQry, "burt's", $filterOptions, $tablePath,  $tableFrom, $primaryName, true, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField,  $isfilter, $handleAction);
			$expected = array (  'joinWhere' =>
				  array (
				  ),
				  'filter' =>
				  array (
				    0 => 'u.user_name LIKE \'%burt&#039;s%\'',
				    1 => 'u.user_login LIKE \'%burt&#039;s%\'',
				    2 => 'u.user_phone LIKE \'%burt&#039;s%\'',
				  ),
				  'listQrySql' =>
				  array (
				  ),
				  'filterFrom' =>
				  array (
				  ),
				  'search' =>
				  array (
				  ),
				  'tableFromName' => '`#user`',
				  'tableFrom' =>
				  array (
				    0 => '`#user`.*',
				  ),
				  'joinsFrom' =>
				  array (
				  ),
				  'joins' =>
				  array (
				  ),
				  'groupField' => '',
				  'orderField' => '',
				  'orderType' => 'ASC',
				  'limitFrom' => 0,
				  'limitTo' => 10,
				);

			$this::assertSame($expected, $result);


		}

		public function test_ModifyListQrySearchField()
		{
			$listQry = 'SELECT u.* FROM `#user`  WHERE 1 ';
			$filterOptions = '';
			$tablePath = '`#user`.';
			$tableFrom = '`#user`';
			$primaryName = 'user_id';
			$raw = false;
			$orderField = null;
			$qryAsc = null;
			$forceFrom = false;
			$qryFrom = 0;
			$forceTo = false;
			$perPage = 10;
			$qryField = null;
			$isfilter = false;
			$handleAction = 'List';

			$this->ui->setFields([
					'user_id'           => array('title'=>'User ID', '__tableField' => 'u.user_id', 'type'=>'int', 'data'=>'int'),
					'user_name' 		=> array('title' => 'Name',	'__tableField' => 'u.user_name', 'type' => 'text',	 'data'=>'safestr'), // Display name
 		            'user_login' 		=> array('title' => 'Login','__tableField' => 'u.user_login', 'type' => 'text',	 'data'=>'safestr'), // Real name (no real vetting)
 		            'user_phone' 		=> array('title' => 'Phone','__tableField' => 'u.user_phone', 'search'=>true, 'type' => 'text',	 'data'=>'safestr'), // Real name (no real vetting)


 			]);
			// Search Specific Field Test

			$this->req->setAction('List');
			$this->req->setQuery('searchquery', '5551234');
			$this->ui->setRequest($this->req);

			// Simulate a custom search handler for user_phone
		    $this->ui->handleListSearchfieldFilter = function ($field)
		    {
		        $search = $this->ui->getQuery('searchquery');
		        return "u.user_phone LIKE '%custom_phone_" .  $search . "%'";
		    };

		    // Test custom search specifically for user_phone
		    $filterOptions = 'searchfield__user_phone';
		    $result = $this->ui->_modifyListQrySearch($listQry, '5551234', $filterOptions, $tablePath, $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField, $isfilter, $handleAction);

		    // Expected result when the custom search handler is used
		    $expected = "SELECT u.* FROM `#user`  WHERE 1  AND u.user_phone LIKE '%custom_phone_5551234%' LIMIT 0, 10";
		    $this::assertSame($expected, $result);


		}

		public function test_ModifyListQrySearch_FromJsonFiles()
		{

			// For Banlist test.

			$this->ui->handleListBanlistIpSearch  = function($srch)
			{
				$ret = array(
					"banlist_ip = '".$srch."'"
				);

				if($ip6 = e107::getIPHandler()->ipEncode($srch,true))
				{
					$ip = str_replace('x', '', $ip6);
					$ret[] = "banlist_ip LIKE '%".$ip."%'";
				}

				return implode(" OR ",$ret);
			};



			// The directory where the JSON files are stored
			$directory = e_BASE . "e107_tests/tests/_data/e_admin_ui/_modifyListQrySearch/";
			if (!is_dir($directory))
			{
				$this::fail("Directory does not exist: " . $directory);
			}

			// Scan the directory for JSON files
			$files = glob($directory . '*.json');

			$this::assertNotEmpty($files, "No JSON files found in the specified directory!");

			foreach ($files as $fl)
			{
				// Ensure the JSON file exists
				$file = realpath(codecept_data_dir().str_replace('/', DIRECTORY_SEPARATOR, '/e_admin_ui/_modifyListQrySearch/') . basename($fl));
				if (!file_exists($file))
				{
					$this::fail("File doesn't exist: " . $file);
				}

				// Load JSON content
				$jsonContent = file_get_contents($file);
				if (empty($jsonContent))
				{
					$this::fail("Failed to read JSON file: " . $file);
				}

				// Decode JSON
				$data = json_decode($jsonContent, true);
				if ($data === null)
				{
					$error = json_last_error_msg(); // Get a readable explanation of the problem
					$this::fail("JSON decoding failed for file: $file. Error: " . $error);
				}

				// Ensure JSON data is valid
				$this::assertNotEmpty($data, "Failed to decode JSON file: " . $file);

				// Extract input parameters from JSON structure
				$methodInvocation   = $data['methodInvocation'];
				$preProcessedData   = $data['preProcessedData'];
				$expected           = $data['expected'];



				// Verify fields are present in the JSON
				if (empty($preProcessedData['fields']))
				{
					$this::fail("Fields are not defined in the JSON file: " . $file);
				}

				if(!empty($preProcessedData['listOrder']))
				{
					$this->ui->setListOrder($preProcessedData['listOrder']);
				}

				$this->ui->setFields($preProcessedData['fields']);

				$queryValue = $this->ui->getQuery('searchquery');

				if(!empty($methodInvocation['searchTerm']))
				{
					$this->ui->setQuery('searchquery', $methodInvocation['searchTerm']);
				}

				if(!empty($methodInvocation['handleAction']))
				{
					$this->req->setAction($methodInvocation['handleAction']);
				}

				$query = $this->ui->_modifyListQrySearch(
					$methodInvocation['listQry'],
					$methodInvocation['searchTerm'],
					$methodInvocation['filterOptions'],
					$methodInvocation['tablePath'],
					$methodInvocation['tableFrom'],
					$methodInvocation['primaryName'],
					$methodInvocation['raw'],
					$methodInvocation['orderField'],
					$methodInvocation['qryAsc'],
					$methodInvocation['forceFrom'],
					$methodInvocation['qryFrom'],
					$methodInvocation['forceTo'],
					$methodInvocation['perPage'],
					$methodInvocation['qryField'],
					$methodInvocation['isfilter'],
					$methodInvocation['handleAction']
				);

				$this::assertEquals($expected, $query, "Test failed for JSON file: " . $file);
			}
		}






/*
		public function testGetSortParent()
		{

		}

		public function testGetFieldPref()
		{

		}

		public function testGetTreeModelSorted()
		{

		}

		public function testManageColumns()
		{

		}

		public function testGetJoinField()
		{

		}

		public function testGetBatchFeaturebox()
		{

		}

		public function testSetModel()
		{

		}

		public function testGetTableFromAlias()
		{

		}

		public function testGetFieldAttr()
		{

		}

		public function testSetJoinData()
		{

		}

		public function testGetPrimaryName()
		{

		}

		public function testGetTableName()
		{

		}

		public function testGetUrl()
		{

		}

		public function testGetParentChildQry()
		{

		}

		public function testSetListModel()
		{

		}

		public function testGetPerPage()
		{

		}

		public function testGetSortField()
		{

		}

		public function testGetUserPref()
		{

		}

		public function testGetFormQuery()
		{

		}

		public function testGetBatchCopy()
		{

		}

		public function testGetTabs()
		{

		}

		public function testSetBatchDelete()
		{

		}

		public function testGetTreePrefix()
		{

		}

		public function testGetPrefs()
		{

		}
*/
/*		public function testGetConfig()
		{
			$result = $this->ui->getConfig();

		}*/
/*
		public function testGetBatchExport()
		{

		}

		public function testGetFieldVar()
		{

		}

		public function testSetFieldAttr()
		{

		}

		public function testSetTreeModel()
		{

		}

		public function testGetEventTriggerName()
		{

		}

		public function testGetBatchDelete()
		{

		}

		public function testGetAfterSubmitOptions()
		{

		}

		public function testGetValidationRules()
		{

		}

		public function testGetPrefTabs()
		{

		}

		public function testGetDefaultOrder()
		{

		}

		public function testGetModel()
		{

		}

		public function testSetBatchCopy()
		{

		}

		public function testGetEventName()
		{

		}

		public function testGetFields()
		{

		}

		public function testAddTab()
		{

		}

		public function testGetTreeModel()
		{

		}

		public function testGetPluginName()
		{

		}

		public function testGetPluginTitle()
		{

		}

		public function testGetUI()
		{

		}

		public function testParentChildSort_r()
		{

		}

		public function testGetGrid()
		{

		}

		public function testSetUI()
		{

		}

		public function testGetJoinData()
		{

		}

		public function testGetBatchLink()
		{

		}

		public function testGetDefaultOrderField()
		{

		}

		public function testGetFeaturebox()
		{

		}

		public function testGetIfTableAlias()
		{

		}

		public function testGetDataFields()
		{

		}

		public function testGetListModel()
		{

		}

		public function testGetBatchOptions()
		{

		}

		public function testSetUserPref()
		{

		}
	*/

	/*	public function testSetConfig()
		{
			$cfg = e107::getConfig('core',true, true);

			$this->assertIsObject($cfg);

			$before = $cfg->get('sitename');
			$this->ui->setConfig($cfg);

			$pref = $this->ui->getConfig();
			$after = $pref->get('sitename');

			$this->assertSame($after, $before);

		}*/




	}
