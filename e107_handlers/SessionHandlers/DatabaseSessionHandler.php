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
 * Session storage in the session table, one row per session keyed by a digest of the session id.
 *
 * @todo PHP 8.1 support with {@see \SessionHandlerInterface}
 */
class DatabaseSessionHandler
{
	/**
	 * Digest the session id is stored under, and the prefix that marks a row as
	 * carrying one. Must be a {@see hash_algos()} name.
	 */
	const KEY_ALGO = 'sha256';

	/**
	 * @var \e_db
	 */
	protected $_db = null;

	/**
	 * @var string
	 */
	protected $_table = 'session';

	/**
	 * @var integer
	 */
	protected $_lifetime = null;

	/**
	 * @param \e_db $db connection the session table is read and written through
	 */
	public function __construct(\e_db $db)
	{
		$this->_db = $db;
	}

	/**
	 * @return string
	 */
	protected function getTable()
	{
		return $this->_table;
	}

	/**
	 * @return integer
	 */
	protected function getLifetime()
	{
		if(null === $this->_lifetime)
		{
			$this->_lifetime = ini_get('session.gc_maxlifetime');
			if(!$this->_lifetime)
			{
				$this->_lifetime = 3600;
			}
		}
		return (int) $this->_lifetime;
	}

	/**
	 * Open session, parameters are ignored
	 * @param string $save_path
	 * @param string $sess_name
	 * @return boolean
	 */
	public function open($save_path, $sess_name)
	{
		return true;
	}

	/**
	 * Close session, collecting on one close in a hundred where the host keeps PHP's own collector off
	 * @return boolean
	 */
	public function close()
	{
		if(ini_get('session.gc_probability') <= 0 && mt_rand(1, 100) === 1)
		{
			$this->gc($this->getLifetime());
		}

		return true;
	}

	/**
	 * Get session data
	 * @param string $session_id
	 * @return string
	 */
	public function read($session_id)
	{
		$keys = self::storageKeys($session_id);
		$rows = $this->readKeys($keys);
		list($key, $legacyKey) = $keys;

		if(false === $rows)
		{
			return false;
		}

		if(isset($rows[$key]) && '' !== $rows[$key])
		{
			return $rows[$key];
		}

		if(isset($rows[$legacyKey]) && '' !== $rows[$legacyKey] && $this->rekey($legacyKey, $key))
		{
			return $rows[$legacyKey];
		}

		return '';
	}

	/**
	 * Storage key for a session id.
	 *
	 * The id is the value of the visitor's session cookie, so a row keyed by it
	 * verbatim turns any read of this table into a set of live credentials. The
	 * algorithm is named in the value so {@see DatabaseSessionHandler::read()} can
	 * recognise a row written before this was introduced, and so the digest can
	 * be changed later without a second migration.
	 *
	 * @param string $session_id
	 * @return string
	 */
	protected static function storageKey($session_id)
	{
		return self::KEY_ALGO.'$'.hash(self::KEY_ALGO, self::_sanitize($session_id));
	}

	/**
	 * @param string $session_id
	 * @return string[] the id's storage key, then the raw id that rows written before v2.3.12 are keyed by
	 */
	private static function storageKeys($session_id)
	{
		return array(self::storageKey($session_id), self::_sanitize($session_id));
	}

	/**
	 * @param string[] $keys
	 * @return string a WHERE clause matching the rows stored under any of $keys
	 */
	private static function whereKeyIn(array $keys)
	{
		return "`session_id` IN ('".implode("', '", $keys)."')";
	}

	/**
	 * @param string[] $keys
	 * @return array|false session data of each live row under the key that found it, false when the table cannot be read
	 */
	protected function readKeys(array $keys)
	{
		$check = $this->_db->select($this->getTable(), 'session_id, session_data', self::whereKeyIn($keys)." AND session_expires>".time());

		if(false === $check)
		{
			return false;
		}

		$rows = array();

		while($row = $this->_db->fetch())
		{
			foreach($keys as $key)
			{
				if(0 === strcasecmp($row['session_id'], $key))
				{
					$rows[$key] = base64_decode($row['session_data']);
				}
			}
		}

		return $rows;
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @return boolean
	 */
	protected function rekey($from, $to)
	{
		$data = array(
			'data' => array('session_id' => $to),
			'_FIELD_TYPES' => array('session_id' => 'str'),
			'WHERE' => "`session_id`='".$from."'",
		);

		return false !== $this->_db->update($this->getTable(), $data);
	}

	/**
	 * Write session data
	 * @param string $session_id
	 * @param string $session_data
	 * @return boolean
	 */
	public function write($session_id, $session_data)
	{
		$data = array(
			'data' => array(
				'session_expires' => time() + $this->getLifetime(),
				'session_data'    => base64_encode($session_data),
				'session_user'    => defset('USERID'),
			),
			'_FIELD_TYPES' => array(
				'session_id'      => 'str',
				'session_expires' => 'int',
				'session_user'    => 'int',
				'session_data'    => 'str'
			),
			'_DEFAULT' => 'str'
		);
		if(!self::_sanitize($session_id))
		{
			return false;
		}

		$session_id = self::storageKey($session_id);

		$check = $this->_db->select($this->getTable(), 'session_id', "`session_id`='{$session_id}'");

		if($check)
		{
			$data['WHERE'] = "`session_id`='{$session_id}'";
			if(false !== $this->_db->update($this->getTable(), $data))
			{
				return true;
			}
		}
		else
		{
			$data['data']['session_id'] = $session_id;
			if($this->_db->insert($this->getTable(), $data))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Destroy session
	 * @param string $session_id
	 * @return boolean
	 */
	public function destroy($session_id)
	{
		$this->_db->delete($this->getTable(), self::whereKeyIn(self::storageKeys($session_id)));
		return true;
	}

	/**
	 * Garbage collection
	 * @param integer $session_maxlf ignored - see write()
	 * @return boolean
	 */
	public function gc($session_maxlf)
	{
		$this->_db->delete($this->getTable(), '`session_expires`<'.time());
		return true;
	}

	/**
	 * Allow only well formed session id string
	 * @param string $session_id
	 * @return string
	 */
	protected static function _sanitize($session_id)
	{
		return preg_replace('#[^0-9a-zA-Z,-]#', '', $session_id);
	}
}
