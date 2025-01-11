<?php


class e107_eventTest extends \Codeception\Test\Unit
{

	/** @var e107_event */
	protected $ev;

	protected function _before()
	{
		try
		{
			$this->ev = $this->make('e107_event');
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

	}

	public function testTriggered()
	{
		e107::getEvent()->trigger('user_profile_display', ['foo'=>'bar']);

		$result = e107::getEvent()->triggered('user_profile_display');
		$this->assertTrue($result);

		$result = e107::getEvent()->triggered('non_event');
		$this->assertFalse($result);

	}


/*
	public function testTrigger()
	{
	}

	public function testOldCoreList()
	{

	}

	public function testDebug()
	{

	}

	public function testInit()
	{

	}

	public function testTriggerAdminEvent()
	{

	}

	public function testCoreList()
	{

	}

	public function test__construct()
	{

	}

	public function testRegister()
	{

	}

	public function testTriggerHook()
	{

	}
*/



}
