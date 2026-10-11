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
 * The account the running session is signed in as, read from the sign-in token login leaves in it.
 */
final class SessionSignIn
{
	/**
	 * @var string
	 */
	private $key;

	/**
	 * @param string $key $_SESSION key holding the signed-in account's "<user_id>.<token>"
	 */
	public function __construct($key)
	{
		$this->key = (string) $key;
	}

	/**
	 * @return int the signed-in account, 0 for a guest
	 */
	public function accountId()
	{
		$token = isset($_SESSION[$this->key]) && is_string($_SESSION[$this->key]) ? $_SESSION[$this->key] : '';
		$userId = strstr($token, '.', true);

		return (false !== $userId && ctype_digit($userId)) ? (int) $userId : 0;
	}
}
