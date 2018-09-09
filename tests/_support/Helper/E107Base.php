<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class E107Base extends Base
{
	const TEST_IN_PROGRESS = 'TEST-IN-PROGRESS';
	const TEST_IN_PROGRESS_FILE = APP_PATH."/".self::TEST_IN_PROGRESS;
	const APP_PATH_E107_CONFIG = APP_PATH."/e107_config.php";
	public $e107_mySQLprefix = 'e107_';

	public function _beforeSuite($settings = array())
	{
		$this->backupLocalE107Config();
		$this->setVcsInProgress();
		parent::_beforeSuite($settings);
		$this->writeLocalE107Config();
	}

	protected function backupLocalE107Config()
	{
		if(file_exists(self::APP_PATH_E107_CONFIG))
		{
			rename(self::APP_PATH_E107_CONFIG, APP_PATH.'/e107_config.php.bak');
		}
	}

	protected function setVcsInProgress()
	{
		if ($this->isVcsInProgress())
		{
			codecept_debug('Git repo shows test in progress. Probably crashed test.');
			$this->unsetVcsInProgress();
		}

		codecept_debug('Setting VCS in progress…');

		touch(self::TEST_IN_PROGRESS_FILE);
		$this->runCommand('git add -f '.escapeshellarg(self::TEST_IN_PROGRESS_FILE));
		$this->runCommand('git add -A');

		$commit_command = 'git commit -a --no-gpg-sign ' .
			"-m '".self::TEST_IN_PROGRESS."! If test crashed, run `git log -1` for instructions' " .
			"-m 'Running the test again after fixing the crash will clear this commit\nand any related stashes.' " .
			"-m 'Alternatively, run these commands to restore the repository to its\npre-test state:' ";
		$unsetVcsInProgress_commands = [
			'git reset --hard HEAD',
			'git clean -fdx',
			'git stash pop',
			'git reset --mixed HEAD^',
			'rm -fv '.escapeshellarg(self::TEST_IN_PROGRESS)
		];
		foreach($unsetVcsInProgress_commands as $command)
		{
			$commit_command .= "-m ".escapeshellarg($command)." ";
		}
		$this->runCommand($commit_command);
		$this->runCommand('git stash push --all -m '.escapeshellarg(self::TEST_IN_PROGRESS));
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
		proc_close($resource);
	}

	protected function unsetVcsInProgress()
	{
		codecept_debug('Rolling back VCS to pre-test state…');
		$this->runCommand('git reset --hard HEAD');
		$this->runCommand('git clean -fdx');

		while ($this->isVcsInProgress('commit'))
		{
			codecept_debug('Going back one commit…');
			$this->runCommand('git reset --mixed HEAD^');
		}

		while ($this->isVcsInProgress('stash'))
		{
			codecept_debug('Popping top of stash…');
			$this->runCommand('git stash pop');
		}

		@unlink(self::TEST_IN_PROGRESS_FILE);
	}

	protected function writeLocalE107Config()
	{
		$twig_loader = new \Twig_Loader_Array([
			'e107_config.php' => file_get_contents(codecept_data_dir()."/e107_config.php.sample")
		]);
		$twig = new \Twig_Environment($twig_loader);

		$db = $this->getModule('\Helper\DelayedDb');

		$e107_config = [];
		$e107_config['mySQLserver'] = $db->_getDbHostname();
		$e107_config['mySQLuser'] = $db->_getDbUsername();
		$e107_config['mySQLpassword'] = $db->_getDbPassword();
		$e107_config['mySQLdefaultdb'] = $db->_getDbName();
		$e107_config['mySQLprefix'] = $this->e107_mySQLprefix;

		$e107_config_contents = $twig->render('e107_config.php', $e107_config);
		file_put_contents(self::APP_PATH_E107_CONFIG, $e107_config_contents);
	}

	public function _afterSuite()
	{
		parent::_afterSuite();
		$this->revokeLocalE107Config();
		$this->unsetVcsInProgress();
		$this->restoreLocalE107Config();
	}

	protected function revokeLocalE107Config()
	{
		if (file_exists(self::APP_PATH_E107_CONFIG))
			unlink(self::APP_PATH_E107_CONFIG);
	}

	protected function restoreLocalE107Config()
	{
		if(file_exists(APP_PATH."/e107_config.php.bak"))
		{
			rename(APP_PATH.'/e107_config.php.bak', self::APP_PATH_E107_CONFIG);
		}
	}

}
