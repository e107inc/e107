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

use e107\Database\ConnectionInterface;

/**
 * The sessions each account is signed in with, one record per session, whichever store keeps the sessions themselves.
 */
class AccountSessions
{
	const TABLE = 'user_session';

	/**
	 * Seconds a record's last-seen time may trail its session's latest request.
	 */
	const SEEN_INTERVAL = 300;

	/** @var ConnectionInterface */
	private $db;

	/** @var int */
	private $lifetime;

	/** @var string */
	private $ip;

	/** @var string */
	private $userAgent;

	/**
	 * @param ConnectionInterface $db
	 * @param int $lifetime seconds an idle session lives, after which its record is no longer one of the account's sessions
	 * @param string $ip the address this request comes from, as {@see \eIPHandler::ipEncode()} stores it
	 * @param string $userAgent the browser this request comes from
	 */
	public function __construct(ConnectionInterface $db, $lifetime, $ip, $userAgent)
	{
		$this->db = $db;
		$this->lifetime = max(0, (int) $lifetime);
		$this->ip = (string) $ip;
		$this->userAgent = substr((string) $userAgent, 0, 255);
	}

	/**
	 * @param int $accountId
	 * @return string the handle of a new record for a session signing in now, '' when it could not be stored
	 */
	public function begin($accountId)
	{
		return $this->store($accountId, time());
	}

	/**
	 * @param int $accountId
	 * @return string the handle of a new record for a session that signed in before records were kept, '' when it could not be stored
	 */
	public function adopt($accountId)
	{
		return $this->store($accountId, 0);
	}

	/**
	 * Notes that the session was seen, at most once per {@see AccountSessions::SEEN_INTERVAL}.
	 *
	 * @param int $accountId
	 * @param string $handle
	 * @return bool FALSE only when the record is gone, so the session has been ended; TRUE when it could not be looked up
	 */
	public function confirm($accountId, $handle)
	{
		if(false === $this->db->createQueryBuilder()
			->select('user_session_seen')
			->from(self::TABLE)
			->where('user_session_handle', (string) $handle)
			->where('user_session_user', (int) $accountId)
			->execute())
		{
			return true;
		}

		$record = $this->db->fetch();

		if(!is_array($record))
		{
			return false;
		}

		if((int) $record['user_session_seen'] < time() - self::SEEN_INTERVAL)
		{
			$this->db->createQueryBuilder()->update(self::TABLE)
				->set('user_session_seen', time())
				->set('user_session_ip', $this->ip)
				->where('user_session_handle', (string) $handle)
				->execute();
		}

		return true;
	}

	/**
	 * @param int $accountId
	 * @return AccountSession[] the account's sessions that may still be alive, the most recently seen first
	 */
	public function sessionsOf($accountId)
	{
		return $this->sessions($this->db->createQueryBuilder()
			->select('*')
			->from(self::TABLE)
			->where('user_session_user', (int) $accountId)
			->where('user_session_seen', '>=', $this->liveSince())
			->orderBy('user_session_seen', 'DESC')
			->fetchAll());
	}

	/**
	 * @param int $accountId
	 * @param string $handle
	 * @return bool TRUE when that account had a session by that handle, now ended
	 */
	public function end($accountId, $handle)
	{
		return 0 < (int) $this->db->createQueryBuilder()->delete(self::TABLE)
			->where('user_session_handle', (string) $handle)
			->where('user_session_user', (int) $accountId)
			->execute();
	}

	/**
	 * @param int $accountId
	 * @param string $keepHandle '' to end them all
	 * @return AccountSession[] the sessions ended
	 */
	public function endOthers($accountId, $keepHandle)
	{
		$others = $this->sessions($this->db->createQueryBuilder()
			->select('*')
			->from(self::TABLE)
			->where('user_session_user', (int) $accountId)
			->where('user_session_handle', '<>', (string) $keepHandle)
			->fetchAll());

		if(array() === $others)
		{
			return array();
		}

		$this->db->createQueryBuilder()->delete(self::TABLE)
			->where('user_session_user', (int) $accountId)
			->whereIn('user_session_handle', array_map(function (AccountSession $session) { return $session->handle(); }, $others))
			->execute();

		return $others;
	}

	/**
	 * Forgets the records of sessions idle for longer than a session lives.
	 *
	 * @return int how many were forgotten
	 */
	public function collect()
	{
		return (int) $this->db->createQueryBuilder()->delete(self::TABLE)
			->where('user_session_seen', '<', $this->liveSince())
			->execute();
	}

	/**
	 * @param int $accountId
	 * @param int $startedAt
	 * @return string
	 */
	private function store($accountId, $startedAt)
	{
		$handle = bin2hex(random_bytes(16));

		$stored = $this->db->createQueryBuilder()->insert(self::TABLE)->values(array(
			'user_session_handle'  => $handle,
			'user_session_user'    => (int) $accountId,
			'user_session_started' => (int) $startedAt,
			'user_session_seen'    => time(),
			'user_session_ip'      => $this->ip,
			'user_session_agent'   => $this->userAgent,
		))->execute();

		return $stored ? $handle : '';
	}

	/**
	 * @param array[] $records
	 * @return AccountSession[]
	 */
	private function sessions(array $records)
	{
		$sessions = array();

		foreach($records as $record)
		{
			$sessions[] = new AccountSession(
				$record['user_session_handle'],
				$record['user_session_user'],
				$record['user_session_started'],
				$record['user_session_seen'],
				$record['user_session_ip'],
				$record['user_session_agent']
			);
		}

		return $sessions;
	}

	/**
	 * @return int the oldest last-seen time a live session's record can have
	 */
	private function liveSince()
	{
		return time() - $this->lifetime - self::SEEN_INTERVAL;
	}
}
