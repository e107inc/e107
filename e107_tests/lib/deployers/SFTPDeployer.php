<?php

class SFTPDeployer extends Deployer
{
	public function start()
	{
		self::println();
		self::println("=== SFTP Deployer – Bring Up ===");
		if (in_array('fs', $this->components))
		{
			$this->start_fs();
		}
	}

	private function getFsParams()
	{
		return $this->params['fs'];
	}

	private function generateSshpassPrefix()
	{
		if (empty($this->getFsParam('privkey_path')) &&
			!empty($this->getFsParam('password')))
		{
			return 'sshpass -p '.escapeshellarg($this->getFsParam('password')).' ';
		}
		return '';
	}

	private function getFsParam($key)
	{
		return $this->getFsParams()[$key];
	}

	/**
	 * Path of the multiplexing master socket for this destination.
	 *
	 * Keyed on the destination rather than on the process, so the suite's own
	 * runs share one master and a stale socket from a killed run is reused
	 * rather than orphaned. Kept short deliberately: a Unix socket path is
	 * limited to about 100 characters, and %C-style expansion is not available
	 * on every OpenSSH the harness runs against.
	 *
	 * @return string
	 */
	private function getControlPath()
	{
		$key = substr(sha1(
			$this->getFsParam('user').'@'.$this->getFsParam('host').':'.$this->getFsParam('port')
		), 0, 12);

		return rtrim(sys_get_temp_dir(), '/').'/e107-tests-ssh-'.$key;
	}

	private function generateRsyncRemoteShell()
	{
		// Every file this deployer writes or deletes is its own ssh command, and
		// on a loaded CI box a fresh TCP connect plus key exchange plus password
		// auth measured 1.27 s each. Multiplexing lets the first connection pay
		// that once and every later one ride the same channel: measured on the
		// acceptance suite, about 1,500 file operations went from ~33 minutes to
		// well under one. ControlPersist keeps the master alive between the
		// individual commands, which are separate processes.
		$prefix = 'ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null'.
			' -o ControlMaster=auto -o ControlPersist=120'.
			' -o ControlPath='.escapeshellarg($this->getControlPath()).
			' -p '.escapeshellarg($this->getFsParam('port'));
		if (!empty($this->getFsParam('privkey_path')))
			return $prefix.' -i ' . escapeshellarg($this->getFsParam('privkey_path'));
		else
			return $prefix;
	}

	/**
	 * Close the multiplexing master, so a run does not leave a connection and a
	 * socket behind for ControlPersist to time out on its own.
	 *
	 * @return void
	 */
	private function closeControlMaster()
	{
		$path = $this->getControlPath();

		if (!file_exists($path))
		{
			return;
		}

		self::runCommand(
			'ssh -o ControlPath='.escapeshellarg($path).
			' -O exit '.escapeshellarg($this->getFsParam('host')).' 2>/dev/null'
		);
		@unlink($path);
	}

	private static function runCommand($command, &$stdout = null, &$stderr = null, $stdin = null)
	{
		$descriptorSpec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		if ($stdin !== null)
		{
			$descriptorSpec[0] = ['pipe', 'r'];
		}
		$pipes = [];
		self::println("Running this command…:");
		self::println($command);
		$resource = proc_open($command, $descriptorSpec, $pipes, APP_PATH);
		if ($stdin !== null)
		{
			fwrite($pipes[0], $stdin);
			fclose($pipes[0]);
			unset($pipes[0]);
		}
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		self::println("---------- stdout ----------");
		self::println(trim($stdout));
		self::println("---------- stderr ----------");
		self::println(trim($stderr));
		self::println("----------------------------");
		foreach ($pipes as $pipe)
		{
			fclose($pipe);
		}
		return proc_close($resource);
	}

	public function stop()
	{
		self::println("=== SFTP Deployer – Tear Down ===");
		$this->closeControlMaster();
	}

	public function unlinkAppFile($relative_path)
	{
		self::println("Deleting file \"$relative_path\" from deployed test location…");
		$fs_params = $this->getFsParams();
		$command = $this->generateSshpassPrefix().
			$this->generateRsyncRemoteShell().
			" ".escapeshellarg("{$fs_params['user']}@{$fs_params['host']}").
			" ".escapeshellarg("rm -v " . escapeshellarg(rtrim($fs_params['path'], '/')."/$relative_path"));
		$retcode = self::runCommand($command);
		if ($retcode === 0)
		{
			self::println("Deleted file \"$relative_path\" from deployed test location");
		}
		else
		{
			self::println("No such file to delete: \"$relative_path\"");
		}
	}

	public function writeAppFile($relative_path, $contents)
	{
		self::println("Writing file \"$relative_path\" to deployed test location…");
		$fs_params = $this->getFsParams();
		$remote_path = rtrim($fs_params['path'], '/')."/$relative_path";
		// Pipe contents through ssh stdin into `cat > target` on the remote.
		// `mkdir -p` first so paths with intermediate directories work.
		$remote_dir = dirname($remote_path);
		$remote_script = "mkdir -p ".escapeshellarg($remote_dir).
			" && cat > ".escapeshellarg($remote_path);
		$command = $this->generateSshpassPrefix().
			$this->generateRsyncRemoteShell().
			" ".escapeshellarg("{$fs_params['user']}@{$fs_params['host']}").
			" ".escapeshellarg($remote_script);
		$retcode = self::runCommand($command, $stdout, $stderr, $contents);
		if ($retcode !== 0)
		{
			throw new RuntimeException("Failed to write \"$relative_path\" to deployed test location (ssh exit $retcode): ".trim((string) $stderr));
		}
		self::println("Wrote file \"$relative_path\" to deployed test location");
	}

	// Deliberately no removeAppPaths(): the only caller sweeps the tree the
	// developer works in, and a deploying deployer never touches that tree,
	// so it is skipped before it can reach here. The base class no-ops.

	private function start_fs()
	{
		$fs_params = $this->getFsParams();
		$fs_params['path'] = rtrim($fs_params['path'], '/') . '/';
		$command = $this->generateSshpassPrefix() .
			'rsync -e ' .
			escapeshellarg($this->generateRsyncRemoteShell()) .
			' --delete -avzHXShs ' .
			escapeshellarg(rtrim(APP_PATH, '/') . '/') . ' ' .
			escapeshellarg("{$fs_params['user']}@{$fs_params['host']}:{$fs_params['path']}");
		$retcode = self::runCommand($command, $stdout, $stderr);
		if ($retcode !== 0) {
			throw new Exception("SFTP deployment failed (rsync exit $retcode): " . trim((string) $stderr));
		}
	}
}