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
 * The connection handles e_db instances share, one per set of connection parameters.
 *
 * @internal
 */
class SharedHandles
{
	/** @var array key => array('handle' => mixed, 'holders' => int) */
	private $entries = array();

	/**
	 * Take a hold on the handle shared for $params.
	 *
	 * @param array $params server, port, user, password and database
	 * @return mixed|null the handle, or null when none is shared for $params
	 */
	public function hold(array $params)
	{
		$key = $this->key($params);

		if(!isset($this->entries[$key]))
		{
			return null;
		}

		$this->entries[$key]['holders']++;

		return $this->entries[$key]['handle'];
	}

	/**
	 * Share $handle for $params with its owner as the first holder, unless a handle is shared for them already.
	 *
	 * @param array $params
	 * @param mixed $handle
	 * @return bool whether $handle is now the one shared for $params
	 */
	public function share(array $params, $handle)
	{
		$key = $this->key($params);

		if(isset($this->entries[$key]))
		{
			return false;
		}

		$this->entries[$key] = array('handle' => $handle, 'holders' => 1);

		return true;
	}

	/**
	 * Give up a hold on $handle; the last one given up forgets it.
	 *
	 * @param array $params
	 * @param mixed $handle
	 * @return void
	 */
	public function release(array $params, $handle)
	{
		$key = $this->key($params);

		if(!isset($this->entries[$key]) || $this->entries[$key]['handle'] !== $handle)
		{
			return;
		}

		if(--$this->entries[$key]['holders'] === 0)
		{
			unset($this->entries[$key]);
		}
	}

	/**
	 * @param array $params
	 * @return string a key that does not show the password
	 */
	private function key(array $params)
	{
		return sha1(serialize($params));
	}
}
