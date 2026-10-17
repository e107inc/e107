<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * Reads a javascript: address the way a browser does before it runs the script.
 */
trait JavascriptAddresses
{
	/**
	 * @param string $address
	 * @return string the script a browser runs for a javascript: address, which it percent-decodes first
	 */
	protected static function scriptOf($address)
	{
		self::assertSame(0, strpos($address, 'javascript:'), 'not a javascript: address: '.$address);

		return rawurldecode((string) substr($address, strlen('javascript:')));
	}
}
