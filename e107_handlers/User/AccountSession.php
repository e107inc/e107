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
 * One session an account is signed in with, as {@see AccountSessions} records it.
 */
final class AccountSession
{
	/** @var string */
	private $handle;

	/** @var int */
	private $accountId;

	/** @var int */
	private $startedAt;

	/** @var int */
	private $lastSeenAt;

	/** @var string */
	private $ip;

	/** @var string */
	private $userAgent;

	/**
	 * @param string $handle
	 * @param int $accountId
	 * @param int $startedAt
	 * @param int $lastSeenAt
	 * @param string $ip
	 * @param string $userAgent
	 */
	public function __construct($handle, $accountId, $startedAt, $lastSeenAt, $ip, $userAgent)
	{
		$this->handle = (string) $handle;
		$this->accountId = (int) $accountId;
		$this->startedAt = (int) $startedAt;
		$this->lastSeenAt = (int) $lastSeenAt;
		$this->ip = (string) $ip;
		$this->userAgent = (string) $userAgent;
	}

	/**
	 * @return string
	 *   What names this session to {@see AccountSessions::end()}. It signs nobody in.
	 */
	public function handle()
	{
		return $this->handle;
	}

	/**
	 * @return int
	 */
	public function accountId()
	{
		return $this->accountId;
	}

	/**
	 * @return int
	 *   When the session signed in; 0 when it signed in before records were kept.
	 */
	public function startedAt()
	{
		return $this->startedAt;
	}

	/**
	 * @return int
	 *   When the session was last seen, up to {@see AccountSessions::SEEN_INTERVAL} early.
	 */
	public function lastSeenAt()
	{
		return $this->lastSeenAt;
	}

	/**
	 * @return string
	 *   The address it was last seen from, as {@see \eIPHandler::ipEncode()} stores it.
	 */
	public function ip()
	{
		return $this->ip;
	}

	/**
	 * @return string
	 *   The browser's user agent when the session signed in.
	 */
	public function userAgent()
	{
		return $this->userAgent;
	}
}
