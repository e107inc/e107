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
 * PHP's own files storage, byte for byte, that can also end an account's other sessions.
 */
class FilesSessionHandler extends \SessionHandler implements SoleSessionStoreInterface
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
	 * @var string
	 */
	private $accountFilePrefix;

	/**
	 * @var int|null
	 */
	private $processUid = null;

	/**
	 * @param string $savePath session.save_path as PHP reads it, "[depth;[mode;]]directory"
	 * @param string $siteKey what tells this site's account files from another site's in a shared directory
	 */
	public function __construct($savePath, $siteKey)
	{
		$parts = explode(';', (string) $savePath);
		$directory = rtrim(array_pop($parts), '/\\');

		$this->directory = '' === $directory ? sys_get_temp_dir() : $directory;
		$this->depth = isset($parts[0]) ? (int) $parts[0] : 0;
		$this->mode = isset($parts[1]) ? octdec($parts[1]) : 0600;
		$this->accountFilePrefix = 'e107_'.preg_replace('#[^0-9A-Za-z]#', '', (string) $siteKey).'_';
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not where open_basedir leaves the session directory out: PHP's own module reaches it regardless, but this class cannot. PHP is asked, so its own rules for links, relative entries and letter case apply.
	 */
	public function canClaim()
	{
		return '' === (string) ini_get('open_basedir') || @is_dir($this->directory);
	}

	/**
	 * {@inheritDoc}
	 *
	 * The account's file names the session that claimed it last. A session already gone is not reported as ended, and one that cannot be ended stays named.
	 */
	public function claim($userId, $sessionId)
	{
		$userId = (int) $userId;
		$accountFile = $this->directory.'/'.$this->accountFilePrefix.$userId;

		if($userId < 1 || !SessionId::isWellFormed($sessionId) || !$this->canClaim() || ($this->exists($accountFile) && !$this->isOwnFile($accountFile)))
		{
			return false;
		}

		$previous = trim($this->readOwnFile($accountFile));
		$ended = $previous !== $sessionId && $this->isStored($previous);

		if($ended && !$this->destroy($previous))
		{
			return false;
		}

		$this->replaceOwnFile($accountFile, $sessionId);

		return $ended;
	}

	/**
	 * @param string $id
	 * @return bool false without a running session, since PHP's module removes a session only through the one it has open; a file it reports removed but leaves, as PHP 5.6 does after a regenerated id, is removed here
	 */
	#[\ReturnTypeWillChange]
	public function destroy($id)
	{
		if(PHP_SESSION_ACTIVE !== session_status() || !parent::destroy($id))
		{
			return false;
		}

		$file = $this->sessionFile($id);

		return !$this->isOwnFile($file) || unlink($file);
	}

	/**
	 * @param int $max_lifetime
	 * @return int|bool
	 */
	#[\ReturnTypeWillChange]
	public function gc($max_lifetime)
	{
		$collected = parent::gc($max_lifetime);
		$this->collectAccountFiles($max_lifetime);

		return $collected;
	}

	/**
	 * Removes each of this site's account files untouched for $max_lifetime seconds whose session is gone.
	 *
	 * @param int $max_lifetime
	 * @return void
	 */
	protected function collectAccountFiles($max_lifetime)
	{
		if(!$this->canClaim())
		{
			return;
		}

		$cutoff = time() - (int) $max_lifetime;

		foreach($this->filesStartingWith($this->accountFilePrefix) as $accountFile)
		{
			if($this->isOwnFile($accountFile) && filemtime($accountFile) < $cutoff && !$this->isStored(trim($this->readOwnFile($accountFile))))
			{
				unlink($accountFile);
			}
		}
	}

	/**
	 * @return string the directory session files are kept under
	 */
	protected function saveDirectory()
	{
		return $this->directory;
	}

	/**
	 * @param string $id
	 * @return string where PHP's files module keeps that session, one subdirectory per level of depth named by the id's leading characters
	 */
	protected function sessionFile($id)
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
	protected function filesStartingWith($prefix)
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
	protected function isOwnFile($path)
	{
		clearstatcache(true, $path);

		return !is_link($path) && is_file($path) && fileowner($path) === $this->processUid();
	}

	/**
	 * @param string $path
	 * @return string what a file this process wrote holds, read through the handle whose owner was checked; '' for anything else
	 */
	protected function readOwnFile($path)
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
	protected function replaceOwnFile($path, $data)
	{
		if($this->exists($path) && !$this->isOwnFile($path))
		{
			return false;
		}

		return \e107::writeFileAtomic($path, $data, $this->mode);
	}

	/**
	 * @param string $id
	 * @return bool whether a session file this process wrote exists for $id
	 */
	private function isStored($id)
	{
		return SessionId::isWellFormed($id) && $this->isOwnFile($this->sessionFile($id));
	}

	/**
	 * @param string $path
	 * @return bool whether anything, a dangling link included, stands at $path
	 */
	private function exists($path)
	{
		clearstatcache(true, $path);

		return file_exists($path) || is_link($path);
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
