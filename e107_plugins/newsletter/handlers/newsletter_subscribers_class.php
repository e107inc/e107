<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

if (!defined('e107_INIT')) { exit; }

/**
 * A newsletter's subscribers as its newsletter_subscribers column stores them: each user id preceded by chr(1).
 */
class newsletter_subscribers
{
	/** @var string */
	private static $separator = "\x01";

	/** @var int[] keyed by themselves, in the order they joined */
	private $ids = array();

	/**
	 * @param string $stored the column's value; an entry that is not a positive id is dropped, and so is a repeat
	 */
	public function __construct($stored)
	{
		foreach(explode(self::$separator, (string) $stored) as $entry)
		{
			$this->add($entry);
		}
	}

	/**
	 * @param int|string $userId added at the end unless it is already in the list or is not a positive id
	 * @return void
	 */
	public function add($userId)
	{
		$userId = (int) $userId;

		if($userId > 0 && !$this->has($userId))
		{
			$this->ids[$userId] = $userId;
		}
	}

	/**
	 * @param int|string $userId
	 * @return void
	 */
	public function remove($userId)
	{
		unset($this->ids[(int) $userId]);
	}

	/**
	 * @param int|string $userId
	 * @return bool
	 */
	public function has($userId)
	{
		return isset($this->ids[(int) $userId]);
	}

	/**
	 * @return int[] in the order they joined
	 */
	public function ids()
	{
		return array_values($this->ids);
	}

	/**
	 * @return string what the column stores for this list; empty when nobody is subscribed
	 */
	public function toStoredString()
	{
		return $this->ids ? self::$separator.implode(self::$separator, $this->ids) : '';
	}
}
