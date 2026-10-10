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
 */
class DatabaseSessionHandler implements \SessionHandlerInterface
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
	 * @var int|null
	 */
	protected $_lifetime = null;

	/**
	 * @var SessionSignIn|null
	 */
	private $signIn;

	/**
	 * @param \e_db $db connection the session table is read and written through
	 * @param SessionSignIn $signIn whose session each row is
	 */
	public function __construct(\e_db $db, SessionSignIn $signIn)
	{
		$this->_db = $db;
		$this->signIn = $signIn;
	}

	/**
	 * @return string
	 */
	protected function getTable()
	{
		return $this->_table;
	}

	/**
	 * @return int
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
	 *
	 * @param string $path
	 * @param string $name
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function open($path, $name)
	{
		return true;
	}

	/**
	 * Close session, collecting on one close in a hundred where the host keeps PHP's own collector off
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
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
	 * @param string $id
	 * @return string|false
	 */
	#[\ReturnTypeWillChange]
	public function read($id)
	{
		$keys = self::storageKeys($id);
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
	 * verbatim turns any read of this table into a set of live credentials.
	 * The algorithm is named in the value so {@see DatabaseSessionHandler::read()} can
	 * recognise a row written before this was introduced, and so the digest can
	 * be changed later without a second migration.
	 *
	 * @param string $id
	 * @return string
	 */
	protected static function storageKey($id)
	{
		return self::KEY_ALGO.'$'.hash(self::KEY_ALGO, self::_sanitize($id));
	}

	/**
	 * @param string $id
	 * @return string[] the id's storage key, then the raw id that rows written before v2.3.12 are keyed by
	 */
	private static function storageKeys($id)
	{
		return array(self::storageKey($id), self::_sanitize($id));
	}

	/**
	 * @param string[] $keys
	 * @return array|false session data of each live row under the key that found it, false when the table cannot be read
	 */
	protected function readKeys(array $keys)
	{
		$check = $this->_db->createQueryBuilder()
			->select('session_id', 'session_data')->from($this->getTable())
			->whereIn('session_id', $keys)
			->where('session_expires', '>', time())
			->execute();

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
	 * @return bool
	 */
	protected function rekey($from, $to)
	{
		return false !== $this->_db->createQueryBuilder()
			->update($this->getTable())
			->set('session_id', $to)
			->where('session_id', $from)
			->execute();
	}

	/**
	 * Write session data
	 * @param string $id
	 * @param string $data
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function write($id, $data)
	{
		$values = array(
			'session_expires' => (int) (time() + $this->getLifetime()),
			'session_data'    => base64_encode($data),
			'session_user'    => $this->owner(),
		);
		if(!self::_sanitize($id))
		{
			return false;
		}

		$id = self::storageKey($id);

		$check = $this->_db->createQueryBuilder()
			->select('session_id')->from($this->getTable())
			->where('session_id', $id)
			->count();

		if($check)
		{
			if(false !== $this->_db->createQueryBuilder()
				->update($this->getTable())
				->set('session_expires', $values['session_expires'])
				->set('session_data', $values['session_data'])
				->set('session_user', $values['session_user'])
				->where('session_id', $id)
				->execute())
			{
				return true;
			}
		}
		else
		{
			$values['session_id'] = $id;
			if($this->_db->createQueryBuilder()
				->insert($this->getTable())
				->values($values)
				->execute())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Destroy session
	 * @param string $id
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function destroy($id)
	{
		$this->_db->createQueryBuilder()
			->delete($this->getTable())
			->whereIn('session_id', self::storageKeys($id))
			->execute();
		return true;
	}

	/**
	 * Garbage collection
	 * @param int $max_lifetime
	 * @return int|false
	 */
	#[\ReturnTypeWillChange]
	public function gc($max_lifetime)
	{
		return $this->_db->createQueryBuilder()
			->delete($this->getTable())
			->where('session_expires', '<', time())
			->execute();
	}

	/**
	 * @return int the account the running session is signed in as, 0 for a guest or where a subclass skipped this constructor
	 */
	private function owner()
	{
		return null === $this->signIn ? 0 : $this->signIn->accountId();
	}

	/**
	 * Allow only well formed session id string
	 * @param string $id
	 * @return string
	 */
	protected static function _sanitize($id)
	{
		return preg_replace('#[^0-9a-zA-Z,-]#', '', $id);
	}
}
