<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_customfieldsTest extends \Codeception\Test\Unit
	{

		/** @var e_customfields  */
		protected $cf;

		protected $config = '{
		    "__tabs__": {
                "extra": "My Tab"
            },
		    "image": {
		        "title": "Image",
		        "type": "image",
		        "writeParms": "",
		        "help": ""
		    },
		    "video": {
		        "title": "Video",
		        "type": "video",
		        "writeParms": "",
		        "help": "Youtube"
		    },
		    "bbarea": {
		        "title": "WYSWIYG",
		        "type": "bbarea",
		        "writeParms": "",
		        "help": ""
		    },
		    "boolean": {
		        "title": "Boolean",
		        "type": "boolean",
		        "writeParms": "",
		        "help": ""
		    },
		    "checkboxes": {
		        "title": "Checkboxes",
		        "type": "checkboxes",
		        "writeParms": "{ \"default\": \"blank\", \"optArray\": { \"car\": \"Car\", \"boat\": \"Boat\", \"plane\": \"Plane\" } }",
		        "help": ""
		    },
		    "country": {
		        "title": "Country",
		        "type": "country",
		        "writeParms": "",
		        "help": ""
		    },
		    "datestamp": {
		        "title": "Datestamp",
		        "type": "datestamp",
		        "writeParms": "",
		        "help": ""
		    },
		    "dropdown": {
		        "title": "Dropdown",
		        "type": "dropdown",
		        "writeParms": "{ \"default\": \"blank\", \"optArray\": { \"blue\": \"Blue\", \"green\": \"Green\", \"red\": \"Red\" } }",
		        "help": ""
		    },
		    "email": {
		        "title": "Email",
		        "type": "email",
		        "writeParms": "",
		        "help": ""
		    },
		    "file": {
		        "title": "File",
		        "type": "file",
		        "writeParms": "",
		        "help": ""
		    },
		    "icon": {
		        "title": "Icon",
		        "type": "icon",
		        "writeParms": "",
		        "help": ""
		    },
		    "language": {
		        "title": "Language",
		        "type": "language",
		        "writeParms": "",
		        "help": ""
		    },
		    "lanlist": {
		        "title": "LanList",
		        "type": "lanlist",
		        "writeParms": "",
		        "help": ""
		    },
		    "number": {
		        "title": "Number",
		        "type": "number",
		        "writeParms": "",
		        "help": ""
		    },
		    "password": {
		        "title": "Password",
		        "type": "password",
		        "writeParms": "",
		        "help": ""
		    },
		    "radio": {
		        "title": "Radio",
		        "type": "radio",
		        "writeParms": "{  \"optArray\": { \"yes\": \"Yes\", \"no\": \"No\", \"maybe\": \"Maybe\" } }",
		        "help": ""
		    },
		    "tags": {
		        "title": "Tags",
		        "type": "tags",
		        "writeParms": "",
		        "help": ""
		    },
		    "textarea": {
		        "title": "Textarea",
		        "type": "textarea",
		        "writeParms": "size=block-level",
		        "help": ""
		    },
		    "url": {
		        "title": "Url",
		        "type": "url",
		        "writeParms": "",
		        "help": ""
		    },
		    "user": {
		        "title": "User",
		        "type": "user",
		        "writeParms": "",
		        "help": ""
		    },
		    "userclass": {
		        "title": "Userclass",
		        "type": "userclass",
		        "writeParms": "",
		        "help": ""
		    },
		     "progressbar": {
		        "title": "Progress Bar",
		        "type": "progressbar",
		        "writeParms": "",
		        "help": "Progress bar"
		    }
		}';


		protected $data = '{
		    "image": "{e_PLUGIN}gallery\/images\/butterfly.jpg",
		    "video": "WcuRPzB4RNc.youtube",
		    "bbarea": "[html]<p><b>Rich text.<\/b><\/p>[\/html]",
		    "boolean": "1",
		    "checkboxes": "boat,plane",
		    "country": "ad",
		    "datestamp": "1484267751",
		    "dropdown": "red",
		    "email": "my@email.com",
		    "file": "{e_MEDIA_FILE}2016-04\/e107_banners.zip",
		    "icon": "fa-check.glyph",
		    "language": "fr",
		    "lanlist": "en",
		    "number": "0",
		    "password": "a8f5f167f44f4964e6c998dee827110c",
		    "tags": "tag1,tag2,tag3",
		    "textarea": "Plain text",
		    "url": "http:\/\/something.com",
		    "user": "1",
		    "userclass": "0",
		    "progressbar": "75"
		}';

		protected $posted = array ('__e_customfields_tabs__'=>"My New Tab", 'e-token' => '1dbda78672ac3b1bd8f73f8c158d0291', 'chapter_icon' => '', 'mediameta_chapter_icon' => '', 'chapter_parent' => '1', 'chapter_name' => 'Chapter 1', 'chapter_template' => 'default', 'chapter_meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ut nunc ac neque egestas ullamcorper. In convallis semper hendrerit. Etiam non dolor nisl, varius facilisis dui. Nunc egestas massa nunc.', 'chapter_meta_keywords' => '', 'chapter_sef' => 'chapter-1', 'chapter_manager' => '0', 'chapter_order' => '0', 'chapter_visibility' => '0', 'chapter_fields' => array ( 0 => array ( 'key' => 'image', 'title' => 'Image', 'type' => 'image', 'writeParms' => '', 'help' => '', ), 1 => array ( 'key' => 'video', 'title' => 'Video', 'type' => 'video', 'writeParms' => '', 'help' => 'Youtube', ), 2 => array ( 'key' => 'bbarea', 'title' => 'WYSWIYG', 'type' => 'bbarea', 'writeParms' => '', 'help' => '', ), 3 => array ( 'key' => 'boolean', 'title' => 'Boolean', 'type' => 'boolean', 'writeParms' => '', 'help' => '', ), 4 => array ( 'key' => 'checkboxes', 'title' => 'Checkboxes', 'type' => 'checkboxes', 'writeParms' => '{ "default": "blank", "optArray": { "car": "Car", "boat": "Boat", "plane": "Plane" } }', 'help' => '', ), 5 => array ( 'key' => 'country', 'title' => 'Country', 'type' => 'country', 'writeParms' => '', 'help' => '', ), 6 => array ( 'key' => 'datestamp', 'title' => 'Datestamp', 'type' => 'datestamp', 'writeParms' => '', 'help' => '', ), 7 => array ( 'key' => 'dropdown', 'title' => 'Dropdown', 'type' => 'dropdown', 'writeParms' => '{ "default": "blank", "optArray": { "blue": "Blue", "green": "Green", "red": "Red" } }', 'help' => '', ), 8 => array ( 'key' => 'email', 'title' => 'Email', 'type' => 'email', 'writeParms' => '', 'help' => '', ), 9 => array ( 'key' => 'file', 'title' => 'File', 'type' => 'file', 'writeParms' => '', 'help' => '', ), 10 => array ( 'key' => 'icon', 'title' => 'Icon', 'type' => 'icon', 'writeParms' => '', 'help' => '', ), 11 => array ( 'key' => 'language', 'title' => 'Language', 'type' => 'language', 'writeParms' => '', 'help' => '', ), 12 => array ( 'key' => 'lanlist', 'title' => 'LanList', 'type' => 'lanlist', 'writeParms' => '', 'help' => '', ), 13 => array ( 'key' => 'number', 'title' => 'Number', 'type' => 'number', 'writeParms' => '', 'help' => '', ), 14 => array ( 'key' => 'password', 'title' => 'Password', 'type' => 'password', 'writeParms' => '', 'help' => '', ), 15 => array ( 'key' => 'radio', 'title' => 'Radio', 'type' => 'radio', 'writeParms' => '{ "optArray": { "yes": "Yes", "no": "No", "maybe": "Maybe" } }', 'help' => '', ), 16 => array ( 'key' => 'tags', 'title' => 'Tags', 'type' => 'tags', 'writeParms' => '', 'help' => '', ), 17 => array ( 'key' => 'textarea', 'title' => 'Textarea', 'type' => 'textarea', 'writeParms' => 'size=block-level', 'help' => '', ), 18 => array ( 'key' => 'url', 'title' => 'Url', 'type' => 'url', 'writeParms' => '', 'help' => '', ), 19 => array ( 'key' => 'user', 'title' => 'User', 'type' => 'user', 'writeParms' => '', 'help' => '', ), 20 => array ( 'key' => 'userclass', 'title' => 'Userclass', 'type' => 'userclass', 'writeParms' => '', 'help' => '', ), ), 'etrigger_submit' => 'update', '__after_submit_action' => 'list', 'submit_value' => '2', 'mode' => NULL, );

		protected function _before()
		{
			try
			{
				$this->cf = $this->make('e_customfields');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_customfields object");
			}

		}

		public function testFieldValues()
		{
			$this->cf->loadConfig($this->config)->loadData($this->data);

			$data= $this->cf->getData();

			$titles = array();

			$titlesExpected = array (
			  0 => 'Image',
			  1 => 'Video',
			  2 => 'WYSWIYG',
			  3 => 'Boolean',
			  4 => 'Checkboxes',
			  5 => 'Country',
			  6 => 'Datestamp',
			  7 => 'Dropdown',
			  8 => 'Email',
			  9 => 'File',
			  10 => 'Icon',
			  11 => 'Language',
			  12 => 'LanList',
			  13 => 'Number',
			  14 => 'Password',
			  15 => 'Tags',
			  16 => 'Textarea',
			  17 => 'Url',
			  18 => 'User',
			  19 => 'Userclass',
			  20 => 'Progress Bar',
			);

			foreach($data as $ok=>$v)
			{

				$titles[] = $this->cf->getFieldTitle($ok);
			//	echo ($title)."\n";
				$value = $this->cf->getFieldValue($ok);
				$valueRaw = $this->cf->getFieldValue($ok, array('mode'=>'raw'));
			}

			// check titles.
			$this->assertEquals($titlesExpected,$titles);

			//@todo more tests for value and valueRaw. 





		}


/*
		public function testProcessConfigPost()
		{

		}

		public function testGetTabId()
		{

		}

		public function testGetFieldTypes()
		{

		}

		public function testRenderConfigForm()
		{

		}

		public function testGetFieldValue()
		{

		}

		public function testSetAdminUIConfig()
		{

		}

		public function testSetAdminUIData()
		{

		}

		public function testGetFieldTitle()
		{

		}

		public function testProcessDataPost()
		{

		}

		public function testLoadConfig()
		{

		}

		public function testRenderTest()
		{

		}

		public function testSetTab()
		{

		}

		public function testLoadData()
		{

		}

		public function testGetConfig()
		{

		}

		public function testGetTabLabel()
		{

		}

		public function testGetData()
		{

		}*/
	}
