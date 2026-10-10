<?php

namespace Helper;

use Codeception\Module;

/**
 * Lets a test declare that it makes e107 try to reach a host outside the stack, which the suite runner's network boundary refuses like any other attempt.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Outbound extends Module
{
	/** @var string[] what the running test has declared; Extension\OutboundLedger files it with the test and clears it */
	public static $expected = array();

	/**
	 * Declare that this test may make e107 try to reach $host: the attempt is refused and recorded as usual, and does not fail the run.
	 *
	 * @param string $host the name the attempt carries (an HTTP Host header, a TLS server name), or the address it was sent to when it carries none
	 * @return void
	 */
	public function expectOutboundRequest($host)
	{
		self::$expected[] = strtolower($host);
	}
}
