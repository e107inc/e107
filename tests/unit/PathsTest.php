<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use PHPUnit\Framework\TestCase;

	class e107PathsTest extends TestCase
	{
		public function testPathToClass2()
		{
			$res = file_exists(APP_PATH."/class2.php");

			$this->assertTrue($res);

		}
	}
