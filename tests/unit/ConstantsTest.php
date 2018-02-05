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

	class e107ConstantsTest extends TestCase
	{
		public function teste_BASE()
		{
			// todo
			$res = defined('e_BASE');
			$this->assertTrue($res);
		}
	}
