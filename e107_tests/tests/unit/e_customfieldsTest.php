<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_customfieldsTest extends \Test\Unit
	{

		/** @var e_customfields  */
		protected $cf;

		protected $config = '{
		    "__tabs__": {
                "additional": "My Tab"
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
		    "progressbar": "75",
		    "__trust__": 1
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

			$this->cf->__construct();

			setlocale(LC_TIME, 'C');
			date_default_timezone_set('UTC');

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

		$expectedValues = array (
		  'image'       => '<img class="img-responsive img-fluid"',
		  'video'       => '<div class="embed-responsive embed-responsive-16by9',
		  'bbarea'      => '<!-- bbcode-html-start --><p><b>Rich text.</b></p><!-- bbcode-html-end -->',
		  'boolean'     => '<i class',
		  'checkboxes'  => 'Boat, Plane',
		  'country'     => 'Andorra',
		  'datestamp'   => '<span>13 Jan 2017 : 00:35</span>',
		  'dropdown'    => 'Red',
		  'email'       => 'my@email.com',
		  'file'        => '<a href=',
		  'icon'        => '<i class=',
		  'language'    => 'French',
		  'lanlist'     => 'English',
		  'number'      => '0',
		  'password'    => 'a8f5f167f44f4964e6c998dee827110c',
		  'tags'        => "<span class='label label-default'",
		  'textarea'    => 'Plain text',
		  'url'         => 'http://something.com',
		  'user'        => \Helper\AdminLogin::ADMIN_USER,
		  'userclass'   => 'Everyone (public)',
		  'progressbar' => "<div  class='progress",

		);	

		$expectedRawValues = array (
		  'image'       => 'thumb.php?src=',
		  'video'       => 'https://www.youtube.com/watch?v=WcuRPzB4RNc',
		  'bbarea'      => '[html]<p><b>Rich text.</b></p>[/html]',
		  'boolean'     => '1',
		  'checkboxes'  => 'boat,plane',
		  'country'     => 'ad',
		  'datestamp'   => '1484267751',
		  'dropdown'    => 'red',
		  'email'       => 'my@email.com',
		  'file'        => 'e107_banners.zip',
		  'icon'        => 'fa-check',
		  'language'    => 'fr',
		  'lanlist'     => 'en',
		  'number'      => '0',
		  'password'    => 'a8f5f167f44f4964e6c998dee827110c',
		  'tags'        => 'tag1,tag2,tag3',
		  'textarea'    => 'Plain text',
		  'url'         => 'http://something.com',
		  'user'        => '1',
		  'userclass'   => '0',
		  'progressbar' => '75%',

		);


		$export = array();
			foreach($data as $ok=>$v)
			{

				$titles[] = $this->cf->getFieldTitle($ok);
			//	echo ($title)."\n";
				$value = $this->cf->getFieldValue($ok);
				$valueRaw = $this->cf->getFieldValue($ok, array('mode'=>'raw'));

				$this->assertStringStartsWith($expectedValues[$ok], $value);
				$this->assertStringContainsString($expectedRawValues[$ok], $valueRaw);
			}

			// check titles.
			$this->assertEquals($titlesExpected,$titles);

		}


		public function testRenderConfigForm()
		{
			$this->cf->loadConfig($this->config)->loadData($this->data);
			$tab = $this->cf->getTabId();
			$this->assertSame('additional', $tab);

			$result= $this->cf->renderConfigForm('custom');
			$this->assertStringNotContainsString('__tab__', $result);

		}

		/**
		 * A value stored as it was posted, as every value was before values went through {@see e_parse::toDB()}, prints
		 * without its markup.
		 */
		public function testStoredMarkupInAValuePrintsAsText()
		{
			$markup = '"><svg onload=alert(1)>';
			$data = array();

			foreach(array_keys(json_decode($this->data, true)) as $key)
			{
				if($key !== '__trust__')
				{
					$data[$key] = 'x'.$markup;
				}
			}

			$data['video'] = 'x'.$markup.'.youtube';
			$data['icon'] = '<i class="fa"></i>'.$markup;
			$data['progressbar'] = '1/2'.$markup;

			$this->cf->loadConfig($this->config)->loadData(json_encode($data));

			foreach(array_keys($data) as $key)
			{
				$this->assertNoMarkupIn((string) $this->cf->getFieldValue($key), $key);
				$this->assertNoMarkupIn((string) $this->cf->getFieldValue($key, array('mode' => 'raw')), $key.' raw');
			}

			$this->cf->loadConfig(array('options' => array('title' => 'Options', 'type' => 'dropdown', 'writeParms' => json_encode(array('optArray' => array('a' => 'A'))), 'help' => '')));
			foreach(array('x'.$markup, json_encode(array('options' => '&lt;img src=x onerror=alert(1)&gt;', '__trust__' => 0))) as $stored)
			{
				$this->cf->loadData(($stored[0] === '{') ? $stored : json_encode(array('options' => $stored)));
				$this->assertNoMarkupIn((string) $this->cf->getFieldValue('options'), 'a field keyed options');
			}

			$this->cf->loadConfig($this->config)->loadData(json_encode(array('video' => 'x'.$markup.'.mp4')));
			$this->assertNoMarkupIn((string) $this->cf->getFieldValue('video'), 'video file');
			$this->assertNoMarkupIn(e107::getParser()->toAudio('x'.$markup.'.mp3'), 'audio file the page form previews');

			foreach(array('javascript:alert(1)//.svg', '{e_HANDLER}javascript:alert(1)//.svg', 'javascript&#58;alert(1)//.svg', 'javascript&colon;alert(1)//.svg',
				'javascript&#58alert(1)//.svg', 'javascript&amp;#58;alert(1)//.svg', 'javascript&amp;amp;#58;alert(1)//.svg', 'x" onload="alert(1)" y=".svg') as $link)
			{
				$this->cf->loadData(json_encode(array('image' => $link, 'url' => $link)));

				foreach(array('image', 'url') as $type)
				{
					$href = $this->cf->getFieldValue($type, array('mode' => 'raw'));
					$this->assertNoMarkupIn('<a href="'.$href.'">x</a>', $type.' link '.$link);
					$this->assertNoMarkupIn('<iframe src="'.html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8').'"></iframe>', $type.' link read back '.$link);
				}
			}

			$this->cf->loadData(json_encode(array('url' => "https://en.wikipedia.org/wiki/Ender&#039;s_Game")));
			$this->assertSame('https://en.wikipedia.org/wiki/Ender%27s_Game', $this->cf->getFieldValue('url', array('mode' => 'raw')));

			$this->cf->loadData(json_encode(array('image' => '{e_MEDIA_IMAGE}2016-04/a-b_c.svg')));
			$this->assertStringEndsWith('2016-04/a-b_c.svg', $this->cf->getFieldValue('image', array('mode' => 'raw')));

			$this->cf->loadData(json_encode(array('url' => 'https://example.com/?a=1&amp;b=2')));
			$this->assertSame('https://example.com/?a=1&amp;b=2', $this->cf->getFieldValue('url', array('mode' => 'raw')));
		}

		/**
		 * The page form loads each stored value and saves it back unchanged, however often it is saved.
		 */
		public function testAValueSurvivesTheFormUnchanged()
		{
			require_once(e_HANDLER.'admin_ui.php');

			$posted = array(
				'page_fields__bbarea' => '<p class="lead">Hi &amp; bye</p><p><img src="/e107_images/e107_icon_32.png" alt="logo"></p>',
				'page_fields__text'   => 'Bob\'s "quoted" text',
				'page_fields__number' => '0',
			);
			$stored = $this->cf->processDataPost('page_fields', $posted);

			for($i = 0; $i < 2; $i++)
			{
				$seen = array();
				$model = $this->make('e_admin_model', array('set' => function ($key, $value = null) use (&$seen) { $seen[$key] = $value; }));
				$ui = $this->make('e_admin_ui', array('getModel' => $model));
				$this->cf->loadData(json_encode($stored['page_fields']))->setAdminUIData('page_fields', $ui);
				unset($seen['page_fields']);

				$submitted = array_map(function ($value) { return is_string($value) ? html_entity_decode($value, ENT_QUOTES) : $value; }, $seen);
				$this->assertSame($stored, $this->cf->processDataPost('page_fields', $submitted), 'save '.($i + 1));
			}

			$this->assertSame('0', $stored['page_fields']['number']);
		}

		/**
		 * A chosen option whose key holds a quote or an ampersand is still found once its value has been stored.
		 */
		public function testAStoredOptionStillNamesItsLabel()
		{
			$this->cf->loadConfig(array(
				'pick'  => array('title' => 'Pick', 'type' => 'dropdown', 'writeParms' => json_encode(array('optArray' => array("don't" => 'Do not', 'R&D' => 'Research'))), 'help' => ''),
				'picks' => array('title' => 'Picks', 'type' => 'checkboxes', 'writeParms' => json_encode(array('optArray' => array("don't" => 'Do not', 'R&D' => 'Research'))), 'help' => ''),
			));

			foreach(array("don't" => 'Do not', 'R&D' => 'Research') as $chosen => $label)
			{
				$stored = $this->cf->processDataPost('page_fields', array('page_fields__pick' => $chosen, 'page_fields__picks' => $chosen));
				$this->cf->loadData(json_encode($stored['page_fields']));

				$this->assertSame($label, $this->cf->getFieldValue('pick'));
				$this->assertSame($label, $this->cf->getFieldValue('picks'));
			}
		}

		/**
		 * A value saved by an administrator who may post HTML prints as HTML where the page prints HTML, in raw mode
		 * too with its quotes back, an icon written as markup only for one with script access, and the same values with
		 * no record of who saved them print as text.
		 */
		public function testAValuePrintsAsItsAuthorMayPostIt()
		{
			$values = array(
				'textarea' => '<p class=&quot;lead&quot;>Lead <b>bold</b></p>',
				'url'      => 'See <b>this</b>',
				'icon'     => '<i class=&quot;fa fa-check&quot;></i>',
			);

			$this->cf->loadConfig($this->config)->loadData(json_encode($values + array('__trust__' => 1)));
			$this->assertStringContainsString('<p class="lead">Lead <b>bold</b></p>', $this->cf->getFieldValue('textarea'));
			$this->assertSame('<p class="lead">Lead <b>bold</b></p>', $this->cf->getFieldValue('textarea', array('mode' => 'raw')));
			$this->assertStringContainsString('<b>this</b>', $this->cf->getFieldValue('url'));
			$this->assertStringStartsNotWith('<i class="fa fa-check">', $this->cf->getFieldValue('icon'));
			$this->assertSame(array_keys($values), array_keys($this->cf->getData()));

			$this->cf->loadData(json_encode(array('textarea' => '<img alt=&quot;x onerror=alert(1)&quot; src=&quot;/e107_images/e107_icon_32.png&quot;>', '__trust__' => 1)));
			$this->assertNoMarkupIn($this->cf->getFieldValue('textarea', array('mode' => 'raw')), 'raw rich text');

			$this->cf->loadData(json_encode($values + array('__trust__' => 2)));
			$this->assertSame('<i class="fa fa-check"></i>', $this->cf->getFieldValue('icon'));

			$this->cf->loadData(json_encode(array('textarea' => '<script>&#036;(&quot;#x&quot;).text(&quot;a&#092;nb&quot;);</script>', '__trust__' => 2)));
			$this->assertSame('<script>$("#x").text("a\nb");</script>', $this->cf->getFieldValue('textarea', array('mode' => 'raw')));

			$this->cf->loadData(json_encode($values));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('textarea'));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('url'));
			$this->assertStringStartsNotWith('<i class="fa fa-check">', $this->cf->getFieldValue('icon'));

			$this->cf->loadData(json_encode(array('textarea' => '&lt;b&gt;bold&lt;/b&gt;', '__trust__' => 0)));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('textarea'));
		}

		public function testProcessDataPostStoresValuesThroughToDb()
		{
			$result = $this->cf->processDataPost('page_fields', array(
				'page_title'          => 'Title',
				'page_fields__text'   => 'Text"><svg onload=alert(1)>',
				'page_fields__video'  => 'WcuRPzB4RNc.youtube',
				'page_fields__icon'   => 'fa-check.glyph',
				'page_fields__x"><svg onload=alert(1)>' => 'Value',
				'page_fields__цена'   => '5',
			));

			$this->assertSame('Title', $result['page_title']);
			$this->assertSame('WcuRPzB4RNc.youtube', $result['page_fields']['video']);
			$this->assertSame('fa-check.glyph', $result['page_fields']['icon']);
			$this->assertStringStartsWith('Text', $result['page_fields']['text']);
			$this->assertStringNotContainsString('<svg', $result['page_fields']['text']);
			$this->assertSame(array('text', 'video', 'icon', 'цена', '__trust__'), array_keys($result['page_fields']));
			$this->assertContains($result['page_fields']['__trust__'], array(0, 1, 2));
		}

		/**
		 * @param string $html
		 * @param string $label
		 */
		private function assertNoMarkupIn($html, $label)
		{
			$doc = new DOMDocument();
			$errors = libxml_use_internal_errors(true);
			$doc->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');
			libxml_clear_errors();
			libxml_use_internal_errors($errors);

			foreach($doc->getElementsByTagName('*') as $element)
			{
				$this->assertNotSame('svg', $element->nodeName, $label.': '.$html);

				foreach($element->attributes as $attribute)
				{
					$this->assertStringStartsNotWith('on', $attribute->name, $label.': '.$html);

					$value = html_entity_decode($attribute->value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
					$this->assertStringStartsNotWith('javascript:', strtolower(preg_replace('/[\x00-\x20]/', '', $value)), $label.': '.$html);
				}
			}
		}
	}
