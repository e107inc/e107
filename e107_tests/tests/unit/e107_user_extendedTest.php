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

		private $structTypes;
		private $structLabels;
		private $userValues;
		private $structRequired;

		/** @var e107_user_extended */
		protected $ue;


		protected function _before()
		{

			try
			{
				$this->ue = e107::getUserExt(); // $this->make('e107_user_extended');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107_user_extended object");
			}

			$this->structTypes = array(
			'text'          => EUF_TEXT,
			'homepage'      => EUF_TEXT,
			'radio'         => EUF_RADIO,
			'dropdown'      => EUF_DROPDOWN,
			'dbfield'       => EUF_DB_FIELD,
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

			$this->structLabels = array (
			  'text'         => 'Text',
			  'homepage'     => 'UE_LAN_HOMEPAGE', // test constant use as well.
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
			  'addon'        => 'Addon'
			);

			$this->structValues = array(
				'dropdown'  => 'drop1,drop2,drop3',
				'dbfield'  => 'core_media_cat,media_cat_id,media_cat_title,media_cat_order',
				'list'      => 'timezones',
				'radio'     => 'M => UE_LAN_MALE,F => UE_LAN_FEMALE',
				'checkbox'  => 'check1,check2,check3',
				'addon'     => e107::serialize(
								array('type'=> 'number', 'data' => 'int',  'title' => $this->structLabels['addon']),
								'json')

			);

			$this->structDefault = array(
				'dropdown'  => 'drop2',
				'dbfield'  => '3',
			//	'list'      => 'timezones',
				'radio'     => 'F',
				'checkbox'  => 'check2'

			);

			$this->structParent = array(
				'list'      => 16,
				'radio'     => 16,
				'textarea'  => 16,
				'country'   => 17,
			);

			$this->structRequired = array(
				 'text'         => 1,
				  'homepage'     => 0, // test constant use as well.
				  'radio'        => 1,
				  'dropdown'     => 1,
				  'dbfield'      => 1,
				  'textarea'     => 0,
				  'integer'      => 1,
				  'date'         => 1,
				  'language'     => 1,
				  'list'         => 1,
				  'checkbox'     => 1,
				  'predefined'   => 1,
				  'country'      => 1,
				  'richtextarea' => 1,
				  'addon'        => 1
			);

			// clear the table.
			$sql = e107::getDb();
			$sql->truncate('user_extended_struct');

			// Add a field of each type.
			foreach($this->structTypes as $k=> $v)
			{
				$value = (isset($this->structValues[$k])) ? $this->structValues[$k] : null;

				$insert = array(
					'name'      => $k,
					'text'      => $this->structLabels[$k],
					'type'      => $v,
					'parms'     => null,
					'values'    => (isset($this->structValues[$k])) ? $this->structValues[$k] : null,
					'default'   => (isset($this->structDefault[$k])) ? $this->structDefault[$k] : null,
					'parent'    => (isset($this->structParent[$k])) ? $this->structParent[$k] : 0,
					'required'    => (isset($this->structRequired[$k])) ? $this->structRequired[$k] : 0,
					'read'         => e_UC_MEMBER,
					'write'        => e_UC_ADMIN,
					'applicable'   => e_UC_MAINADMIN,
				);

				// simulate data from e_user.php 'settings' method.
				if($v === EUF_ADDON)
				{
					$insert['fieldType']    = 'int(2)';
					$insert['parms']    = '_blank'; // plugin dir.
				}

				$this->ue->user_extended_add($insert);
			//	$this->ue->user_extended_add($k, ucfirst($k), $v , null, $value);
			}

			// Insert a User-Extended Category
			$insertCategory = array(
					'name'         => 'mycategory',
					'text'         => 'Category Name',
					'type'         => 0,
					'read'         => e_UC_PUBLIC,
					'write'        => e_UC_ADMIN,
					'applicable'   => e_UC_MEMBER,
					'order'        => 1,
			);

			if($this->ue->user_extended_add($insertCategory) === false)
			{
				trigger_error("failed to create user-extended category");
			}

					// Insert a User-Extended Category
			$insertCategory2 = array(
					'name'         => 'mycategory2 ',
					'text'         => 'Category Name 2',
					'type'         => 0,
					'read'         => e_UC_ADMIN,
					'write'        => e_UC_ADMIN,
					'applicable'   => e_UC_MEMBER,
					'order'        => 2,
			);

			if($this->ue->user_extended_add($insertCategory2) === false)
			{
				trigger_error("failed to create user-extended category");
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
		 * FIXME - has problems with shuffling. 
		 */
		/*public function testUserextendedCatShortcode()
		{
			$sc = e107::getScBatch('usersettings');
			$sc->reset();

			$perms = array(e_UC_PUBLIC, e_UC_MEMBER, e_UC_ADMIN,  e_UC_MAINADMIN);
			$sc->setVars(array('userclass_list' => implode(',',$perms)));
			$cat = (int) e107::getDb()->retrieve('user_extended_struct', 'user_extended_struct_id', "user_extended_struct_name = 'mycategory' LIMIT 1");


			$result = $sc->sc_userextended_cat($cat);
			$this->assertStringContainsString('<h3>Category Name</h3>', $result);
			$this->assertStringContainsString("<label class='col-sm-3 control-label'>Radio <span class='required'>", $result);
			$this->assertStringContainsString("<textarea id='ue-user-textarea'", $result);
			$this->assertStringContainsString("<select id='ue-user-list'", $result);

			$result = $sc->sc_userextended_cat(0);
			$this->assertStringContainsString('<h3>Miscellaneous</h3>', $result);
			$this->assertStringContainsString("<label class='col-sm-3 control-label'>Dropdown", $result);


		}*/

		public function testUserExtendedFieldShortcode()
		{
			$sc = e107::getScBatch('usersettings');
			$sc->reset();

			$perms = array(e_UC_PUBLIC, e_UC_MEMBER, e_UC_ADMIN,  e_UC_MAINADMIN);
			$sc->setVars(array('userclass_list' => implode(',',$perms)));

			$result = $sc->sc_userextended_field('radio');
			$this->assertStringContainsString("Radio <span class='required'", $result);


		}


		/**
		 * Test the {USER_EXTENDED} shortcode.
		 */
		public function testUserExtendedShortcode()
		{
		//	$this->fixRegistry('before');

			foreach($this->userValues as $field => $value)
			{
				$this->ue->set(1, $field, $value); // set user extended value for user_id:  1.
			}

			$legacyExpectedValues = array (
			  'text'         => 'Some Text',
			  'homepage'     => 'https://e107.org',
			  'radio'        => 'Male',
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


			foreach($this->userValues as $field => $value)
			{
				$parm = $field.'.text.1';
				$result = $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.
				$expected = defset($this->structLabels[$field],$this->structLabels[$field]);
				$this->assertEquals($expected, $result);
			}


			$legacyExpectedLabelValues = array (
				  'text'         => 'Text: Some Text',
				  'homepage'     => 'Homepage: https://e107.org',
				  'radio'        => 'Radio: Male',
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
				if(!isset($legacyExpectedIcons[$field]))
				{
					continue;
				}

				$parm = $field.'.icon.1';
				$result = (string) $tp->parseTemplate('{USER_EXTENDED='.$parm.'}', true);  // retrieve value for $field of user_id: 1.

				if(empty($result) && empty($legacyExpectedIcons[$field]))
				{
					continue;
				}

				$this->assertStringContainsString($legacyExpectedIcons[$field], $result);
			}

		//	$this->fixRegistry('after');
		}


		public function testUserExtendedAllShortcode()
		{
			e107::getUserExt()->init();

			/** @var usersettings_shortcodes $sc */
			$sc = e107::getScBatch('usersettings');
			$sc->wrapper('not-a-real-wrapper');
			$sc->reset();

			$sc->setVars(array('userclass_list' => '253,251,0,254,250'));

			$result = e107::getParser()->parseTemplate('{USEREXTENDED_ALL}', false, $sc);

			$this->assertStringContainsString('<h3>Category Name</h3>',$result);
			$this->assertStringContainsString('<h3>Category Name 2</h3>',$result);
			$this->assertStringContainsString('<h3>Miscellaneous</h3>', $result);

		}

		public function testloadUECatData()
		{
			/** @var usersettings_shortcodes $sc */
			$sc = e107::getScBatch('usersettings');

			$data = $sc->loadUECatData('write');
			$this->assertCount(3, $data); // 3 categories including "Misc"

		}


		public function testloadUEFieldData()
		{
			/**
			 * All fields have been created with the following.
			 * 	'read'         => e_UC_MEMBER,
				'write'        => e_UC_ADMIN,
				'applicable'   => e_UC_MAINADMIN,
			 *
			 * 		$this->structParent = array(
				'list'      => 16,
				'radio'     => 16,
				'textarea'  => 16,
				'country'   => 17,
			);
			 */

			$sc = e107::getScBatch('usersettings');
			$GLOBALS['_E107']['phpunit'] = true; // disable universal CLI access in check_class()

			$perms = array(e_UC_PUBLIC, e_UC_MEMBER, e_UC_ADMIN,  e_UC_MAINADMIN);
			$sc->setVars(array('userclass_list' => implode(',',$perms)));

			// Load Fields from parent =  0
			$data = $sc->loadUEFieldData('write', 0);
			$this->assertArrayHasKey('text', $data);
			$this->assertArrayHasKey('homepage', $data);
			$this->assertArrayNotHasKey('radio', $data);

			// Load Fields from parent = 16
			$data = $sc->loadUEFieldData('write', 16);
			$this->assertArrayHasKey('radio', $data);
			$this->assertArrayHasKey('list', $data);
			$this->assertArrayHasKey('textarea', $data);

			// Load Fields from parent = 17
			$data = $sc->loadUEFieldData('write', 17);
			$this->assertArrayHasKey('country', $data);


			$perms = array(e_UC_GUEST); // ie USERCLASS_LIST
			$sc->setVars(array('userclass_list' => implode(',',$perms)));
			$data = $sc->loadUEFieldData('write', 16);
			$this->assertCount(0, $data);  // should be empty

			$GLOBALS['_E107']['phpunit'] = false; // fix check_class()

		}

		public function testSignupExtendedUserFieldsShortcode()
		{
		//	$this->fixRegistry('before');
			return null; // FIXME - inconsistent results.


			e107::getUserExt()->init();
			$sc = e107::getScBatch('signup');


			$template = array(
				'extended-category'     => "\n-- {EXTENDED_CAT_TEXT} --\n",
				'extended-user-fields'  => "<label>{EXTENDED_USER_FIELD_TEXT}{EXTENDED_USER_FIELD_REQUIRED}</label>\n" // {EXTENDED_USER_FIELD_EDIT}
			);

			$expected = array(
				"<label>Text<span class='required'><!-- empty --></span></label>",
				"<label>Dropdown<span class='required'><!-- empty --></span></label>",
				"<label>Dbfield<span class='required'><!-- empty --></span></label>",
				"<label>Integer<span class='required'><!-- empty --></span></label>",
				"<label>Date<span class='required'><!-- empty --></span></label>",
				"<label>Language<span class='required'><!-- empty --></span></label>",
				"<label>Checkbox<span class='required'><!-- empty --></span></label>",
				"<label>Predefined<span class='required'><!-- empty --></span></label>",
				"<label>Addon<span class='required'><!-- empty --></span></label>",
				"<label>Richtextarea<span class='required'><!-- empty --></span></label>",
				"-- Category Name --",
				"<label>Radio<span class='required'><!-- empty --></span></label>",
				"<label>List<span class='required'><!-- empty --></span></label>",
				"-- Category Name 2 --",
				"<label>Country<span class='required'><!-- empty --></span></label>"
			);

			$sc->template = $template;

			$this->assertNotEmpty($sc->template);

			$result = e107::getParser()->parseTemplate('{SIGNUP_EXTENDED_USER_FIELDS}', false, $sc);
			foreach($expected as $exp)
			{
				$this->assertStringContainsString($exp, $result);
			}


			foreach($this->structRequired as $field=>$v)
			{
				if($v === 0)
				{
					$this->assertStringNotContainsString($this->structLabels[$field], $result);
				}

			}

		//	$this->fixRegistry('after');



		}

		private function fixRegistry($mode)
		{
			return null;
			$regID = 'core/e107/singleton/e107_user_extended';

			static $originalRegistry;

			if($mode == 'before')
			{
				$originalRegistry = e107::getRegistry($regID);
				e107::setRegistry($regID, $this->ue);
			}

			if($mode === 'after')
			{
				e107::setRegistry($regID, $originalRegistry);
			}


		}
/*
		public function testGetUserExtendedFieldData()
		{
			$sc = e107::getScBatch('usersettings');

			$sc->setVars(array('userclass_list' => '253,251,0,254,250'));

			$expected = array (
			  'user_extended_struct_id' => '3',
			  'user_extended_struct_name' => 'radio',
			  'user_extended_struct_text' => 'Radio',
			  'user_extended_struct_type' => '2',
			  'user_extended_struct_parms' => '',
			  'user_extended_struct_values' => 'M =&gt; UE_LAN_MALE,F =&gt; UE_LAN_FEMALE',
			  'user_extended_struct_default' => 'F',
			  'user_extended_struct_read' => '253',
			  'user_extended_struct_write' => '254',
			  'user_extended_struct_required' => '1',
			  'user_extended_struct_signup' => '0',
			  'user_extended_struct_applicable' => '250',
			  'user_extended_struct_order' => '2',
			  'user_extended_struct_parent' => '16',
			);

			$result = $sc->getUserExtendedFieldData('radio');
			$this->assertEquals($result, $expected);

		}
*/

		public function testGetStructure()
		{
		//	e107::setRegistry('core/userextended/structure'); // clear the registry.

			$result = $this->ue->getStructure();

			foreach($this->structTypes as $k=> $v)
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

			foreach($this->structTypes as $field=>$type)
			{
				$fieldname = 'user_'.$field;
				$result = $this->ue->getFieldType($fieldname);
				$this->assertEquals($type, $result);
			}


		}

		public function testGetFieldValues()
		{
			foreach($this->structValues as $key=>$value)
			{
				$result = $this->ue->getFieldValues('user_'.$key);
				$this->assertEquals($value, $result);
			}

		}

		public function testGetFieldLabel()
		{
			foreach($this->structLabels as $field => $label)
			{
				$result = $this->ue->getFieldLabel('user_'.$field);
				$this->assertSame(defset($label, $label), $result);
			}
		}

		function testGetFieldAttibute()
		{
			foreach($this->structValues as $field=>$val)
			{
				$result = $this->ue->getFieldAttribute('user_'.$field,'values');
				$this->assertSame($val, $result);
			}

		}

/*
		public function testUser_extended_getvalue()
		{

		}
*/
		public function testHasPermission()
		{

			/**
			 * All fields have been createad with the following.
			 * 	'read'         => e_UC_MEMBER,
				'write'        => e_UC_ADMIN,
				'applicable'   => e_UC_MAINADMIN,
			 */

			$GLOBALS['_E107']['phpunit'] = true; // fix check_class()
		//	$_E107['phpunit'] = true;
			$ret = [];
			foreach($this->structTypes as $field => $name)
			{
				$fid = 'user_'.$field;

				// test public access against read =  member.
				$result = $this->ue->hasPermission($fid,'read', e_UC_PUBLIC);
				$this->assertFalse($result);

				// test member against write = admin.
				$result = $this->ue->hasPermission($fid,'write', e_UC_MEMBER);
				$this->assertFalse($result);

				// test member against write = admin.
				$result = $this->ue->hasPermission($fid,'applicable', e_UC_ADMIN);
				$this->assertFalse($result);

				// test member against write = admin.
				$result = $this->ue->hasPermission($fid,'applicable', e_UC_MAINADMIN);
				$this->assertTrue($result);

			}

			$GLOBALS['_E107']['phpunit'] = false;


		}

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

		public function testGetCategoryAttribute()
		{
			$result = $this->ue->getCategoryAttribute('mycategory', 'read');
			$this->assertSame(e_UC_PUBLIC, $result);

			$result = $this->ue->getCategoryAttribute('mycategory', 'write');
			$this->assertSame(e_UC_ADMIN, $result);

			$result = $this->ue->getCategoryAttribute('user_text', 'read');
			$this->assertSame(false, $result);

			$result = $this->ue->getCategoryAttribute('not-a-category', 'read');
			$this->assertSame(false, $result);

		}

		public function testGetCategories()
		{
			$expected = array (
			  0 =>
			  array (
			 //   'user_extended_struct_id' => '16',
			    'user_extended_struct_name' => 'mycategory',
			    'user_extended_struct_text' => 'Category Name',
			    'user_extended_struct_type' => '0',
			    'user_extended_struct_parms' => '',
			    'user_extended_struct_values' => '',
			    'user_extended_struct_default' => '',
			    'user_extended_struct_read' => '0',
			    'user_extended_struct_write' => '254',
			    'user_extended_struct_required' => '0',
			    'user_extended_struct_signup' => '0',
			    'user_extended_struct_applicable' => '253',
			    'user_extended_struct_order' => '1',
			    'user_extended_struct_parent' => '0',
			  ),
			  1 =>
			  array (
			//    'user_extended_struct_id' => '17',
			    'user_extended_struct_name' => 'mycategory2',
			    'user_extended_struct_text' => 'Category Name 2',
			    'user_extended_struct_type' => '0',
			    'user_extended_struct_parms' => '',
			    'user_extended_struct_values' => '',
			    'user_extended_struct_default' => '',
			    'user_extended_struct_read' => '254',
			    'user_extended_struct_write' => '254',
			    'user_extended_struct_required' => '0',
			    'user_extended_struct_signup' => '0',
			    'user_extended_struct_applicable' => '253',
			    'user_extended_struct_order' => '2',
			    'user_extended_struct_parent' => '0',
			  ),
			);


			$result = $this->ue->getCategories(false);

			$this->assertNotEmpty($result);

			$id = 0;
			foreach($result as $row)
			{
				unset($row['user_extended_struct_id']);
				$this->assertSame($expected[$id], $row);
				$id++;
			}

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
*/
		public function testRenderValue()
		{
			$expectedRenderedValues = array (
			  'text'         => 'Some Text',
			  'homepage'     => 'https://e107.org',
			  'radio'        => 'Male',
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

			foreach($this->userValues as $field => $v)
			{
				$name  = 'user_'.$field;
				$result = $this->ue->renderValue($v, $name);
				$this->assertEquals($expectedRenderedValues[$field], $result);
			}


		}

		public function testGetFieldNames()
		{
			$expected = array (
			  0 => 'user_text',
			  1 => 'user_homepage',
			  2 => 'user_radio',
			  3 => 'user_dropdown',
			  4 => 'user_dbfield',
			  5 => 'user_textarea',
			  6 => 'user_integer',
			  7 => 'user_date',
			  8 => 'user_language',
			  9 => 'user_list',
			  10 => 'user_checkbox',
			  11 => 'user_predefined',
			  12 => 'user_addon',
			  13 => 'user_country',
			  14 => 'user_richtextarea',
			);

			$result = $this->ue->getFieldNames();

			foreach($expected as $field)
			{
				$this->assertContains($field, $result);
			}

		}
/*
		public function testUser_extended_modify()
		{

		}

		public function testUser_extended_remove()
		{

		}

		public function testSet()
		{

		}
*/
		public function testUser_extended_get_categories()
		{
			$expected = array (
				  'mycategory' =>
				  array (
				    'user_extended_struct_name' => 'mycategory',
				    'user_extended_struct_text' => 'Category Name',
				    'user_extended_struct_type' => '0',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '0',
				    'user_extended_struct_write' => '254',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '253',
				    'user_extended_struct_order' => '1',
				    'user_extended_struct_parent' => '0',
				  ),
				  'mycategory2' =>
				  array (
				    'user_extended_struct_name' => 'mycategory2',
				    'user_extended_struct_text' => 'Category Name 2',
				    'user_extended_struct_type' => '0',
				    'user_extended_struct_parms' => '',
				    'user_extended_struct_values' => '',
				    'user_extended_struct_default' => '',
				    'user_extended_struct_read' => '254',
				    'user_extended_struct_write' => '254',
				    'user_extended_struct_required' => '0',
				    'user_extended_struct_signup' => '0',
				    'user_extended_struct_applicable' => '253',
				    'user_extended_struct_order' => '2',
				    'user_extended_struct_parent' => '0',
				  ),
				);

			// Test 1.
			$result = $this->compileCategoryResult($this->ue->user_extended_get_categories());
			$this->assertNotEmpty($result);
			$this->assertEquals($expected, $result);

			// Test 2
			$result = $this->compileCategoryResult($this->ue->user_extended_get_categories(false), false);
			$this->assertNotEmpty($result);
			$this->assertEquals($expected, $result);


		}

		/**
		 * Remove ID since it could change during testing.
		 * @param array $result
		 */
		private function compileCategoryResult($result, $other=true)
		{
			if(empty($result))
			{
				return array();
			}

			$myresult = [];

			if($other === false)
			{
				$result = array($result);
			}

			foreach($result as $row)
			{
				foreach($row as $arr)
				{
					unset($arr['user_extended_struct_id']);
					$id = (string) $arr['user_extended_struct_name'];
					foreach($arr as $field=>$val)
					{
						$myresult[$id][$field] = $val;
					}

				}
			}

			return $myresult;
		}
/*
		public function testAddDefaultFields()
		{

		}
*/
	// FIXME shuffling may cause the id numbers to change.
	/*	public function testUser_extended_get_fields()
		{
			$expected = array (
			  16 =>
			  array (
			    0 =>
			    array (
			      'user_extended_struct_id' => '3',
			      'user_extended_struct_name' => 'radio',
			      'user_extended_struct_text' => 'Radio',
			      'user_extended_struct_type' => '2',
			      'user_extended_struct_parms' => '',
			      'user_extended_struct_values' => 'M =&gt; UE_LAN_MALE,F =&gt; UE_LAN_FEMALE',
			      'user_extended_struct_default' => 'F',
			      'user_extended_struct_read' => '253',
			      'user_extended_struct_write' => '254',
			      'user_extended_struct_required' => '1',
			      'user_extended_struct_signup' => '0',
			      'user_extended_struct_applicable' => '250',
			      'user_extended_struct_order' => '2',
			      'user_extended_struct_parent' => '16',
			    ),
			    1 =>
			    array (
			      'user_extended_struct_id' => '6',
			      'user_extended_struct_name' => 'textarea',
			      'user_extended_struct_text' => 'Textarea',
			      'user_extended_struct_type' => '5',
			      'user_extended_struct_parms' => '',
			      'user_extended_struct_values' => '',
			      'user_extended_struct_default' => '',
			      'user_extended_struct_read' => '253',
			      'user_extended_struct_write' => '254',
			      'user_extended_struct_required' => '0',
			      'user_extended_struct_signup' => '0',
			      'user_extended_struct_applicable' => '250',
			      'user_extended_struct_order' => '5',
			      'user_extended_struct_parent' => '16',
			    ),
			    2 =>
			    array (
			      'user_extended_struct_id' => '10',
			      'user_extended_struct_name' => 'list',
			      'user_extended_struct_text' => 'List',
			      'user_extended_struct_type' => '9',
			      'user_extended_struct_parms' => '',
			      'user_extended_struct_values' => 'timezones',
			      'user_extended_struct_default' => '',
			      'user_extended_struct_read' => '253',
			      'user_extended_struct_write' => '254',
			      'user_extended_struct_required' => '1',
			      'user_extended_struct_signup' => '0',
			      'user_extended_struct_applicable' => '250',
			      'user_extended_struct_order' => '9',
			      'user_extended_struct_parent' => '16',
			    ),
			  ),
			);


			$result = $this->ue->user_extended_get_fields(16);
			$this->assertEquals($expected, $result);

		}*/
/*
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
*/
		public function testGetFields()
		{
			$data = $this->ue->getFields();
			$this->assertArrayHasKey('text', $data);
		//	$this->assertCount(15, $data);

			$data = $this->ue->getFields(16);
			$this->assertArrayHasKey('radio', $data);
		//	$this->assertCount(3, $data);

			$data = $this->ue->getFields(17);
			$this->assertArrayHasKey('country', $data);
		//	$this->assertCount(1, $data);
		}
/*

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
*/
// Fixme needs to be reworked to ignore the struct_id.
/*
		public function testUser_extended_get_fieldList()
		{
			$expected = array (
			  'radio' =>
			  array (
			    'user_extended_struct_id' => '3',
			    'user_extended_struct_name' => 'radio',
			    'user_extended_struct_text' => 'Radio',
			    'user_extended_struct_type' => '2',
			    'user_extended_struct_parms' => '',
			    'user_extended_struct_values' => 'M =&gt; UE_LAN_MALE,F =&gt; UE_LAN_FEMALE',
			    'user_extended_struct_default' => 'F',
			    'user_extended_struct_read' => '253',
			    'user_extended_struct_write' => '254',
			    'user_extended_struct_required' => '1',
			    'user_extended_struct_signup' => '0',
			    'user_extended_struct_applicable' => '250',
			    'user_extended_struct_order' => '2',
			    'user_extended_struct_parent' => 16,
			  ),
			  'textarea' =>
			  array (
			    'user_extended_struct_id' => '6',
			    'user_extended_struct_name' => 'textarea',
			    'user_extended_struct_text' => 'Textarea',
			    'user_extended_struct_type' => '5',
			    'user_extended_struct_parms' => '',
			    'user_extended_struct_values' => '',
			    'user_extended_struct_default' => '',
			    'user_extended_struct_read' => '253',
			    'user_extended_struct_write' => '254',
			    'user_extended_struct_required' => '0',
			    'user_extended_struct_signup' => '0',
			    'user_extended_struct_applicable' => '250',
			    'user_extended_struct_order' => '5',
			    'user_extended_struct_parent' => 16,
			  ),
			  'list' =>
			  array (
			    'user_extended_struct_id' => '10',
			    'user_extended_struct_name' => 'list',
			    'user_extended_struct_text' => 'List',
			    'user_extended_struct_type' => '9',
			    'user_extended_struct_parms' => '',
			    'user_extended_struct_values' => 'timezones',
			    'user_extended_struct_default' => '',
			    'user_extended_struct_read' => '253',
			    'user_extended_struct_write' => '254',
			    'user_extended_struct_required' => '1',
			    'user_extended_struct_signup' => '0',
			    'user_extended_struct_applicable' => '250',
			    'user_extended_struct_order' => '9',
			    'user_extended_struct_parent' => 16,
			  ),
			);

			$result = $this->ue->user_extended_get_fieldList(16, 'user_extended_struct_name');
			$this->assertSame($expected, $result);
		}
*/



	}
