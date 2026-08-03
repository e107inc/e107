<?php


class e_pluginbuilderTest extends \Codeception\Test\Unit
{

	/** @var e_pluginbuilder */
	protected $pb;
	
	protected $posted;

	protected function _before()
	{
		require_once(e_HANDLER."e_pluginbuilder_class.php");
		try
		{
			$this->pb = $this->make('e_pluginbuilder');
		}

		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
		
		$this->posted = array (
		  'xml' => 
		  array (
		    'main-name' => 'Test',
		    'main-lang' => '',
		    'main-version' => '1.0',
		    'main-date' => '2022-12-12',
		    'main-compatibility' => '2.0',
		    'author-name' => 'admin',
		    'author-url' => 'https://e107.org',
		    'summary-summary' => 'Test Plugin Creation',
		    'description-description' => 'Example of a plugin description',
		    'keywords-one' => 'generic',
		    'keywords-two' => 'test',
		    'keywords-three' => 'unit',
		    'category-category' => 'content',
		    'copyright-copyright' => 'copyright info',
		  ),
		  'example_ui' => 
		  array (
		    'pluginName' => 'ExamplePlugin',
		    'table' => 'example',
		    'mode' => 'main',
		    'fields' => 
		    array (
		      'checkboxes' => 
		      array (
		        'title' => '',
		        'type' => '',
		        'data' => '',
		        'width' => '5%',
		        'thclass' => 'center',
		        'forced' => 'value',
		        'class' => 'center',
		        'toggle' => 'e-multiselect',
		        'fieldpref' => 'value',
		      ),
		      'example_id' => 
		      array (
		        'title' => 'LAN_ID',
		        'type'  => 'number',
		        'data' => 'int',
		        'width' => '5%',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_icon' => 
		      array (
		        'title' => 'LAN_ICON',
		        'type' => 'icon',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_type' => 
		      array (
		        'title' => 'LAN_TYPE',
		        'type' => 'dropdown',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'batch' => '1',
		        'filter' => '1',
		        'inline' => '1',
		        'fieldpref' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_name' => 
		      array (
		        'title' => 'LAN_TITLE',
		        'type' => 'text',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'inline' => '1',
		        'fieldpref' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_folder' => 
		      array (
		        'title' => 'Folder',
		        'type' => 'method',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_version' => 
		      array (
		        'title' => 'Version',
		        'type' => 'text',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'readonly' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_author' => 
		      array (
		        'title' => 'LAN_AUTHOR',
		        'type' => 'text',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_authorURL' => 
		      array (
		        'title' => 'AuthorURL',
		        'type' => 'text',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_date' => 
		      array (
		        'title' => 'LAN_DATESTAMP',
		        'type' => 'datestamp',
		        'data' => 'int',
		        'width' => 'auto',
		        'filter' => '1',
		        'fieldpref' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_compatibility' => 
		      array (
		        'title' => 'Compatibility',
		        'type' => 'text',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_url' => 
		      array (
		        'title' => 'LAN_URL',
		        'type' => 'url',
		        'data' => 'safestr',
		        'width' => 'auto',
		        'inline' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_media' => 
		      array (
		        'title' => 'Media',
		        'type' => 'image',
		        'data' => 'str',
		        'width' => 'auto',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'example_class' => 
		      array (
		        'title' => 'LAN_USERCLASS',
		        'type' => 'userclass',
		        'data' => 'int',
		        'width' => 'auto',
		        'batch' => '1',
		        'filter' => '1',
		        'inline' => '1',
		        'fieldpref' => '1',
		        'help' => '',
		        'readParms' => '',
		        'writeParms' => '',
		        'class' => 'left',
		        'thclass' => 'left',
		      ),
		      'options' => 
		      array (
		        'title' => 'LAN_OPTIONS',
		        'type' => '',
		        'data' => '',
		        'width' => '10%',
		        'thclass' => 'center last',
		        'class' => 'center last',
		        'forced' => 'value',
		        'fieldpref' => 'value',
		      ),
		    ),
		    'pid' => 'example_id',
		  ),
		  'pluginPrefs' => 
		  array (
		    0 => 
		    array (
		      'index' => 'active',
		      'value' => '1',
		      'type' => 'boolean',
		      'help' => 'A help tip',
		    ),
		    1 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    2 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    3 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    4 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    5 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    6 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    7 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    8 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		    9 => 
		    array (
		      'index' => '',
		      'value' => '',
		      'type' => '',
		      'help' => '',
		    ),
		  ),
		  'newplugin' => 'example',
		  'step' => '4',
		);	

	}
/*
	public function testSpecial()
	{

	}

	public function testGuess()
	{

	}

	public function testForm()
	{

	}

	public function testCreateXml()
	{

	}*/

	public function testBuildAdminUI()
	{
		$result = $this->pb->buildAdminUI($this->posted, 'pluginfolder', 'PluginTitle');
		$expected = "'example_id'              => array ( 'title' => LAN_ID, 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',)";
		$this->assertStringContainsString($expected, $result);

	//	file_put_contents(__DIR__."/testBuild.php", "<?php\n\n".$result);

	}

	/**
	 * What actually keeps an unauthenticated visitor out of a generated admin
	 * page is the getperms('P') gate at its head, which runs before the
	 * dispatcher subclass is even defined.
	 *
	 * getperms() answers false whenever ADMIN is undefined (class2.php), so a
	 * guest is redirected before any of the generated code below runs. Nothing
	 * about the position of auth.php changes that: it is included after the
	 * dispatcher is constructed on every core admin page and every bundled
	 * plugin, because e107_admin/auth.php branches on e_ADMIN_UI, which is
	 * defined by e_admin_dispatcher::__construct(). Emitting auth.php first
	 * sends auth.php down its legacy branch, so header.php is never included
	 * and the generated page renders with no admin chrome at all.
	 *
	 * @see e107_admin/auth.php  the e_ADMIN_UI branch
	 * @see e107_handlers/admin_ui.php  e_admin_dispatcher::__construct()
	 */
	public function testBuildAdminUIGuardsTheGeneratedPageAndKeepsTheCoreOrdering()
	{
		$result = $this->pb->buildAdminUI($this->posted, 'pluginfolder', 'PluginTitle');

		$class2 = strpos($result, "require_once('../../class2.php');");
		$gate = strpos($result, "getperms('P')");
		$definition = strpos($result, 'class pluginfolder_adminArea extends e_admin_dispatcher');

		$this->assertNotFalse($class2, 'The generated admin page never bootstraps e107.');
		$this->assertNotFalse($gate, "The generated admin page carries no getperms('P') gate, which is "
			.'the only thing that refuses a caller who is not an administrator of this plugin.');
		$this->assertNotFalse($definition, 'The generated admin page never defines its dispatcher.');

		$this->assertLessThan($gate, $class2, 'The generated admin page tests a permission before it '
			.'has bootstrapped the application that defines getperms().');
		$this->assertLessThan($definition, $gate, "The generated admin page defines its dispatcher "
			."before the getperms('P') gate, so the gate no longer covers the code below it.");

		$construct = strpos($result, 'new pluginfolder_adminArea();');
		$auth = strpos($result, 'require_once(e_ADMIN."auth.php");');
		$run = strpos($result, 'e107::getAdminUI()->runPage();');
		$footer = strpos($result, 'require_once(e_ADMIN."footer.php");');

		$this->assertNotFalse($construct, 'The generated admin page never constructs its dispatcher.');
		$this->assertNotFalse($auth, 'The generated admin page never requires auth.php.');
		$this->assertNotFalse($run, 'The generated admin page never runs its page.');
		$this->assertNotFalse($footer, 'The generated admin page never requires footer.php.');

		$this->assertTrue($construct < $auth && $auth < $run && $run < $footer,
			'The generated admin page must emit the tail every core admin page emits: construct the '
			.'dispatcher, then auth.php, then runPage(), then footer.php. auth.php takes its '
			.'header.php branch only when e_ADMIN_UI is already defined, and the dispatcher '
			."constructor is what defines it, so any other order renders a page with no admin "
			."header. Offsets: construct=$construct auth=$auth run=$run footer=$footer. Tail:\n"
			.substr($result, $construct - 40, 260));
	}

	/**
	 * Positive control for the tail above: the generated page must still be
	 * a working admin page, and must still parse.
	 *
	 * Moving a require_once past a statement is the kind of edit that is easy
	 * to make and easy to make wrong, and nothing else in this suite executes
	 * the wizard's output.
	 */
	public function testBuildAdminUIStillEmitsARunnableAdminPage()
	{
		$result = $this->pb->buildAdminUI($this->posted, 'pluginfolder', 'PluginTitle');

		$this->assertStringContainsString("require_once('../../class2.php');", $result);
		$this->assertStringContainsString("getperms('P')", $result);
		$this->assertStringContainsString('class pluginfolder_adminArea extends e_admin_dispatcher', $result);
		$this->assertStringContainsString('new pluginfolder_adminArea();', $result);
		$this->assertStringContainsString('e107::getAdminUI()->runPage();', $result);
		$this->assertStringContainsString('footer.php', $result);

		$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

		if(in_array('exec', $disabled, true))
		{
			$this->markTestSkipped('exec() is disabled here, so the generated code cannot be linted.');
		}

		$file = tempnam(sys_get_temp_dir(), 'e107pb');
		file_put_contents($file, "<?php\n".$result);

		$output = array();
		$status = 0;
		exec('php -l '.escapeshellarg($file).' 2>&1', $output, $status);
		unlink($file);

		$this->assertSame(0, $status,
			"The generated admin page is not valid PHP:\n".implode("\n", $output));
	}

/*	public function isValidCode($code)
	{
		$temp_file = tempnam(sys_get_temp_dir(), 'PHP');
		file_put_contents($temp_file, $code);
		$output = shell_exec("php -l $temp_file");
		unlink($temp_file);

		if (strpos($output, 'No syntax errors detected') === false)
		{
			return $output;
		   $this->fail("The code is not valid. Error message: $output");
		}

		return true;

	}*/
/*
	public function testRun()
	{

	}

	public function testPluginXml()
	{

	}

	public function testXmlInput()
	{

	}

	public function testStep4()
	{

	}

	public function testStep3()
	{

	}

	public function testStep1()
	{

	}

	public function testEnterMysql()
	{

	}

	public function testFieldType()
	{

	}

	public function testFieldData()
	{

	}

	public function testPrefs()
	{

	}*/


}
