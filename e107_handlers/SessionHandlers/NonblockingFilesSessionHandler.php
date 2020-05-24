<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;

use RuntimeException;

/**
 * Non-blocking session handler that saves one session per file
 *
 * Functionally equivalent to the PHP internal "files" session handler, except that multiple clients can access the same
 * session at the same time. See {@link FilesSessionHandler} for the blocking implementation.
 *
 * Except for the blocking issue, the downsides in {@link FilesSessionHandler} also apply to this implementation.
 *
 * This implementation does block, but only when committing and reading the raw session data. It acquires an exclusive
 * lock when writing and a shared lock when reading. If concurrent session writes are taking place, the last one will
 * ultimately prevail, and any concurrent session reads will read data from the last successful write.
 */
class NonblockingFilesSessionHandler extends BaseSessionHandler
{
	const SESSION_FILE_PREFIX = 'sess_';

	/**
	 * @var string
	 */
	private $savePath;

	public function close()
	{
		return true;
	}

	public function destroy($session_id)
	{
		if (!$this->isValidSessionId($session_id)) return false;
		$path = "{$this->savePath}/sess_{$session_id}";
		if (file_exists($path)) unlink($path);

		return true;
	}

	public function gc($maxlifetime)
	{
		$handle = opendir($this->savePath);
		if (!$handle) return false;

		while (($filename = readdir($handle)) !== false)
		{
			if (substr($filename, 0, strlen(self::SESSION_FILE_PREFIX)) !== self::SESSION_FILE_PREFIX)
				continue;
			$path = "{$this->savePath}/$filename";
			$this->expireFile($path, $maxlifetime);
		}

		closedir($handle);
		return true;
	}

	/**
	 * @throws RuntimeException if the save path is not writable
	 */
	public function open($save_path, $name)
	{
		$this->savePath = $save_path;
		if (!is_dir($this->savePath))
		{
			@mkdir($this->savePath, 0777, true);
		}
		if (!is_writable($this->savePath))
		{
			throw new RuntimeException(
				"Session directory is not writable! Check that session.save_path in php.ini is correctly set."
			);
		}
		return true;
	}

	public function read($session_id)
	{
		if (!$this->isValidSessionId($session_id)) return false;
		$session_path = $this->savePath . "/" . self::SESSION_FILE_PREFIX . $session_id;
		$session_file = @fopen($session_path, "r");
		if (!$session_file) return '';
		flock($session_file, LOCK_SH);
		$contents = @file_get_contents($session_path);
		flock($session_file, LOCK_UN);
		fclose($session_file);
		if (!$contents) return '';
		return $contents;
	}

	public function write($session_id, $session_data)
	{
		if (!$this->isValidSessionId($session_id)) return false;
		return file_put_contents(
				$this->savePath . "/" . self::SESSION_FILE_PREFIX . $session_id,
				$session_data,
				LOCK_EX
			) !== false;
	}

	/**
	 * Determines whether the provided session ID is acceptable for filesystem storage
	 *
	 * @param $session_id string The user-provided session ID
	 * @return bool TRUE if the session ID passed validation; FALSE otherwise
	 */
	private function isValidSessionId($session_id)
	{
		if (
			strlen($session_id) > 128 ||
			strpos($session_id, '/') !== false ||
			strpos($session_id, '\\') !== false
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete a file if it has not been updated in the provided amount of time
	 * @param $path string Absolute path to the file to check
	 * @param $maxlifetime int The maximum number of seconds old that the file's last update is allowed to be
	 */
	private function expireFile($path, $maxlifetime)
	{
		if (filemtime($path) + $maxlifetime < time() && file_exists($path)) unlink($path);
	}
}
