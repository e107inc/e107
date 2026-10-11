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
 * A store that ends nothing and records every claim it is asked for, for {@see SoleSessionTest}.
 */
class RecordingSoleSessionStore implements SoleSessionStoreInterface
{
	/** @var array[] array($userId, $sessionId) per claim, in order */
	public $claims = array();

	public function claim($userId, $sessionId)
	{
		$this->claims[] = array($userId, $sessionId);

		return true;
	}

	public function canClaim()
	{
		return true;
	}
}
