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
 * Holds an account to the session it signed in with last, through a store that can end the others.
 */
final class SoleSession
{
	/**
	 * @var SoleSessionStoreInterface
	 */
	private $store;

	/**
	 * @var SessionSignIn
	 */
	private $signIn;

	/**
	 * @var callable
	 */
	private $isEnabled;

	/**
	 * @var string $_SESSION key naming the account the running session was claimed for
	 */
	private $claimedKey = 'e107_sole_session';

	/**
	 * @param SoleSessionStoreInterface $store
	 * @param SessionSignIn $signIn
	 * @param callable $isEnabled whether the site disallows multiple logins, asked at each claim
	 */
	public function __construct(SoleSessionStoreInterface $store, SessionSignIn $signIn, callable $isEnabled)
	{
		$this->store = $store;
		$this->signIn = $signIn;
		$this->isEnabled = $isEnabled;
	}

	/**
	 * Make the running session the account's only one, ending the others.
	 *
	 * @param int $userId
	 * @return bool whether another session of the account was ended
	 */
	public function claim($userId)
	{
		if(!$this->applies())
		{
			return false;
		}

		$_SESSION[$this->claimedKey] = (int) $userId;

		return $this->store->claim((int) $userId, session_id());
	}

	/**
	 * Claim the running session again under the id it has now, if it was claimed for the account still signed in; for after the session is given a new id.
	 *
	 * @return bool whether another session of the account was ended
	 */
	public function renew()
	{
		$userId = isset($_SESSION[$this->claimedKey]) ? (int) $_SESSION[$this->claimedKey] : 0;

		if(!$this->applies() || $userId < 1 || $userId !== $this->signIn->accountId())
		{
			return false;
		}

		return $this->store->claim($userId, session_id());
	}

	/**
	 * @return bool whether multiple logins are disallowed and a session is running to claim
	 */
	private function applies()
	{
		return PHP_SESSION_ACTIVE === session_status() && '' !== session_id() && call_user_func($this->isEnabled);
	}
}
