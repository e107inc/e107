<?php

include_once(__DIR__ . "/../cpaneluapi/cpaneluapi.class.php");


class cPanelDeployer
{
	protected $credentials;
	protected $cPanel;
	protected $run_id;

	protected $homedir;
	protected $docroot;
	protected $domain;

	protected const TEST_PREFIX = 'test-';
	protected const TARGET_RELPATH = 'public_html/';
	protected const DEFAULT_COMPONENTS = ['db', 'fs'];

        function __construct($credentials)
        {
                $this->credentials = $credentials;
        }

	public function getUrl()
	{
		return "http://".$this->domain."/".$this->run_id."/";
	}

        public function start($components = self::DEFAULT_COMPONENTS)
        {
		self::println();
		self::println("=== cPanel Deployer – Bring Up ===");
		$creds = $this->credentials;
		if (!$creds['hostname'] ||
		    !$creds['username'] ||
		    !$creds['password'])
		{
			self::println("Cannot deploy cPanel environment because credentials are missing. Falling back to manual mode…");
			return false;
		}

		$this->prepare();

		foreach ($components as $component)
		{
			$method = "prepare_${component}";
			if (!method_exists($this, $method))
			{
				self::println("Unsupported component \"${component}\" requested. Falling back to manual mode…");
				return false;
			}
		}
		foreach ($components as $component)
		{
			$method = "prepare_${component}";
			$this->$method();
		}

		return true;
	}

	public function stop()
	{
		self::println("=== cPanel Deployer – Tear Down ===");
		$cPanel = $this->cPanel;
		$acceptance_tests = self::get_active_acceptance_tests($cPanel, $this->homedir);
		self::println("Removing this test (".$this->run_id.") from registered tests list…");
		self::prune_acceptance_tests($acceptance_tests, $this->run_id);
		self::write_acceptance_tests($cPanel, $this->homedir, $acceptance_tests);

		$valid_acceptance_test_ids = self::get_acceptance_test_ids($acceptance_tests);
		self::println("Current unexpired tests: [".implode(", ", $valid_acceptance_test_ids)."]");
		self::prune_inactive_acceptance_test_resources($cPanel, $valid_acceptance_test_ids);
	}

	private function prepare()
	{
		$username = &$this->credentials['username'];
		$password = &$this->credentials['password'];
		$hostname = &$this->credentials['hostname'];
		
		$this->run_id = $run_id = uniqid(self::TEST_PREFIX);

		self::println("Test run ID: ".$this->run_id);

		$this->cPanel = $cPanel = new cpanelAPI($username, $password, $hostname);

		self::println("Connecting to cPanel at \"${hostname}\" with username \"${username}\"…");
		
		$domains_data = $cPanel->uapi->DomainInfo->domains_data();
		if (!$domains_data)
		{
			throw new Exception("Cannot connect to cPanel at \"${hostname}\" with username \"${username}\" and password \"${password}\"");
		}
		$userdata = $domains_data->{'data'};
		$this->homedir = $homedir = $userdata->{'main_domain'}->{'homedir'};
		$this->docroot = $docroot = $userdata->{'main_domain'}->{'documentroot'};
		$this->domain = $domain = $userdata->{'main_domain'}->{'domain'};

		self::println("Obtained home directory from cPanel: " . $this->homedir);
		self::println("Obtained document root from cPanel:  " . $this->docroot);
		self::println("Obtained domain name from cPanel:    " . $this->domain);

		$acceptance_tests = self::get_active_acceptance_tests($cPanel, $homedir);
		
		self::println("Adding this test (".$this->run_id.") to registered tests list…");
		$run_time = microtime(true);
		array_push($acceptance_tests,
		           ['id' => $run_id,
		            'time' => $run_time
			   ]);
		
		self::write_acceptance_tests($cPanel, $homedir, $acceptance_tests);
		
		$valid_acceptance_test_ids = self::get_acceptance_test_ids($acceptance_tests);
		self::println("Current unexpired tests: [".implode(", ", $valid_acceptance_test_ids)."]");

		self::prune_inactive_acceptance_test_resources($cPanel, $valid_acceptance_test_ids);
	}

	private function prepare_db()
	{
		$cPanel = $this->cPanel;
		$username = &$this->credentials['username'];
		$run_id = &$this->run_id;
		$db_id = "${username}_${run_id}";
		self::println("Creating new MySQL database \"${db_id}\"…");
		$cPanel->uapi->Mysql->create_database(['name' => $db_id]);

		self::println("Creating new MySQL user \"${db_id}\" with password \"${run_id}\"…");
		$cPanel->uapi->Mysql->create_user(['name' => $db_id, 'password' => $run_id]);
		self::println("Granting ALL PRIVILEGES to MySQL user \"${db_id}\"…");
		$cPanel->uapi->Mysql->set_privileges_on_database(['user' => $db_id,
		                                                  'database' => $db_id,
		                                                  'privileges' => 'ALL PRIVILEGES'
		                                                  ]);
	}

	private function prepare_fs()
	{
		$cPanel = $this->cPanel;
		$app_archive = self::archive_app(APP_PATH, $this->run_id);
		$app_archive_path = stream_get_meta_data($app_archive)['uri'];
		$app_archive_name = basename($app_archive_path);
		self::println("Sending archive to cPanel server…");
		$cPanel->uapi->post->Fileman
		       ->upload_files(['dir' => self::TARGET_RELPATH,
		                       'file-1' => new CURLFile($app_archive_path)
		                       ]);
		self::println("Extracting archive on cPanel server…");
		$cPanel->api2->Fileman
		       ->fileop(['op' => 'extract',
		                 'sourcefiles' => self::TARGET_RELPATH.$app_archive_name,
		                 'destfiles' => '.'
		                 ]);
		self::println("Deleting archive from cPanel server…");
		$cPanel->api2->Fileman
		       ->fileop(['op' => 'unlink',
		                 'sourcefiles' => self::TARGET_RELPATH.$app_archive_name
		                 ]);
	}

