<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class db_table_adminTest extends \Codeception\Test\Unit
	{

		/** @var db_table_admin */
		protected $dta;

		protected function _before()
		{

			try
			{
				$this->dta = $this->make('db_table_admin');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load db_table_admin object");
			}

		}
/*
		public function testCompare_field_lists()
		{

		}*/

		public function testParse_field_defs()
		{
			$baseStruct = $this->dta->get_current_table('core');
			$baseStruct = isset($baseStruct[0][2]) ? $baseStruct[0][2] : null;

			$result = $this->dta->parse_field_defs($baseStruct);

			$expected = array (
				  0 => array (
				    'type' => 'field',
				    'name' => 'e107_name',
				    'fieldtype' => 'varchar(100)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  1 => array (
				    'type' => 'field',
				    'name' => 'e107_value',
				    'fieldtype' => 'text',
				    'nulltype' => 'NOT NULL',
				  ),
				  2 => array (
				    'type' => 'pkey',
				    'name' => '(e107_name)',
				  ),
				);

				$this->assertSame($expected,$result);

			$test2 = "userjournals_id int(10) unsigned NOT NULL auto_increment,
  userjournals_userid int(10) unsigned NOT NULL default '0',
  userjournals_subject varchar(64) NOT NULL default '',
  userjournals_categories varchar(100) NOT NULL default '',
  userjournals_playing varchar(50) NOT NULL default '',
  userjournals_mood enum('','happy','sad','alienated','beat_up','angry','annoyed') NOT NULL default 'happy',
  userjournals_entry longtext NOT NULL,
  userjournals_date varchar(64) NOT NULL default '',
  userjournals_timestamp varchar(32) NOT NULL default '',
  userjournals_is_comment int(1) NOT NULL default '0',
  PRIMARY KEY  (userjournals_id)";

			$result2 = $this->dta->parse_field_defs($test2);

			$expected = array (
				  0 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_id',
				    'fieldtype' => 'int(10)',
				    'vartype' => 'unsigned',
				    'nulltype' => 'NOT NULL',
				    'autoinc' => true,
				  ),
				  1 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_userid',
				    'fieldtype' => 'int(10)',
				    'vartype' => 'unsigned',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  2 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_subject',
				    'fieldtype' => 'varchar(64)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  3 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_categories',
				    'fieldtype' => 'varchar(100)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  4 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_playing',
				    'fieldtype' => 'varchar(50)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  5 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_mood',
				    'fieldtype' => 'enum(\'\',\'happy\',\'sad\',\'alienated\',\'beat_up\',\'angry\',\'annoyed\')',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'happy\'',
				  ),
				  6 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_entry',
				    'fieldtype' => 'longtext',
				    'nulltype' => 'NOT NULL',
				  ),
				  7 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_date',
				    'fieldtype' => 'varchar(64)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  8 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_timestamp',
				    'fieldtype' => 'varchar(32)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  9 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_is_comment',
				    'fieldtype' => 'int(1)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  10 =>
				  array (
				    'type' => 'pkey',
				    'name' => '(userjournals_id)',
				  ),
				);

			$this->assertSame($expected,$result2);


		}

/*		public function testUpdate_table_structure()
		{

		}*/

		public function testMake_field_types()
		{
			$fieldDefs = array (
				  0 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_id',
				    'fieldtype' => 'int(10)',
				    'vartype' => 'unsigned',
				    'nulltype' => 'NOT NULL',
				    'autoinc' => true,
				  ),
				  1 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_userid',
				    'fieldtype' => 'int(10)',
				    'vartype' => 'unsigned',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  2 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_subject',
				    'fieldtype' => 'varchar(64)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  3 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_categories',
				    'fieldtype' => 'varchar(100)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  4 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_playing',
				    'fieldtype' => 'varchar(50)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  5 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_mood',
				    'fieldtype' => 'enum(\'\',\'happy\',\'sad\',\'alienated\',\'beat_up\',\'angry\',\'annoyed\')',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'happy\'',
				  ),
				  6 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_entry',
				    'fieldtype' => 'longtext',
				    'nulltype' => 'NOT NULL',
				  ),
				  7 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_date',
				    'fieldtype' => 'varchar(64)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  8 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_timestamp',
				    'fieldtype' => 'varchar(32)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'\'',
				  ),
				  9 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_is_comment',
				    'fieldtype' => 'int(1)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  10 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_comment_parent',
				    'fieldtype' => 'int(1)',
				    'default' => 'NULL',
				  ),
				  11 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_is_blog_desc',
				    'fieldtype' => 'int(1)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  12 =>
				  array (
				    'type' => 'field',
				    'name' => 'userjournals_is_published',
				    'fieldtype' => 'int(1)',
				    'nulltype' => 'NOT NULL',
				    'default' => '\'0\'',
				  ),
				  13 =>
				  array (
				    'type' => 'pkey',
				    'name' => '(userjournals_id)',
				  ),
				);

			$result = $this->dta->make_field_types($fieldDefs);

			$expected = array (
			  '_FIELD_TYPES' =>
			  array (
			    'userjournals_id' => 'int',
			    'userjournals_userid' => 'int',
			    'userjournals_subject' => 'escape',
			    'userjournals_categories' => 'escape',
			    'userjournals_playing' => 'escape',
			    'userjournals_mood' => 'escape',
			    'userjournals_entry' => 'escape',
			    'userjournals_date' => 'escape',
			    'userjournals_timestamp' => 'escape',
			    'userjournals_is_comment' => 'int',
			    'userjournals_comment_parent' => 'int',
			    'userjournals_is_blog_desc' => 'int',
			    'userjournals_is_published' => 'int',
			  ),
			  '_NOTNULL' =>
			  array (
			    'userjournals_id' => '',
			    'userjournals_entry' => '',
			  ),
			);

			$this->assertSame($expected, $result);
		}
/*
		public function testCreateTable()
		{

		}*/

		public function testGet_current_table()
		{


			$expected = array (
  0 =>
  array (
    0 => 'CREATE TABLE e107_core (
  e107_name varchar(100) NOT NULL DEFAULT \'\',
  e107_value text NOT NULL,
  PRIMARY KEY (e107_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
    1 => 'e107_core',
    2 => 'e107_name varchar(100) NOT NULL DEFAULT \'\',
  e107_value text NOT NULL,
  PRIMARY KEY (e107_name)',
    3 => 'MyISAM DEFAULT CHARSET=utf8',
  ),
);

			$result = $this->dta->get_current_table('core');

			array_walk_recursive($result, function(&$element)
			{
				// MySQL 8.0+
				//   Alias CHARSET=utf8mb3 to CHARSET=utf8
				$element = str_replace("CHARSET=utf8mb3", "CHARSET=utf8", $element);
				// MariaDB 10.3.37, 10.4.27, 10.5.18, 10.6.11, 10.7.7, 10.8.6, 10.9.4, 10.10.2, 10.11.0
				//   Ignore COLLATE clause (https://jira.mariadb.org/browse/MDEV-29446)
				$element = preg_replace("/ COLLATE=[^\s;]+/", "", $element);
			});

			$this->assertSame($expected, $result);

		}




/*
		public function testMake_changes_list()
		{

		}

		public function testMake_field_list()
		{

		}

		public function testGet_table_def()
		{

		}

		public function testMake_table_list()
		{

		}

		public function testMake_def()
		{

		}
*/



	}
