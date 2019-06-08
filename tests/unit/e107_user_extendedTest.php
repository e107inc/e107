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

			$typeArray = array(
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
			foreach($typeArray as $k=>$v)
			{
				$this->ue->user_extended_add($k, ucfirst($k), $v );
			}

			$this->ue->init();


		}

		public function testGetStructure()
		{
			$result = $this->ue->getStructure();
			$expected =
				array (
				  'user_text' =>
				  array (
				    'user_extended_struct_id' => '1',
				    'user_extended_struct_name' => 'text',
				    'user_extended_struct_text' => 'Text',
				    'user_extended_struct_type' => '1',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '0',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_radio' =>
				  array (
				    'user_extended_struct_id' => '2',
				    'user_extended_struct_name' => 'radio',
				    'user_extended_struct_text' => 'Radio',
				    'user_extended_struct_type' => '2',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '1',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_dropdown' =>
				  array (
				    'user_extended_struct_id' => '3',
				    'user_extended_struct_name' => 'dropdown',
				    'user_extended_struct_text' => 'Dropdown',
				    'user_extended_struct_type' => '3',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '2',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_db field' =>
				  array (
				    'user_extended_struct_id' => '4',
				    'user_extended_struct_name' => 'db field',
				    'user_extended_struct_text' => 'Db field',
				    'user_extended_struct_type' => '4',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '3',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_textarea' =>
				  array (
				    'user_extended_struct_id' => '5',
				    'user_extended_struct_name' => 'textarea',
				    'user_extended_struct_text' => 'Textarea',
				    'user_extended_struct_type' => '5',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '4',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_integer' =>
				  array (
				    'user_extended_struct_id' => '6',
				    'user_extended_struct_name' => 'integer',
				    'user_extended_struct_text' => 'Integer',
				    'user_extended_struct_type' => '6',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '5',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_date' =>
				  array (
				    'user_extended_struct_id' => '7',
				    'user_extended_struct_name' => 'date',
				    'user_extended_struct_text' => 'Date',
				    'user_extended_struct_type' => '7',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '6',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_language' =>
				  array (
				    'user_extended_struct_id' => '8',
				    'user_extended_struct_name' => 'language',
				    'user_extended_struct_text' => 'Language',
				    'user_extended_struct_type' => '8',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '7',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_list' =>
				  array (
				    'user_extended_struct_id' => '9',
				    'user_extended_struct_name' => 'list',
				    'user_extended_struct_text' => 'List',
				    'user_extended_struct_type' => '9',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '8',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_checkbox' =>
				  array (
				    'user_extended_struct_id' => '10',
				    'user_extended_struct_name' => 'checkbox',
				    'user_extended_struct_text' => 'Checkbox',
				    'user_extended_struct_type' => '10',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '9',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_predefined' =>
				  array (
				    'user_extended_struct_id' => '11',
				    'user_extended_struct_name' => 'predefined',
				    'user_extended_struct_text' => 'Predefined',
				    'user_extended_struct_type' => '11',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '10',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_addon' =>
				  array (
				    'user_extended_struct_id' => '12',
				    'user_extended_struct_name' => 'addon',
				    'user_extended_struct_text' => 'Addon',
				    'user_extended_struct_type' => '12',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '11',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_country' =>
				  array (
				    'user_extended_struct_id' => '13',
				    'user_extended_struct_name' => 'country',
				    'user_extended_struct_text' => 'Country',
				    'user_extended_struct_type' => '13',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '12',
				    'user_extended_struct_parent' => '0',
				  ),
				  'user_richtextarea' =>
				  array (
				    'user_extended_struct_id' => '14',
				    'user_extended_struct_name' => 'richtextarea',
				    'user_extended_struct_text' => 'Richtextarea',
				    'user_extended_struct_type' => '14',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '0',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '0',
				    'user_extended_struct_order' => '13',
				    'user_extended_struct_parent' => '0',
				  ),
				);

			$this->assertEquals($expected, $result);



		}

		public function testGetFieldList()
		{
			$list = $this->ue->getFieldList();

		}

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
