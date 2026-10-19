<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * Ban list rows a test writes, under its class's REASON constant unless it names a reason, and finds again by REASON.
 */
trait BanlistRows
{
	/**
	 * @param string $ip stored as given
	 * @param int $type
	 * @param int $expires
	 * @param string|null $reason null for the class's REASON
	 * @return array the row as the table takes it
	 */
	private function banRow($ip, $type, $expires = 0, $reason = null)
	{
		return array(
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time() - 60,
			'banlist_banexpires' => $expires,
			'banlist_admin'      => 0,
			'banlist_reason'     => ($reason === null) ? self::REASON : $reason,
			'banlist_notes'      => '',
		);
	}

	/**
	 * @param string $ip stored as given
	 * @param int $type
	 * @param int $expires
	 * @param string|null $reason null for the class's REASON
	 * @return int|false banlist_id, or false where the table refused the row
	 */
	private function insertRow($ip, $type, $expires = 0, $reason = null)
	{
		return \e107::getDb()->createQueryBuilder()->insert('banlist')->insertGetId($this->banRow($ip, $type, $expires, $reason));
	}

	/**
	 * @param string $ip stored as given
	 * @param int $type
	 * @param int $expires
	 * @param string|null $reason null for the class's REASON
	 * @return int banlist_id
	 */
	private function haveRow($ip, $type, $expires = 0, $reason = null)
	{
		$id = $this->insertRow($ip, $type, $expires, $reason);
		$this->assertNotEmpty($id, 'could not write the banlist row this test needs');

		return (int) $id;
	}

	/**
	 * @return int[] the banlist_id values this test wrote that are still stored, ascending
	 */
	private function rowsThisTestWrote()
	{
		$ids = array_map('intval', \e107::getDb()->createQueryBuilder()->select('banlist_id')->from('banlist')
			->where('banlist_reason', self::REASON)->fetchColumn());
		sort($ids);

		return $ids;
	}
}
