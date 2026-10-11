<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\User;

/**
 * What a signed-in session keeps under the cookie_name key: "<account id>.<md5 of the stored password hash>.<handle of its {@see AccountSessions} record>".
 */
final class SignInToken
{
	/** @var int */
	private $accountId;

	/** @var string */
	private $passwordDigest;

	/** @var string */
	private $handle;

	/**
	 * @param int $accountId
	 * @param string $passwordDigest
	 * @param string $handle '' for none
	 */
	public function __construct($accountId, $passwordDigest, $handle = '')
	{
		$this->accountId = (int) $accountId;
		$this->passwordDigest = (string) $passwordDigest;
		$this->handle = (string) $handle;
	}

	/**
	 * @param string $token as a session keeps it
	 * @return SignInToken
	 */
	public static function parse($token)
	{
		list($accountId, $passwordDigest, $handle) = array_pad(explode('.', (string) $token, 3), 3, '');

		return new self($accountId, $passwordDigest, $handle);
	}

	/**
	 * @return int
	 */
	public function accountId()
	{
		return $this->accountId;
	}

	/**
	 * @return string
	 */
	public function passwordDigest()
	{
		return $this->passwordDigest;
	}

	/**
	 * @return string '' when the session has no record
	 */
	public function handle()
	{
		return $this->handle;
	}

	/**
	 * @param string $handle
	 * @return SignInToken
	 */
	public function withHandle($handle)
	{
		return new self($this->accountId, $this->passwordDigest, $handle);
	}

	/**
	 * @return string as a session keeps it
	 */
	public function __toString()
	{
		return $this->accountId.'.'.$this->passwordDigest.('' === $this->handle ? '' : '.'.$this->handle);
	}
}
