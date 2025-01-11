<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107BounceTest extends \Codeception\Test\Unit
	{

		/** @var e107Bounce */
		protected $bnc;

		protected function _before()
		{
		//	define('e107_INIT', true);
			parent::_before();
			global $_E107;

			$_E107['phpunit'] = true;

			require_once(e_HANDLER."bounce_handler.php");
			try
			{
				$this->bnc = $this->make('e107Bounce');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107Bounce object");
			}

		}

		public function testProcess()
		{
			/* FIXME: https://github.com/e107inc/e107/issues/4031
			$path = $icon = codecept_data_dir()."eml/bounced_01.eml";

			$this->bnc->setSource($path);
			$result = $this->bnc->process(false);
			$this->assertEquals("99999999", $result);
			*/
		}

		public function testSetUser_Bounced()
		{

		}

		public function test__construct()
		{

		}

		public function testGetHeader()
		{

		}

		public function testMailRead()
		{

		}




	}
