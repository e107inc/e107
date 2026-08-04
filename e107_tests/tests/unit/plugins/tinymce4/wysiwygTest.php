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
	/**
	 * Issue #5792 leg 1: the generated tinymce.init() config must set
	 * pagebreak_separator to e107's own [newpage] marker, as a quoted JS
	 * string. Without it TinyMce's pagebreak button emits its default
	 * <!-- pagebreak --> comment, which e107's pagination never parses.
	 * The value starts with "[", which convertBoolean() would otherwise emit
	 * as a bare JS array literal, so this also guards the serialisation.
	 */
	public function testRenderConfigSetsNewpagePagebreakSeparator()
	{
		$config = $this->tm->renderConfig('mainadmin');

		$this->assertStringContainsString('pagebreak_separator: "[newpage]"', $config,
			'The pagebreak button must insert e107\'s [newpage] marker as a quoted JS string.');
		$this->assertStringNotContainsString('pagebreak_separator: [newpage]', $config,
			'The separator must be a quoted string, not a bare JS array literal.');
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
