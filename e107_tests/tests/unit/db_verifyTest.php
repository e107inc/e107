<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class db_verifyTest extends \Codeception\Test\Unit
	{

		/** @var db_verify */
		private $dbv;

		protected function _before()
		{
			require_once(e_HANDLER."db_verify_class.php");
			try
			{
				$this->dbv = $this->make('db_verify');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load db_verify object");
			}

			$this->dbv->__construct();
		}

		public function testGetFields()
		{
			$data = "table_id int(10) unsigned NOT NULL auto_increment,
  table_name varchar(100) NOT NULL default '',
  table_email varchar(100) NOT NULL default '',
  table_user int(10) unsigned NOT NULL default '0',
  table_title varchar(200) NOT NULL default '',
  table_category tinyint(3) unsigned NOT NULL default '0',
  table_json JSON NOT NULL,
  table_item text NOT NULL,
  table_datestamp int(10) unsigned NOT NULL default '0',
  table_ip varchar(45) NOT NULL default '',
  table_auth tinyint(3) unsigned NOT NULL default '0',
  table_file text NOT NULL,
  table_keywords  varchar(255) NOT NULL default '',
  table_description text,
  table_summary text,
  table_media text,
  table_email2 tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (table_id)";

	$expected = array (
		  'table_id' =>
		  array (
		    'type' => 'INT',
		    'value' => '10',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'AUTO_INCREMENT',
		  ),
		  'table_name' =>
		  array (
		    'type' => 'VARCHAR',
		    'value' => '100',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'\'',
		  ),
		  'table_email' =>
		  array (
		    'type' => 'VARCHAR',
		    'value' => '100',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'\'',
		  ),
		  'table_user' =>
		  array (
		    'type' => 'INT',
		    'value' => '10',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'0\'',
		  ),
		  'table_title' =>
		  array (
		    'type' => 'VARCHAR',
		    'value' => '200',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'\'',
		  ),
		  'table_category' =>
		  array (
		    'type' => 'TINYINT',
		    'value' => '3',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'0\'',
		  ),
		  'table_json' =>
		  array (
		    'type' => 'JSON',
		    'value' => '',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => '',
		  ),
		  'table_item' =>
		  array (
		    'type' => 'TEXT',
		    'value' => '',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => '',
		  ),
		  'table_datestamp' =>
		  array (
		    'type' => 'INT',
		    'value' => '10',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'0\'',
		  ),
		  'table_ip' =>
		  array (
		    'type' => 'VARCHAR',
		    'value' => '45',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'\'',
		  ),
		  'table_auth' =>
		  array (
		    'type' => 'TINYINT',
		    'value' => '3',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'0\'',
		  ),
		  'table_file' =>
		  array (
		    'type' => 'TEXT',
		    'value' => '',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => '',
		  ),
		  'table_keywords' =>
		  array (
		    'type' => 'VARCHAR',
		    'value' => '255',
		    'attributes' => '',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'\'',
		  ),
		  'table_description' =>
		  array (
		    'type' => 'TEXT',
		    'value' => '',
		    'attributes' => '',
		    'null' => '',
		    'default' => '',
		  ),
		  'table_summary' =>
		  array (
		    'type' => 'TEXT',
		    'value' => '',
		    'attributes' => '',
		    'null' => '',
		    'default' => '',
		  ),
		  'table_media' =>
		  array (
		    'type' => 'TEXT',
		    'value' => '',
		    'attributes' => '',
		    'null' => '',
		    'default' => '',
		  ),
		  'table_email2' =>
		  array (
		    'type' => 'TINYINT',
		    'value' => '3',
		    'attributes' => 'UNSIGNED',
		    'null' => 'NOT NULL',
		    'default' => 'DEFAULT \'0\'',
		  ),
		);

           $actual =  $this->dbv->getFields($data);
           $this->assertEquals($expected,$actual);



		}
/*
		public function testClearCache()
		{

		}

		public function testRenderNotes()
		{

		}

		public function testCompareAll()
		{

		}

		public function testRenderTableName()
		{

		}

		public function testGetId()
		{

		}

		public function testGetSqlData()
		{

		}
*/
		public function testGetIndex()
		{

			$data = "`schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `schedule_cust_id` int(11) NOT NULL,
			  `schedule_complete` int(1) unsigned NOT NULL DEFAULT 0,
			  PRIMARY KEY (`schedule_id`),
			  UNIQUE KEY `schedule_cust_id` (`schedule_cust_id`),
			  KEY `schedule_invoice_id` (`schedule_invoice_id`)";

			$expected = array(
				'schedule_id'         =>
					array(
					'type'    => 'PRIMARY',
					'keyname' => 'schedule_id',
					'field'   => 'schedule_id',
				),
				'schedule_cust_id'    =>
					array(
						'type'    => 'UNIQUE',
						'keyname' => 'schedule_cust_id',
						'field'   => 'schedule_cust_id',
					),
				'schedule_invoice_id' =>
					array(
						'type'    => '',
						'keyname' => 'schedule_invoice_id',
						'field'   => 'schedule_invoice_id',
					),
			);


			$result = $this->dbv->getIndex($data);
			$this->assertEquals($expected,$result);
		}

		/**
		 * FIXME: This test has no assertions!
		 */
		/*
		public function testCompare()
		{

			e107::getDb()->gen('ALTER TABLE `#submitnews` CHANGE `submitnews_id` `submitnews_id` INT(10) UNSIGNED NOT NULL;');
			e107::getDb()->gen('ALTER TABLE `#submitnews` DROP INDEX submitnews_id;');

			$this->dbv->__construct();

		//	print_r($this->dbv->sqlFileTables);

			$this->dbv->compare('core');
			$this->dbv->compileResults();


			//FIXME

		//	print_r($this->dbv->errors);
		//	print_r($this->dbv->results['submitnews']);
		//	print_r($this->dbv->indices['submitnews']);
		//	print_r($this->dbv->results);
		}
		*/

		public function testGetFixQuery()
		{

			$sqlFileData = "table_id int(10) unsigned NOT NULL auto_increment,
  table_name varchar(100) NOT NULL default '',
  table_email varchar(100) NOT NULL default '',
  table_user int(10) unsigned NOT NULL default '0',
  table_title varchar(200) NOT NULL default '',
  table_category tinyint(3) unsigned NOT NULL default '0',
  table_json JSON NOT NULL,
  table_item text NOT NULL,
  table_datestamp int(10) unsigned NOT NULL default '0',
  table_ip varchar(45) NOT NULL default '',
  table_auth tinyint(3) unsigned NOT NULL default '0',
  table_file text NOT NULL,
  table_keywords  varchar(255) NOT NULL default '',
  table_description text,
  table_summary text,
  table_media text,
  PRIMARY KEY  (table_id)
  UNIQUE KEY `table_email` (`table_email`),
  KEY `table_user` (`table_user`)
  ";

			$actual = $this->dbv->getFixQuery('alter', 'table', 'table_ip', $sqlFileData);
			$expected = "ALTER TABLE `e107_table` CHANGE `table_ip` `table_ip` VARCHAR(45)  NOT NULL DEFAULT ''";
			$this->assertEquals($expected,$actual);


			$actual = $this->dbv->getFixQuery('insert', 'table', 'table_auth', $sqlFileData);
			$expected = "ALTER TABLE `e107_table` ADD `table_auth` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER table_ip";
			$this->assertEquals($expected,$actual);

			$actual = $this->dbv->getFixQuery('insert', 'table', 'table_json', $sqlFileData);
			$expected = "ALTER TABLE `e107_table` ADD `table_json` JSON NOT NULL AFTER table_category";
			$this->assertEquals($expected,$actual);

			$actual = $this->dbv->getFixQuery('index', 'table', 'table_email', $sqlFileData);
			$expected = 'ALTER TABLE `e107_table` ADD UNIQUE `table_email` (table_email);';
			$this->assertEquals($expected,$actual);

			$actual = $this->dbv->getFixQuery('index', 'table', 'table_user', $sqlFileData);
			$expected = 'ALTER TABLE `e107_table` ADD INDEX `table_user` (table_user);';
			$this->assertEquals($expected,$actual);

			$actual = $this->dbv->getFixQuery('create', 'table', 'table_user', $sqlFileData, 'InnoDB');
			$expected = 'CREATE TABLE `e107_table` (table_id int(10) unsigned NOT NULL auto_increment,
				  table_name varchar(100) NOT NULL default \'\',
				  table_email varchar(100) NOT NULL default \'\',
				  table_user int(10) unsigned NOT NULL default \'0\',
				  table_title varchar(200) NOT NULL default \'\',
				  table_category tinyint(3) unsigned NOT NULL default \'0\',
				  table_json JSON NOT NULL,
				  table_item text NOT NULL,
				  table_datestamp int(10) unsigned NOT NULL default \'0\',
				  table_ip varchar(45) NOT NULL default \'\',
				  table_auth tinyint(3) unsigned NOT NULL default \'0\',
				  table_file text NOT NULL,
				  table_keywords  varchar(255) NOT NULL default \'\',
				  table_description text,
				  table_summary text,
				  table_media text,
				  PRIMARY KEY  (table_id)
				  UNIQUE KEY `table_email` (`table_email`),
				  KEY `table_user` (`table_user`)
				  ) ENGINE=InnoDB;';

			$expected = str_replace("\t", "",$expected);
			$actual = str_replace("\t", "",$actual);

			$this->assertEquals($expected,$actual);

		//
		//	echo $actual;


		}

		public function testToMysql()
		{
			$tests = array(
				0 =>
					  array (
					    'type' => 'TINYINT',
					    'value' => '3',
					    'attributes' => 'UNSIGNED',
					    'null' => 'NOT NULL',
					    'default' => 'DEFAULT \'0\'',
					  ),
				 1 =>
					  array (
					    'type' => 'JSON',
					    'value' => '',
					    'attributes' => '',
					    'null' => 'NOT NULL',
					    'default' => '',
					  ),
				);


			$expected = array(
				"TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'",
				"JSON NOT NULL",
			);


			foreach($tests as $k=>$data)
			{
				$result = $this->dbv->toMysql($data);
				$this->assertEquals($expected[$k], $result);

			}

		}
