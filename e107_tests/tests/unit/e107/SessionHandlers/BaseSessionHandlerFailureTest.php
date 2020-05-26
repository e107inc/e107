<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;

use e107\Factories\SessionHandlerFactory;

function is_in_failure_test()
{
	foreach (debug_backtrace(false) as $line)
	{
		if (isset($line['class']) && $line['class'] == BaseSessionHandlerFailureTest::class)
		{
			return true;
		}
	}
	return false;
}

function session_write_close()
{
	if (is_in_failure_test()) return false;
	return \session_write_close();
}

function session_set_save_handler($sessionHandler)
{
	if (is_in_failure_test()) return false;
	return \session_set_save_handler($sessionHandler);
}

function session_start()
{
	if (is_in_failure_test()) return false;
	return \session_start();
}

class BaseSessionHandlerFailureTest extends \Codeception\Test\Unit
{
	public function testActivateThrowsExceptionIfPhpFunctionReturnsFalse()
	{
		$this->expectException(\RuntimeException::class);

		$sessionHandler = SessionHandlerFactory::make();
		$sessionHandler->activate();
	}
}