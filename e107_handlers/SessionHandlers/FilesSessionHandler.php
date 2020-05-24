<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace e107\SessionHandlers;

use SessionHandler;

/**
 * PHP native session handler
 *
 * Interface methods are passed through to the native SessionHandler.
 *
 * This implementation blocks reads and writes until the session is closed. e107 invocations made in parallel on the
 * same session (i.e. the same web browser) will execute sequentially, which may cause unacceptable performance for
 * some website workloads. It is recommended to use this implementation when your site stores data in sessions that are
 * susceptible to race conditions.
 *
 * Note these additional downsides to using this implementation:
 * - Consumes one inode per session. If never cleaned, the admin's hosting account could exhaust its file count quota.
 * - Resource-intensive to scan through session files to determine which ones to prune
 * - Another application could garbage-collect session files earlier than configured, leading to unexpected logouts for
 *   users.
 * - It is a common practice to store the session files in `/tmp`, which is typically wiped every reboot. This causes
 *   unexpected logouts for users.
 * - Session files are often stored with permissive permissions, which leaves potentially sensitive data to be accessed
 *   by other sites/applications on the same shared hosting server.
 * - Harder to scale horizontally; the session files must be put in shared storage for the sessions to be usable by
 *   multiple web nodes in the cluster.
 */
class FilesSessionHandler extends BaseSessionHandler
{
	/**
	 * @var SessionHandler
	 */
	private $nativeSessionHandler;

	public function __construct()
	{
		$this->nativeSessionHandler = new SessionHandler();
	}

	public function activate()
	{
		if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
		ini_set('session.save_handler', 'files');
		session_start();
	}

	public function close()
	{
		return $this->nativeSessionHandler->close();
	}

	public function destroy($session_id)
	{
		return $this->nativeSessionHandler->destroy($session_id);
	}

	public function gc($maxlifetime)
	{
		return $this->nativeSessionHandler->gc($maxlifetime);
	}

	public function open($save_path, $name)
	{
		return $this->nativeSessionHandler->open($save_path, $name);
	}

	public function read($session_id)
	{
		return $this->nativeSessionHandler->read($session_id);
	}

	public function write($session_id, $session_data)
	{
		return $this->nativeSessionHandler->write($session_id, $session_data);
	}
}