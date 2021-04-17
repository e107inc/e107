<?php


	class e_front_modelTest extends \Codeception\Test\Unit
	{

		/** @var e_front_model */
		protected $model;

		private $dataFields;

		protected function _before()
		{

			try
			{
				$this->model = $this->make('e_front_model');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$this->dataFields = array(
				'myfield'                   => 'str',
				'myfield2'                  => 'int',
				'myfield3'                  => 'str',
				'myfield4'                  => 'json',
				'myfield5'                  => 'array',
				'myfield6'                  => 'str',
				'gateways/other'            => 'str',
				'gateways/paypal/active'    => 'int',
				'gateways/paypal/title'     => 'str',
				'other/active'              => 'bool',
				'other/bla/active'          => 'array',
				'another/one/active'        => 'bool',
			);


			$this->model->setDataFields($this->dataFields);

		}


/*
		public function testIsValidFieldKey()
		{

			$res = [];
			foreach($this->dataFields as $k=>$var)
			{
				$res[$k] = $this->model->isValidFieldKey($k);

			}


		}*/


		/**
		 * santize() takes posted data and then sanitized it based on the dataFields value.
		 */
		public function testSanitize()
		{
			// test simple text field.
			$result = $this->model->sanitize('myfield', 'My Field Value');
			$this->assertSame('My Field Value', $result);


			$result = $this->model->sanitize(array('myfield' => 'My Field Value'));
			$this->assertSame(array( 'myfield' => 'My Field Value' ), $result);



			// test posting of array and conversion to json.
			$posted = array('myfield4' => array('level1' => array('level2' => 'level2 value')) );
			$expected = array (
  'myfield4' => '{
    "level1": {
        "level2": "level2 value"
    }
}',
);
			$result = $this->model->sanitize($posted);

			$this->assertSame($expected, $result);

			// test posting of array returned as array.
			$posted = array('myfield5' => array('level1' => array('level2' => 'level2 value')) );
			$result = $this->model->sanitize($posted);
			$this->assertSame($posted, $result);


			// test posting of array returned as array.
			$posted = array('myfield6' => array('opt1', 'opt2', 'opt3'));
			$result = $this->model->sanitize($posted);
			$this->assertSame($posted, $result);


			// test undefined field.
			$result = $this->model->sanitize('non_field', 1);
			$this->assertNull($result);

			// test multi-dimensional field.
			$result = $this->model->sanitize('gateways/paypal/active', 1);
			$this->assertSame(1, $result);

			// Non admin-ui example.
			$posted = array('gateways/paypal/active' =>
				    array (
				      'paypal' =>
				        array (
				          'title' => 'PayPal Express' ,
				          'icon' =>  'fa-paypal',
						)
				    )
			);


			// Real example from vstore prefs. key becomes multi-dimensional array when posted.
			$posted = array(
				'myfield'   => 'my string',
				'gateways' => array (
				      'paypal' =>
				        array (
				          'active' =>  '0',
				          'title' => 'PayPal Express' ,
						)
				),
				'other' => array(
					'active' => 5,

				),
				'another' => array(
					'one'   => array('active' => 1)
				)
			);

			$expected = array (
			  'myfield'   => 'my string',
			  'gateways' =>
			  array (
			    'paypal' =>
			    array (
			      'active' => 0, // converted to int.
			      'title' => 'PayPal Express',
			    ),
			  ),
			  'other' =>
			  array (
			    'active' => true, // converted to bool
			  ),
			  'another' =>
			  array (
			    'one' =>
			    array (
			      'active' => true, //  converted to bool
			    ),
			  ),
			);

			$result = $this->model->sanitize($posted);
			$this->assertSame($expected, $result);
			$this->assertNotSame($result, $posted);
		}

		public function testSanitizeNoUnexpectedFields()
		{
			$dataFields = [
				'expected' => 'bool',
				'nested/expected' => 'int',
			];

			$input = [
				'expected' => '5',
				'unexpected' => 'SHOULD_BE_REMOVED',
				'nested' => [
					'expected' => "7331",
					'unexpected' => 'SHOULD BE REMOVED',
				]
			];

			$expected = [
				'expected' => true,
				'nested' => [
					'expected' => 7331
				]
			];

			$this->model->setDataFields($dataFields);

			$result = $this->model->sanitize($input);
			$this->assertSame($expected, $result);
		}

		public function testCustomFieldsSanitize()
		{
			$dataFields = array ( 'chapter_id' => 'int', 'chapter_icon' => 'str', 'chapter_parent' => 'str', 'chapter_name' => 'str', 'chapter_template' => 'str', 'chapter_meta_description' => 'str', 'chapter_meta_keywords' => 'str', 'chapter_sef' => 'str', 'chapter_manager' => 'int', 'chapter_order' => 'str', 'chapter_visibility' => 'int', 'chapter_fields' => 'json', 'chapter_image' => 'str', );
			$this->model->setDataFields($dataFields);

			$posted = array ( 'lastname_74758209201093747' => '', 'e-token' => 'a51c3769f784b6d980bdb86a93e56998', 'chapter_icon' => '', 'mediameta_chapter_icon' => '', 'chapter_parent' => '1', 'chapter_name' => 'Custom Fields 10', 'chapter_template' => 'default', 'chapter_meta_description' => 'Chapter containing custom fields', 'chapter_meta_keywords' => '', 'chapter_sef' => 'customfields', 'chapter_manager' => '254', 'chapter_order' => '0', 'chapter_visibility' => '0', 'chapter_image' => '', 'mediameta_chapter_image' => '', '__e_customfields_tabs__' => 'Custom Fields 10', 'chapter_fields' => array ( '__tabs__' => array ( 'additional' => 'Custom Fields 10', ), 'mybbarea' => array ( 'title' => 'Rich Text', 'type' => 'bbarea', 'writeParms' => '{ "rows": "4" }', 'help' => '', ), 'myboolean' => array ( 'title' => 'Boolean', 'type' => 'boolean', 'writeParms' => '', 'help' => '', ), 'mycheckbox' => array ( 'title' => 'Checkbox', 'type' => 'checkbox', 'writeParms' => '', 'help' => '', ), 'mycountry' => array ( 'title' => 'Country', 'type' => 'country', 'writeParms' => '', 'help' => '', ), 'mydatestamp' => array ( 'title' => 'Date', 'type' => 'datestamp', 'writeParms' => '{ "format": "yyyy-mm-dd" }', 'help' => '', ), 'mydropdown' => array ( 'title' => 'Selection', 'type' => 'dropdown', 'writeParms' => '{ "optArray": { "blue": "Blue", "green": "Green", "red": "Red" }, "default": "blank" }', 'help' => '', ), 'myemail' => array ( 'title' => 'Email', 'type' => 'email', 'writeParms' => '', 'help' => '', ), 'myfile' => array ( 'title' => 'File', 'type' => 'file', 'writeParms' => '', 'help' => '', ), 'myicon' => array ( 'title' => 'Icon', 'type' => 'icon', 'writeParms' => '', 'help' => '', ), 'myimage' => array ( 'title' => 'Image', 'type' => 'image', 'writeParms' => '', 'help' => '', ), 'mylanguage' => array ( 'title' => 'Language', 'type' => 'language', 'writeParms' => '', 'help' => '', ), 'mynumber' => array ( 'title' => 'Number', 'type' => 'number', 'writeParms' => '', 'help' => '', ), 'myprogressbar' => array ( 'title' => 'Progress', 'type' => 'progressbar', 'writeParms' => '', 'help' => '', ), 'mytags' => array ( 'title' => 'Tags', 'type' => 'tags', 'writeParms' => '', 'help' => '', ), 'mytext' => array ( 'title' => 'Text', 'type' => 'text', 'writeParms' => '', 'help' => '', ), 'myurl' => array ( 'title' => 'URL', 'type' => 'url', 'writeParms' => '', 'help' => '', ), 'myvideo' => array ( 'title' => 'Video', 'type' => 'video', 'writeParms' => '', 'help' => '', ), ), 'etrigger_submit' => 'update', '__after_submit_action' => 'list', 'submit_value' => '9', );
			$expected = array (
  'chapter_icon' => '',
  'chapter_parent' => '1',
  'chapter_name' => 'Custom Fields 10',
  'chapter_template' => 'default',
  'chapter_meta_description' => 'Chapter containing custom fields',
  'chapter_meta_keywords' => '',
  'chapter_sef' => 'customfields',
  'chapter_manager' => 254,
  'chapter_order' => '0',
  'chapter_visibility' => 0,
  'chapter_image' => '',
  'chapter_fields' => '{
    "__tabs__": {
        "additional": "Custom Fields 10"
    },
    "mybbarea": {
        "title": "Rich Text",
        "type": "bbarea",
        "writeParms": "{ \\"rows\\": \\"4\\" }",
        "help": ""
    },
    "myboolean": {
        "title": "Boolean",
        "type": "boolean",
        "writeParms": "",
        "help": ""
    },
    "mycheckbox": {
        "title": "Checkbox",
        "type": "checkbox",
        "writeParms": "",
        "help": ""
    },
    "mycountry": {
        "title": "Country",
        "type": "country",
        "writeParms": "",
        "help": ""
    },
    "mydatestamp": {
        "title": "Date",
        "type": "datestamp",
        "writeParms": "{ \\"format\\": \\"yyyy-mm-dd\\" }",
        "help": ""
    },
    "mydropdown": {
        "title": "Selection",
        "type": "dropdown",
        "writeParms": "{ \\"optArray\\": { \\"blue\\": \\"Blue\\", \\"green\\": \\"Green\\", \\"red\\": \\"Red\\" }, \\"default\\": \\"blank\\" }",
        "help": ""
    },
    "myemail": {
        "title": "Email",
        "type": "email",
        "writeParms": "",
        "help": ""
    },
    "myfile": {
        "title": "File",
        "type": "file",
        "writeParms": "",
        "help": ""
    },
    "myicon": {
        "title": "Icon",
        "type": "icon",
        "writeParms": "",
        "help": ""
    },
    "myimage": {
        "title": "Image",
        "type": "image",
        "writeParms": "",
        "help": ""
    },
    "mylanguage": {
        "title": "Language",
        "type": "language",
        "writeParms": "",
        "help": ""
    },
    "mynumber": {
        "title": "Number",
        "type": "number",
        "writeParms": "",
        "help": ""
    },
    "myprogressbar": {
        "title": "Progress",
        "type": "progressbar",
        "writeParms": "",
        "help": ""
    },
    "mytags": {
        "title": "Tags",
        "type": "tags",
        "writeParms": "",
        "help": ""
    },
    "mytext": {
        "title": "Text",
        "type": "text",
        "writeParms": "",
        "help": ""
    },
    "myurl": {
        "title": "URL",
        "type": "url",
        "writeParms": "",
        "help": ""
    },
    "myvideo": {
        "title": "Video",
        "type": "video",
        "writeParms": "",
        "help": ""
    }
}',
);
			$result = $this->model->sanitize($posted);
			$this->assertSame($expected, $result);



		}



