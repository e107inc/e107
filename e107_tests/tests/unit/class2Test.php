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



		function testLoadClass2()
		{
			require_once(e_BASE."class2.php"); // already loaded but coverage says otherwise.

		}


		function testGetPerms()
		{

			$result = getperms('N', '0');
			$this->assertTrue($result);

			$result = getperms('N', '0.');
			$this->assertTrue($result);

			$result = getperms('U1|U2', '0.');
			$this->assertTrue($result);



			$pid = e107::getDb()->retrieve('plugin', 'plugin_id', "plugin_path = 'gallery'");

			$result = getperms('P', 'P'.$pid);
			$this->assertFalse($result);


			$result = getperms('P', 'P'.$pid, 'http://localhost/e107v2/e107_plugins/gallery/admin_config.php');
			$this->assertTrue($result);


		}



		function testCheckClass()
		{
			// XXX: Should not use some flag just to make tests pass!
			global $_E107;
			$_E107['phpunit'] = true;

			$result = check_class(0, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class('NEWSLETTER', "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('NEWSLETTER', "253,254,250,251,3,0"); // NEWSLETTER = 3
			$this->assertTrue($result);

			$result = check_class('-NEWSLETTER', "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(254, "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class('0', "253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(null, "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('-254', "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class('-254', "253,250,251,0");
			$this->assertTrue($result);

			$result = check_class(-254, "253,250,251,0");
			$this->assertTrue($result);

			$result = check_class(-254, "254,253,250,251,0");
			$this->assertFalse($result);

			$result = check_class(e_UC_NOBODY, "253,254,250,251,0");
			$this->assertFalse($result);

			$result = check_class(e_UC_NEWUSER, "247,253,254,250,251,0");
			$this->assertTrue($result);

			$result = check_class(e_UC_NEWUSER, "253,254,250,251,0");
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
