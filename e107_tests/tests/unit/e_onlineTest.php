<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 1/31/2019
	 * Time: 1:50 PM
	 */


	class e_onlineTest extends \Codeception\Test\Unit
	{
		/** @var e_online */
		private $on;

		protected function _before()
		{
		try
		{
			$this->on = $this->make('e_online');
			$this->on->__construct();
		}
		catch (Exception $e)
		{
			$this->assertTrue(false, "Couldn't load e_online object");
		}



		}


		/**
		 * FIXME: This test has no assertions!
		 */
		/*
		public function testGoOnline()
		{
			$this->on->goOnline(true, true);

			$this->on->goOnline(false, false);
		}
		*/

		public function testIsBot()
		{

		}

		public function testGuestList()
		{

		}

		public function testUserList()
		{

		}
	}
