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

		}
/*
		public function testGetStructure()
		{

		}

		public function testGetFieldList()
		{

		}

		public function testGetFieldType()
		{

		}

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
