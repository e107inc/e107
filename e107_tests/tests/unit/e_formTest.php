<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_formTest extends \Codeception\Test\Unit
{
	/** @var e_form */
	protected $_frm;

	protected $_id = 567; // simulated record number.


	// admin_ui $fields format..
	/**
	 * Any set to inline=false are not designed to have inline support at all.
	 * @var array
	 */
	protected $_fields = array(

		'text_001' => array('title'=> "Text 001",	'type' => 'text', 'writeParms'=>array('size'=>'xlarge')),

		// 'text_002' etc..  add other configurations with type='text' in here.

		'number_001'    => array('title'=> "Number 001",	'type' => 'number', 'writeParms'=>array('min'=>0)),
		//	'number_002'    => array('title'=> "Number 002",	'type' => 'number', 'inline'=>true, 'writeParms'=>array('min'=>0)),

		'bool_001'      => array('title'=> "Bool 001",	'type' => 'bool', 'writeParms'=>array('size'=>'xlarge')),
	//	'bool_002'      => array('title'=> "Bool 002",	'type' => 'bool', 'inline'=>true, 'readParms' => array ('enabled'=>'TÉMA', 'disabled'=>'ČLÁNOK'), 'writeParms' => array ('enabled'=>'TÉMA', 'disabled'=>'ČLÁNOK'), ),

		'dropdown_001'  => array('title'=>'Dropdown 001', 'type'=>'dropdown', 'tab'=>1, 'writeParms' => array('optArray'=>array('opt_value_1'=>'Label 1', 'opt_value_2'=>'Label 2')) ),
		'dropdown_002' => array(
			'title'      => 'Dropdown 002',
			'type'       => 'dropdown',
			'width'      => 'auto',
			'readonly'   => false,
			'filter'     => true,
			'thclass'    => 'center',
			'class'      => 'center',
			'writeParms' => array(
				'empty' => 0,
				'optArray' => array(
					0 => "Option 0",
					1 => "Option 1",
					2 => "Option 2"
				),
			),
			'readParms'  => array(
				'optArray' => array(
					0 => "Option 0",
					1 => "Option 1",
					2 => "Option 2"
				),
			),
			'tab'        => 0,
		),

		'textarea_001'      => array('title'=> "Textarea 001",	'type' => 'textarea', 'writeParms'=>array('size'=>'xlarge','rows'=> 5)),

		'layout_001'        => array ( 'title' => 'Layouts 001', 'type' => 'layouts',  'writeParms' => 'plugin=news&id=news_view&merge=1' ), // 'news', 'news_view', 'front'
		'layout_002'        => array ( 'title' => 'Layouts 002', 'type' => 'layouts', 'writeParms'=>array('plugin'=>'news', 'id'=>'news_view', 'area'=> 'front', 'merge'=>false)), // 'news', 'news_view', 'front'

		'image_001' 	    => array('title'=>"Image 001",	'type' => 'image', 	'inline'=>false, 'data' => 'str', 'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60&legacyPath={e_FILE}downloadimages', 'readonly'=>TRUE,	'batch' => FALSE, 'filter'=>FALSE),
		'image_002' 	    => array('title'=>"Image 002",	'type' => 'image', 	'inline'=>false, 'data' => 'str', 'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60&legacyPath={e_FILE}downloadimages', 'readonly'=>TRUE,	'batch' => FALSE, 'filter'=>FALSE),

		'checkboxes_001'       => array('title'=>'Checkboxes', 'type'=>'checkboxes', 'writeParms'=>array('optArray'=>array(1=>'Check Opt 1', 2=>'Check Opt 2', 3=>'Check Opt 3', 4=>'<img src="images/foo.jpg" />'))),
		'country_001'       => array('title'=>'Country',    'type'=>'country'),
		'country_002'       => array('title'=>'Country',    'type'=>'country'),
		'ip_001'            => array('title'=>'IP',         'type'=>'ip',           'inline'=>false),
		'templates_001'     => array('title'=>'Templates',  'type'=>'templates', 'writeParms'=>array('plugin'=>'forum')),
		'radio_001'         => array('title'=>'Radio',      'type'=>'radio', 'writeParms'=>array('optArray'=>array(1=>'Radio Opt 1', 2=>'Radio Opt 2', 3=>'Radio Opt 3'))),
		'tags_001'          => array('title'=>'Tags',       'type'=>'tags'),
		'bbarea_001'        => array('title'=>'BBarea',     'type'=>'bbarea',       'inline'=>false),
		'icon_001'          => array('title'=>'Icon',       'type'=>'icon',         'inline'=>false),
		//	'media_001'         => array('title'=>'Media',      'type'=>'media',        'inline'=>false),
		//	'file_001'          => array('title'=>'File',       'type'=>'file',         'inline'=>false), //FIXME
		//		'files_001'         => array('title'=>'File',       'type'=>'files',        'inline'=>false), //FIXME
		'datestamp_001'     => array('title'=>'Datestamp',  'type'=>'datestamp',    'inline'=>false),
		'date_001'          => array('title'=>'Date',       'type'=>'date'),
		'userclass_001'     => array('title'=>'Userclass',   'type'=>'userclass'),
		'userclasses_001'   => array('title'=>'Userclasses', 'type'=>'userclasses'),
		'user_001'          => array('title'=>'User',       'type'=>'user'),
		'url_001'           => array('title'=>'URL',        'type'=>'url',          'inline'=>false),
		'email_001'         => array('title'=>'Email',      'type'=>'email',        'inline'=>false),
		'hidden_001'        => array('title'=>'Hidden',     'type'=>'hidden',       'inline'=>false),
		//	'method_001'        => array('title'=>'Method' ,    'type'=>'method',       'inline'=>false),
		'language_001'      => array('title'=>'Language' ,  'type'=>'language'),
		'userclass_002'     => array('title'=>'Userclass',   'type'=>'userclass', 'writeParms'=>array('default'=>255 /* e_UC_NOBODY*/)),
		//	'lanlist_001'       => array('title'=>'Lanlist' ,   'type'=>'lanlist',      'inline'=>false),



	);

	// simulated database/form values.
	protected $_values = array(
		'text_001'          => 'some text',

		'number_001'        => 555,
		'number_002'        => 444,

		'bool_001'          => 1,
		'bool_002'          => 1,

		'dropdown_001'      => 'opt_value_2',
		'dropdown_002'      => '1,2',

		'textarea_001'      => "the quick brown fox jumps over the lazy dog",

		'layout_001'        => 'default',
		'layout_002'        => 'default',

		'image_001'         => '{e_THEME}bootstrap3/images/e107_adminlogo.png',
		'image_002'         => 'butterfly.jpg',

		'checkboxes_001'       => '2,3',
		'country_001'       => 'au',
		'country_002'       => '',
		'ip_001'            => '::1',
		'templates_001'     => 'mytemplate',
		'radio_001'         => 2,
		'tags_001'          => 'keyword1,keyword2,keyword3',
		'bbarea_001'        => '[html]<b>bold</b>[/html]',
		'icon_001'          => '{e_IMAGE}e107_icon_32.png',
		//	'media_001'         => '', // TODO - saves as json format.
		'file_001'          => '{e_MEDIA_FILE}test.zip',
		//	'files_001'         => '{e_MEDIA_FILE}test.zip',
		'datestamp_001'     => 1454367600,
		'date_001'          => '2018-08-23',
		'userclass_001'     => 0,
		'userclasses_001'   => '0,1',
		'user_001'          => 1,
		'url_001'           => 'https://e107.org',
		'email_001'         => 'me@email.com',
		'hidden_001'        => 'hidden-value',
		'method_001'        => 'custom-value',
		'language_001'      => 'fr',
		'userclass_002'     => '',
		//		'lanlist_001'       => 'German',
	);




	protected function _before()
	{
		try
		{
			$this->_frm = $this->make('e_form');
			$this->_frm->__construct();
		}
		catch (Exception $e)
		{
			$this->assertTrue(false, "Couldn't load e_form object");
		}

		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
		include_once(e_CORE."templates/admin_icons_template.php");
		include_once(e_PLUGIN.'forum/forum_class.php');
		include_once(e_PLUGIN.'forum/templates/forum_icons_template.php');

		$legacyDir = APP_PATH."/e107_files/downloadimages/";
		$legacyFile = APP_PATH."/e107_files/downloadimages/butterfly.jpg";

		if(!is_dir($legacyDir))
		{
			mkdir($legacyDir, 0775, true);
		}

		if(!file_exists($legacyFile))
		{
			copy(APP_PATH."/e107_plugins/gallery/images/butterfly.jpg", $legacyFile);
		}

		if(!file_exists($legacyFile))
		{
			$this->assertTrue(false,"Couldn't copy legacy image 'butterfly.jpg' to e107_files folder");
		}

	}

	protected function _after()
	{
		unlink(APP_PATH."/e107_files/downloadimages/butterfly.jpg");
	}


	/*
			public function testAddWarning()
			{

			}

			public function testOpen()
			{

			}

			public function testClose()
			{

			}

			public function testCountry()
			{

			}
*/
			public function testGetCountry()
			{

				$tests = array(
					array('value'=>'', 'expected'=>''),
					array('value'=>'au', 'expected'=>'Australia')
				);

				foreach($tests as $t)
				{
					$actual = $this->_frm->getCountry($t['value']);
					$this->assertEquals($t['expected'], $actual);
				}

				// return array.
				$actual = $this->_frm->getCountry();
				$this->assertArrayHasKey('au',$actual);



			}
/*
			public function testGetRequiredString()
			{

			}

			public function testSetRequiredString()
			{

			}

			public function testTags()
			{

			}

			public function testTabs()
			{

			}

			public function testCarousel()
			{

			}

			public function testUrl()
			{

			}

			public function testText()
			{

			}

			public function testNumber()
			{

			}

			public function testEmail()
			{

			}

			public function testIconpreview()
			{

			}

			public function testIconpicker()
			{

			}

			public function testAvatarpicker()
			{

			}

			public function testImagepicker()
			{

			}

			public function testFilepicker()
			{

			}*/

	public function testDatepicker()
	{

		date_default_timezone_set('UTC');
		$time = strtotime('January 1st, 2018 1am');
		$actual = $this->_frm->datepicker('date_field',$time,'type=datetime&format=MM, dd, yyyy hh:ii');
		$expected = "<input class='tbox e-datetime input-xlarge form-control' type='text' size='40' id='e-datepicker-date-field' value='January, 01, 2018 01:00' data-date-unix ='true' data-date-format='MM, dd, yyyy hh:ii' data-date-ampm='false' data-date-language='en' data-date-firstday='0'     /><input type='hidden' name='date_field' id='date-field' value='1514768400' />";

		$this->assertEquals($expected, $actual);

		// test timezone change...
		date_default_timezone_set('America/Los_Angeles');
		$actual = $this->_frm->datepicker('date_field',$time,'type=datetime&format=MM, dd, yyyy hh:ii');
		$expected = "<input class='tbox e-datetime input-xlarge form-control' type='text' size='40' id='e-datepicker-date-field' value='December, 31, 2017 17:00' data-date-unix ='true' data-date-format='MM, dd, yyyy hh:ii' data-date-ampm='false' data-date-language='en' data-date-firstday='0'     /><input type='hidden' name='date_field' id='date-field' value='1514768400' />";

		$this->assertEquals($expected, $actual);
	}
	/*
			public function testUserlist()
			{

			}

			public function testUserpicker()
			{

			}

			public function testRate()
			{

			}

			public function testLike()
			{

			}

			public function testFile()
			{

			}

			public function testUpload()
			{

			}

			public function testPassword()
			{

			}

			public function testPagination()
			{

			}
*/
			public function testProgressBar()
			{
				$tests = array(
					0   => array('value' => '10/20',    'expected' => 'width: 50%'),
					1   => array('value' => '4/5',      'expected' => 'width: 80%'),
					2   => array('value' => '150/300',  'expected' => 'width: 50%'),
					3   => array('value' => '30%',      'expected' => 'width: 30%'),
					4   => array('value' => '30.4%',    'expected' => 'width: 30%'),
					5   => array('value' => '30.5%',    'expected' => 'width: 31%'),
				);

				foreach($tests as $var)
				{
					$result = $this->_frm->progressBar('progress', $var['value']);
					$this->assertStringContainsString($var['expected'],$result);
				}

			}
/*
			public function testTextarea()
			{

			}

			public function testBbarea()
			{

			}
*/
			public function testCheckbox()
			{

				$result = $this->_frm->checkbox('name', 2, 2);
				$expected = "<input type='checkbox' name='name' value='2' id='name-2' class='form-check-input' checked='checked' />";
				$this->assertEquals($expected,$result);

			}

			public function testCheckboxes()
			{
				$opts = array(
					1   => "one",
					2   => "two",
					3   => "three"
				);


				$result = $this->_frm->checkboxes('name', $opts, array(2=>'two'));
				$expected = "<div id='name-container' class='checkboxes checkbox' style='display:inline-block'><label class='checkbox form-check'><input type='checkbox' name='name[1]' value='1' id='name-1-1' class='form-check-input' /><span>one</span></label><label class='checkbox form-check'><input type='checkbox' name='name[2]' value='1' id='name-2-1' class='form-check-input' checked='checked' /><span>two</span></label><label class='checkbox form-check'><input type='checkbox' name='name[3]' value='1' id='name-3-1' class='form-check-input' /><span>three</span></label></div>";
				$this->assertEquals($expected,$result);

				$result = $this->_frm->checkboxes('name', $opts, 2, array('useKeyValues'=> 1));
				$expected = "<div id='name-container' class='checkboxes checkbox' style='display:inline-block'><label class='checkbox form-check'><input type='checkbox' name='name[]' value='1' id='name-1' class='form-check-input' /><span>one</span></label><label class='checkbox form-check active'><input type='checkbox' name='name[]' value='2' id='name-2' class='form-check-input' checked='checked' /><span>two</span></label><label class='checkbox form-check'><input type='checkbox' name='name[]' value='3' id='name-3' class='form-check-input' /><span>three</span></label></div>";
				$this->assertEquals($expected,$result);

				$result = $this->_frm->checkboxes('name', $opts, 'two', array('useLabelValues'=> 1));
				$expected= "<div id='name-container' class='checkboxes checkbox' style='display:inline-block'><label class='checkbox form-check'><input type='checkbox' name='name[]' value='one' id='name-one' class='form-check-input' /><span>one</span></label><label class='checkbox form-check active'><input type='checkbox' name='name[]' value='two' id='name-two' class='form-check-input' checked='checked' /><span>two</span></label><label class='checkbox form-check'><input type='checkbox' name='name[]' value='three' id='name-three' class='form-check-input' /><span>three</span></label></div>";
				$this->assertEquals($expected,$result);

			}
/*
			public function testCheckbox_label()
			{

			}

			public function testCheckbox_switch()
			{

			}

			public function testCheckbox_toggle()
			{

			}

			public function testUc_checkbox()
			{

			}

			public function test_uc_checkbox_cb()
			{

			}

			public function testUc_label()
			{

			}

			public function testRadio()
			{

			}

			public function testRadio_switch()
			{

			}

			public function testFlipswitch()
			{

			}

			public function testLabel()
			{

			}

			public function testHelp()
			{

			}

			public function testSelect_open()
			{

			}

			public function testSelectbox()
			{

			}
	*/
	public function testSelect()
	{
		$this->_frm->__construct(true);
		$options = array('optDisabled'=>array('opt_2'));
		$selected =  'opt_3';
		$opt_array = array('opt_1'=>"Option 1", 'opt_2'=>"Option 2", 'opt_3'=>"Option 3");
		$actual = $this->_frm->select('name', $opt_array, $selected, $options);

		$actual = str_replace("\n", "", $actual);

		$expected = "<select name='name' id='name' class='tbox select form-control' tabindex='1'><option value='opt_1'>Option 1</option><option value='opt_2' disabled='disabled'>Option 2</option><option value='opt_3' selected='selected'>Option 3</option></select>";

		$this->assertEquals($expected,$actual);


		// test group opt-array.

		$opt_array = array(
			'GROUP 1' => array ('opt_1'=>"Option 1", 'opt_2'=>"Option 2", 'opt_3'=>"Option 3"),
			'GROUP 2' => array ('opt_4'=>"Option 4", 'opt_5'=>"Option 5", 'opt_6'=>"Option 6"),
		);

		$actual = $this->_frm->select('name', $opt_array, $selected, $options);
		$expected = "<select name='name' id='name' class='tbox select form-control' tabindex='2'>
<optgroup class='optgroup level-1' label='GROUP 1'>
<option value='opt_1'>Option 1</option>
<option value='opt_2' disabled='disabled'>Option 2</option>
<option value='opt_3' selected='selected'>Option 3</option>
</optgroup>
<optgroup class='optgroup level-1' label='GROUP 2'>
<option value='opt_4'>Option 4</option>
<option value='opt_5'>Option 5</option>
<option value='opt_6'>Option 6</option>
</optgroup>

</select>";

		$actual = str_replace(array("\n", "\r"), "", $actual);
		$expected = str_replace(array("\n", "\r"), "", $expected);

		$this->assertEquals($expected,$actual);


	}
	/*
			public function testUserclass()
			{

			}

			public function testSearch()
			{

			}
	*/

	public function testUcSelect()
	{

		// 'nobody,public,main,admin,classes,matchclass,member, no-excludes'; // 255, 0, 250, 254,

		$tests = array(
			0   => array('value' => '', 'default'=>null, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='255' selected"),
			1   => array('value' => 0, 'default'=>null, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='0' selected"),
			2   => array('value' => '0', 'default'=>null, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='0' selected"),
			3   => array('value' => null, 'default'=>null, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='255' selected"),
			4   => array('value' => null, 'default'=>254, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='254' selected"),
			5   => array('value' => '', 'default'=>254, 'options'=>'nobody,public,main,admin,member,no-excludes', 'expected' => "value='254' selected"),
		);

		foreach($tests as $var)
		{
			$result = $this->_frm->uc_select('uc', $var['value'], $var['options'], array('default'=>$var['default']));
			$this->assertStringContainsString($var['expected'],$result);
		}




	}



	public function testUc_select_single_numeric()
	{
		$uc_options = 'admin';
		$select_options = array('multiple' => false);
		$opt_options = array();
		$actual = $this->_frm->uc_select('uc', 254, $uc_options, $select_options, $opt_options);
		$expected = "<select name='uc' id='uc' class='tbox select form-control'>\n<option value='254' selected='selected'>&nbsp;&nbsp;Admin</option>\n\n<optgroup label='Everyone but..'>\n<option value='-254'>&nbsp;&nbsp;Not Admin</option>\n</optgroup>\n\n</select>";

		$this->assertEquals($expected, $actual);
	}

	public function testUc_select_single_string()
	{
		$uc_options = 'admin';
		$select_options = array('multiple' => false);
		$opt_options = array();
		$actual = $this->_frm->uc_select('uc', 'Admin', $uc_options, $select_options, $opt_options);
		$expected = "<select name='uc' id='uc' class='tbox select form-control'>\n<option value='254' selected='selected'>&nbsp;&nbsp;Admin</option>\n\n<optgroup label='Everyone but..'>\n<option value='-254'>&nbsp;&nbsp;Not Admin</option>\n</optgroup>\n\n</select>";

		$this->assertEquals($expected, $actual);
	}

	public function testUc_select_multi_numeric()
	{
		$uc_options = 'member,admin';
		$select_options = array('multiple' => true);
		$opt_options = array();
		$actual = $this->_frm->uc_select('uc', '254,253', $uc_options, $select_options, $opt_options);
		$expected = "<select name='uc[]' id='uc' class='tbox select form-control' multiple='multiple'>\n<option value='254' selected='selected'>&nbsp;&nbsp;Admin</option>\n<option value='253' selected='selected'>&nbsp;&nbsp;Members</option>\n\n<optgroup label='Everyone but..'>\n<option value='-254'>&nbsp;&nbsp;Not Admin</option>\n<option value='-253'>&nbsp;&nbsp;Not Members</option>\n</optgroup>\n\n</select>";

		$this->assertEquals($expected, $actual);
	}

	public function testUc_select_multi_string()
	{
		$uc_options = 'member,admin';
		$select_options = array('multiple' => true);
		$opt_options = array();
		$actual = $this->_frm->uc_select('uc', 'Admin,Members', $uc_options, $select_options, $opt_options);
		$expected = "<select name='uc[]' id='uc' class='tbox select form-control' multiple='multiple'>\n<option value='254' selected='selected'>&nbsp;&nbsp;Admin</option>\n<option value='253' selected='selected'>&nbsp;&nbsp;Members</option>\n\n<optgroup label='Everyone but..'>\n<option value='-254'>&nbsp;&nbsp;Not Admin</option>\n<option value='-253'>&nbsp;&nbsp;Not Members</option>\n</optgroup>\n\n</select>";

		$this->assertEquals($expected, $actual);
	}

	public function testUc_select_multi_mixed()
	{
		$uc_options = 'member,admin';
		$select_options = array('multiple' => true);
		$opt_options = array();
		$actual = $this->_frm->uc_select('uc', 'Admin,253', $uc_options, $select_options, $opt_options);
		$expected = "<select name='uc[]' id='uc' class='tbox select form-control' multiple='multiple'>\n<option value='254' selected='selected'>&nbsp;&nbsp;Admin</option>\n<option value='253' selected='selected'>&nbsp;&nbsp;Members</option>\n\n<optgroup label='Everyone but..'>\n<option value='-254'>&nbsp;&nbsp;Not Admin</option>\n<option value='-253'>&nbsp;&nbsp;Not Members</option>\n</optgroup>\n\n</select>";

		$this->assertEquals($expected, $actual);
	}

	/*
			public function test_uc_select_cb()
			{

			}

			public function testOptgroup_open()
			{

			}
	*/
	public function testOption()
	{
		$options = array('disabled'=>true);
		$actual = $this->_frm->option('name','value', '', $options);
		$expected = "<option value='value' disabled='disabled'>name</option>";

		$this->assertEquals($expected, $actual);

	}
	/*
			public function testOption_multi()
			{

			}

			public function testOptgroup_close()
			{

			}

			public function testSelect_close()
			{

			}

			public function testHidden()
			{

			}

			public function testToken()
			{

			}

			public function testSubmit()
			{

			}

			public function testSubmit_image()
			{

			}

			public function testAdmin_trigger()
			{

			}

			public function testButton()
			{

			}

			public function testBreadcrumb()
			{

			}

			public function testInstantEditButton()
			{

			}

			public function testAdmin_button()
			{

			}

			public function testDefaultButtonClassExists()
			{

			}

			public function testGetDefaultButtonClassByAction()
			{

			}

			public function testGetNext()
			{

			}

			public function testGetCurrent()
			{

			}

			public function testResetTabindex()
			{

			}

			public function testGet_attributes()
			{

			}

			public function test_format_id()
			{

			}
*/
			public function testName2id()
			{
				$text       = "Something?hello=there and test";
				$expected   = 'something-hello-there-and-test';

				$result = $this->_frm->name2id($text);
				
				$this->assertEquals($expected, $result);
			}
/*
			public function testFormat_options()
			{

			}

			public function test_default_options()
			{

			}

			public function testColumnSelector()
			{

			}

			public function testColGroup()
			{

			}

			public function testThead()
			{

			}

			public function testRenderHooks()
			{

			}

			public function testRenderRelated()
			{

			}

			public function testRenderTableRow()
			{

			}

			public function testRenderInline()
			{

			}
	*/
	public function testRenderValue()
	{
		date_default_timezone_set('America/Los_Angeles');

		$frm = $this->_frm;

		$expected = array(
			'text_001' => 'some text',

			'number_001' => 555,
			'number_002' => "<a class='e-tip e-editable editable-click' data-name='number_002' title=\"Edit Number 002\" data-type='text' data-pk='0' data-url='".e_SELF."?mode=&action=inline&id=0&ajax_used=1' href='#'>444</a>",

			'bool_001' => ADMIN_TRUE_ICON,
			'bool_002' => "<a class='e-tip e-editable editable-click e-editable-boolean' data-name='bool_002' data-source='{\"0\":\"\u0026cross;\",\"1\":\"\u0026check;\"}'   title=\"Edit Bool 002\" data-type='select' data-inputclass='x-editable-bool-002 e-editable-boolean' data-value=\"1\"   href='#'  data-class='e-editable-boolean' data-url='".e_SELF."?mode=&amp;action=inline&amp;id=0&amp;ajax_used=1'>&check;</a>",

			'dropdown_001' => 'Label 2',
			'dropdown_002' => "",

			'textarea_001' => "the quick brown fox jumps over the lazy dog",

			'layout_001'    => 'default',
			'layout_002'    => 'default',
			'image_001'    => "<a href=\"".e_HTTP."e107_themes/bootstrap3/images/e107_adminlogo.png\" data-modal-caption=\"e107_adminlogo.png\" data-target=\"#uiModal\" class=\"e-modal e-image-preview\" title=\"e107_adminlogo.png\" rel=\"external\"><img class='thumbnail e-thumb' src='".e_HTTP."thumb.php?src=e_THEME%2Fbootstrap3%2Fimages%2Fe107_adminlogo.png&amp;w=60&amp;h=0' alt=\"e107_adminlogo.png\" srcset=\"".e_HTTP."thumb.php?src=e_THEME%2Fbootstrap3%2Fimages%2Fe107_adminlogo.png&amp;w=240&amp;h=0 4x\" width=\"60\"  /></a>",
			'image_002'     => "<a href=\"".e_HTTP."e107_files/downloadimages/butterfly.jpg\" data-modal-caption=\"butterfly.jpg\" data-target=\"#uiModal\" class=\"e-modal e-image-preview\" title=\"butterfly.jpg\" rel=\"external\"><img class='thumbnail e-thumb' src='".e_HTTP."e107_files/downloadimages/butterfly.jpg' alt=\"butterfly.jpg\" width=\"60\"  /></a>",


			'checkboxes_001'       => 'Check Opt 2, Check Opt 3',
			'country_001'       => 'Australia',
			'country_002'       => '',
			'ip_001'            =>  "<span title='::1'>::1</span>",
			'templates_001'     => 'mytemplate',
			'radio_001'         => 'Radio Opt 2',
			'tags_001'          => 'keyword1, keyword2, keyword3',
			'bbarea_001'        => '<!-- bbcode-html-start --><b>bold</b><!-- bbcode-html-end -->',
			'icon_001'          => "<span class='icon-preview'><img class='icon' src='".e_HTTP."e107_images/e107_icon_32.png' alt='e107_icon_32.png'  /></span>",
			'media_001'         => '',
			//		'file_001'          => '<a href="'.e_HTTP.'e107_media/0f00f1d468/files/test.zip" title="Direct link to {e_MEDIA_FILE}test.zip" rel="external">{e_MEDIA_FILE}test.zip</a>',
			//		'files_001'         => '{e_MEDIA_FILE}test.zip',
			'datestamp_001'     => '01 Feb 2016 : 15:00',
			'date_001'          => '2018-08-23',
			'userclass_001'     => 'Everyone (public)',
			'userclasses_001'   => 'Everyone (public)<br />PRIVATEMENU',
			'user_001'          => 'e107',
			'url_001'           => "<a href='https://e107.org' title='https://e107.org'>https://e107.org</a>",
			'email_001'         => "<a href='mailto:me@email.com' title='me@email.com'>me@email.com</a>",
			'hidden_001'        => '',
			//	'method_001'        => 'custom-value',
			'language_001'      => 'French',
			'userclass_002'     => 'Everyone (public)',
			//	'lanlist_001'       => 'German', // only works with multiple languages installed.


		);

//Check Opt 2, Check Opt 3

		foreach($this->_fields as $field=>$att)
		{
			$value = $this->_values[$field];
			$result  = $frm->renderValue($field, $value, $att);

			/*	echo "-- ".$field."-- \n";
				print_r($result);
				echo "\n\n";*/

			if(!isset($expected[$field]))
			{
				$this->expectExceptionMessage('\$expected value for '.$field.' not set in script');
				$this->expectExceptionMessage($result);
			}

			$this->assertEquals($expected[$field], $result, 'Mismatch on '.$field);
		}


	}

	public function testRenderValueInline()
	{
		foreach($this->_fields as $field=>$att)
		{
			if(isset($this->_fields[$field]['inline']))
			{
				continue;
			}

			$this->_fields[$field]['inline'] = true;
		}

		foreach($this->_fields as $field=>$att)
		{
			if($att['inline'] !== true)
			{
				continue;
			}

			$value = $this->_values[$field];
			$result  = $this->_frm->renderValue($field, $value, $att, 23);

			if(!isset($this->_values[$field]))
			{
				$this->expectExceptionMessage('\$expected value for \$field not set in script');
				//	$this->expectExceptionMessage($result);
			}

			$this->assertStringContainsString('data-token',$result,$field." doesn't contain 'data-token'");
		}

	}

	public function testRenderElement()
	{
		$frm = $this->_frm;
		$frm->__construct(true);

		date_default_timezone_set('America/Phoenix');

		$expected = array(
			'text_001' => "<input type='text' name='text_001' value='some text' maxlength=255  id='text-001' class='tbox form-control input-xlarge' tabindex='1' />",

			'number_001' => "<input type='number' name='number_001'  min='0'  step='1' value='555'  id='number-001' class='tbox number e-spinner  input-small form-control' tabindex='2' pattern='^[0-9]*' />",
			'number_002' => "<input type='number' name='number_002'  min='0'  step='1' value='444'  id='number-002' class='tbox number e-spinner  input-small form-control' tabindex='3' pattern='^[0-9]*' />",

			'bool_001' => "<label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='bool_001' value='1' checked='checked' /> <span>On</span></label> 	<label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='bool_001' value='0' /> <span>Off</span></label>",
			'bool_002' => "<label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='bool_002' value='1' checked='checked' /> <span>On</span></label> 	<label class='radio-inline'><input type='radio' name='bool_002' value='0' /> <span>Off</span></label>",


			'dropdown_001' => "<select name='dropdown_001' id='dropdown-001' class='tbox select form-control' tabindex='3'><option value='opt_value_1'>Label 1</option><option value='opt_value_2' selected='selected'>Label 2</option></select>",
			'dropdown_002' => "<select name='dropdown_002' id='dropdown-002' class='tbox select form-control' tabindex='4'><option value='0'>Option 0</option><option value='1' selected='selected'>Option 1</option><option value='2'>Option 2</option></select>",


			'textarea_001' => "<textarea name='textarea_001' rows='5' cols='40' id='textarea-001' class='form-control input-xlarge' tabindex='5'>the quick brown fox jumps over the lazy dog</textarea>",

			'layout_001'    => "<select name='layout_001' id='news_view' class='tbox select form-control' tabindex='6'><option value='default' selected='selected'>Default</option><option value='videos'>Videos (experimental)</option></select>",
			'layout_002'    => "<select name='layout_002' id='news_view' class='tbox select form-control' tabindex='7'><option value='default' selected='selected'>Default</option><option value='videos'>Videos (experimental)</option></select>",

			'image_001'     => "<a href=\"".e_HTTP."e107_themes/bootstrap3/images/e107_adminlogo.png\" data-modal-caption=\"e107_adminlogo.png\" data-target=\"#uiModal\" class=\"e-modal e-image-preview\" title=\"e107_adminlogo.png\" rel=\"external\"><img class='thumbnail e-thumb' src='".e_HTTP."thumb.php?src=e_THEME%2Fbootstrap3%2Fimages%2Fe107_adminlogo.png&amp;w=60&amp;h=0' alt=\"e107_adminlogo.png\" srcset=\"".e_HTTP."thumb.php?src=e_THEME%2Fbootstrap3%2Fimages%2Fe107_adminlogo.png&amp;w=240&amp;h=0 4x\" width=\"60\"  /></a><input type='hidden' name='image_001' value='{e_THEME}bootstrap3/images/e107_adminlogo.png' id='image-001-e-THEME-bootstrap3-images-e107-adminlogo-png' />",
			'image_002'     => "<a href=\"".e_HTTP."e107_files/downloadimages/butterfly.jpg\" data-modal-caption=\"butterfly.jpg\" data-target=\"#uiModal\" class=\"e-modal e-image-preview\" title=\"butterfly.jpg\" rel=\"external\"><img class='thumbnail e-thumb' src='".e_HTTP."e107_files/downloadimages/butterfly.jpg' alt=\"butterfly.jpg\" width=\"60\"  /></a><input type='hidden' name='image_002' value='butterfly.jpg' id='image-002-butterfly-jpg' />",

			'checkboxes_001'       => "<div id='checkboxes-001-container' class='checkboxes checkbox' style='display:inline-block'><label class='checkbox form-check'><input type='checkbox' name='checkboxes_001[1]' value='1' id='checkboxes-001-1-1' class='form-check-input' checked='checked' tabindex='8' /><span>Check Opt 1</span></label><label class='checkbox form-check'><input type='checkbox' name='checkboxes_001[2]' value='1' id='checkboxes-001-2-1' class='form-check-input' tabindex='9' /><span>Check Opt 2</span></label><label class='checkbox form-check'><input type='checkbox' name='checkboxes_001[3]' value='1' id='checkboxes-001-3-1' class='form-check-input' tabindex='10' /><span>Check Opt 3</span></label><label class='checkbox form-check'><input type='checkbox' name='checkboxes_001[4]' value='1' id='checkboxes-001-4-1' class='form-check-input' tabindex='11' /><span><img src=\"images/foo.jpg\" /></span></label></div>",
			'country_001'       => "<select name='country_001' id='country-001' class='tbox select form-control' tabindex='12'><option value=''> </option><option value='af'>Afghanistan</option><option value='al'>Albania</option><option value='dz'>Algeria</option><option value='as'>American Samoa</option><option value='ad'>Andorra</option><option value='ao'>Angola</option><option value='ai'>Anguilla</option><option value='aq'>Antarctica</option><option value='ag'>Antigua and Barbuda</option><option value='ar'>Argentina</option><option value='am'>Armenia</option><option value='aw'>Aruba</option><option value='au' selected='selected'>Australia</option><option value='at'>Austria</option><option value='az'>Azerbaijan</option><option value='bs'>Bahamas</option><option value='bh'>Bahrain</option><option value='bd'>Bangladesh</option><option value='bb'>Barbados</option><option value='by'>Belarus</option><option value='be'>Belgium</option><option value='bz'>Belize</option><option value='bj'>Benin</option><option value='bm'>Bermuda</option><option value='bt'>Bhutan</option><option value='bo'>Bolivia</option><option value='ba'>Bosnia-Herzegovina</option><option value='bw'>Botswana</option><option value='bv'>Bouvet Island</option><option value='br'>Brazil</option><option value='io'>British Indian Ocean Territory</option><option value='bn'>Brunei Darussalam</option><option value='bg'>Bulgaria</option><option value='bf'>Burkina Faso</option><option value='bi'>Burundi</option><option value='kh'>Cambodia</option><option value='cm'>Cameroon</option><option value='ca'>Canada</option><option value='cv'>Cape Verde</option><option value='ky'>Cayman Islands</option><option value='cf'>Central African Republic</option><option value='td'>Chad</option><option value='cl'>Chile</option><option value='cn'>China</option><option value='cx'>Christmas Island</option><option value='cc'>Cocos (Keeling) Islands</option><option value='co'>Colombia</option><option value='km'>Comoros</option><option value='cg'>Congo</option><option value='cd'>Congo (Dem.Rep)</option><option value='ck'>Cook Islands</option><option value='cr'>Costa Rica</option><option value='hr'>Croatia</option><option value='cu'>Cuba</option><option value='cy'>Cyprus</option><option value='cz'>Czech Republic</option><option value='dk'>Denmark</option><option value='dj'>Djibouti</option><option value='dm'>Dominica</option><option value='do'>Dominican Republic</option><option value='tp'>East Timor</option><option value='ec'>Ecuador</option><option value='eg'>Egypt</option><option value='sv'>El Salvador</option><option value='gq'>Equatorial Guinea</option><option value='er'>Eritrea</option><option value='ee'>Estonia</option><option value='et'>Ethiopia</option><option value='fk'>Falkland Islands</option><option value='fo'>Faroe Islands</option><option value='fj'>Fiji</option><option value='fi'>Finland</option><option value='fr'>France</option><option value='gf'>French Guyana</option><option value='tf'>French Southern Territories</option><option value='ga'>Gabon</option><option value='gm'>Gambia</option><option value='ge'>Georgia</option><option value='de'>Germany</option><option value='gh'>Ghana</option><option value='gi'>Gibraltar</option><option value='gr'>Greece</option><option value='gl'>Greenland</option><option value='gd'>Grenada</option><option value='gp'>Guadeloupe (French)</option><option value='gu'>Guam (USA)</option><option value='gt'>Guatemala</option><option value='gn'>Guinea</option><option value='gw'>Guinea Bissau</option><option value='gy'>Guyana</option><option value='ht'>Haiti</option><option value='hm'>Heard and McDonald Islands</option><option value='hn'>Honduras</option><option value='hk'>Hong Kong</option><option value='hu'>Hungary</option><option value='is'>Iceland</option><option value='in'>India</option><option value='id'>Indonesia</option><option value='ir'>Iran</option><option value='iq'>Iraq</option><option value='ie'>Ireland</option><option value='il'>Israel</option><option value='it'>Italy</option><option value='ci'>Ivory Coast (Cote D'Ivoire)</option><option value='jm'>Jamaica</option><option value='jp'>Japan</option><option value='jo'>Jordan</option><option value='kz'>Kazakhstan</option><option value='ke'>Kenya</option><option value='ki'>Kiribati</option><option value='kp'>Korea (North)</option><option value='kr'>Korea (South)</option><option value='kw'>Kuwait</option><option value='kg'>Kyrgyzstan</option><option value='la'>Laos</option><option value='lv'>Latvia</option><option value='lb'>Lebanon</option><option value='ls'>Lesotho</option><option value='lr'>Liberia</option><option value='ly'>Libya</option><option value='li'>Liechtenstein</option><option value='lt'>Lithuania</option><option value='lu'>Luxembourg</option><option value='mo'>Macau</option><option value='mk'>Macedonia</option><option value='mg'>Madagascar</option><option value='mw'>Malawi</option><option value='my'>Malaysia</option><option value='mv'>Maldives</option><option value='ml'>Mali</option><option value='mt'>Malta</option><option value='mh'>Marshall Islands</option><option value='mq'>Martinique (French)</option><option value='mr'>Mauritania</option><option value='mu'>Mauritius</option><option value='yt'>Mayotte</option><option value='mx'>Mexico</option><option value='fm'>Micronesia</option><option value='md'>Moldavia</option><option value='mc'>Monaco</option><option value='mn'>Mongolia</option><option value='me'>Montenegro</option><option value='ms'>Montserrat</option><option value='ma'>Morocco</option><option value='mz'>Mozambique</option><option value='mm'>Myanmar</option><option value='na'>Namibia</option><option value='nr'>Nauru</option><option value='np'>Nepal</option><option value='nl'>Netherlands</option><option value='an'>Netherlands Antilles</option><option value='nc'>New Caledonia (French)</option><option value='nz'>New Zealand</option><option value='ni'>Nicaragua</option><option value='ne'>Niger</option><option value='ng'>Nigeria</option><option value='nu'>Niue</option><option value='nf'>Norfolk Island</option><option value='mp'>Northern Mariana Islands</option><option value='no'>Norway</option><option value='om'>Oman</option><option value='pk'>Pakistan</option><option value='pw'>Palau</option><option value='pa'>Panama</option><option value='pg'>Papua New Guinea</option><option value='py'>Paraguay</option><option value='pe'>Peru</option><option value='ph'>Philippines</option><option value='pn'>Pitcairn Island</option><option value='pl'>Poland</option><option value='pf'>Polynesia (French)</option><option value='pt'>Portugal</option><option value='pr'>Puerto Rico</option><option value='ps'>Palestine</option><option value='qa'>Qatar</option><option value='re'>Reunion (French)</option><option value='ro'>Romania</option><option value='ru'>Russia</option><option value='rw'>Rwanda</option><option value='gs'>S. Georgia &amp; S. Sandwich Isls.</option><option value='sh'>Saint Helena</option><option value='kn'>Saint Kitts &amp; Nevis</option><option value='lc'>Saint Lucia</option><option value='pm'>Saint Pierre and Miquelon</option><option value='st'>Saint Tome (Sao Tome) and Principe</option><option value='vc'>Saint Vincent &amp; Grenadines</option><option value='ws'>Samoa</option><option value='sm'>San Marino</option><option value='sa'>Saudi Arabia</option><option value='sn'>Senegal</option><option value='rs'>Serbia</option><option value='sc'>Seychelles</option><option value='sl'>Sierra Leone</option><option value='sg'>Singapore</option><option value='sk'>Slovak Republic</option><option value='si'>Slovenia</option><option value='sb'>Solomon Islands</option><option value='so'>Somalia</option><option value='za'>South Africa</option><option value='es'>Spain</option><option value='lk'>Sri Lanka</option><option value='sd'>Sudan</option><option value='sr'>Suriname</option><option value='sj'>Svalbard and Jan Mayen Islands</option><option value='sz'>Swaziland</option><option value='se'>Sweden</option><option value='ch'>Switzerland</option><option value='sy'>Syria</option><option value='tj'>Tadjikistan</option><option value='tw'>Taiwan</option><option value='tz'>Tanzania</option><option value='th'>Thailand</option><option value='ti'>Tibet</option><option value='tg'>Togo</option><option value='tk'>Tokelau</option><option value='to'>Tonga</option><option value='tt'>Trinidad and Tobago</option><option value='tn'>Tunisia</option><option value='tr'>Turkey</option><option value='tm'>Turkmenistan</option><option value='tc'>Turks and Caicos Islands</option><option value='tv'>Tuvalu</option><option value='ug'>Uganda</option><option value='ua'>Ukraine</option><option value='ae'>United Arab Emirates</option><option value='gb'>United Kingdom</option><option value='us'>United States</option><option value='uy'>Uruguay</option><option value='um'>US Minor Outlying Islands</option><option value='uz'>Uzbekistan</option><option value='vu'>Vanuatu</option><option value='va'>Vatican City State</option><option value='ve'>Venezuela</option><option value='vn'>Vietnam</option><option value='vg'>Virgin Islands (British)</option><option value='vi'>Virgin Islands (USA)</option><option value='wf'>Wallis and Futuna Islands</option><option value='eh'>Western Sahara</option><option value='ye'>Yemen</option><option value='zm'>Zambia</option><option value='zw'>Zimbabwe</option></select>",
			'ip_001'            =>  "<input type='text' name='ip_001' value='::1' maxlength=32  id='ip-001' class='tbox form-control' tabindex='14' />",
			'templates_001'     => "<select name='templates_001' id='templates-001' class='tbox select form-control' tabindex='15'><option value='bbcode'>Bbcode</option><option value='forum_icons'>Forum Icons</option><option value='forum_poll'>Forum Poll</option><option value='forum_post'>Forum Post</option><option value='forum_posted'>Forum Posted</option><option value='forum_preview'>Forum Preview</option><option value='forum'>Forum</option><option value='forum_viewforum'>Forum Viewforum</option><option value='forum_viewtopic'>Forum Viewtopic</option><option value='newforumposts_menu'>Newforumposts Menu</option></select>",
			'radio_001'         => "<label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='radio_001' value='1' /> <span>Radio Opt 1</span></label> <label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='radio_001' value='2' checked='checked' /> <span>Radio Opt 2</span></label> <label class='radio-inline form-check-inline'><input class='form-check-input' type='radio' name='radio_001' value='3' /> <span>Radio Opt 3</span></label>",

			//todo check tags_001 is correct.
			'tags_001'          => "<input type='text' name='tags_001' value='keyword1,keyword2,keyword3' maxlength=255  id='tags-001' tabindex='16' />",

			//	'bbarea_001'        => '<!-- bbcode-html-start --><b>bold</b><!-- bbcode-html-end -->',
			//		'icon_001'          => "<span class='icon-preview'><img class='icon' src='".e_HTTP."e107_images/e107_icon_32.png' alt='e107_icon_32.png'  /></span>",
			//		'media_001'         => '',
			//		'file_001'          => '<a href="'.e_HTTP.'e107_media/0f00f1d468/files/test.zip" title="Direct link to {e_MEDIA_FILE}test.zip" rel="external">{e_MEDIA_FILE}test.zip</a>',
			//		'files_001'         => '{e_MEDIA_FILE}test.zip',
			'datestamp_001'     => "<input class='tbox e-date input-xlarge form-control' type='text' size='40' id='e-datepicker-datestamp-001' value='Monday, 01 Feb, 2016' data-date-unix ='true' data-date-format='DD, dd M, yyyy' data-date-ampm='false' data-date-language='en' data-date-firstday='0'     /><input type='hidden' name='datestamp_001' id='datestamp-001' value='1454367600' />",
			'date_001'          => "<input class='tbox e-date input-xlarge form-control' type='text' size='40' id='e-datepicker-date-001' value='Thursday, 23 Aug, 2018' data-date-unix ='true' data-date-format='DD, dd M, yyyy' data-date-ampm='false' data-date-language='en' data-date-firstday='0'     /><input type='hidden' name='date_001' id='date-001' value='1535007600' />",
			'userclass_001'     => "<select name='userclass_001' id='userclass-001' class='tbox select form-control' tabindex='18'><option value='0' selected='selected'>Everyone (public)</option><option value='254'>&nbsp;&nbsp;Admin</option><option value='249'>&nbsp;&nbsp;Admins and Mods</option><option value='2'>&nbsp;&nbsp;CONTACT PEOPLE</option><option value='248'>&nbsp;&nbsp;Forum Moderators</option><option value='252'>&nbsp;&nbsp;Guests</option><option value='250'>&nbsp;&nbsp;Main Admin</option><option value='253'>&nbsp;&nbsp;Members</option><option value='1'>&nbsp;&nbsp;PRIVATEMENU</option><option value='255'>No One (inactive)</option><option value='3'>&nbsp;&nbsp;NEWSLETTER</option><optgroup label='Everyone but..'><option value='-254'>&nbsp;&nbsp;Not Admin</option><option value='-249'>&nbsp;&nbsp;Not Admins and Mods</option><option value='-2'>&nbsp;&nbsp;Not CONTACT PEOPLE</option><option value='-248'>&nbsp;&nbsp;Not Forum Moderators</option><option value='-252'>&nbsp;&nbsp;Not Guests</option><option value='-250'>&nbsp;&nbsp;Not Main Admin</option><option value='-253'>&nbsp;&nbsp;Not Members</option><option value='-1'>&nbsp;&nbsp;Not PRIVATEMENU</option><option value='-3'>&nbsp;&nbsp;Not NEWSLETTER</option></optgroup></select>",
			'userclasses_001'   => "<select name='userclasses_001[]' id='userclasses-001' class='tbox select form-control' tabindex='19' multiple='multiple'><option value='0'>Everyone (public)</option><option value='254'>&nbsp;&nbsp;Admin</option><option value='249'>&nbsp;&nbsp;Admins and Mods</option><option value='2'>&nbsp;&nbsp;CONTACT PEOPLE</option><option value='248'>&nbsp;&nbsp;Forum Moderators</option><option value='252'>&nbsp;&nbsp;Guests</option><option value='250'>&nbsp;&nbsp;Main Admin</option><option value='253'>&nbsp;&nbsp;Members</option><option value='1' selected='selected'>&nbsp;&nbsp;PRIVATEMENU</option><option value='255'>No One (inactive)</option><option value='3'>&nbsp;&nbsp;NEWSLETTER</option><optgroup label='Everyone but..'><option value='-254'>&nbsp;&nbsp;Not Admin</option><option value='-249'>&nbsp;&nbsp;Not Admins and Mods</option><option value='-2'>&nbsp;&nbsp;Not CONTACT PEOPLE</option><option value='-248'>&nbsp;&nbsp;Not Forum Moderators</option><option value='-252'>&nbsp;&nbsp;Not Guests</option><option value='-250'>&nbsp;&nbsp;Not Main Admin</option><option value='-253'>&nbsp;&nbsp;Not Members</option><option value='-1'>&nbsp;&nbsp;Not PRIVATEMENU</option><option value='-3'>&nbsp;&nbsp;Not NEWSLETTER</option></optgroup></select>",
			//todo check user_001 is correct
			'user_001'          => "<input type='text' name='user_001' value='1' maxlength=100  id='user-001' tabindex='20' />",
			'url_001'           => "<input type='text' name='url_001' value='https://e107.org' maxlength=255  id='url-001' class='tbox form-control' tabindex='21' pattern='^\S*$' />",
			'email_001'         => "<input type='email' name='email_001' value='me@email.com' maxlength=255  id='email-001' class='tbox form-control' tabindex='22' />",
			'hidden_001'        => "<input type='hidden' name='hidden_001' value='hidden-value' id='hidden-001-hidden-value' />",
			//	'method_001'        => 'custom-value',
			'language_001'      => "<select name='language_001' id='language-001' class='tbox select form-control' tabindex='23'><option value='aa'>Afar</option><option value='ab'>Abkhazian</option><option value='af'>Afrikaans</option><option value='am'>Amharic</option><option value='ar'>Arabic</option><option value='as'>Assamese</option><option value='ae'>Avestan</option><option value='ay'>Aymara</option><option value='az'>Azerbaijani</option><option value='ba'>Bashkir</option><option value='be'>Belarusian</option><option value='bn'>Bengali</option><option value='bh'>Bihari</option><option value='bi'>Bislama</option><option value='bo'>Tibetan</option><option value='bs'>Bosnian</option><option value='br'>Breton</option><option value='bg'>Bulgarian</option><option value='my'>Burmese</option><option value='ca'>Catalan</option><option value='cs'>Czech</option><option value='ch'>Chamorro</option><option value='ce'>Chechen</option><option value='cn'>ChineseSimp</option><option value='tw'>ChineseTrad</option><option value='cv'>Chuvash</option><option value='kw'>Cornish</option><option value='co'>Corsican</option><option value='da'>Danish</option><option value='nl'>Dutch</option><option value='dz'>Dzongkha</option><option value='de'>German</option><option value='en'>English</option><option value='eo'>Esperanto</option><option value='et'>Estonian</option><option value='eu'>Basque</option><option value='fo'>Faroese</option><option value='fa'>Persian</option><option value='fj'>Fijian</option><option value='fi'>Finnish</option><option value='fr' selected='selected'>French</option><option value='fy'>Frisian</option><option value='gd'>Gaelic</option><option value='el'>Greek</option><option value='ga'>Irish</option><option value='gl'>Gallegan</option><option value='gn'>Guarani</option><option value='gu'>Gujarati</option><option value='ha'>Hausa</option><option value='he'>Hebrew</option><option value='hz'>Herero</option><option value='hi'>Hindi</option><option value='ho'>Hiri Motu</option><option value='hr'>Croatian</option><option value='hu'>Hungarian</option><option value='hy'>Armenian</option><option value='iu'>Inuktitut</option><option value='ie'>Interlingue</option><option value='id'>Indonesian</option><option value='ik'>Inupiaq</option><option value='is'>Icelandic</option><option value='it'>Italian</option><option value='jw'>Javanese</option><option value='ja'>Japanese</option><option value='kl'>Kalaallisut</option><option value='kn'>Kannada</option><option value='ks'>Kashmiri</option><option value='ka'>Georgian</option><option value='kk'>Kazakh</option><option value='km'>Khmer</option><option value='ki'>Kikuyu</option><option value='rw'>Kinyarwanda</option><option value='ky'>Kirghiz</option><option value='kv'>Komi</option><option value='ko'>Korean</option><option value='ku'>Kurdish</option><option value='lo'>Lao</option><option value='la'>Latin</option><option value='lv'>Latvian</option><option value='ln'>Lingala</option><option value='lt'>Lithuanian</option><option value='lb'>Letzeburgesch</option><option value='mh'>Marshall</option><option value='ml'>Malayalam</option><option value='mr'>Marathi</option><option value='mk'>Macedonian</option><option value='mg'>Malagasy</option><option value='mt'>Maltese</option><option value='mo'>Moldavian</option><option value='mn'>Mongolian</option><option value='mi'>Maori</option><option value='ms'>Malay</option><option value='gv'>Manx</option><option value='na'>Nauru</option><option value='nv'>Navajo</option><option value='ng'>Ndonga</option><option value='ne'>Nepali</option><option value='no'>Norwegian</option><option value='ny'>Chichewa</option><option value='or'>Oriya</option><option value='om'>Oromo</option><option value='pa'>Panjabi</option><option value='pi'>Pali</option><option value='pl'>Polish</option><option value='pt'>Portuguese</option><option value='ps'>Pushto</option><option value='qu'>Quechua</option><option value='ro'>Romanian</option><option value='rn'>Rundi</option><option value='ru'>Russian</option><option value='sg'>Sango</option><option value='sa'>Sanskrit</option><option value='si'>Sinhala</option><option value='sk'>Slovak</option><option value='sl'>Slovenian</option><option value='sm'>Samoan</option><option value='sn'>Shona</option><option value='sd'>Sindhi</option><option value='so'>Somali</option><option value='es'>Spanish</option><option value='sq'>Albanian</option><option value='sc'>Sardinian</option><option value='sr'>Serbian</option><option value='ss'>Swati</option><option value='su'>Sundanese</option><option value='sw'>Swahili</option><option value='sv'>Swedish</option><option value='ty'>Tahitian</option><option value='ta'>Tamil</option><option value='tt'>Tatar</option><option value='te'>Telugu</option><option value='tg'>Tajik</option><option value='tl'>Tagalog</option><option value='th'>Thai</option><option value='ti'>Tigrinya</option><option value='tn'>Tswana</option><option value='ts'>Tsonga</option><option value='tk'>Turkmen</option><option value='tr'>Turkish</option><option value='ug'>Uighur</option><option value='uk'>Ukrainian</option><option value='ur'>Urdu</option><option value='uz'>Uzbek</option><option value='vi'>Vietnamese</option><option value='cy'>Welsh</option><option value='wo'>Wolof</option><option value='xh'>Xhosa</option><option value='yi'>Yiddish</option><option value='yo'>Yoruba</option><option value='za'>Zhuang</option><option value='zu'>Zulu</option></select>",
			//	'lanlist_001'       => 'German', // only works with multiple languages installed.
		);



		foreach($this->_fields as $field=>$att)
		{
			$value = $this->_values[$field];
			$result  = $frm->renderElement($field, $value, $att);

			$result = str_replace(array("\n", "\r"), "", $result);


			if(empty($expected[$field]))
			{
				continue;
				//	echo $result;
				//	echo "\n\n";
				//	$this->expectExceptionMessage('\$expected value for \$field not set in script');
				//	$this->expectExceptionMessage($result);
			}



			$this->assertEquals($expected[$field], $result, 'Field: '.$field);
		}


	}
	/*
			public function testRenderListForm()
			{

			}

			public function testRenderGridForm()
			{

			}

			public function testRenderCreateForm()
			{

			}

			public function testRenderCreateFieldset()
			{

			}

			public function testRenderCreateButtonsBar()
			{

			}

			public function testRenderForm()
			{

			}

			public function testRenderFieldset()
			{

			}

			public function testRenderValueTrigger()
			{

			}

			public function testRenderElementTrigger()
			{

			}*/

	public function testInlineTokenGeneratedOnlyOnce()
	{
		$class = new \ReflectionClass('e_form');

		$method = $class->getMethod('inlineToken');
		$method->setAccessible(true);

		$results = [];
		$results[] = $method->invoke($this->_frm);
		$results[] = $method->invoke($this->_frm);

		$this->assertEquals($results[0], $results[1],
			"Generated tokens differ. Watch out for performance penalty!");
	}


	public function testRenderLink()
	{
		$tests = array(
			0   => array(
				'value'     => 'Some text',
				'parms'     => array('link'=>'myurl.php', 'target'=>'blank'),
				'expected'  => "<a class='e-tip'  rel='external'  href='myurl.php'  title='Quick View' >Some text</a>"
			),
			1   => array(
				'value'     => 'Some text',
				'parms'     => array('link'=>'myurl.php?id=[id]', 'target'=>'modal'),
				'expected'  => "<a class='e-tip'  href='myurl.php?id=3'  data-toggle='modal' data-cache='false' data-target='#uiModal'  title='Quick View' >Some text</a>"
			),
			2   => array(
				'value'     => 'Some text',
				'parms'     => array('link'=>'url_001', 'target'=>'blank'),
				'expected'  => "<a class='e-tip'  rel='external'  href='https://e107.org'  title='Quick View' >Some text</a>"
			),
			3   => array(
				'value'     => 'Some text',
				'parms'     => array('link'=>'myurl.php?country=[country_001]', 'target'=>'dialog'),
				'expected'  => "<a class='e-tip e-modal'  href='myurl.php?country=au'  title='Quick View' >Some text</a>"
			),
		/*	4   => array(
				'value'     => 'Some text',
				'parms'     => array('url'=>'rss', 'title'=>'Click Here'),
				'expected'  => "<a class='e-tip'  href='".e_HTTP."feed/rss-sefurl/rss/5'  title='Click Here' >Some text</a>"
			)*/


		);



		try
		{
			/** @var e_admin_model $model */
			$model = $this->make('e_admin_model');
		}
		catch (Exception $e)
		{
			$this->assertTrue(false, "Couldn't load e_admin_model object");
		}

		$model->setData($this->_values);

		$model->setData('rss_url', 'rss-sefurl');
		$model->setData('rss_topicid', '5');

		e107::setRegistry('core/adminUI/currentListModel', $model);
		e107::setRegistry('core/adminUI/currentPlugin', 'rss_menu');



		foreach($tests as $t)
		{
			$result = $this->_frm->renderLink($t['value'], $t['parms'], 3);
			$this->assertEquals($t['expected'],$result);
		}



	}

}
