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
 * PHP's own session files without the lock: a visitor's requests run side by side, and each write replaces the file whole.
 */
class NonblockingFilesSessionHandler implements \SessionHandlerInterface
{
	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var int
	 */
	private $depth;

	/**
	 * @var int
	 */
	private $mode;

	/**
	 * @var int|null
	 */
	private $processUid = null;

	/**
	 * @var string[] by id, the end mark each session carried when this request read it, '' for none
	 */
	private $markSeen = array();

	/**
	 * @param string $savePath session.save_path as PHP reads it, "[depth;[mode;]]directory"
	 */
	public function __construct($savePath)
	{
		$parts = explode(';', (string) $savePath);
		$directory = rtrim(array_pop($parts), '/\\');

		$this->directory = '' === $directory ? sys_get_temp_dir() : $directory;
		$this->depth = isset($parts[0]) ? (int) $parts[0] : 0;
		$this->mode = isset($parts[1]) ? octdec($parts[1]) : 0600;
	}

	/**
	 * @return bool FALSE where open_basedir leaves out the session directory, which PHP's own module reaches regardless
	 */
	public function isReachable()
	{
		return '' === (string) ini_get('open_basedir') || @is_dir($this->directory);
	}

	/**
	 * @param string $path
	 * @param string $name
	 * @return bool false when the session directory cannot be written, as PHP's files module answers
	 */
	#[\ReturnTypeWillChange]
	public function open($path, $name)
	{
		return is_dir($this->directory) && is_writable($this->directory);
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

		if($this->endedSinceRead($id))
		{
			$this->removeSessionFile($id);
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
	 * @return string beside the session file, under a name no session id can produce
	 */
	private function endMarker($id)
	{
		return $this->sessionFile($id).'.ended';
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
	 * @return string where PHP's files module keeps that session, one subdirectory per level of depth named by the id's leading characters
	 */
	private function sessionFile($id)
	{
		$path = $this->directory;

		for($level = 0; $level < $this->depth && $level < strlen($id); $level++)
		{
			$path .= '/'.$id[$level];
		}

		return $path.'/sess_'.$id;
	}

	/**
	 * @param string $prefix
	 * @return \Generator paths of the entries in the session directory whose names start with $prefix
	 */
	private function filesStartingWith($prefix)
	{
		$handle = is_dir($this->directory) && is_readable($this->directory) ? opendir($this->directory) : false;

		if(false === $handle)
		{
			return;
		}

		while(false !== ($entry = readdir($handle)))
		{
			if(0 === strpos($entry, $prefix))
			{
				yield $this->directory.'/'.$entry;
			}
		}

		closedir($handle);
	}

	/**
	 * @param string $path
	 * @return bool whether $path is a file this process wrote, rather than a link or a file another account planted in a shared directory
	 */
	private function isOwnFile($path)
	{
		clearstatcache(true, $path);

		return !is_link($path) && is_file($path) && fileowner($path) === $this->processUid();
	}

	/**
	 * @param string $path
	 * @return string what a file this process wrote holds, read through the handle whose owner was checked; '' for anything else
	 */
	private function readOwnFile($path)
	{
		if(!$this->isOwnFile($path))
		{
			return '';
		}

		$handle = fopen($path, 'rb');

		if(false === $handle)
		{
			return '';
		}

		$stat = fstat($handle);
		$data = (false !== $stat && $stat['uid'] === $this->processUid()) ? stream_get_contents($handle) : '';
		fclose($handle);

		return (string) $data;
	}

	/**
	 * Replaces $path whole, at the session files' mode, unless something this process did not write stands there.
	 *
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	private function replaceOwnFile($path, $data)
	{
		clearstatcache(true, $path);

		if((file_exists($path) || is_link($path)) && !$this->isOwnFile($path))
		{
			return false;
		}

		$temporary = dirname($path).'/.'.basename($path).'.'.bin2hex(random_bytes(8));
		$handle = @fopen($temporary, 'x');

		if(false === $handle)
		{
			return false;
		}

		$written = strlen($data) === fwrite($handle, $data);
		fclose($handle);

		if($written && chmod($temporary, $this->mode) && rename($temporary, $path))
		{
			return true;
		}

		unlink($temporary);

		return false;
	}

	/**
	 * @return int the user id files this process writes are owned by
	 */
	private function processUid()
	{
		if(null === $this->processUid)
		{
			$this->processUid = function_exists('posix_geteuid') ? posix_geteuid() : $this->ownerOfANewFile();
		}

		return $this->processUid;
	}

	/**
	 * @return int the owner of a file created in the session directory, -1 when none can be created there
	 */
	private function ownerOfANewFile()
	{
		$probe = tempnam($this->directory, 'e107');

		if(false === $probe)
		{
			return -1;
		}

		$owner = fileowner($probe);
		unlink($probe);

		return false === $owner ? -1 : $owner;
	}
}
