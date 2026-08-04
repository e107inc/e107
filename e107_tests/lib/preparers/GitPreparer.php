<?php

class GitPreparer implements Preparer
{
	const WORKTREE_PREFIX = 'e107-TEST-IN-PROGRESS-';

	/** @var string The original (non-worktree) app path */
	private $appPath;

	/** @var string|null Path to the created worktree */
	private $worktreePath;

	/** @var resource|null Open file handle holding the flock */
	private $lockHandle;

	/**
	 * @param string $appPath The original app path (source tree)
	 */
	public function __construct($appPath)
	{
		$this->appPath = $appPath;
	}

	public function snapshot()
	{
		if ($this->worktreePath !== null)
		{
			return; // idempotent: worktree already created
		}

		$this->pruneOrphans();

		$pid = getmypid();
		$hash = substr(md5($this->appPath), 0, 8);
		$this->worktreePath = sys_get_temp_dir() . '/' . self::WORKTREE_PREFIX . $pid . '-' . $hash;

		$this->debug('Creating worktree at ' . $this->worktreePath);

		$stdout = '';
		$stderr = '';
		$rc = $this->runCommand(
			'git worktree add --detach ' . escapeshellarg($this->worktreePath) . ' HEAD',
			$stdout, $stderr
		);
		if ($rc !== 0)
		{
			$this->worktreePath = null;
			$this->debug('Failed to create worktree: ' . trim($stderr));
			throw new Exception('GitPreparer: failed to create worktree: ' . trim($stderr));
		}

		$this->overlayDirtyFiles();
		$this->acquireLock();

		// Defer cleanup to AFTER all other shutdown handlers.
		// PriorityCallbacks fires early (registered during bootstrap).
		// From within it, register_shutdown_function() appends to the
		// end of the queue, so the actual removal runs after e107's
		// e107_debug_shutdown and any other application handlers.
		PriorityCallbacks::instance()->register_shutdown_function(function()
		{
			register_shutdown_function(function()
			{
				$this->cleanup();
			});
		});

		$this->debug('Worktree ready');
	}

	public function rollback()
	{
		// No-op when called from _afterSuite(). The worktree must
		// persist through all shutdown handlers; actual removal is
		// deferred via the late shutdown function registered above.
	}

	private function cleanup()
	{
		if ($this->worktreePath === null)
		{
			return;
		}
		$this->debug('Removing worktree at ' . $this->worktreePath);

		if ($this->lockHandle !== null)
		{
			flock($this->lockHandle, LOCK_UN);
			fclose($this->lockHandle);
			$this->lockHandle = null;
		}

		$this->removeWorktree($this->worktreePath);
		$this->worktreePath = null;
	}

	public function getAppPath()
	{
		// Isolated copy: ensure the worktree exists (snapshot is idempotent),
		// then run from it so the source tree stays pristine.
		$this->snapshot();
		return $this->worktreePath;
	}

	private function acquireLock()
	{
		$lockFile = $this->worktreePath . '/.lock';
		$this->lockHandle = fopen($lockFile, 'w');
		if ($this->lockHandle === false)
		{
			throw new Exception('GitPreparer: failed to create lock file');
		}
		flock($this->lockHandle, LOCK_EX);
		fwrite($this->lockHandle, json_encode(array(
			'pid' => getmypid(),
			'appPath' => $this->appPath,
			'created' => time(),
		)));
		fflush($this->lockHandle);
	}

	private function pruneOrphans()
	{
		$pattern = sys_get_temp_dir() . '/' . self::WORKTREE_PREFIX . '*';
		$candidates = glob($pattern);
		if (!is_array($candidates))
		{
			return;
		}

		foreach ($candidates as $dir)
		{
			if (!is_dir($dir))
			{
				continue;
			}

			$lockFile = $dir . '/.lock';
			if (!file_exists($lockFile))
			{
				$this->debug('Pruning orphan (no lock): ' . $dir);
				$this->removeWorktree($dir);
				continue;
			}

			$handle = @fopen($lockFile, 'r');
			if ($handle === false)
			{
				continue;
			}

			if (flock($handle, LOCK_EX | LOCK_NB))
			{
				flock($handle, LOCK_UN);
				fclose($handle);
				$this->debug('Pruning orphan (lock released): ' . $dir);
				$this->removeWorktree($dir);
			}
			else
			{
				fclose($handle);
			}
		}

		$this->pruneOwnOrphanEntries();
	}

	/**
	 * Drop worktree registrations belonging to this preparer whose directory
	 * has gone.
	 *
	 * Deliberately not `git worktree prune`. That judges every registered
	 * worktree by whether its path exists from where the command runs, and
	 * the suite runs inside a container with the repository mounted at a
	 * different path. Every worktree the developer keeps on the host is
	 * therefore invisible from in here, so a bare prune unregisters the lot
	 * of them: directories survive, but their administrative files do not,
	 * and submodules make the damage awkward to undo. Only entries pointing
	 * at our own temporary worktrees are considered here.
	 */
	private function pruneOwnOrphanEntries()
	{
		$stdout = '';
		if ($this->runCommand('git rev-parse --git-common-dir', $stdout) !== 0)
		{
			return;
		}

		$common = trim($stdout);
		if ($common === '')
		{
			return;
		}
		if (substr($common, 0, 1) !== '/')
		{
			$common = $this->appPath . '/' . $common;
		}

		$entries = glob($common . '/worktrees/*', GLOB_ONLYDIR);
		if (!is_array($entries))
		{
			return;
		}

		foreach ($entries as $entry)
		{
			if (!is_file($entry . '/gitdir'))
			{
				continue;
			}

			$dir = preg_replace('#/\.git$#', '',
				trim((string) file_get_contents($entry . '/gitdir')));

			if (strpos(basename($dir), self::WORKTREE_PREFIX) !== 0 || is_dir($dir))
			{
				continue;
			}

			$this->debug('Removing stale worktree registration: ' . $entry);
			$this->deleteDir($entry);
		}
	}

	private function overlayDirtyFiles()
	{
		$stdout = '';
		$stderr = '';
		$rc = $this->runCommand(
			'rsync -a --exclude=.git '
			. escapeshellarg($this->appPath . '/') . ' '
			. escapeshellarg($this->worktreePath . '/'),
			$stdout, $stderr
		);
		if ($rc !== 0)
		{
			$this->debug('rsync overlay warning: ' . trim($stderr));
		}
	}

	private function removeWorktree($path)
	{
		$this->runCommand('git worktree remove --force ' . escapeshellarg($path));

		if (is_dir($path))
		{
			$this->deleteDir($path);
			$this->pruneOwnOrphanEntries();
		}
	}

	private function deleteDir($dirPath)
	{
		if (!is_dir($dirPath))
		{
			return;
		}
		$entries = scandir($dirPath);
		foreach ($entries as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}
			$full = $dirPath . '/' . $entry;
			if (is_dir($full))
			{
				$this->deleteDir($full);
			}
			else
			{
				unlink($full);
			}
		}
		rmdir($dirPath);
	}

	/**
	 * @param string $command
	 * @param string $stdout
	 * @param string $stderr
	 * @return int Exit code
	 */
	private function runCommand($command, &$stdout = '', &$stderr = '')
	{
		$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		$resource = proc_open($command, $descriptorspec, $pipes, $this->appPath);
		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe)
		{
			fclose($pipe);
		}
		return proc_close($resource);
	}

	private function debug($message)
	{
		codecept_debug(__CLASS__ . ': ' . $message);
	}
}
