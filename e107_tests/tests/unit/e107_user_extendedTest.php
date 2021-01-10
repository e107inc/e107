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
			'homepage'      => EUF_TEXT,
			'radio'         => EUF_RADIO,
			'dropdown'      => EUF_DROPDOWN,
			'dbfield'      => EUF_DB_FIELD,
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

			$this->structValues = array(
				'dropdown'  => 'drop1,drop2,drop3',
				'dbfield'  => 'core_media_cat,media_cat_id,media_cat_title,media_cat_order',
				'list'      => 'timezones',
				'radio'     => 'M => UE_LAN_MALE,F => UE_LAN_FEMALE',
				'checkbox'  => 'check1,check2,check3'
			);

			$this->structDefault = array(
				'dropdown'  => 'drop2',
				'dbfield'  => '3',
			//	'list'      => 'timezones',
				'radio'     => 'F',
				'checkbox'  => 'check2'

			);


			// Add a field of each type.
			foreach($this->typeArray as $k=>$v)
			{
				$value = (isset($this->structValues[$k])) ? $this->structValues[$k] : null;

				$insert = array(
					'name'      => $k,
					'text'      => ucfirst($k),
					'type'      => $v,
					'parms'     => null,
					'values'    => (isset($this->structValues[$k])) ? $this->structValues[$k] : null,
					'default'   => (isset($this->structDefault[$k])) ? $this->structDefault[$k] : null,
				);

				$this->ue->user_extended_add($insert);
			//	$this->ue->user_extended_add($k, ucfirst($k), $v , null, $value);
			}

			// As $_POSTED.
			$this->userValues = array(
				'text'          => 'Some Text',
				'homepage'      => 'https://e107.org',
				'radio'         => 'M',
				'dropdown'      => 'drop3',
				'dbfield'       => '5',
				'textarea'      => 'Text area value',
				'integer'       => 21,
				'date'          => '2001-01-11',
				'language'      => 'English',
				'list'          => 'America/Aruba',
				'checkbox'	    => array ( 0 => 'value2',  1 => 'value3'),
				'predefined'    => 'predefined', //  Used in plugin installation routine.
		//		'addon'         => EUF_ADDON,
				'country'       => 'us',
				'richtextarea' 	=> '<b>Rich text</b>',

			);




			$this->ue->init();


		}

		public function testSetGet()
		{
			// set them all first.
			foreach($this->userValues as $field => $value)
			{
				$this->ue->set(1, $field, $value); // set user extended value for user_id:  1.
			}

			foreach($this->userValues as $field => $value)
			{
				$result = $this->ue->get(1, $field); // retrieve value for $field of user_id: 1.
				$this->assertSame($this->userValues[$field], $result);
			}


		}

		/**
		 * Test the {USER_EXTENDED} shortcode.
		 */
		public function testUserExtendedShortcode()
		{
			foreach($this->userValues as $field => $value)
			{
				$this->ue->set(1, $field, $value); // set user extended value for user_id:  1.
			}

			$legacyExpectedValues = array (
			  'text'         => 'Some Text',
			  'homepage'     => 'https://e107.org',
			  'radio'        => 'M',
			  'dropdown'     => 'drop3',
			  'dbfield'      => 'News',
			  'textarea'     => 'Text area value',
			  'integer'      => '21',
			  'date'         => '2001-01-11',
			  'language'     => 'English',
			  'list'         => 'America/Aruba (-04:00)',
			  'checkbox'     => 'value2, value3',
			  'predefined'   => 'predefined',
			  'country'      => 'United States',
			  'richtextarea' => '<b>Rich text</b>',

			);

			$tp = e107::getParser();

			foreach($this->userValues as $field => $value)
			{
				$parm = $field.'.value.1';
				$result = $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.
				$this->assertEquals($legacyExpectedValues[$field], $result);
			}


			$legacyExpectedLabels = array (
			  'text'         => 'Text',
			  'homepage'     => 'Homepage',
			  'radio'        => 'Radio',
			  'dropdown'     => 'Dropdown',
			  'dbfield'      => 'Dbfield',
			  'textarea'     => 'Textarea',
			  'integer'      => 'Integer',
			  'date'         => 'Date',
			  'language'     => 'Language',
			  'list'         => 'List',
			  'checkbox'     => 'Checkbox',
			  'predefined'   => 'Predefined',
			  'country'      => 'Country',
			  'richtextarea' => 'Richtextarea',

			);

			foreach($this->userValues as $field => $value)
			{
				$parm = $field.'.text.1';
				$result = $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.
				$this->assertEquals($legacyExpectedLabels[$field], $result);
			}


			$legacyExpectedLabelValues = array (
				  'text'         => 'Text: Some Text',
				  'homepage'     => 'Homepage: https://e107.org',
				  'radio'        => 'Radio: M',
				  'dropdown'     => 'Dropdown: drop3',
				  'dbfield'      => 'Dbfield: News',
				  'textarea'     => 'Textarea: Text area value',
				  'integer'      => 'Integer: 21',
				  'date'         => 'Date: 2001-01-11',
				  'language'     => 'Language: English',
				  'list'         => 'List: America/Aruba (-04:00)',
				  'checkbox'     => 'Checkbox: value2, value3',
				  'predefined'   => 'Predefined: predefined',
				  'country'      => 'Country: United States',
				  'richtextarea' => 'Richtextarea: <b>Rich text</b>',
			);

			foreach($this->userValues as $field => $value)
			{
				$parm = $field.'.text_value.1';
				$result = $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.
				$this->assertEquals($legacyExpectedLabelValues[$field], $result);

			}

			$legacyExpectedIcons = array (
			  'text'         => '',
			  'homepage'     => 'e107_images/user_icons/user_homepage.png\' style=\'width:16px; height:16px\' alt=\'\' />',
			  'radio'        => '',
			  'dropdown'     => '',
			  'dbfield'      => '',
			  'textarea'     => '',
			  'integer'      => '',
			  'date'         => '',
			  'language'     => '',
			  'list'         => '',
			  'checkbox'     => '',
			  'predefined'   => '',
			  'country'      => '',
			  'richtextarea' => '',

			);

			foreach($this->userValues as $field => $value)
			{
				$parm = $field.'.icon.1';
				$result = $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.
				$this->assertStringContainsString($legacyExpectedIcons[$field], $result);
			}


		}


		public function testGetStructure()
		{
			e107::setRegistry('core/userextended/structure'); // clear the registry.

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
			'user_dbfield'      => "extra",
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
			  'user_dbfield' => 'extra',
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
*/
		public function testAddFieldTypes()
		{
			$data = array();
			foreach($this->userValues as $k=>$v)
			{
				$data['user_'.$k] = $v;
			}

			$target = array('data'=>$data);
			$this->ue->addFieldTypes($target);

			$this->assertNotEmpty($target['_FIELD_TYPES']);

			$expected =   array (
				'user_text'         => 'todb',
				'user_homepage'     => 'todb',
			    'user_radio'        => 'todb',
			    'user_dropdown'     => 'todb',
			    'user_dbfield'      => 'todb',
			    'user_textarea'     => 'todb',
			    'user_integer'      => 'int',
			    'user_date'         => 'todb',
			    'user_language'     => 'todb',
			    'user_list'         => 'todb',
			    'user_checkbox'     => 'array',
			    'user_richtextarea' => 'todb',
			);

			$this->assertSame($expected, $target['_FIELD_TYPES']);

		}
/*
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
