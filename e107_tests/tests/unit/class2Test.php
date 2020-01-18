<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class class2Test extends \Codeception\Test\Unit
	{

		/** @var ${TESTED_NAME} */
		protected $ep;

		protected function _before()
		{

			/*try
			{
				$this->ep = $this->make('${TESTED_NAME}');
			} catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load ${TESTED_NAME} object");
			}*/

		}


		function testGetPerms()
		{

			$result = getperms('N', '0');
			$this->assertTrue($result);

			$result = getperms('N', '0.');
			$this->assertTrue($result);

			$result = getperms('U1|U2', '0.');
			$this->assertTrue($result);

		}



		function testCheckClass()
		{
			// XXX: Should not use some flag just to make tests pass!
			global $_E107;
			$_E107['phpunit'] = true;

			$result = check_class(0, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(254, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class('0', "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(null, "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class(e_UC_NOBODY, "253,254,250,251,0");
			$this->assertFalse($result);

			unset($_E107['phpunit']);
		}



		function testCheckEmail()
		{
			$result = check_email("test@somewhere.com"); // good email.
			$this->assertEquals('test@somewhere.com', $result);

			$result = check_email("test@somewherecom"); // Missing .
			$this->assertFalse($result);

			$result = check_email("test@somewhere.technology"); // New TLDs
			$this->assertEquals('test@somewhere.technology',$result);

		}





	}
