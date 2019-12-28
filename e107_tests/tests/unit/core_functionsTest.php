<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class core_functionsTest extends \Codeception\Test\Unit
	{
		protected function _before()
		{



		}





		function testAsortbyindex()
		{
			$array = array(
				1 => array ( 0 => '/e107v2/e107_admin/banlist.php',    1 => 'Banlist',    2 => 'Ban visitors' ),
				2 => array ( 0 => '/e107v2/e107_admin/updateadmin.php', 1 => 'Admin password',  2 => 'Change your password' ),
				3 => array ( 0 => '/e107v2/e107_admin/administrator.php', 1 => 'Administrators', 2 => 'Add/delete site administrators' ),
				4 => array ( 0 => '/e107v2/e107_admin/cache.php', 1 => 'Cache', 2 => 'Set cache status')
			);
			
			$expected = array (
		        0 => array ( 0 => '/e107v2/e107_admin/updateadmin.php', 1 => 'Admin password', 2 => 'Change your password'),
				1 => array ( 0 => '/e107v2/e107_admin/administrator.php',  1 => 'Administrators',  2 => 'Add/delete site administrators'),
		        2 => array ( 0 => '/e107v2/e107_admin/banlist.php',  1 => 'Banlist',  2 => 'Ban visitors'	),
		        3 => array ( 0 => '/e107v2/e107_admin/cache.php',  1 => 'Cache',  2 => 'Set cache status'	),
			);

			$result = asortbyindex($array,1);

			$this->assertEquals($expected,$result);





		}






	}
