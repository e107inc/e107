<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;

use RuntimeException;
use SessionHandlerInterface;

/**
 * e107 Session Handler
 */
abstract class BaseSessionHandler implements SessionHandlerInterface
{
	/**
	 * Replace the global session handler with this session handler after cleanly ending any existing session
	 * @return void
	 */
	public function activate()
	{
		if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
		$success = session_set_save_handler($this) && session_start();
		if (!$success)
		{
			throw new RuntimeException(
				"Failed to activate session handler " . static::class . " with no exception thrown"
			);
		}
	}
}