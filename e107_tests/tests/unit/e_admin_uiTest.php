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
			}
		}

	}
