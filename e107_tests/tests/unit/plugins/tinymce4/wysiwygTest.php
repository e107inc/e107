<?php


	class wysiwygTest extends \Codeception\Test\Unit
	{

		/** @var wysiwyg */
		protected $tm;

		protected function _before()
		{
			require_once(e_PLUGIN."tinymce4/wysiwyg_class.php");
			try
			{
				$this->tm = $this->make('wysiwyg');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}
/*
		public function testGetExternalPlugins()
		{

		}

		public function testConvertBoolean()
		{

		}

		public function test__construct()
		{

		}*/

		public function testGetEditorCSS()
		{
			$expected = array (
			  0 => '/e107_web/lib/bootstrap/3/css/bootstrap.min.css',
			  1 => '/e107_web/lib/font-awesome/5/css/all.min.css',
			  2 => '/e107_web/lib/font-awesome/5/css/v4-shims.min.css',
			  3 => '/e107_web/lib/animate.css/animate.min.css',
			  4 => '/e107_plugins/tinymce4/editor.css',
			);

			$result = $this->tm->getEditorCSS();
			$this->assertSame($expected, $result);

		}
/*
		public function testGetTemplates()
		{

		}

		public function testFilter_plugins()
		{

		}

		public function testRenderConfig()
		{

		}

		public function testTinymce_lang()
		{

		}

		public function testGetConfig()
		{

		}
*/



	}