/*
		public function testRunFix()
		{

		}

		public function testRenderTableSelect()
		{

		}

		public function testVerify()
		{

		}

		public function testGetPrevious()
		{

		}

		public function testRenderResults()
		{

		}

		public function testErrors()
		{

		}
*/
		public function testGetSqlFileTables()
		{
			$tests = array(

			'missing_index' =>
				"CREATE TABLE `e107_submitnews` (
				 `submitnews_id` int(10) unsigned NOT NULL,
				 `submitnews_name` varchar(100) NOT NULL DEFAULT '',
				 `submitnews_email` varchar(100) NOT NULL DEFAULT '',
				 `submitnews_title` varchar(200) NOT NULL DEFAULT '',
				 `submitnews_category` tinyint(3) unsigned NOT NULL DEFAULT '0',
				 `submitnews_item` text NOT NULL,
				 `submitnews_datestamp` int(10) unsigned NOT NULL DEFAULT '0',
				 `submitnews_ip` varchar(45) NOT NULL DEFAULT '',
				 `submitnews_auth` tinyint(3) unsigned NOT NULL DEFAULT '0',
				 `submitnews_file` text NOT NULL,
				 `submitnews_keywords` varchar(255) NOT NULL DEFAULT '',
				 `submitnews_description` text,
				 `submitnews_summary` text,
				 `submitnews_media` text,
				 `submitnews_user` int(10) unsigned NOT NULL DEFAULT '0'
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;",


			'user_extended' =>
				"CREATE TABLE `e107_user_extended` (
				 `user_extended_id` int(10) unsigned NOT NULL DEFAULT '0',
				 `user_hidden_fields` text,
				 `user_country` varchar(255) DEFAULT NULL,
				 `user_szulido` date NOT NULL,
				 `user_tag` varchar(255) DEFAULT 'Tagsága nem él. (((',
				 `user_jegyzet` text,
				 `user_homepage` varchar(255) DEFAULT NULL,
				 `user_tagimappa` varchar(255) DEFAULT NULL,
				 `user_belepesi` varchar(255) DEFAULT 'Egyeztetés alatt',
				 `user_timezone` varchar(255) DEFAULT '+0',
				 PRIMARY KEY (`user_extended_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

			'banlist' =>
					"CREATE TABLE `e107_banlist` (
					 `banlist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					 `banlist_ip` varchar(100) NOT NULL DEFAULT '',
					 `banlist_bantype` tinyint(3) NOT NULL DEFAULT '0',
					 `banlist_datestamp` int(10) unsigned NOT NULL DEFAULT '0',
					 `banlist_banexpires` int(10) unsigned NOT NULL DEFAULT '0',
					 `banlist_admin` smallint(5) unsigned NOT NULL DEFAULT '0',
					 `banlist_reason` tinytext NOT NULL,
					 `banlist_notes` tinytext NOT NULL,
					 PRIMARY KEY (`banlist_id`),
					 KEY `banlist_datestamp` (`banlist_datestamp`),
					 KEY `banlist_banexpires` (`banlist_banexpires`),
					 KEY `banlist_ip` (`banlist_ip`)
					) ENGINE=MyISAM AUTO_INCREMENT=182 DEFAULT CHARSET=utf8;",

			'test_json' =>
					"CREATE TABLE `e107_test_comment` (
					`eml_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`eml_hash` varchar(20) NOT NULL,
					`eml_datestamp` int(11) unsigned NOT NULL,
					`eml_json` JSON NOT NULL,
					`eml_to` varchar(50) NOT NULL,
					PRIMARY KEY (`eml_id`),
					UNIQUE KEY `eml_hash` (`eml_hash`)
				  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

			'test_comment' =>
					"CREATE TABLE `e107_test_comment` (
					`eml_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`eml_hash` varchar(20) NOT NULL,
					`eml_datestamp` int(11) unsigned NOT NULL,
					`eml_from` varchar(50) NOT NULL COMMENT 'This is the from field',
					`eml_to` varchar(50) NOT NULL,
					PRIMARY KEY (`eml_id`),
					UNIQUE KEY `eml_hash` (`eml_hash`)
				  	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

			'multiple'  =>

					"CREATE TABLE e107_plugin (
					  plugin_id int(10) unsigned NOT NULL auto_increment,
					  plugin_name varchar(100) NOT NULL default '',
					  plugin_version varchar(10) NOT NULL default '',
					  plugin_path varchar(100) NOT NULL default '',
					  plugin_installflag tinyint(1) unsigned NOT NULL default '0',
					  plugin_addons text NOT NULL,
					  plugin_category varchar(100) NOT NULL default '',
					  PRIMARY KEY  (plugin_id),
					  UNIQUE KEY plugin_path (plugin_path)
					) ENGINE=MyISAM;					
					CREATE TABLE e107_rate (
					  rate_id int(10) unsigned NOT NULL auto_increment,
					  rate_table varchar(100) NOT NULL default '',
					  rate_itemid int(10) unsigned NOT NULL default '0',
					  rate_rating int(10) unsigned NOT NULL default '0',
					  rate_votes int(10) unsigned NOT NULL default '0',
					  rate_voters text NOT NULL,
					  rate_up int(10) unsigned NOT NULL default '0',
					  rate_down int(10) unsigned NOT NULL default '0',
					  PRIMARY KEY  (rate_id)
					) ENGINE=MyISAM;
							
				",

			);

			$expected = array(

				'missing_index' => array (
					  'tables' =>
					  array (
					    0 => 'submitnews',
					  ),
					  'data' =>
					  array (
					    0 => '`submitnews_id` int(10) unsigned NOT NULL,
					 `submitnews_name` varchar(100) NOT NULL DEFAULT \'\',
					 `submitnews_email` varchar(100) NOT NULL DEFAULT \'\',
					 `submitnews_title` varchar(200) NOT NULL DEFAULT \'\',
					 `submitnews_category` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
					 `submitnews_item` text NOT NULL,
					 `submitnews_datestamp` int(10) unsigned NOT NULL DEFAULT \'0\',
					 `submitnews_ip` varchar(45) NOT NULL DEFAULT \'\',
					 `submitnews_auth` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
					 `submitnews_file` text NOT NULL,
					 `submitnews_keywords` varchar(255) NOT NULL DEFAULT \'\',
					 `submitnews_description` text,
					 `submitnews_summary` text,
					 `submitnews_media` text,
					 `submitnews_user` int(10) unsigned NOT NULL DEFAULT \'0\'',
					  ),
					  'engine' =>
					  array (
					    0 => 'MyISAM',
					  ),
					),


				'user_extended' => array (
					  'tables' =>
						  array (
						    0 => 'user_extended',
						  ),
					  'data' =>
						  array (
						    0 => '`user_extended_id` int(10) unsigned NOT NULL DEFAULT \'0\',
								 `user_hidden_fields` text,
								 `user_country` varchar(255) DEFAULT NULL,
								 `user_szulido` date NOT NULL,
								 `user_tag` varchar(255) DEFAULT \'Tagsága nem él. (((\',
								 `user_jegyzet` text,
								 `user_homepage` varchar(255) DEFAULT NULL,
								 `user_tagimappa` varchar(255) DEFAULT NULL,
								 `user_belepesi` varchar(255) DEFAULT \'Egyeztetés alatt\',
								 `user_timezone` varchar(255) DEFAULT \'+0\',
								 PRIMARY KEY (`user_extended_id`)',
						  ),
					  'engine' =>
						  array (
						    0 => 'MyISAM',
						  ),
				),

				'banlist'   => array (
				    'tables' =>
						  array (
						    0 => 'banlist',
						  ),
					  'data' =>
						  array (
						    0 => '`banlist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
									 `banlist_ip` varchar(100) NOT NULL DEFAULT \'\',
									 `banlist_bantype` tinyint(3) NOT NULL DEFAULT \'0\',
									 `banlist_datestamp` int(10) unsigned NOT NULL DEFAULT \'0\',
									 `banlist_banexpires` int(10) unsigned NOT NULL DEFAULT \'0\',
									 `banlist_admin` smallint(5) unsigned NOT NULL DEFAULT \'0\',
									 `banlist_reason` tinytext NOT NULL,
									 `banlist_notes` tinytext NOT NULL,
									 PRIMARY KEY (`banlist_id`),
									 KEY `banlist_datestamp` (`banlist_datestamp`),
									 KEY `banlist_banexpires` (`banlist_banexpires`),
									 KEY `banlist_ip` (`banlist_ip`)',
					  ),
					  'engine' =>
						  array (
						    0 => 'MyISAM',
						  ),
				),


				'test_json'     => array (
				  'tables' =>
				  array (
				    0 => 'test_comment',
				  ),
				  'data' =>
				  array (
				    0 => '`eml_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`eml_hash` varchar(20) NOT NULL,
				`eml_datestamp` int(11) unsigned NOT NULL,
				`eml_json` JSON NOT NULL,
				`eml_to` varchar(50) NOT NULL,
				PRIMARY KEY (`eml_id`),
				UNIQUE KEY `eml_hash` (`eml_hash`)',
				  ),
				  'engine' =>
				  array (
				    0 => 'MyISAM',
				  ),
				),

				'test_comment'  => array (
					  'tables' =>
						  array (
						    0 => 'test_comment',
						  ),
					  'data' =>
						  array (
						    0 => '`eml_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
								`eml_hash` varchar(20) NOT NULL,
									`eml_datestamp` int(11) unsigned NOT NULL,
									`eml_from` varchar(50) NOT NULL COMMENT \'This is the from field\',
									`eml_to` varchar(50) NOT NULL,
									PRIMARY KEY (`eml_id`),
									UNIQUE KEY `eml_hash` (`eml_hash`)',
						  ),
					  'engine' =>
						  array (
						    0 => 'InnoDB',
						  ),
				),

				'multiple'  =>
					array (
					  'tables' =>
						  array (
						    0 => 'plugin',
						    1 => 'rate',
						  ),
					  'data' =>
						  array (
						    0 => 'plugin_id int(10) unsigned NOT NULL auto_increment,
										  plugin_name varchar(100) NOT NULL default \'\',
										  plugin_version varchar(10) NOT NULL default \'\',
										  plugin_path varchar(100) NOT NULL default \'\',
										  plugin_installflag tinyint(1) unsigned NOT NULL default \'0\',
										  plugin_addons text NOT NULL,
										  plugin_category varchar(100) NOT NULL default \'\',
										  PRIMARY KEY  (plugin_id),
										  UNIQUE KEY plugin_path (plugin_path)',
					        1 => 'rate_id int(10) unsigned NOT NULL auto_increment,
										  rate_table varchar(100) NOT NULL default \'\',
										  rate_itemid int(10) unsigned NOT NULL default \'0\',
										  rate_rating int(10) unsigned NOT NULL default \'0\',
										  rate_votes int(10) unsigned NOT NULL default \'0\',
										  rate_voters text NOT NULL,
										  rate_up int(10) unsigned NOT NULL default \'0\',
										  rate_down int(10) unsigned NOT NULL default \'0\',
										  PRIMARY KEY  (rate_id)',
					  ),
					  'engine' =>
						  array (
						    0 => 'MyISAM',
						    1 => 'MyISAM',
						  ),
					)



			);


			foreach($tests as $table => $sql)
			{

				$actual = $this->dbv->getSqlFileTables($sql);

				$this->assertEquals($actual['tables'], $expected[$table]['tables'], "Table ".$table." could not be parsed.");

				foreach($expected[$table]['data'] as $k=>$data)
				{
					$data = str_replace("\t", '', $data);
					$this->assertEquals($actual['data'][$k], $data, "Table ".$table."['data'][".$k."] did not match.");
				}

				$this->assertEquals($actual['engine'], $expected[$table]['engine']);

			}

		}


		public function testPrepareResults()
		{

			$fileData = array();
			$sqlData = array();

			$sql = "`schedule_id` int(10) unsigned NOT NULL auto_increment,
				`schedule_user_id`  int(11) NOT NULL,
				`schedule_invoice_id` int(11) NOT NULL,
				`schedule_name`  varchar(50) NOT NULL default '',
				`schedule_location`  varchar(50) NOT NULL default '',
				`schedule_data` LONGTEXT DEFAULT NULL,
				`schedule_results` text NOT NULL,
				PRIMARY KEY  (`schedule_id`);";

			$file = "`schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `schedule_user_id` int(11) NOT NULL,
			  `schedule_invoice_id` int(11) NOT NULL,
			  `schedule_name` varchar(100) NOT NULL DEFAULT '',
			  `schedule_location` varchar(50) NOT NULL DEFAULT '',
			  `schedule_begin_date` int(11) NOT NULL,
			  `schedule_data` JSON DEFAULT NULL,
			  `schedule_results` text NOT NULL,
			  PRIMARY KEY (`schedule_id`),
			  UNIQUE KEY `schedule_user_id` (`schedule_user_id`),
			  KEY `schedule_invoice_id` (`schedule_invoice_id`)
            ";


			$fileData['field']	= $this->dbv->getFields($file);
			$sqlData['field']	= $this->dbv->getFields($sql);

			$fileData['index']	= $this->dbv->getIndex($file);
			$sqlData['index']	= $this->dbv->getIndex($sql);


			$this->dbv->prepareResults('schedule', 'myplugin', $sqlData, $fileData);

			$resultFields = $this->dbv->getResults('fields');
			$expected = array(
				'schedule' =>
					array(
						'schedule_id'         =>
							array(
								'_status' => 'ok',
							),
						'schedule_user_id'    =>
							array(
								'_status' => 'ok',
							),
						'schedule_invoice_id' =>
							array(
								'_status' => 'ok',
							),
						'schedule_name'       =>
							array(
								'_status'  => 'mismatch',
								'_diff'    =>
									array(
										'value' => '100',
									),
								'_valid'   =>
									array(
										'type'       => 'VARCHAR',
										'value'      => '100',
										'attributes' => '',
										'null'       => 'NOT NULL',
										'default'    => 'DEFAULT \'\'',
									),
								'_invalid' =>
									array(
										'type'       => 'VARCHAR',
										'value'      => '50',
										'attributes' => '',
										'null'       => 'NOT NULL',
										'default'    => 'DEFAULT \'\'',
									),
								'_file'    => 'myplugin',
							),
						'schedule_location'   =>
							array(
								'_status' => 'ok',
							),
						'schedule_begin_date' =>
							array(
								'_status' => 'missing_field',
								'_valid'  =>
									array(
										'type'       => 'INT',
										'value'      => '11',
										'attributes' => '',
										'null'       => 'NOT NULL',
										'default'    => '',
									),
								'_file'   => 'myplugin',
							),
						'schedule_data'       =>
							array(
								'_status' => 'ok',
							),
						'schedule_results'    =>
							array(
								'_status' => 'ok',
							),
					),
			);




			$this->assertEquals($expected, $resultFields);


			$resultIndices = $this->dbv->getResults('indices');
			$expected = array(
				'schedule' =>
					array(
						'schedule_id'         =>
							array(
								'_status' => 'ok',
							),
						'schedule_user_id'    =>
							array(
								'_status' => 'missing_index',
								'_valid'  =>
									array(
										'type'    => 'UNIQUE',
										'keyname' => 'schedule_user_id',
										'field'   => 'schedule_user_id',
									),
								'_file'   => 'myplugin',
							),
						'schedule_invoice_id' =>
							array(
								'_status' => 'missing_index',
								'_valid'  =>
									array(
										'type'    => '',
										'keyname' => 'schedule_invoice_id',
										'field'   => 'schedule_invoice_id',
									),
								'_file'   => 'myplugin',
							),
					),
			);

			$this->assertEquals($expected, $resultIndices);

		}



/*
		public function testFixForm()
		{

		}

		public function testRunComparison()
		{

		}

		public function testCompileResults()
		{

		}

		public function testGetSqlLanguages()
		{

		}*/
	}
