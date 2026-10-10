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
 * PHP's own files storage, byte for byte, as a handler core can extend.
 */
class FilesSessionHandler extends \SessionHandler
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
