<?php


class wysiwygTest extends \Codeception\Test\Unit
{

	/** @var wysiwyg */
	protected $tm;

	protected function _before()
	{

		require_once(e_PLUGIN . "tinymce4/wysiwyg_class.php");
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

		$tests = array(
			'bootstrap3' => array(
				0 => '/e107_web/lib/bootstrap/3/css/bootstrap.min.css',
				1 => '/e107_web/lib/font-awesome/6/css/all.min.css',
				2 => '/e107_web/lib/font-awesome/6/css/v4-shims.min.css',
				3 => '/e107_web/lib/animate.css/animate.min.css',
				4 => '/e107_plugins/tinymce4/editor.css',
			),
			'voux'  => array (
				0 => '/e107_web/lib/bootstrap/3/css/bootstrap.min.css',
				1 => '/e107_web/lib/font-awesome/4.7.0/css/font-awesome.min.css',
				2 => '/e107_web/lib/animate.css/animate.min.css',
				3 => '/e107_plugins/tinymce4/editor.css',
			)


		);


		foreach($tests as $themedir => $expected)
		{
			$result = $this->tm->getEditorCSS($themedir);

			if(empty($expected))
			{
				var_export($result);
				continue;
			}

			$this->assertSame($expected, $result);
		}


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
