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
		protected $_frm;

		protected $_id = 567; // simulated record number.


		// admin_ui $fields format..
		protected $_fields = array(

			'text_001' => array('title'=> "Text 001",	'type' => 'text', 'writeParms'=>array('size'=>'xlarge')),

   	        // 'text_002' etc..  add other configurations with type='text' in here.

			'number_001'    => array('title'=> "Number 001",	'type' => 'number', 'writeParms'=>array('min'=>0)),
			'number_002'    => array('title'=> "Number 002",	'type' => 'number', 'inline'=>true, 'writeParms'=>array('min'=>0)),

			'bool_001'      => array('title'=> "Bool 001",	'type' => 'text', 'writeParms'=>array('size'=>'xlarge')),
			'bool_002'      => array('title'=> "Bool 002",	'type' => 'text', 'inline'=>true, 'writeParms'=>array('size'=>'xlarge')),

			'dropdown_001'  => array('title'=>'Dropdown 001', 'type'=>'dropdown', 'tab'=>1, 'writeParms' => array('optArray'=>array('opt_value_1'=>'Label 1', 'opt_value_2'=>'Label 2')) ),


		   	'textarea_001' => array('title'=> "Textarea 001",	'type' => 'textarea', 'writeParms'=>array('size'=>'xlarge','rows'=> 5)),

			'layout_001' =>   array ( 'title' => 'Layouts 001', 'type' => 'layouts', 'inline' => false, 'writeParms' => 'plugin=news&id=news_view&merge=1' ), // 'news', 'news_view', 'front'
			'layout_002' =>   array ( 'title' => 'Layouts 002', 'type' => 'layouts', 'inline' => true, 'writeParms'=>array('plugin'=>'news', 'id'=>'news_view', 'area'=> 'front', 'merge'=>false)), // 'news', 'news_view', 'front'
		//	'layout_003' =>   array ( 'title' => 'Layouts 003', 'type' => 'layouts', 'inline' => true, 'writeParms'=>array('plugin'=>'news', 'id'=>'news_view', 'area'=> 'front', 'merge'=>false)), // 'news', 'news_view', 'front'

		);

		// simulated database/form values.
		protected $_values = array(
			'text_001' => 'some text',

			'number_001' => 555,
			'number_002' => 444,

			'bool_001' => 1,
			'bool_002' => 1,

			'dropdown_001' => 'opt_value_2',

			'textarea_001' => "the quick brown fox jumps over the lazy dog",

			'layout_001'    => 'default',
			'layout_002'    => 'default'

		);




		protected function _before()
		{
			try
			{
				$this->_frm = $this->make('e_form');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_parser object");
			}
		}

	    protected function _after()
	    {

	    }



	/*	public function testAddWarning()
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

		public function testGetCountry()
		{

		}

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

		}

		public function testDatepicker()
		{

		}

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

		public function testProgressBar()
		{

		}

		public function testTextarea()
		{

		}

		public function testBbarea()
		{

		}

		public function testCheckbox()
		{

		}

		public function testCheckboxes()
		{

		}

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

		public function testSelect()
		{

		}

		public function testUserclass()
		{

		}

		public function testSearch()
		{

		}

		public function testUc_select()
		{

		}

		public function test_uc_select_cb()
		{

		}

		public function testOptgroup_open()
		{

		}

		public function testOption()
		{

		}

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

		public function testName2id()
		{

		}

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

			$frm = $this->_frm;

			$expected = array(
				'text_001' => 'some text',

				'number_001' => 555,
				'number_002' => "<a class='e-tip e-editable editable-click' data-name='number_002' title=\"Edit Number 002\" data-type='text' data-pk='0' data-url='".e_SELF."?mode=&action=inline&id=0&ajax_used=1' href='#'>444</a>",

				'bool_001' => 1,
				'bool_002' => "<a class='e-tip e-editable editable-click' data-emptytext='-' data-name='bool_002' title=\"Edit Bool 002\" data-type='text' data-pk='0'  data-url='".e_SELF."?mode=&amp;action=inline&amp;id=0&amp;ajax_used=1' href='#'>1</a>",

				'dropdown_001' => 'Label 2',

				'textarea_001' => "the quick brown fox jumps over the lazy dog",

				'layout_001'    => 'default',
				'layout_002'    => "<a class='e-tip e-editable editable-click ' data-name='layout_002' data-source='{\"default\":\"Default\",\"videos\":\"Videos (experimental)\"}'   title=\"Edit Layouts 002\" data-type='select' data-inputclass='x-editable-layout-002 ' data-value=\"default\"   href='#'  data-url='".e_SELF."?mode=&amp;action=inline&amp;id=0&amp;ajax_used=1'>Default</a>"

			);




			foreach($this->_fields as $field=>$att)
			{
				$value = $this->_values[$field];
				$result  = $frm->renderValue($field, $value, $att);

				if(!isset($expected[$field]))
				{
					$this->expectExceptionMessage('\$expected value for \$field not set in script');
				}

				$this->assertEquals($expected[$field], $result);
			}


		}

		public function testRenderElement()
		{

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
	}
