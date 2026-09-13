<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	class e_admin_controller_uiTest extends \Test\Unit
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

			// Simulate a custom search handler for user_phone
			$this->ui = $this->make(e_admin_ui::class, array(
				'handleListSearchfieldFilter' => function ($field)
				{
					$search = $this->ui->getQuery('searchquery');
					return "u.user_phone LIKE '%custom_phone_" .  $search . "%'";
				},
			));

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

		    // Test custom search specifically for user_phone
		    $filterOptions = 'searchfield__user_phone';
		    $result = $this->ui->_modifyListQrySearch($listQry, '5551234', $filterOptions, $tablePath, $tableFrom, $primaryName, $raw, $orderField, $qryAsc, $forceFrom, $qryFrom, $forceTo, $perPage, $qryField, $isfilter, $handleAction);

		    // Expected result when the custom search handler is used
		    $expected = "SELECT u.* FROM `#user`  WHERE 1  AND u.user_phone LIKE '%custom_phone_5551234%' LIMIT 0, 10";
		    $this::assertSame($expected, $result);


		}

		/**
		 * Replays a list query the banlist admin page once ran, recorded with its inputs and the SQL it produced.
		 *
		 * @dataProvider recordedListQueries
		 * @param string $file
		 */
		public function testModifyListQrySearchReproducesARecordedQuery($file)
		{
			require_once(__DIR__ . '/fixtures/AdminUiBanlistSearchFixture.php');
			$this->ui = $this->make(AdminUiBanlistSearchFixture::class);
			$this->ui->setRequest($this->req);

			$data = json_decode(file_get_contents($file), true);
			$this::assertNotNull($data, basename($file) . ' does not decode: ' . json_last_error_msg());

			$call = $data['methodInvocation'];
			$prepared = $data['preProcessedData'];

			if(!empty($prepared['listOrder']))
			{
				$this->ui->setListOrder($prepared['listOrder']);
			}

			$this->ui->setFields($prepared['fields']);

			if(!empty($call['searchTerm']))
			{
				$this->ui->setQuery('searchquery', $call['searchTerm']);
			}

			if(!empty($call['handleAction']))
			{
				$this->req->setAction($call['handleAction']);
			}

			$query = $this->ui->_modifyListQrySearch(
				$call['listQry'], $call['searchTerm'], $call['filterOptions'], $call['tablePath'],
				$call['tableFrom'], $call['primaryName'], $call['raw'], $call['orderField'],
				$call['qryAsc'], $call['forceFrom'], $call['qryFrom'], $call['forceTo'],
				$call['perPage'], $call['qryField'], $call['isfilter'], $call['handleAction']
			);

			$this::assertEquals($data['expected'], $query);
		}

		/**
		 * @return array one case per file under _data/e_admin_ui/_modifyListQrySearch/, named for it
		 */
		public function recordedListQueries()
		{
			$cases = array();

			foreach(glob(codecept_data_dir('e_admin_ui/_modifyListQrySearch/*.json')) as $file)
			{
				$cases[basename($file, '.json')] = array($file);
			}

			if(empty($cases))
			{
				throw new RuntimeException('No recorded list queries to replay under _data/e_admin_ui/_modifyListQrySearch/');
			}

			return $cases;
		}

	}
