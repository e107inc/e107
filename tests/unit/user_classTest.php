<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class user_classTest extends \Codeception\Test\Unit
	{
		protected $uc;


		protected function _before()
		{
			try
			{
				$this->uc = $this->make('user_class');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load user_class object");
			}
		}

/*
		public function testGetFixedClassDescription()
		{

		}

		public function testGet_all_user_classes()
		{

		}

		public function testUc_get_classname()
		{

		}

		public function testUc_required_class_list()
		{

		}

		public function testGetIdentifier()
		{

		}

		public function testGetID()
		{

		}
*/
		/**
		 * @todo
		 */
		public function testUcAdd()
		{

		}
/*
		public function testUc_get_classlist()
		{

		}

		public function testCheckbox()
		{

		}

		public function testGet_users_in_class()
		{

		}

		public function testGet_editable_classes()
		{

		}

		public function testCheckbox_desc()
		{

		}

		public function testIsEditableClass()
		{

		}

		public function testGetClassList()
		{

		}

		public function testGetClassFromKey()
		{

		}

		public function testStripFixedClasses()
		{

		}

		public function testUc_get_classdescription()
		{

		}

		public function testUcRemove()
		{

		}

		public function testReadTree()
		{

		}

		public function testIsAdmin()
		{

		}

		public function testUc_get_classicon()
		{

		}

		public function testClearCache()
		{

		}

		public function testGetDescription()
		{

		}

		public function testUc_dropdown()
		{

		}

		public function testUc_checkboxes()
		{

		}

		public function testNormalise_classes()
		{

		}

		public function testSelect()
		{

		}

		public function testMergeClassLists()
		{

		}

		public function testGetName()
		{

		}

		public function testUcGetClassIDFromName()
		{

		}

		public function testVetted_tree()
		{

		}
*/


	}
