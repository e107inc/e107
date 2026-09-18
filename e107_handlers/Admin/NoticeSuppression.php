<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * An administrator's decision not to be shown a notice again.
 *
 * Every suppression lives in one core preference, keyed by the notice's id,
 * so a dismissal survives Empty Cache and travels with a database backup. A
 * record is tied to a fingerprint the notice presents each time it asks: when
 * the thing the notice is about changes, the fingerprint changes with it, the
 * record stops matching, and the notice returns without this class knowing
 * what ended it. A record may also lapse at a time.
 */
class NoticeSuppression
{
	const PREF = 'admin_notice_suppressions';

	/** @var \e_pref */
	private $config;

	/** @var \e_admin_log */
	private $log;

	/** @var int */
	private $userId;

	/**
	 * @param \e_pref $config
	 *   The core preferences.
	 * @param \e_admin_log $log
	 * @param int $userId
	 *   Whose dismissals these are, for the record.
	 */
	public function __construct(\e_pref $config, \e_admin_log $log, $userId)
	{
		$this->config = $config;
		$this->log = $log;
		$this->userId = (int) $userId;
	}

	/**
	 * @param string $id
	 * @param string $while
	 *   Fingerprint the suppression holds for; '' when there is none.
	 * @param int $until
	 *   Unix time at which the suppression lapses; 0 for never.
	 * @return void
	 */
	public function suppress($id, $while = '', $until = 0)
	{
		$this->store($id, array(
			'while' => (string) $while,
			'until' => (int) $until,
			'by'    => $this->userId,
			'at'    => time(),
		));

		$this->log->add('NOTICE_DISMISSED', (string) $id, \E_LOG_INFORMATIVE, 'NOTICE');
	}

	/**
	 * @param string $id
	 * @param string $while
	 *   The fingerprint the notice presents now.
	 * @return bool
	 */
	public function isSuppressed($id, $while = '')
	{
		$records = $this->records();

		return isset($records[(string) $id]) && hash_equals($records[(string) $id]['while'], (string) $while);
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function release($id)
	{
		$records = $this->records();

		if(!isset($records[(string) $id]))
		{
			return;
		}

		$this->config->removePref(self::PREF.'/'.$id)->save(false, true, false);
	}

	/**
	 * The records still in force; anything lapsed or malformed is left out.
	 *
	 * @return array
	 */
	private function records()
	{
		$now = time();
		$live = array();

		foreach((array) $this->config->get(self::PREF) as $id => $record)
		{
			if(!is_array($record))
			{
				continue;
			}

			$until = isset($record['until']) ? (int) $record['until'] : 0;

			if($until > 0 && $until <= $now)
			{
				continue;
			}

			$live[(string) $id] = array(
				'while' => isset($record['while']) ? (string) $record['while'] : '',
				'until' => $until,
				'by'    => isset($record['by']) ? (int) $record['by'] : 0,
				'at'    => isset($record['at']) ? (int) $record['at'] : 0,
			);
		}

		return $live;
	}

	/**
	 * Writes one record rather than the whole map, so that two administrators
	 * dismissing two notices at once keep both: {@see \e_pref::save()} merges by
	 * replaying the mutation that was recorded, and a mutation naming the map
	 * would carry away whichever entry the other one had just added.
	 *
	 * @param string $id
	 * @param array $record
	 * @return void
	 */
	private function store($id, array $record)
	{
		$this->prune();
		$this->config->setPref(self::PREF.'/'.$id, $record)->save(false, true, false);
	}

	/**
	 * Drops the records {@see NoticeSuppression::records()} already ignores, one
	 * at a time so that each removal merges like the write it travels with.
	 *
	 * @return void
	 */
	private function prune()
	{
		$stored = $this->config->get(self::PREF);

		if(!is_array($stored))
		{
			return;
		}

		$live = $this->records();

		foreach(array_keys($stored) as $id)
		{
			if(!isset($live[(string) $id]))
			{
				$this->config->removePref(self::PREF.'/'.$id);
			}
		}
	}
}
