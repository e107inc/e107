<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107_user_extendedTest extends \Codeception\Test\Unit
	{

		private $typeArray;

		/** @var e107_user_extended */
		protected $ue;

		protected function _before()
		{

			try
			{
				$this->ue = $this->make('e107_user_extended');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107_user_extended object");
			}



			$this->ue->__construct();

			$this->typeArray = array(
			'text'          => EUF_TEXT,
			'radio'         => EUF_RADIO,
			'dropdown'      => EUF_DROPDOWN,
			'db field'      => EUF_DB_FIELD,
			'textarea'      => EUF_TEXTAREA,
			'integer'       => EUF_INTEGER,
			'date'          => EUF_DATE,
			'language'      => EUF_LANGUAGE,
			'list'          => EUF_PREDEFINED,
			'checkbox'	    => EUF_CHECKBOX,
			'predefined'    => EUF_PREFIELD, //  Used in plugin installation routine.
			'addon'         => EUF_ADDON,
			'country'       => EUF_COUNTRY,
			'richtextarea' 	=> EUF_RICHTEXTAREA,
			);


			// Add a field of each type.
			foreach($this->typeArray as $k=>$v)
			{
				$this->ue->user_extended_add($k, ucfirst($k), $v );
			}

			$this->ue->init();


		}

		public function testGetStructure()
		{
			$result = $this->ue->getStructure();

			foreach($this->typeArray as $k=>$v)
			{
				$key = 'user_'.$k;
				$this->assertArrayHasKey($key,$result);
				$this->assertEquals($k, $result[$key]['user_extended_struct_name']);

			}



		}
/*
		public function testGetFieldList()
		{
			$list = $this->ue->getFieldList();

		}
*/
		public function testGetFieldType()
		{
			$result = $this->ue->getFieldType('user_radio');

			$this->assertEquals(EUF_RADIO,$result);
		}
/*
		public function testUser_extended_getvalue()
		{

		}

		public function testHasPermission()
		{

		}
*/
		public function testGetFieldTypes()
		{
			$result = $this->ue->getFieldTypes();

			$expected = array (
			  1 => 'Text Box',
			  2 => 'Radio Buttons',
			  3 => 'Drop-Down Menu',
			  4 => 'DB Table Field',
			  5 => 'Textarea',
			  14 => 'Rich Textarea (WYSIWYG)',
			  6 => 'Integer',
			  7 => 'Date',
			  8 => 'Language',
			  9 => 'Predefined list',
			  10 => 'Checkboxes',
			  13 => 'Country',
			);


			$this->assertEquals($expected,$result);

		}

		public function testSanitizeAll()
		{
			$posted = array(
			'user_text'          => "Some text",
			'user_radio'         => "1",
			'user_dropdown'      => "drop-value-1",
			'user_db field'      => "extra",
			'user_textarea'      => "Some text",
			'user_integer'       => "3",
			'user_date'          => "2000-01-03",
			'user_language'      => "English",
			'user_list'          => "list-item",
			'user_checkbox'	    => "1",
			'user_predefined'    => "pre-value", //  Used in plugin installation routine.
			'user_addon'         => "pre-value",
			'user_country'       => "USA",
			'user_richtextarea' 	=> "[html]<p>Some text</p>[/html]",


			);

			$expected = array(
			  'user_text' => 'Some text',
			  'user_radio' => '1',
			  'user_dropdown' => 'drop-value-1',
			  'user_db field' => 'extra',
			  'user_textarea' => 'Some text',
			  'user_integer' => 3,
			  'user_date' => '2000-01-03',
			  'user_language' => 'English',
			  'user_list' => 'list-item',
			  'user_checkbox' => '1',
			  'user_predefined'   => 'pre-value',
			  'user_addon' => 'pre-value',
			  'user_country' => 'USA',
			  'user_richtextarea' => "[html]<p>Some text</p>[/html]",
			);


			$result = $this->ue->sanitizeAll($posted);

			$this->assertEquals($expected, $result);

		}
/*
		public function testUser_extended_edit()
		{

		}

		public function testParse_extended_xml()
		{

		}

		public function testGetCategories()
		{

		}

		public function testRenderValue()
		{

		}

		public function testGetFieldNames()
		{

		}

		public function testUser_extended_modify()
		{

		}

		public function testUser_extended_remove()
		{

		}

		public function testSet()
		{

		}

		public function testUser_extended_get_categories()
		{

		}

		public function testAddDefaultFields()
		{

		}

		public function testUser_extended_get_fields()
		{

		}

		public function testUser_extended_type_text()
		{

		}

		public function testUser_extended_hide()
		{

		}

		public function testAddFieldTypes()
		{

		}

		public function testUser_extended_setvalue()
		{

		}

		public function testGetFields()
		{

		}

		public function testGet()
		{

		}

		public function testUser_extended_field_exist()
		{

		}

		public function testUser_extended_add()
		{

		}

		public function testUser_extended_display_text()
		{

		}

		public function testUserExtendedValidateAll()
		{

		}

		public function testClear_cache()
		{

		}

		public function testUser_extended_reserved()
		{

		}

		public function testUser_extended_add_system()
		{

		}

		public function testUser_extended_getStruct()
		{

		}

		public function testUser_extended_validate_entry()
		{

		}

		public function testUser_extended_get_fieldList()
		{

		}
*/



	}
