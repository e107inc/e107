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
	}
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
