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
 * Session files without the lock: a visitor's requests run side by side, and each write replaces the file whole.
 */
class NonblockingFilesSessionHandler extends FilesSessionHandler
{
	/**
	 * @var string[] by id, the end mark each session carried when this request read it, '' for none
	 */
	private $markSeen = array();

	/**
	 * @param string $path
	 * @param string $name
	 * @return bool false when the session directory cannot be written, as PHP's files module answers
	 */
	#[\ReturnTypeWillChange]
	public function open($path, $name)
	{
		$directory = $this->saveDirectory();

		return is_dir($directory) && is_writable($directory);
	}

	/**
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function close()
	{
		return true;
	}

	/**
	 * @param string $id
	 * @return string|false the session's data, '' for a session with no file of this process's own
	 */
	#[\ReturnTypeWillChange]
	public function read($id)
	{
		if(!SessionId::isWellFormed($id))
		{
			return false;
		}

		$this->markSeen[$id] = $this->endMark($id);

		return $this->readOwnFile($this->sessionFile($id));
	}

	/**
	 * @param string $id
	 * @param string $data
	 * @return bool true without writing, or with the write taken back, for a session ended after this request read it
	 */
	#[\ReturnTypeWillChange]
	public function write($id, $data)
	{
		if(!SessionId::isWellFormed($id))
		{
			return false;
		}

		if($this->endedSinceRead($id))
		{
			return true;
		}

		$written = $this->replaceOwnFile($this->sessionFile($id), $data);

		if($this->endedSinceRead($id) && $this->isOwnFile($this->sessionFile($id)))
		{
			unlink($this->sessionFile($id));
		}

		return $written;
	}

	/**
	 * Removes the session, marks it ended, and removes it again, so that no request which read it before the mark writes it back.
	 *
	 * @param string $id
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function destroy($id)
	{
		if(!SessionId::isWellFormed($id))
		{
			return true;
		}

		$this->removeSessionFile($id);
		$this->replaceOwnFile($this->endMarker($id), uniqid('', true));
		$this->removeSessionFile($id);

		return true;
	}

	/**
	 * Removes the session files and end marks nobody has written for $max_lifetime seconds; below a depth directory it removes nothing, as PHP's files module does.
	 *
	 * @param int $max_lifetime
	 * @return int how many were removed
	 */
	#[\ReturnTypeWillChange]
	public function gc($max_lifetime)
	{
		$removed = 0;
		$cutoff = time() - (int) $max_lifetime;

		foreach($this->filesStartingWith('sess_') as $file)
		{
			if($this->isOwnFile($file) && filemtime($file) < $cutoff && unlink($file))
			{
				$removed++;
			}
		}

		return $removed;
	}

	/**
	 * @param string $id
	 * @return bool whether the session was ended after this request read it
	 */
	private function endedSinceRead($id)
	{
		return isset($this->markSeen[$id]) && $this->endMark($id) !== $this->markSeen[$id];
	}

	/**
	 * @param string $id
	 * @return string what the session's end mark holds, unique to each end; '' when it was never ended
	 */
	private function endMark($id)
	{
		return $this->readOwnFile($this->endMarker($id));
	}

	/**
	 * @param string $id
	 * @return void
	 */
	private function removeSessionFile($id)
	{
		if($this->isOwnFile($this->sessionFile($id)))
		{
			unlink($this->sessionFile($id));
		}
	}

	/**
	 * @param string $id
	 * @return string beside the session file, under a name no session id can produce
	 */
	private function endMarker($id)
	{
		return $this->sessionFile($id).'.ended';
	}
}
