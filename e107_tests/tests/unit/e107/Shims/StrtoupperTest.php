<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

class StrtoupperTest extends \Test\Unit
{
	public function testStrtoupperDefault()
	{
		$this->runStrtoupperImplementation(array(InternalShims::class, 'strtoupper'));
	}

	public function testStrtoupperDefaultLegacy()
	{
		$this->runStrtoupperImplementation(array(\eShims::class, 'strtoupper'));
	}

	public function testStrtoupperAlt()
	{
		$this->runStrtoupperImplementation(array(InternalShims::class, 'strtoupper_alt'));
	}

	protected function runStrtoupperImplementation($implementation)
	{
		$this->assertSame('JAVASCRIPT:ALERT(1)', call_user_func($implementation, 'javascript:alert(1)'));
		$this->assertSame("CAF\xC3\xA9 \xC4\xB1", call_user_func($implementation, "caf\xC3\xA9 \xC4\xB1"));
		$this->assertSame('', call_user_func($implementation, ''));

		$previous = setlocale(LC_CTYPE, 0);

		if (setlocale(LC_CTYPE, 'tr_TR.UTF-8', 'tr_TR.utf8') === false)
		{
			$this->markTestSkipped('No Turkish locale is installed.');
		}

		try
		{
			$this->assertSame('ISTANBUL JAVASCRIPT:', call_user_func($implementation, 'istanbul javascript:'));
		}
		finally
		{
			setlocale(LC_CTYPE, $previous);
		}
	}
}
