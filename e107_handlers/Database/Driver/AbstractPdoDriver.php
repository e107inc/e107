<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

require_once(__DIR__.'/PdoDriverInterface.php');

/**
 * What every PDO-backed engine does alike: error numbers read from the exception.
 */
abstract class AbstractPdoDriver implements PdoDriverInterface
{
	/**
	 * Before PHP 7.3.22 and 7.4.10 (php-src bug #64705) a connection failure sets no errorInfo and puts the errno
	 * in the exception code as an int, while a SQLSTATE always arrives there as a string, so the test is is_int()
	 * and never is_numeric(): SQLSTATE values such as '23000' are all digits.
	 *
	 * @inheritDoc
	 */
	public function errorNumber($exception)
	{
		if(isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] !== 0)
		{
			return (int) $exception->errorInfo[1];
		}

		$code = $exception->getCode();

		return (is_int($code) && $code !== 0) ? $code : -1;
	}
}
