<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;


class FilesSessionHandlerTest extends BaseSessionHandlerTest
{
	/**
	 * Different implementation for PHP 5.6 and below because of a PHP bug in {@link SessionHandler}
	 * @link https://wiki.php.net/rfc/session.user.return-value
	 */
	public function testSessionLifecycle()
	{
		if (PHP_MAJOR_VERSION <= 5)
		{
			$this->quirksMode = true;
		}
		return parent::testSessionLifecycle();
	}
}