<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

/**
 * A lock the database server grants by name to one session at a time, and takes back when that session disconnects.
 */
final class NamedLock
{
	/** @var ConnectionInterface connection the lock is taken and given back on */
	private $db;

	/** @var string */
	private $name;

	/** @var bool */
	private $held = false;

	/**
	 * @param ConnectionInterface $db
	 * @param string $name at most 64 bytes, which is as long as the server accepts
	 */
	public function __construct(ConnectionInterface $db, $name)
	{
		$this->db = $db;
		$this->name = $name;
	}

	/**
	 * Take the lock without waiting for a session that already has it; taking it again while held asks the server nothing.
	 *
	 * @return bool|null true where this session has it, false where another holds it, null where the server did not say.
	 */
	public function take()
	{
		if($this->held)
		{
			return true;
		}

		$this->db->execute('SELECT GET_LOCK(:name, 0) AS locked', array('name' => $this->name));
		$row = $this->db->fetch();
		$locked = (is_array($row) && isset($row['locked'])) ? (string) $row['locked'] : null;
		$this->held = ($locked === '1');

		return ($locked === null) ? null : $this->held;
	}

	/**
	 * Give the lock back where take() got it.
	 */
	public function release()
	{
		if(!$this->held)
		{
			return;
		}

		$this->held = false;
		$this->db->execute('SELECT RELEASE_LOCK(:name)', array('name' => $this->name));
	}
}
