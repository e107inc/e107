<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_admin_uiTest extends \Codeception\Test\Unit
	{


		public function testPregReplace()
		{
			$tests = array(
				0   => array('text'=>"something", 'expected'=>"something"),

			);


			foreach($tests as $var)
			{
				$result = preg_replace('/[^\w\-:.]/', '', $var['text']); // this pattern used in parts of the admin-ui.
				$this->assertEquals($var['expected'], $result);
				//var_dump($result);
			}

			// echo array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];


		}




/*
		public function testListEcolumnsTrigger()
		{

		}

		public function testBatchTriggered()
		{

		}

		public function testListBatchTrigger()
		{

		}

		public function testGridBatchTrigger()
		{

		}

		public function testHandleCommaBatch()
		{

		}

		public function testListDeleteTrigger()
		{

		}

		public function testBeforeDelete()
		{

		}

		public function testAfterDelete()
		{

		}

		public function testListHeader()
		{

		}

		public function testListObserver()
		{

		}

		public function testGridObserver()
		{

		}

		public function testFilterAjaxPage()
		{

		}

		public function testInlineAjaxPage()
		{

		}

		public function testLogajax()
		{

		}

		public function testSortAjaxPage()
		{

		}

		public function testListPage()
		{

		}

		public function testGridPage()
		{

		}

		public function testListAjaxObserver()
		{

		}

		public function testGridAjaxObserver()
		{

		}

		public function testListAjaxPage()
		{

		}

		public function testGridAjaxPage()
		{

		}

		public function testEditObserver()
		{

		}

		public function testEditCancelTrigger()
		{

		}

		public function testEditSubmitTrigger()
		{

		}

		public function testEditHeader()
		{

		}

		public function testEditPage()
		{

		}

		public function testCreateObserver()
		{

		}

		public function testCreateCancelTrigger()
		{

		}

		public function testCreateSubmitTrigger()
		{

		}

		public function testBeforeCreate()
		{

		}

		public function testAfterCreate()
		{

		}

		public function testOnCreateError()
		{

		}

		public function testBeforeUpdate()
		{

		}

		public function testAfterUpdate()
		{

		}

		public function testOnUpdateError()
		{

		}

		public function testAfterCopy()
		{

		}

		public function testAfterSort()
		{

		}

		public function testRenderHelp()
		{

		}

		public function testCreateHeader()
		{

		}

		public function testCreatePage()
		{

		}

		public function testPrefsSaveTrigger()
		{

		}

		public function testPrefsObserver()
		{

		}

		public function testPrefsPage()
		{

		}

		public function testGetPrimaryName()
		{

		}

		public function testGetTableName()
		{

		}

		public function testGetValidationRules()
		{

		}

		public function testGetDataFields()
		{

		}

		public function testSetDropDown()
		{

		}

		public function test_setModel()
		{

		}

		public function test_setTreeModel()
		{

		}

		public function test_setUI()
		{

		}*/
	}
