<?php

class GitPreparer implements Preparer
{
	const TEST_IN_PROGRESS = 'TEST-IN-PROGRESS';
	const TEST_IN_PROGRESS_FILE = APP_PATH."/".self::TEST_IN_PROGRESS;

	public function snapshot()
	{
		$this->debug('Snapshot requested');
		return $this->setVcsInProgress();
	}

	public function rollback()
	{
		$this->debug('Rollback requested');
		return $this->unsetVcsInProgress();
	}

	protected function setVcsInProgress()
	{
		// Cleanup in case of a fatal error
		PriorityCallbacks::instance()->register_shutdown_function([$this, 'rollback']);

		if ($this->isVcsInProgress())
		{
			$this->debug('Git repo shows test in progress. Probably crashed test.');
			$this->unsetVcsInProgress();
		}

		$this->debug('Setting test locks in Git…');

		touch(self::TEST_IN_PROGRESS_FILE);
		$this->runCommand('git add -f '.escapeshellarg(self::TEST_IN_PROGRESS_FILE));
		$this->runCommand('git add -A -f');

		$commit_command = 'git -c user.name="Test Run" -c user.email="testrun@example.com" commit -a --no-gpg-sign ' .
			"-m '".self::TEST_IN_PROGRESS."! If test crashed, run `git log -1` for instructions' " .
			"-m 'Running the test again after fixing the crash will clear this commit\nand any related stashes.' " .
			"-m 'Alternatively, run these commands to restore the repository to its\npre-test state:' ";
		$unsetVcsInProgress_commands = [
			'git reset --hard HEAD',
			'git clean -fdx',
			'git reset --mixed HEAD^',
			'rm -fv '.escapeshellarg(self::TEST_IN_PROGRESS)
		];
		foreach($unsetVcsInProgress_commands as $command)
		{
			$commit_command .= "-m ".escapeshellarg($command)." ";
		}

		$stdout = '';
		$stderr = '';
		$rc = $this->runCommand($commit_command, $stdout, $stderr);
		if ($rc !== 0)
		{
			@unlink(self::TEST_IN_PROGRESS_FILE);
			$this->debug('Error taking snapshot with Git!');
			$this->debug('========== STDOUT ==========');
			$this->debug($stdout);
			$this->debug('========== STDERR ==========');
			$this->debug($stderr);
			throw new Exception("Error taking snapshot with Git!");
		}
	}

	protected function isVcsInProgress($case = '')
	{
		$in_progress = [];

		$in_progress['file'] = file_exists(self::TEST_IN_PROGRESS_FILE);

		$stdout = '';
		$this->runCommand('git log -1 --pretty=%B', $stdout);
		$in_progress['commit'] = strpos($stdout, self::TEST_IN_PROGRESS) !== false;

		$stdout = '';
		$this->runCommand('git stash list', $stdout);
		$in_progress['stash'] = strpos($stdout, self::TEST_IN_PROGRESS) !== false;

		if(!empty($case)) return $in_progress[$case];
		return in_array(true, $in_progress);
	}

	/**
	 * @param string $command The command to run
	 * @param string $stdout Reference to the STDOUT output as a string
	 * @param string $stderr Reference to the STDERR output as a string
	 * @return int Return code of the command that was run
	 */
	protected function runCommand($command, &$stdout = "", &$stderr = "")
	{
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open($command, $descriptorspec, $pipes, APP_PATH);
		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe)
		{
			fclose($pipe);
		}
		return proc_close($resource);
	}

	protected function unsetVcsInProgress()
	{
		if (!$this->isVcsInProgress())
		{
			$this->debug('No test locks found');
			return;
		}

		$this->debug('Rolling back Git repo to pre-test state…');
		$this->runCommand('git reset --hard HEAD');
		$this->runCommand('git clean -fdx');

		while ($this->isVcsInProgress('commit'))
		{
			$this->debug('Going back one commit…');
			$this->runCommand('git reset --mixed HEAD^');
		}

		while ($this->isVcsInProgress('stash'))
		{
			$this->debug('Popping top of stash…');
			$this->runCommand('git stash pop');
		}

		@unlink(self::TEST_IN_PROGRESS_FILE);
	}

	protected function debug($message)
	{
		codecept_debug(get_class() . ': ' . $message);
	}
}
