<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Flood;

use e107\Database\ConnectionInterface;

/**
 * How recently something the site rations happened for one source.
 *
 * A form that sends mail on demand can be held open by a script, and the
 * site-wide {@see \floodprotect} would let one caller close it for everybody
 * else. This rations each source on its own instead: a caller, who can be
 * slowed without the form going away for anyone but them, or the account a
 * mail goes to, which is then sent no more than one per window.
 *
 * Attempts are kept as rows in the temporary table, which the bootstrap
 * already prunes, so nothing accumulates and a source that stops trying is
 * forgotten on its own.
 */
class SourceGate
{
	const TABLE = 'tmp';

	/** @var ConnectionInterface */
	private $db;

	/** @var bool */
	private $enabled;

	/** @var int */
	private $timeout;

	/**
	 * @param ConnectionInterface $db
	 * @param bool $enabled
	 *   Whether this ration applies at all, such as FLOODPROTECT for a caller.
	 * @param int $timeout
	 *   Seconds one source waits between attempts, such as FLOODTIMEOUT for a caller.
	 */
	public function __construct(ConnectionInterface $db, $enabled, $timeout)
	{
		$this->db = $db;
		$this->enabled = (bool) $enabled;
		$this->timeout = max(1, (int) $timeout);
	}

	/**
	 * @param string $kind
	 *   What is being rationed.
	 * @param string $source
	 *   Who is asking, or whom it is done to, such as the account a mail goes to.
	 *   A caller who can choose their own address is one source however many they
	 *   use, so see {@see \e107\Ip\Address::toSubscriberBlock()} before passing one.
	 * @return bool
	 *   TRUE when this source tried too recently to be allowed another.
	 */
	public function isClosedTo($kind, $source)
	{
		if(!$this->enabled)
		{
			return false;
		}

		return (bool) $this->db->createQueryBuilder()
			->from(self::TABLE)
			->where('tmp_ip', (string) $kind)
			->where('tmp_info', (string) $source)
			->where('tmp_time', '>', time())
			->count();
	}

	/**
	 * @param string $kind
	 * @param string $source
	 * @return void
	 */
	public function record($kind, $source)
	{
		if(!$this->enabled)
		{
			return;
		}

		$this->forget($kind, $source);

		$this->db->createQueryBuilder()->insert(self::TABLE)->values(array(
			'tmp_ip'   => (string) $kind,
			'tmp_time' => time() + $this->timeout,
			'tmp_info' => (string) $source,
		))->execute();
	}

	/**
	 * @param string $kind
	 * @param string $source
	 * @return void
	 */
	public function forget($kind, $source)
	{
		$this->db->createQueryBuilder()->delete(self::TABLE)
			->where('tmp_ip', (string) $kind)
			->where('tmp_info', (string) $source)
			->execute();
	}
}
