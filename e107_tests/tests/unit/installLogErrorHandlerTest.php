<?php
/**
 * Fails while the installer's error handler raises an error of its own, as naming a constant the running PHP deprecates does.
 */
class installLogErrorHandlerTest extends \Test\Unit
{
	/** @var string */
	private static $renamedClass = 'installLog_for_handler_test';

	/**
	 * @beforeClass
	 */
	public static function loadRenamedInstallLog()
	{
		if (class_exists(self::$renamedClass, false))
		{
			return;
		}

		$source = \Test\Tokens::classSource(file_get_contents(APP_PATH . '/install.php'), 'installLog');
		self::assertNotNull($source, 'install.php no longer declares installLog.');

		eval(preg_replace('/\bclass\s+installLog\b/', 'class ' . self::$renamedClass, $source, 1));
	}

	public function testTheShutdownCallRaisesNothing()
	{
		$raised = array();

		set_error_handler(function ($errno, $errstr) use (&$raised)
		{
			$raised[] = $errstr;

			return true;
		});

		try
		{
			call_user_func(array(self::$renamedClass, 'errorHandler'));
		}
		finally
		{
			restore_error_handler();
		}

		$this->assertSame(array(), $raised);
	}
}
