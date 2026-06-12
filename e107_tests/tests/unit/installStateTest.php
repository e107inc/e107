<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Regression coverage for GHSA-c8h6-wpj3-4cr8.
 *
 * The installer carries its wizard state across requests in a client-supplied
 * hidden field. Decoding that field must never reconstruct a PHP object, or a
 * crafted POP chain (e.g. GuzzleHttp\Psr7\PumpStream) yields unauthenticated
 * RCE. These tests pin the codec's behaviour: JSON in, arrays/scalars out,
 * objects impossible.
 */
class installStateTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		// Boots e107, which file-scope includes the vendor autoloader so the
		// gadget class below is available, then loads the codec under test.
		e107::getInstance();
		require_once(e_HANDLER . 'install_state.php');
	}

	/**
	 * The exact GHSA-c8h6-wpj3-4cr8 payload (a serialized PumpStream whose
	 * __toString would invoke an attacker-chosen callable) must not survive
	 * decoding as a live object.
	 */
	public function testDecodeRejectsObjectInjectionPayload()
	{
		$payload = $this->pumpStreamPayload('var_dump');

		$state = install_state_decode($payload);

		// base64(serialize(object)) is not JSON, so it is rejected wholesale.
		$this->assertSame(array(), $state,
			'install_state_decode must reject serialized (non-JSON) input outright');
	}

	/**
	 * Even if a payload were smuggled into a deeper position, no decode path may
	 * ever return an object anywhere in the structure.
	 */
	public function testDecodeNeverReturnsObjects()
	{
		$payload = $this->pumpStreamPayload('phpinfo');
		$state = install_state_decode($payload);

		array_walk_recursive($state, function ($value) {
			$this->assertFalse(is_object($value), 'decoded state must not contain objects');
		});
		$this->assertSame(array(), $state);
	}

	/**
	 * Legitimate scalar wizard state round-trips intact, including passwords with
	 * quotes and backslashes that JSON must escape.
	 */
	public function testRoundTripPreservesScalarState()
	{
		$state = array(
			'mysql'    => array('server' => 'localhost', 'user' => 'u', 'password' => 'p"x\\y/z', 'db' => 'd', 'prefix' => 'e107_'),
			'language' => 'English',
			'admin'    => array('user' => 'admin', 'email' => 'a@b.c'),
			'paths'    => array('hash' => '0123456789'),
		);

		$this->assertSame($state, install_state_decode(install_state_encode($state)));
	}

	/**
	 * Garbage, empty input and legacy base64(serialize()) state all decode to an
	 * empty array (restarting the wizard) rather than touching unserialize().
	 */
	public function testNonJsonInputDecodesToEmptyArray()
	{
		$legacySerialized = base64_encode(serialize(array('language' => 'English')));

		$this->assertSame(array(), install_state_decode(''));
		$this->assertSame(array(), install_state_decode('not base64 @@@'));
		$this->assertSame(array(), install_state_decode($legacySerialized));
	}

	/**
	 * Build the GHSA-c8h6-wpj3-4cr8 POP chain against the bundled psr7 classes.
	 *
	 * @param string $sink callable the PumpStream would invoke
	 * @return string base64(serialize(['paths' => ['hash' => PumpStream]]))
	 */
	private function pumpStreamPayload($sink)
	{
		$bs = new ReflectionClass('GuzzleHttp\\Psr7\\BufferStream');
		$buffer = $bs->newInstanceWithoutConstructor();
		$p = $bs->getProperty('hwm');    $p->setAccessible(true); $p->setValue($buffer, 16384);
		$p = $bs->getProperty('buffer'); $p->setAccessible(true); $p->setValue($buffer, '');

		$ps = new ReflectionClass('GuzzleHttp\\Psr7\\PumpStream');
		$stream = $ps->newInstanceWithoutConstructor();
		foreach (array('source' => $sink, 'buffer' => $buffer, 'size' => null, 'tellPos' => 0, 'metadata' => array()) as $k => $v)
		{
			$p = $ps->getProperty($k); $p->setAccessible(true); $p->setValue($stream, $v);
		}

		return base64_encode(serialize(array('paths' => array('hash' => $stream))));
	}
}
