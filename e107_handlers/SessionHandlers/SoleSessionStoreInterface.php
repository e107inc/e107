<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SessionHandlers;

/**
 * Session storage that can end an account's other sessions, which is what "Disallow multiple logins" asks of it.
 */
interface SoleSessionStoreInterface
{
	/**
	 * Make $sessionId the account's only session, ending any other this store holds for it; call while that session is running.
	 *
	 * @param int $userId
	 * @param string $sessionId
	 * @return bool whether another session of the account was ended
	 */
	public function claim($userId, $sessionId);

	/**
	 * @return bool whether this store can end an account's other sessions on this host at all
	 */
	public function canClaim();
}
