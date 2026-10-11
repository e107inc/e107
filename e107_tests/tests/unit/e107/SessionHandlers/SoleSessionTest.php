<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SessionHandlers;

/**
 * What the policy asks of its store, in a child whose session is running; the store records rather than ends.
 */
class SoleSessionTest extends \Test\Unit
{
	/** A child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable. */
	const BUFFERED = '-d output_buffering=On';

	const ID = 'solesessiontest0123456789abcdef';

	const AUTH_KEY = 'solesessiontestauth';

	/**
	 * @param string $php run with the session already restarted as self::ID, $sole and $store in scope; it prints the JSON it wants back
	 * @return mixed
	 */
	private function inChild($php)
	{
		$code = "session_write_close(); ini_set('session.use_cookies', '0'); session_id('".self::ID."'); session_start(); ";
		$code .= "require_once(".var_export(__DIR__.'/recording_sole_session_store.php', true)."); ";
		$code .= "\$store = new \\e107\\SessionHandlers\\RecordingSoleSessionStore(); \$sole = new \\e107\\SessionHandlers\\SoleSession(\$store, new \\e107\\SessionHandlers\\SessionSignIn('".self::AUTH_KEY."'), function() { return empty(\$GLOBALS['multiple_logins_allowed']); }); ";
		$code .= $php;
		$code .= " session_destroy();";

		list($output) = $this->runInBootedCli($code, self::BUFFERED);
		$printed = implode("\n", $output);

		$this->assertSame(1, preg_match('/@@(.*)@@/', $printed, $matches), $printed);

		return json_decode($matches[1], true);
	}

	public function testAClaimHandsTheStoreTheAccountAndTheRunningSession()
	{
		$result = $this->inChild("\$ended = \$sole->claim('77'); fwrite(STDERR, '@@'.json_encode(array(\$ended, \$store->claims)).'@@');");

		$this->assertSame(array(true, array(array(77, self::ID))), $result);
	}

	public function testASessionNeverClaimedIsNotClaimedWhenItsIdChanges()
	{
		$result = $this->inChild("\$renewed = \$sole->renew(); fwrite(STDERR, '@@'.json_encode(array(\$renewed, \$store->claims)).'@@');");

		$this->assertSame(array(false, array()), $result, 'a session from before the preference was on must not end a newer sign-in');
	}

	public function testAClaimedSessionIsClaimedAgainUnderItsNewId()
	{
		$result = $this->inChild("\$_SESSION['".self::AUTH_KEY."'] = '77.".md5('token')."'; \$sole->claim(77); session_regenerate_id(true); \$sole->renew(); fwrite(STDERR, '@@'.json_encode(array(session_id(), \$store->claims)).'@@');");

		list($newId, $claims) = $result;
		$this->assertNotSame(self::ID, $newId);
		$this->assertSame(array(array(77, self::ID), array(77, $newId)), $claims);
	}

	public function testASessionSignedInToAnotherAccountSinceItsClaimIsNotClaimedAgain()
	{
		$result = $this->inChild("\$_SESSION['".self::AUTH_KEY."'] = '77.".md5('token')."'; \$sole->claim(77); \$_SESSION['".self::AUTH_KEY."'] = '78.".md5('token')."'; \$renewed = \$sole->renew(); fwrite(STDERR, '@@'.json_encode(array(\$renewed, count(\$store->claims))).'@@');");

		$this->assertSame(array(false, 1), $result, 'account 77 must not be handed the session account 78 now holds');
	}

	public function testASessionSignedOutSinceItsClaimIsNotClaimedAgain()
	{
		$result = $this->inChild("\$_SESSION['".self::AUTH_KEY."'] = '77.".md5('token')."'; \$sole->claim(77); unset(\$_SESSION['".self::AUTH_KEY."']); \$renewed = \$sole->renew(); fwrite(STDERR, '@@'.json_encode(array(\$renewed, count(\$store->claims))).'@@');");

		$this->assertSame(array(false, 1), $result);
	}

	public function testNothingIsClaimedWhileMultipleLoginsAreAllowed()
	{
		$result = $this->inChild("\$_SESSION['".self::AUTH_KEY."'] = '77.".md5('token')."'; \$sole->claim(77); \$GLOBALS['multiple_logins_allowed'] = true; \$claimed = \$sole->claim(77); \$renewed = \$sole->renew(); fwrite(STDERR, '@@'.json_encode(array(\$claimed, \$renewed, count(\$store->claims))).'@@');");

		$this->assertSame(array(false, false, 1), $result);
	}

	public function testNothingIsClaimedWithoutARunningSession()
	{
		$result = $this->inChild("session_write_close(); \$ended = \$sole->claim(77); \$renewed = \$sole->renew(); session_start(); fwrite(STDERR, '@@'.json_encode(array(\$ended, \$renewed, \$store->claims)).'@@');");

		$this->assertSame(array(false, false, array()), $result);
	}
}