/*
		public function testAddValidationError()
		{

		}

		public function testResetMessages()
		{

		}

		public function testGetSqlErrorNumber()
		{

		}

		public function testRenderMessages()
		{

		}

		public function testHasPostedData()
		{

		}

		public function testDataHasChangedFor()
		{

		}

		public function testSetValidationRule()
		{

		}

		public function testGetPostedData()
		{

		}

		public function testSetValidationRules()
		{

		}

		public function testRenderValidationErrors()
		{

		}

		public function testMergeData()
		{

		}

		public function testGetOptionalRules()
		{

		}

		public function testHasSqlError()
		{

		}

		public function testIsPostedData()
		{

		}

		public function testAddPostedData()
		{

		}

		public function testGetSqlQuery()
		{

		}

		public function testHasValidationError()
		{

		}

		public function testSetPosted()
		{

		}

		public function testSetPostedData()
		{

		}

		public function testSetOptionalRules()
		{

		}

		public function testGetDbTypes()
		{

		}

		public function testGetPosted()
		{

		}

		public function testGetIfPosted()
		{

		}

		public function testRemovePostedData()
		{

		}

		public function testDataHasChanged()
		{

		}

		public function testSave()
		{

		}

		public function testMergePostedData()
		{

		}

		public function testHasError()
		{

		}

		public function testIsPosted()
		{

		}

		public function testSetDbTypes()
		{

		}

		public function testSaveDebug()
		{

		}

		public function testSetMessages()
		{

		}
*/
/*
		public function testDestroy()
		{

		}

		public function testGetValidationRules()
		{

		}

		public function testGetValidator()
		{

		}

		public function testRemovePosted()
		{

		}

		public function testHasPosted()
		{

		}

		public function testValidate()
		{

		}

		public function testVerify()
		{

		}

		public function testGetSqlError()
		{

		}

		public function testLoad()
		{

		}

		public function testToSqlQuery()
		{

		}*/


	}