	private static function println($text = '')
	{
		echo("${text}\n");
		//$prefix = debug_backtrace()[1]['function'];
		//echo("[\033[1m${prefix}\033[0m] ${text}\n");
	}

	private static function prune_inactive_acceptance_test_resources($cPanel, $valid_acceptance_test_ids)
	{
		self::println("Pruning expired tests…");
		$listdbs = $cPanel->api2->MysqlFE->listdbs()->{'cpanelresult'}->{'data'};
		self::prune_mysql_databases($listdbs, $valid_acceptance_test_ids, $cPanel);

		$listdbusers = $cPanel->api2->MysqlFE->listusers()->{'cpanelresult'}->{'data'};
		self::prune_mysql_users($listdbusers, $valid_acceptance_test_ids, $cPanel);

		$target_files_apiresponse = $cPanel->uapi->Fileman->list_files(['dir' => self::TARGET_RELPATH]);
		$target_files = $target_files_apiresponse->{'data'};
		foreach ($target_files as $target_file)
		{
			$questionable_filename = $target_file->{'file'};
			if (substr($questionable_filename, 0, strlen(self::TEST_PREFIX)) === self::TEST_PREFIX &&
			    !in_array($questionable_filename, $valid_acceptance_test_ids))
			{
				self::println("Deleting expired test folder \"".self::TARGET_RELPATH.$questionable_filename."\"…");
				$cPanel->api2->Fileman->fileop(['op' => 'unlink', 'sourcefiles' => self::TARGET_RELPATH.$questionable_filename]);
			}
		}
	}

	private static function get_active_acceptance_tests($cPanel, $homedir)
	{
		self::println("Retrieving existing registered tests from cPanel account…");
		$acceptance_tests = [];
		$acceptance_tests_apiresponse = $cPanel->uapi->Fileman->get_file_content(['dir' => $homedir, 'file' => 'acceptance_tests.status.txt']);
		if (!is_null($acceptance_tests_apiresponse->{'data'}))
		{
			$acceptance_tests_raw = $acceptance_tests_apiresponse->{'data'}->{'content'};
			$acceptance_tests = (array) json_decode($acceptance_tests_raw, true);
			self::prune_acceptance_tests($acceptance_tests);
		}
		return $acceptance_tests;
	}

	private static function prune_acceptance_tests(array &$list, $id_to_remove = null)
	{
		foreach ($list as $key => $item)
		{
			$time = $item['time'];
			if ($item['id'] === $id_to_remove || $time <= strtotime("now - 10 seconds"))
			{
				unset($list[$key]);
			}
		}
		$list = array_values($list);
		return $list;
	}

	private static function get_acceptance_test_ids(array $list)
	{
		$ids = [];
		foreach ($list as $item)
		{
			$ids[] = $item['id'];
		}
		return $ids;
	}

	private static function write_acceptance_tests($cPanel, $homedir, $acceptance_tests)
	{
		$acceptance_tests_json = json_encode($acceptance_tests, JSON_PRETTY_PRINT);
		
		self::println("Saving registered tests list to cPanel account…");
		$cPanel->uapi->Fileman->save_file_content(['dir' => $homedir, 'file' => 'acceptance_tests.status.txt', 'content' => $acceptance_tests_json]);
	}

	private static function prune_mysql_databases($dbs, $ids, $cPanel)
	{
		foreach ($dbs as $db)
		{
			$db = (array) $db;
			$offset = strpos($db['db'], self::TEST_PREFIX);
			$questionable_db = substr($db['db'], $offset);
			if (!in_array($questionable_db, $ids))
			{
				self::println("Deleting expired MySQL database \"".$db['db']."\"…");
				$cPanel->uapi->Mysql->delete_database(['name' => $db['db']]);
			}
		}
	}

	private static function prune_mysql_users($users, $ids, $cPanel)
	{
		foreach ($users as $user)
		{
			$user = (array) $user;
			$offset = strpos($user['user'], self::TEST_PREFIX);
			$questionable_user = substr($user['user'], $offset);
			if (!in_array($questionable_user, $ids))
			{
				self::println("Deleting expired MySQL user \"".$user['user']."\"…");
				$cPanel->uapi->Mysql->delete_user(['name' => $user['user']]);
			}
		}
	}

	private static function archive_app($path, $prefix = '')
	{
		$tmp_file = tmpfile();
		$tmp_file_path = stream_get_meta_data($tmp_file)['uri'];
		self::println("Touched temporary archive file; path: ".$tmp_file_path);
		$archive = new ZipArchive();
		$archive->open($tmp_file_path, ZipArchive::OVERWRITE);
		$i = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
		self::println("Adding app to temporary archive…");
		$path = realpath($path);
		foreach ($i as $relpath => $value)
		{
			$realpath = realpath($relpath);
			if (substr($realpath, 0, strlen($path)) === $path)
				$relpath = substr($realpath, strlen($path));
			if (substr($relpath, -3) === "/.." ||
			    substr($relpath, -2) === "/." ||
			    !file_exists($realpath) ||
			    !is_file($realpath) ||
			    empty($relpath)) continue;
			$relpath = $prefix . $relpath;
			$archive->addFile($realpath, $relpath);
		}
		$archive->close();

		return $tmp_file;
	}
}
