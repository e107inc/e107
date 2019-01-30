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
/*
		public function testGetFields()
		{

		}

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

		public function testGetIndex()
		{

		}
*/
		public function testCompare()
		{
			e107::getDB()->gen('ALTER TABLE `#submitnews` CHANGE `submitnews_id` `submitnews_id` INT(10) UNSIGNED NOT NULL;');
			e107::getDB()->gen('ALTER TABLE `#submitnews` DROP INDEX submitnews_id;');

			define('e_DEBUG', true);

			$this->dbv->__construct();

		//	print_r($this->dbv->sqlFileTables);

			$this->dbv->compare('core');


			//FIXME

		//	print_r($this->dbv->errors);
		//	print_r($this->dbv->results['submitnews']);
		//	print_r($this->dbv->indices['submitnews']);
		//	print_r($this->dbv->results);
		}
/*
		public function testToMysql()
		{

		}

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
				  	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

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
							
				"
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
						    0 => 'MyISAM',
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

			/*	if($table == 'test_json')
				{
					var_export($actual);
				}*/

				$this->assertEquals($actual['tables'], $expected[$table]['tables'], "Table ".$table." could not be parsed.");

				foreach($expected[$table]['data'] as $k=>$data)
				{
					$data = str_replace("\t", '', $data);
					$this->assertEquals($actual['data'][$k], $data, "Table ".$table."['data'][".$k."] did not match.");
				}

				$this->assertEquals($actual['engine'], $expected[$table]['engine']);

			}

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
