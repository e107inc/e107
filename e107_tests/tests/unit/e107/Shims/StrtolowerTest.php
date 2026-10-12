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

class StrtolowerTest extends \Test\Unit
{
	public function testStrtolowerDefault()
	{
		$this->runStrtolowerImplementation(array(InternalShims::class, 'strtolower'));
	}

	public function testStrtolowerDefaultLegacy()
	{
		$this->runStrtolowerImplementation(array(\eShims::class, 'strtolower'));
	}

	public function testStrtolowerAlt()
	{
		$this->runStrtolowerImplementation(array(InternalShims::class, 'strtolower_alt'));
	}

	protected function runStrtolowerImplementation($implementation)
	{
		$this->assertSame('javascript:alert(1)', call_user_func($implementation, 'JAVASCRIPT:alert(1)'));
		$this->assertSame("caf\xC3\x89 \xC4\xB0", call_user_func($implementation, "CAF\xC3\x89 \xC4\xB0"));
		$this->assertSame('', call_user_func($implementation, ''));

		$previous = setlocale(LC_CTYPE, 0);

		if (setlocale(LC_CTYPE, 'tr_TR.UTF-8', 'tr_TR.utf8') === false)
		{
			$this->markTestSkipped('No Turkish locale is installed.');
		}

		try
		{
			$this->assertSame('istanbul javascript:', call_user_func($implementation, 'ISTANBUL JAVASCRIPT:'));
		}
		finally
		{
			setlocale(LC_CTYPE, $previous);
		}
	}
}
