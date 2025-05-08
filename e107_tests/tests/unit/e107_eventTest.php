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
			$this::fail($e->getMessage());
		}

	}

	public function testTriggered()
	{
		e107::getEvent()->trigger('user_profile_display', ['foo'=>'bar']);

		$result = e107::getEvent()->triggered('user_profile_display');
		$this::assertTrue($result);

		$result = e107::getEvent()->triggered('non_event');
		$this::assertFalse($result);

	}

	public function testTriggerClass()
	{

		e107::getPlugin()->install('_blank');
		e107::getEvent()->init();

		$result = e107::getEvent()->trigger('_blank_custom_class', ['foo'=>'bar']);
		$expected = 'Blocking more triggers of: _blank_custom_class {"foo":"bar"}'; // @see e107_plugins/_blank/e_event.php
		$this::assertSame($expected, $result);

		e107::getPlugin()->uninstall('_blank');
		e107::getEvent()->init();

	}

	public function testTriggerStatic()
	{
		e107::getPlugin()->install('_blank');
		e107::getEvent()->init();

		$result = e107::getEvent()->trigger('_blank_static_event', ['foo'=>'bar']);
		$expected = 'error in event: _blank_static_event'; // @see e107_plugins/_blank/e_event.php
		$this::assertSame($expected, $result);

		e107::getPlugin()->uninstall('_blank');
		e107::getEvent()->init();



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
