<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Coverage for GHSA-gm6q-rqm6-p9m4.
 *
 * Every token, key, password, nonce and CAPTCHA answer in e107 now draws from
 * e_random. These tests pin the output shapes the consumers depend on, and the
 * fail-closed contract: a missing CSPRNG or a bad argument has to throw rather
 * than return something a caller would mint a secret from.
 *
 * This file is deliberately identical on master and release/v2.3.x. The two
 * branches ship the same public e_random surface (master reaches it through a
 * class_alias onto e107\Security\Random), so the coverage moves between the
 * branches unchanged. Keep it that way.
 */
class e_randomTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		e107::getInstance();
		require_once(e_HANDLER . 'random_handler.php');
	}

	public function testIsAvailable()
	{
		$this->assertTrue(e_random::isAvailable());
	}

	public function testBytesReturnsRequestedLength()
	{
		foreach(array(1, 16, 20, 32, 64) as $length)
		{
			$this->assertSame($length, strlen(e_random::bytes($length)));
		}
	}

	public function testBytesDoesNotRepeat()
	{
		$seen = array();

		for($i = 0; $i < 200; $i++)
		{
			$value = bin2hex(e_random::bytes(16));
			$this->assertArrayNotHasKey($value, $seen);
			$seen[$value] = true;
		}
	}

	/**
	 * $length counts HEX CHARACTERS, not bytes. The consumers pin exact
	 * character counts: 32 for e_user_model::randomKey() and the guest CSRF
	 * cookie, 40 for the CHAP challenge and the cron secret, 64 for the
	 * installer provisioning token and the session CSRF seed. Odd lengths are
	 * included because ceil()/substr() is where an off-by-one would hide.
	 */
	public function testHexShapes()
	{
		foreach(array(1, 31, 32, 40, 63, 64) as $length)
		{
			$value = e_random::hex($length);
			$this->assertSame($length, strlen($value));
			$this->assertSame(1, preg_match('/^[0-9a-f]+$/', $value));
		}
	}

	public function testHexDoesNotRepeat()
	{
		$seen = array();

		for($i = 0; $i < 200; $i++)
		{
			$value = e_random::hex(32);
			$this->assertArrayNotHasKey($value, $seen);
			$seen[$value] = true;
		}
	}

	/**
	 * generateRandomString() draws indexes with e_random::int(0, strlen($set) - 1),
	 * so the range must be inclusive at both ends or characters go missing.
	 */
	public function testIntIsInclusiveAtBothEnds()
	{
		$seen = array();

		for($i = 0; $i < 2000; $i++)
		{
			$value = e_random::int(0, 4);
			$this->assertGreaterThanOrEqual(0, $value);
			$this->assertLessThanOrEqual(4, $value);
			$seen[$value] = true;
		}

		$keys = array_keys($seen);
		sort($keys);

		$this->assertSame(array(0, 1, 2, 3, 4), $keys);
	}

	public function testIntWithEqualBounds()
	{
		$this->assertSame(7, e_random::int(7, 7));
	}

	public function testPickReturnsAMember()
	{
		$values = array('alpha', 'beta', 'gamma');

		for($i = 0; $i < 50; $i++)
		{
			$this->assertContains(e_random::pick($values), $values);
		}
	}

	public function testBytesRejectsZeroLength()
	{
		$this->expectException('e_random_exception');
		e_random::bytes(0);
	}

	public function testHexRejectsZeroLength()
	{
		$this->expectException('e_random_exception');
		e_random::hex(0);
	}

	public function testIntRejectsInvertedRange()
	{
		$this->expectException('e_random_exception');
		e_random::int(5, 1);
	}

	public function testPickRejectsEmptyArray()
	{
		$this->expectException('e_random_exception');
		e_random::pick(array());
	}

	/**
	 * The fail-closed contract, which is the whole point of the class: with no
	 * CSPRNG behind it, every method has to throw rather than hand back
	 * something a caller would mint a secret from.
	 *
	 * The unavailability is injected into a child process with random_bytes()
	 * and random_int() disabled, which is what a hardened php.ini looks like
	 * from inside the class, so the host's own CSPRNG is never touched.
	 */
	public function testEveryAccessorThrowsWithNoCsprngAvailable()
	{
		$output = $this->runProbe(true);

		$this->assertSame('UNAVAILABLE|THREW|THREW|THREW|THREW', $output);
	}

	/**
	 * The control for the test above: the same probe, same child process, with
	 * the CSPRNG left in place. Without this, a probe that failed to load the
	 * handler at all would look like a pass.
	 */
	public function testTheProbeReportsSuccessWhenACsprngIsAvailable()
	{
		$output = $this->runProbe(false);

		$this->assertSame('AVAILABLE|OK|OK|OK|OK', $output);
	}

	/**
	 * @param bool $disabled whether to run with random_bytes()/random_int() disabled
	 * @return string the child process's combined output
	 */
	private function runProbe($disabled)
	{
		if(!function_exists('shell_exec') || !defined('PHP_BINARY') || PHP_BINARY === '')
		{
			$this->markTestSkipped('No usable PHP binary to run a child process with.');
		}

		$probe = 'define("e107_INIT", true);'
			. ' require ' . var_export(e_HANDLER . 'random_handler.php', true) . ';'
			. ' echo e_random::isAvailable() ? "AVAILABLE" : "UNAVAILABLE";'
			. ' foreach(array("bytes" => 16, "hex" => 32, "int" => 0, "pick" => 0) as $method => $arg)'
			. ' {'
			. '     echo "|";'
			. '     try'
			. '     {'
			. '         if($method === "int") { $value = e_random::int(0, 255); }'
			. '         elseif($method === "pick") { $value = e_random::pick(array("a", "b")); }'
			. '         else { $value = e_random::$method($arg); }'
			. '         echo (strlen($value) > 0) ? "OK" : "EMPTY";'
			. '     }'
			. '     catch(Exception $e)'
			. '     {'
			. '         echo "THREW";'
			. '     }'
			. ' }';

		$command = escapeshellarg(PHP_BINARY)
			. ($disabled ? ' -d disable_functions=random_bytes,random_int' : '')
			. ' -r ' . escapeshellarg($probe)
			. ' 2>&1';

		return trim((string) shell_exec($command));
	}
}
