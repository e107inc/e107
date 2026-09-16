<?php

namespace Test;

/**
 * The addresses the suite's subprocesses arrive from and answer on, allocated once per process.
 *
 * Keep this in PHP 5.6 syntax: it is part of the shipping-adjacent test tree
 * that the downgrade pipeline walks.
 */
class Addresses
{
	/** TEST-NET-2 (RFC 5737): the range the suite's subprocesses present themselves from. */
	const VISITOR_PREFIX = '198.51.100.';

	/** The hosts {@see Addresses::VISITOR_PREFIX} holds, so the cycle cannot walk out of the range it names. */
	const VISITOR_HOSTS = 254;

	/** The address the site answers on, which e107_handlers/mail.php reads unguarded inside a block it gates on the visitor's. */
	const SERVER = '127.0.0.1';

	/** @var int addresses handed out so far in this process */
	private static $seated = 0;

	/**
	 * Hands out the next child's own address, so e107's per-address flood control reads a run as many visitors rather than one.
	 *
	 * @return string
	 */
	public static function nextVisitor()
	{
		return self::VISITOR_PREFIX.((self::$seated++ % self::VISITOR_HOSTS) + 1);
	}
}
