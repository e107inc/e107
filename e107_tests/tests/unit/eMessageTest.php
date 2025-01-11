<?php


	class eMessageTest extends \Codeception\Test\Unit
	{

		/** @var eMessage */
		protected $mes;


		protected function _before()
		{

			try
			{
				$this->mes = e107::getMessage();
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}
/*
		public function testMoveStack()
		{

		}

		public function testAdd()
		{

		}

		public function testGetSession()
		{

		}

		public function testIsType()
		{

		}

		public function testMoveToSession()
		{

		}

		public function testGetAllSession()
		{

		}

		public function testGetSessionHandler()
		{

		}

		public function testReset()
		{

		}

		public function test__construct()
		{

		}

		public function testAddDebug()
		{

		}
*/
		public function testGetAll()
		{
			$this->mes->reset();
			$result = $this->mes->getAll();
			$this->assertEmpty($result);

			$this->mes->addInfo('Info Message');
			$this->mes->addSuccess('Success Message');
			$result = $this->mes->getAll();
			$this->assertArrayHasKey('success', $result);
			$this->assertArrayHasKey('info', $result);


		}
/*
		public function testAddWarning()
		{

		}

		public function testSetSessionId()
		{

		}

		public function testSetUnique()
		{

		}

		public function testRender()
		{

		}

		public function testFormatMessage()
		{

		}

		public function testAddSuccess()
		{

		}

		public function testSetIcon()
		{

		}

		public function testAddError()
		{

		}

		public function testMergeWithSession()
		{

		}
*/
		public function testHasMessage()
		{
			$this->mes->reset();
			$this->mes->reset(false, 'default', true);

			$result = $this->mes->hasMessage();
			$this->assertFalse($result);

			$result= $this->mes->hasMessage(E_MESSAGE_WARNING);
			$this->assertFalse($result);

			$this->mes->addWarning("Warning message");
			$result= $this->mes->hasMessage(E_MESSAGE_WARNING);
			$this->assertTrue($result);

			$result = $this->mes->hasMessage();
			$this->assertTrue($result);

			$result= $this->mes->hasMessage(E_MESSAGE_INFO);
			$this->assertFalse($result);

		}
/*
		public function testGetInstance()
		{

		}

		public function testAddInfo()
		{

		}

		public function testSetTitle()
		{

		}

		public function testGet()
		{

		}

		public function testSetClose()
		{

		}

		public function testResetSession()
		{

		}

		public function testAddSessionStack()
		{

		}

		public function test__call()
		{

		}

		public function testMoveSessionStack()
		{

		}

		public function testAddAuto()
		{

		}

		public function testAddSession()
		{

		}

		public function testAddStack()
		{

		}

		public function testGetTitle()
		{

		}
*/


	}
