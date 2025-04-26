<?php

namespace E107\Plugins\_blank\Tests\Unit;
use Codeception\Test\Unit;

/* To use, run these commands from the root directory of e107 in CLI:

	cd e107_tests
	vendor/bin/codecept run unit ../e107_plugins/_blank/tests/unit

	vendor/bin/codecept run unit ../e107_plugins/_blank/tests/unit/_blank_eventTest:testMyfunction

OR with debug options:

	vendor/bin/codecept run unit ../e107_plugins/_blank/tests/unit --steps --debug
*/


class _blank_eventTest extends Unit
{

	/** @var _blank_event */
	protected $ep;

	public function testMyfunction()
	{

		$value = "THIS IS THE BLANK TEST";
		self::assertSame($value, "THIS IS THE BLANK TEST");
	}

	protected function _before()
	{
		require_once(dirname(__FILE__) . '/../../e_event.php');

		try
		{
			$this->ep = $this->make('_blank_event');
		}

		catch(Exception $e)
		{
			self::fail($e->getMessage());
		}

	}


}
