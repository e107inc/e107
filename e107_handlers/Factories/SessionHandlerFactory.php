<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\Factories;

use e107;
use e107\SessionHandlers\DatabaseSessionHandler;
use e107\SessionHandlers\FilesSessionHandler;
use e107\SessionHandlers\NonblockingFilesSessionHandler;

/**
 * Factory that makes new session handlers
 * @link e107\SessionHandlers\BaseSessionHandler
 */
class SessionHandlerFactory extends BaseFactory
{
	public static function getImplementations()
	{
		return [
			DatabaseSessionHandler::class,
			FilesSessionHandler::class,
			NonblockingFilesSessionHandler::class,
		];
	}

	public static function getDefaultImplementation()
	{
		$session_handler = e107::getConfig()->getPref('session_handler');
		if (in_array($session_handler, static::getImplementations())) return $session_handler;
		return DatabaseSessionHandler::class;
	}

	/**
	 * {@inheritDoc}
	 * @return e107\SessionHandlers\BaseSessionHandler
	 */
	public static function make($type = null, ...$args)
	{
		return parent::make($type, $args);
	}
}
