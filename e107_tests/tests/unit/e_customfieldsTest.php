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
		use \Test\PostClasses;

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
		    "__trust__": {"image": 1, "video": 1, "bbarea": 1, "boolean": 1, "checkboxes": 1, "country": 1, "datestamp": 1, "dropdown": 1,
		        "email": 1, "file": 1, "icon": 1, "language": 1, "lanlist": 1, "number": 1, "password": 1, "tags": 1, "textarea": 1, "url": 1,
		        "user": 1, "userclass": 1, "progressbar": 1}
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
		  'user'        => 'e107',
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
		 * none of the markup in it that could run.
		 */
		public function testStoredMarkupInAValueNeverRuns()
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
			foreach(array('x'.$markup, json_encode(array('options' => '&lt;img src=x onerror=alert(1)&gt;', '__trust__' => array('options' => 0)))) as $stored)
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
				$submitted = $this->postedBack($stored['page_fields']);
				$this->assertSame($stored, $this->cf->processDataPost('page_fields', $submitted), 'save '.($i + 1));
			}

			$this->assertSame('0', $stored['page_fields']['number']);
		}

		/**
		 * A value the page form posts back as it showed it is stored again byte for byte, though {@see e_parse::toDB()}
		 * would change it, whether a level is recorded for it or not and whoever saves.
		 */
		public function testAnUntouchedValueIsStoredAgainByteForByte()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig($this->config);
			$values = array('textarea' => 'Lead [p][/p]<b>bold</b>', 'url' => 'https://example.com/[h][/h]cfq300', 'tags' => 'java[p][/p]script:cfq300()');

			foreach(array(array('textarea' => 2, 'url' => 1, 'tags' => 0), null) as $levels)
			{
				$stored = $values + (($levels === null) ? array() : array('__trust__' => $levels));

				foreach(array(e_UC_PUBLIC, e_UC_NOBODY) as $saver)
				{
					$resaved = $this->withPostClasses($saver, $saver, function () use ($stored, $values)
					{
						foreach($values as $value)
						{
							$this->assertNotSame($value, e107::getParser()->toDB($value), 'toDB() keeps '.$value);
						}

						return $this->cf->processDataPost('page_fields', $this->postedBack($stored));
					});

					$this->assertSame($stored, $resaved['page_fields'], 'levels '.json_encode($levels).', saver '.$saver);
				}
			}
		}

		/**
		 * A value is stored again byte for byte, its level kept, on an untouched save however a browser posts it back,
		 * whoever saves.
		 */
		public function testAValueABrowserPostsBackAlteredIsStoredAgainByteForByte()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$config = json_decode($this->config, true);
			$config['email']['writeParms'] = '{"multiple": 1}';
			$config['tags']['writeParms'] = '{"maxItems": "3"}';
			$config['__trust__'] = array('tags' => array('writeParms' => 2));
			$this->cf->loadConfig($config);
			$values = array(
				'url'      => "Col\nour &lt;b onclick=cfq300()&gt;y&lt;/b&gt;",
				'tags'     => "\xC2\xA0&lt;i onclick=cfq300()&gt;x&lt;/i&gt;,b\r\n\0,&lt;i onclick=cfq300()&gt;x&lt;/i&gt;,c,d,e,f,g,h",
				'textarea' => "\nLead &lt;i onclick=cfq300()&gt;x&lt;/i&gt;\nend",
				'email'    => "a@example.com , &lt;b onclick=cfq300()&gt;@example.com",
			);

			foreach(array(array_fill_keys(array_keys($values), 0), array_fill_keys(array_keys($values), 2), null) as $levels)
			{
				$stored = $values + (($levels === null) ? array() : array('__trust__' => $levels));
				$posted = $this->postedBack($stored);

				foreach($values as $key => $value)
				{
					$this->assertNotSame(html_entity_decode($value, ENT_QUOTES), $posted['page_fields__'.$key], 'the browser posts '.$key.' back as shown');
				}

				foreach(array(e_UC_PUBLIC, e_UC_NOBODY) as $saver)
				{
					$resaved = $this->withPostClasses($saver, $saver, function () use ($stored, $posted)
					{
						return $this->cf->loadData(json_encode($stored))->processDataPost('page_fields', $posted);
					});

					$this->assertSame($stored, $resaved['page_fields'], 'levels '.json_encode($levels).', saver '.$saver);
				}
			}
		}

		/**
		 * An untouched value is stored again byte for byte, its level kept, whoever saves, however the page form shows
		 * it and its elements post it back: one in a text input whose name gives the id a tag list's name gives, one in
		 * an input the field's parameters make an email input, and one holding the '+' and '%' {@see e_parse::toForm()}
		 * would rewrite.
		 */
		public function testAValueIsKeptHoweverThePageFormsElementsPostItBack()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig(array(
				'a'  => array('title' => 'A', 'type' => 'text', 'writeParms' => '', 'help' => ''),
				'a-' => array('title' => 'Tags', 'type' => 'tags', 'writeParms' => '', 'help' => ''),
				'b'  => array('title' => 'B', 'type' => 'text', 'writeParms' => '{"type": "email", "multiple": 1}', 'help' => ''),
				'c'  => array('title' => 'C', 'type' => 'text', 'writeParms' => '', 'help' => ''),
			));
			$typed = '&lt;i onclick=cfq300()&gt;x&lt;/i&gt;';

			$stored = $this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function () use ($typed)
			{
				return $this->cf->loadData('')->processDataPost('page_fields', array('page_fields__a' => $typed.','.$typed,
					'page_fields__a-' => 'x', 'page_fields__b' => $typed.' , '.$typed, 'page_fields__c' => '&lt;i onclick=cfq300()&gt;1+1&lt;/i&gt;%3Ci onclick=cfq300()%3E'));
			});
			$posted = $this->postedFromPageForm($stored['page_fields']);

			$this->assertNotSame(html_entity_decode($stored['page_fields']['b'], ENT_QUOTES), $posted['page_fields__b'], 'the browser posts b back as shown');

			foreach(array(e_UC_PUBLIC, e_UC_NOBODY) as $saver)
			{
				$resaved = $this->withPostClasses($saver, $saver, function () use ($stored, $posted)
				{
					return $this->cf->loadData(json_encode($stored['page_fields']))->processDataPost('page_fields', $posted);
				});

				$this->assertSame($stored['page_fields'], $resaved['page_fields'], 'saver '.$saver);
			}
		}

		/**
		 * An untouched value starting with '[html]', which {@see e_parse::toForm()} shows with every entity but those
		 * of its quotes and backslashes as written, is stored again byte for byte with its level, or with none, and
		 * goes on printing as its author may post it after an administrator with script access saves the page.
		 */
		public function testAnUntouchedHtmlValueKeepsItsLevel()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig(array(
				'i0' => array('title' => 'Text icon', 'type' => 'icon', 'writeParms' => '', 'help' => ''),
				'i1' => array('title' => 'HTML icon', 'type' => 'icon', 'writeParms' => '', 'help' => ''),
				'in' => array('title' => 'Legacy icon', 'type' => 'icon', 'writeParms' => '', 'help' => ''),
				't1' => array('title' => 'HTML text', 'type' => 'textarea', 'writeParms' => '', 'help' => ''),
				'r1' => array('title' => 'HTML rich text', 'type' => 'bbarea', 'writeParms' => '', 'help' => ''),
				'f1' => array('title' => 'HTML file', 'type' => 'file', 'writeParms' => '', 'help' => ''),
			));
			$stored = array(
				'i0'        => '[html]x&#039; onerror=&#039;cfq300()&#039; y+.png',
				'i1'        => '[html]x&#039; onerror=&#039;cfq300()&#039; y&amp;.png',
				'in'        => "[html]x' onerror='cfq300()' 50% y.png",
				't1'        => '[html]&lt;b id=cfq300t onclick=cfq300()&gt;t',
				'r1'        => '[html]&lt;b id=cfq300r onclick=cfq300()&gt;r',
				'f1'        => '[html]x&quot; onclick=&quot;cfq300()&quot; &amp;lt;y.zip',
				'__trust__' => array('i0' => 0, 'i1' => 1, 't1' => 1, 'r1' => 1, 'f1' => 1),
			);

			$resaved = $this->withPostClasses(e_UC_PUBLIC, e_UC_PUBLIC, function () use ($stored)
			{
				$posted = $this->postedFromPageForm($stored);

				return $this->cf->loadData(json_encode($stored))->processDataPost('page_fields', $posted);
			});

			$this->assertSame($stored, $resaved['page_fields']);
			$this->cf->loadData(json_encode($resaved['page_fields']));

			foreach(array('i0', 'i1', 'in', 't1', 'r1', 'f1') as $key)
			{
				$this->assertNoMarkupIn((string) $this->cf->getFieldValue($key), $key);
			}
		}

		/**
		 * The widget {@see e_form} puts on a tag list's or a user field's input attaches to that input, though the name
		 * of the field before it gives the id its own name gives.
		 */
		public function testAFieldsWidgetAttachesToItsOwnInput()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig(array(
				'a'  => array('title' => 'A', 'type' => 'text', 'writeParms' => '', 'help' => ''),
				'a-' => array('title' => 'Tags', 'type' => 'tags', 'writeParms' => '', 'help' => ''),
				'u'  => array('title' => 'U', 'type' => 'text', 'writeParms' => '', 'help' => ''),
				'u-' => array('title' => 'User', 'type' => 'user', 'writeParms' => '', 'help' => ''),
			));

			$this->assertSame(array('page_fields__a-' => 'page_fields__a-', 'page_fields__u-' => 'page_fields__u-'),
				$this->widgetTargets($this->renderedPageForm(array('a' => 'x,x', 'u' => 'x,x'))));
		}

		/**
		 * A post that carries the custom field column itself, and none of its fields, stores nothing of what
		 * it carried.
		 */
		public function testAPostedColumnIsNotStoredAsPosted()
		{
			$result = $this->cf->processDataPost('page_fields', array(
				'page_title'  => 'x',
				'page_fields' => array('url' => '<b id="cfq3c" onclick="cfq300()">r</b>', '__trust__' => array('url' => 2)),
			));

			$this->assertSame('x', $result['page_title']);
			$this->assertArrayHasKey('page_fields', $result);
			$this->assertNull($result['page_fields']);
		}

		/**
		 * A level recorded as anything but the integer an administrator's save records is no level: the value prints
		 * as text.
		 */
		public function testALevelNotRecordedAsAnIntegerIsNone()
		{
			foreach(array('"2"', '2.0', 'true', '"1"') as $level)
			{
				$this->cf->loadConfig($this->config)->loadData('{"url": "<b id=\\"cfq3l\\" onclick=\\"cfq300()\\">r</b>", "__trust__": {"url": '.$level.'}}');
				$this->assertNoMarkupIn((string) $this->cf->getFieldValue('url'), 'level '.$level);
			}
		}

		/**
		 * A stored value that does not decode to an array loads as no values, so the page form still opens.
		 */
		public function testValuesThatDoNotDecodeLoadAsNone()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$seen = array();
			$model = $this->make('e_admin_model', array('set' => function ($key, $value = null) use (&$seen) { $seen[$key] = $value; }));
			$ui = $this->make('e_admin_ui', array('getModel' => $model));

			foreach(array('{', 'not json') as $stored)
			{
				$seen = array();
				$this->cf->loadData($stored)->setAdminUIData('page_fields', $ui);

				$this->assertSame(array(), $this->cf->getData(), $stored);
				$this->assertSame(array('page_fields' => null), $seen, $stored);
			}
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
		 * no record of who saved them print as text, but for the text area's, which prints as one saved by an
		 * administrator who may post HTML but not script.
		 */
		public function testAValuePrintsAsItsAuthorMayPostIt()
		{
			$values = array(
				'textarea' => '<p class=&quot;lead&quot;>Lead <b>bold</b></p>',
				'url'      => 'See <b>this</b>',
				'icon'     => '<i class=&quot;fa fa-check&quot;></i>',
			);

			$this->cf->loadConfig($this->config)->loadData(json_encode($values + array('__trust__' => array_fill_keys(array_keys($values), 1))));
			$this->assertStringContainsString('<p class="lead">Lead <b>bold</b></p>', $this->cf->getFieldValue('textarea'));
			$this->assertSame('<p class="lead">Lead <b>bold</b></p>', $this->cf->getFieldValue('textarea', array('mode' => 'raw')));
			$this->assertStringContainsString('<b>this</b>', $this->cf->getFieldValue('url'));
			$this->assertStringStartsNotWith('<i class="fa fa-check">', $this->cf->getFieldValue('icon'));
			$this->assertSame(array_keys($values), array_keys($this->cf->getData()));

			$this->cf->loadData(json_encode(array('textarea' => '<img alt=&quot;x onerror=alert(1)&quot; src=&quot;/e107_images/e107_icon_32.png&quot;>', '__trust__' => array('textarea' => 1))));
			$this->assertNoMarkupIn($this->cf->getFieldValue('textarea', array('mode' => 'raw')), 'raw rich text');

			$this->cf->loadData(json_encode($values + array('__trust__' => array_fill_keys(array_keys($values), 2))));
			$this->assertSame('<i class="fa fa-check"></i>', $this->cf->getFieldValue('icon'));

			$this->cf->loadData(json_encode(array('textarea' => '<script>&#036;(&quot;#x&quot;).text(&quot;a&#092;nb&quot;);</script>', '__trust__' => array('textarea' => 2))));
			$this->assertSame('<script>$("#x").text("a\nb");</script>', $this->cf->getFieldValue('textarea', array('mode' => 'raw')));

			$this->cf->loadData(json_encode($values));
			$this->assertStringContainsString('<b>bold</b>', $this->cf->getFieldValue('textarea'));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('url'));
			$this->assertStringStartsNotWith('<i class="fa fa-check">', $this->cf->getFieldValue('icon'));

			$this->cf->loadData(json_encode(array('textarea' => '&lt;b&gt;bold&lt;/b&gt;', '__trust__' => array('textarea' => 0))));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('textarea'));
		}

		/**
		 * A rich text or text area value with no record of who saved it, stored as it was posted, prints as the same
		 * value prints once {@see e_parse::toDB()} has stored it for an author who may post HTML but not script: its
		 * markup kept and its scripts and event handlers gone, in raw mode too, whatever the current user may post.
		 */
		public function testAValueWithNoLevelPrintsAsAnHtmlAuthorWithoutScriptSavedIt()
		{
			$this->cf->loadConfig(array(
				'rich' => array('title' => 'Rich', 'type' => 'bbarea', 'writeParms' => '', 'help' => ''),
				'text' => array('title' => 'Text', 'type' => 'textarea', 'writeParms' => '', 'help' => ''),
			));
			$formatted = array(
				'rich' => '[html]<p><b id="cfq418b">Sale</b></p>[/html]',
				'text' => 'Say "hi" & <i id="cfq418w">wave</i>, it\'s $5',
			);
			$scripted = array(
				'rich' => '[html]<p><img id="cfq418i" src="x.png" onerror="cfq418()">Sale</p>'
					.'<script>var cfq418 = 1;</script>[/html]',
				'text' => '<b id="cfq418t" onclick="cfq418()">Sale</b><script>var cfq418 = 1;</script>',
			);

			foreach(array($formatted, $scripted) as $values)
			{
				$saved = $this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function () use ($values)
				{
					return array_map(function ($value)
					{
						return e107::getParser()->toDB($value, false, false, false, 1);
					}, $values);
				});
				$saved['__trust__'] = array_fill_keys(array_keys($values), 1);

				foreach(array(e_UC_PUBLIC, e_UC_NOBODY) as $reader)
				{
					$this->withPostClasses($reader, $reader, function () use ($values, $saved, $reader)
					{
						foreach(array(array(), array('mode' => 'raw')) as $parm)
						{
							foreach(array_keys($values) as $key)
							{
								$this->cf->loadData(json_encode($saved));
								$expected = $this->cf->getFieldValue($key, $parm);
								$this->cf->loadData(json_encode($values));
								$label = $key.' '.json_encode($parm).' read by class '.$reader;
								$this->assertSame($expected, $this->cf->getFieldValue($key, $parm), $label);
							}
						}
					});
				}
			}

			$printed = $this->withPostClasses(e_UC_NOBODY, e_UC_NOBODY, function () use ($formatted, $scripted)
			{
				$printed = array();

				foreach(array('formatted' => $formatted, 'scripted' => $scripted) as $name => $values)
				{
					$this->cf->loadData(json_encode($values));

					foreach(array('normal' => array(), 'raw' => array('mode' => 'raw')) as $mode => $parm)
					{
						$printed[$name][$mode] = $this->cf->getFieldValue('rich', $parm)
							.$this->cf->getFieldValue('text', $parm);
					}
				}

				return $printed;
			});

			$xpath = new DOMXPath($this->htmlDocument($printed['formatted']['normal']));
			$this->assertSame(2, $xpath->query('//p/b[@id="cfq418b"] | //i[@id="cfq418w"]')->length, 'formatting kept');

			foreach($printed['scripted'] as $mode => $html)
			{
				$xpath = new DOMXPath($this->htmlDocument($html));
				$label = $mode.': '.$html;

				$this->assertSame(2, $xpath->query('//img[@src="x.png"] | //b[@id="cfq418t"]')->length, $label);
				$this->assertSame(0, $xpath->query('//script')->length, $label);
				$this->assertNoMarkupIn($html, $label);
				$this->assertStringNotContainsString('cfq418', preg_replace('/ id="cfq418[it]"/', '', $html), $label);
			}
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
			$this->assertSame(array('text', 'video', 'icon', 'цена'), array_keys($result['page_fields']['__trust__']));
			$this->assertContains($result['page_fields']['__trust__']['text'], array(0, 1, 2));
		}

		/**
		 * Saving a page again keeps every value the administrator left as the form showed it exactly as its author
		 * stored it, so one who may post only text does not strip the markup and scripts of one with script access; the
		 * value they change is stored as they may post it.
		 */
		public function testALowerAdministratorsSaveKeepsTheValuesTheyLeftAlone()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig($this->config);

			$stored = $this->withPostClasses(e_UC_PUBLIC, e_UC_PUBLIC, function ()
			{
				return $this->cf->loadData('')->processDataPost('page_fields', array(
					'page_fields__textarea' => '<p onclick="cfq300()">Lead</p><script>var cfq300 = 1;</script>',
					'page_fields__icon'     => '<i class="fa fa-check"></i>',
					'page_fields__url'      => 'See <b>this</b>',
				));
			});

			$posted = $this->postedBack($stored['page_fields']);
			$posted['page_fields__url'] = 'Now <b>that</b>';

			$resaved = $this->withPostClasses(e_UC_NOBODY, e_UC_NOBODY, function () use ($stored, $posted)
			{
				return $this->cf->loadData(json_encode($stored['page_fields']))->processDataPost('page_fields', $posted);
			});

			$this->assertSame($stored['page_fields']['textarea'], $resaved['page_fields']['textarea']);
			$this->assertSame($stored['page_fields']['icon'], $resaved['page_fields']['icon']);

			$this->cf->loadData(json_encode($resaved['page_fields']));
			$this->assertStringContainsString('<p onclick="cfq300()">Lead</p><script>var cfq300 = 1;</script>', $this->cf->getFieldValue('textarea', array('mode' => 'raw')));
			$this->assertSame('<i class="fa fa-check"></i>', $this->cf->getFieldValue('icon'));
			$this->assertStringContainsString('Now', $this->cf->getFieldValue('url'));
			$this->assertStringNotContainsString('<b>', $this->cf->getFieldValue('url'));
		}

		/**
		 * Text an author who may post HTML, or only text, wrote with entities in it keeps printing as text after an
		 * administrator with script access saves the page without changing it, though the form shows those
		 * entities decoded.
		 */
		public function testAHigherAdministratorsSaveDoesNotPromoteTextTheyLeftAlone()
		{
			require_once(e_HANDLER.'admin_ui.php');
			$this->cf->loadConfig($this->config);

			foreach(array(array(e_UC_PUBLIC, '&lt;i onclick=cfq300()&gt;x&lt;/i&gt;'), array(e_UC_NOBODY, '&#60;i onclick=cfq300()&#62;x&#60;/i&#62;')) as $author)
			{
				list($html, $typed) = $author;

				$stored = $this->withPostClasses($html, e_UC_NOBODY, function () use ($typed)
				{
					return $this->cf->loadData('')->processDataPost('page_fields', array('page_fields__textarea' => $typed, 'page_fields__icon' => $typed,
						'page_fields__tags' => '__proto__,'.$typed.','.$typed));
				});

				$posted = $this->postedBack($stored['page_fields']);

				$resaved = $this->withPostClasses(e_UC_PUBLIC, e_UC_PUBLIC, function () use ($stored, $posted)
				{
					return $this->cf->loadData(json_encode($stored['page_fields']))->processDataPost('page_fields', $posted);
				});

				$this->cf->loadData(json_encode($resaved['page_fields']));

				foreach(array('textarea', 'icon', 'tags') as $key)
				{
					$this->assertNoMarkupIn((string) $this->cf->getFieldValue($key), $key.' '.$typed);
					$this->assertNoMarkupIn((string) $this->cf->getFieldValue($key, array('mode' => 'raw')), $key.' raw '.$typed);
				}
			}
		}

		/**
		 * @param array $stored a page's custom field values as {@see e_customfields::processDataPost()} stored them
		 * @return array what the page form posts back for them untouched: each value it is given, as a browser reads it
		 *               once decoded and posts it back from the field's input or text area
		 */
		private function postedBack(array $stored)
		{
			$config = $this->cf->getConfig();
			$posted = array();

			foreach($this->formValues($stored) as $name => $value)
			{
				$key = (string) substr($name, strlen('page_fields__'));
				$type = isset($config[$key]['type']) ? $config[$key]['type'] : 'text';
				$parms = isset($config[$key]['writeParms']) ? json_decode($config[$key]['writeParms'], true) : null;
				$limit = !empty($parms['maxItems']) ? (int) $parms['maxItems'] : 7;
				$posted[$name] = is_string($value) ? $this->asBrowserPosts(html_entity_decode($value, ENT_QUOTES), $type, $limit) : $value;
			}

			return $posted;
		}

		/**
		 * @param string $value a value as a form element shows it
		 * @param string $type the field type whose element shows it
		 * @param int $limit how many tags a tag widget keeps
		 * @return string the value as a browser posts it back from that element, a tag list as the tag widget
		 *                rewrites it
		 */
		private function asBrowserPosts($value, $type, $limit = 7)
		{
			$value = str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", "\xEF\xBF\xBD"), $value);

			if($type === 'textarea' || $type === 'bbarea')
			{
				return str_replace("\n", "\r\n", preg_replace('/^\n/', '', $value));
			}

			$value = str_replace("\n", '', $value);

			if($type === 'tags')
			{
				$value = preg_replace('/^[\s\x{a0}\x{feff}]+|[\s\x{a0}\x{feff}]+$/u', '', $value);

				$tags = array_diff(explode(',', $value), array('__proto__'));

				return ($value === '') ? '' : implode(',', array_slice(array_values(array_unique($tags)), 0, $limit));
			}

			return ($type === 'email') ? implode(',', array_map(function ($address) { return trim($address, " \t\f"); }, explode(',', $value))) : $value;
		}

		/**
		 * @param array $stored a page's custom field values as {@see e_customfields::processDataPost()} stored them
		 * @return array the values {@see e_customfields::setAdminUIData()} gives the page form's model for them, keyed
		 *               by name
		 */
		private function formValues(array $stored)
		{
			$seen = array();
			$model = $this->make('e_admin_model', array('set' => function ($key, $value = null) use (&$seen) { $seen[$key] = $value; }));
			$ui = $this->make('e_admin_ui', array('getModel' => $model));
			$this->cf->loadData(json_encode($stored))->setAdminUIData('page_fields', $ui);
			unset($seen['page_fields']);

			return $seen;
		}

		/**
		 * @param array $stored a page's custom field values as {@see e_customfields::processDataPost()} stored them
		 * @return array the page form's custom fields for the loaded configuration ('fields'), and a {@see DOMXPath}
		 *               over the elements {@see e_form} renders for them and $stored ('xpath')
		 */
		private function renderedPageForm(array $stored)
		{
			$fields = array();
			$ui = $this->make('e_admin_ui', array(
				'addTab'       => function () {},
				'setFieldAttr' => function ($attributes) use (&$fields) { $fields = $attributes; },
			));
			$this->cf->setAdminUIConfig('page_fields', $ui);
			$values = $this->formValues($stored);
			$html = '';

			foreach($fields as $name => $field)
			{
				$html .= '<div>'.e107::getForm()->renderElement($name, e107::getParser()->toForm(isset($values[$name]) ? $values[$name] : null), $field).'</div>';
			}

			return array('fields' => $fields, 'xpath' => new DOMXPath($this->htmlDocument($html)));
		}

		/**
		 * @param array $form a page form as {@see e_customfieldsTest::renderedPageForm()} gives it
		 * @return array for each tag list and user field, keyed by its name, the name of the element the widget
		 *               {@see e_form} puts on its input attaches to: the first element with that input's id
		 */
		private function widgetTargets(array $form)
		{
			$targets = array();

			foreach($form['fields'] as $name => $field)
			{
				if($field['type'] === 'tags' || $field['type'] === 'user')
				{
					$input = $form['xpath']->query('//input[@name="'.$name.'"]')->item(0);
					$targets[$name] = $form['xpath']->query('//*[@id="'.$input->getAttribute('id').'"]')->item(0)->getAttribute('name');
				}
			}

			return $targets;
		}

		/**
		 * @param array $stored a page's custom field values as {@see e_customfields::processDataPost()} stored them
		 * @return array what the page form posts back for them untouched, read from the elements {@see e_form} renders
		 *               for the loaded configuration, each as a browser posts it from that element, the element a tag
		 *               widget attaches to as the widget rewrites it
		 */
		private function postedFromPageForm(array $stored)
		{
			$form = $this->renderedPageForm($stored);
			$tagged = array_flip(array_intersect_key($this->widgetTargets($form), array_filter($form['fields'], function ($field) { return $field['type'] === 'tags'; })));
			$posted = array();

			foreach($form['xpath']->query('//input[@name]|//textarea[@name]') as $element)
			{
				$name = $element->getAttribute('name');
				$textarea = ($element->nodeName === 'textarea');
				$email = ($element->getAttribute('type') === 'email');
				$value = $this->asBrowserPosts($textarea ? $element->textContent : $element->getAttribute('value'),
					$textarea ? 'textarea' : (($email && $element->hasAttribute('multiple')) ? 'email' : 'text'));
				$value = ($email && !$element->hasAttribute('multiple')) ? trim($value, " \t\n\f\r") : $value;
				$posted[$name] = isset($tagged[$name]) ? $this->asBrowserPosts($value, 'tags') : $value;
			}

			return $posted;
		}

		/**
		 * @param string $html
		 * @param string $label
		 */
		private function assertNoMarkupIn($html, $label)
		{
			foreach($this->htmlDocument($html)->getElementsByTagName('*') as $element)
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

		/**
		 * @param string $html markup for a page's body
		 * @return DOMDocument the page as an HTML parser reads it
		 */
		private function htmlDocument($html)
		{
			$doc = new DOMDocument();
			$errors = libxml_use_internal_errors(true);
			$doc->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>');
			libxml_clear_errors();
			libxml_use_internal_errors($errors);

			return $doc;
		}
	}
