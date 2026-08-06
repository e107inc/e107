<?php


class e107_eventTest extends \Codeception\Test\Unit
{

	/** @var e107_event */
	protected $ev;

	/** @var string[] plugins this test installed, to be given back in _after() */
	private $installedPlugins = [];

	/** @var string */
	private $includeFile = '';

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

	/**
	 * Install a plugin, and register its event handlers, for the length of one
	 * test.
	 *
	 * e107Test::testUrl() builds its assertions by walking every route in
	 * e107::getAddonConfig('e_url'), and _blank ships two of them, so leaving
	 * it installed changes how much that test covers. Recording the install
	 * here, rather than pairing it with an uninstall at the end of the test
	 * body, gives the plugin back when the test fails part way through too.
	 *
	 * @param string $plugin plugin folder name
	 * @return void
	 */
	private function havePluginInstalled($plugin)
	{
		e107::getPlugin()->install($plugin);
		$this->installedPlugins[] = $plugin;

		e107::getEvent()->init();
	}

	protected function _after()
	{
		if($this->includeFile !== '' && file_exists($this->includeFile))
		{
			unlink($this->includeFile);
		}

		$this->includeFile = '';
		unset($GLOBALS['e107help_event_include_ran']);

		if(empty($this->installedPlugins))
		{
			return;
		}

		foreach($this->installedPlugins as $plugin)
		{
			e107::getPlugin()->uninstall($plugin);
		}

		$this->installedPlugins = [];
		e107::getEvent()->init();
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

		$this->havePluginInstalled('_blank');

		$result = e107::getEvent()->trigger('_blank_custom_class', ['foo'=>'bar']);
		$expected = 'Blocking more triggers of: _blank_custom_class {"foo":"bar"}'; // @see e107_plugins/_blank/e_event.php
		$this::assertSame($expected, $result);

	}

	public function testTriggerStatic()
	{
		$this->havePluginInstalled('_blank');

		$result = e107::getEvent()->trigger('_blank_static_event', ['foo'=>'bar']);
		$expected = 'error in event: _blank_static_event'; // @see e107_plugins/_blank/e_event.php
		$this::assertSame($expected, $result);

	}

	/**
	 * register() appends to functions[] every time but to includes[] only when
	 * an include was given, while trigger() walks the two in lockstep by index.
	 * One registration without an include therefore hands the next callback's
	 * include to the callback in front of it.
	 */
	public function testRegisterKeepsEachIncludeWithItsOwnCallback()
	{
		// A distinct name per run: e107_include_once() is include_once, so a
		// path already loaded in this process would never run its body again.
		$this->includeFile = codecept_output_dir().'e107help_event_'.uniqid().'.php';
		file_put_contents($this->includeFile, '<?php $GLOBALS[\'e107help_event_include_ran\'] = true;');

		$event = new e107_event();
		$event->register('e107help_probe', 'e107help_event_halt');
		$event->register('e107help_probe', 'e107help_event_never', $this->includeFile);

		$ret = $event->trigger('e107help_probe');

		self::assertSame('halted', $ret, 'the first callback halts the chain by returning a value');
		self::assertArrayNotHasKey(
			'e107help_event_include_ran',
			$GLOBALS,
			'the second callback never ran, so its include must not have been loaded'
		);

		$event = new e107_event();
		$event->register('e107help_probe', 'e107help_event_halt');
		$event->register('e107help_probe', 'e107help_event_never', $this->includeFile);

		self::assertSame(
			array(1 => $this->includeFile),
			$event->includes['e107help_probe'],
			'the include belongs to the callback registered with it, which is the second one'
		);
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

if(!function_exists('e107help_event_halt'))
{
	function e107help_event_halt($data, $eventname)
	{
		return 'halted';
	}
}

if(!function_exists('e107help_event_never'))
{
	function e107help_event_never($data, $eventname)
	{
		return null;
	}
}
