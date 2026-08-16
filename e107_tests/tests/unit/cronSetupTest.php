<?php

class cronSetupTest extends \Codeception\Test\Unit
{
	/**
	 * Carries the two characters rawurlencode() changes, so that a URL built
	 * with the raw token cannot pass for one built with the encoded token.
	 */
	const TOKEN = 'ab+cd/ef0123456789abcdef0123456789abcdef';

	protected function _before()
	{
		require_once(e_HANDLER.'cron_class.php');
		e107::coreLan('cron', true);
	}

	/**
	 * @param array $overrides
	 * @return array
	 */
	private function unixEnv($overrides = array())
	{
		return array_merge(array(
			'os'              => 'unix',
			'panel'           => 'cpanel',
			'panel_url'       => 'https://example.com:2083/',
			'php_version'     => '8.3.1',
			'php_cli'         => '/opt/cpanel/ea-php83/root/usr/bin/php',
			'php_cli_pinned'  => true,
			'open_basedir'    => false,
			'cron_executable' => false,
			'cron_mode'       => '644',
			'root'            => '/home/site/public_html/',
			'siteurl'         => 'https://example.com/',
			'https'           => true,
			'host'            => 'example.com',
		), $overrides);
	}

	/**
	 * @param array $overrides
	 * @return array
	 */
	private function windowsEnv($overrides = array())
	{
		return array_merge($this->unixEnv(), array(
			'os'          => 'windows',
			'panel'       => null,
			'panel_url'   => null,
			'php_cli'     => 'C:\\php\\php.exe',
			'root'        => 'C:\\inetpub\\wwwroot\\',
			'siteurl'     => 'http://example.com/',
			'https'       => false,
		), $overrides);
	}

	/**
	 * @param array $options
	 * @return array
	 */
	private function ids($options)
	{
		$ids = array();

		foreach($options as $option)
		{
			$ids[] = $option['id'];
		}

		return $ids;
	}

	/**
	 * @param array $options
	 * @param string $id
	 * @return array
	 */
	private function option($options, $id)
	{
		foreach($options as $option)
		{
			if($option['id'] === $id)
			{
				return $option;
			}
		}

		self::fail("No '$id' option was offered.");

		return array();
	}

	public function testUnixOptionsAreOfferedBestFirst()
	{
		$options = cronSetup::options($this->unixEnv(), self::TOKEN);

		self::assertSame(array('http', 'cli', 'shebang'), $this->ids($options));
		self::assertTrue($options[0]['recommended'], 'the web request must be the recommended option');
		self::assertFalse($options[1]['recommended']);
		self::assertFalse($options[2]['recommended']);
	}

	public function testUnixWebRequestCommands()
	{
		$http = $this->option(cronSetup::options($this->unixEnv(), self::TOKEN), 'http');

		$url = 'https://example.com/cron.php?token=ab%2Bcd%2Fef0123456789abcdef0123456789abcdef';

		self::assertSame($url, $http['url']);
		self::assertSame("curl -fsS '$url' >/dev/null 2>&1", $http['command']);
		self::assertSame("wget -qO /dev/null '$url'", $http['alt_command']);
		self::assertSame("* * * * * curl -fsS '$url' >/dev/null 2>&1", $http['crontab_line']);
		self::assertArrayNotHasKey('schtasks', $http);
		self::assertContains(LAN_CRON_SETUP_HTTP_FALLBACK_NOTE, $http['notes']);
		self::assertNotContains(LAN_CRON_SETUP_CURL_EXE_NOTE, $http['notes'], 'the Windows curl note has no business on a Unix host');
		self::assertStringNotContainsString('--max-time', $http['command']);
	}

	public function testUnixCommandLineUsesTheVerifiedBinary()
	{
		$cli = $this->option(cronSetup::options($this->unixEnv(), self::TOKEN), 'cli');

		$expected = '/opt/cpanel/ea-php83/root/usr/bin/php -q /home/site/public_html/cron.php token='
			.self::TOKEN.' >/dev/null 2>&1';

		self::assertSame($expected, $cli['command']);
		self::assertSame('* * * * * '.$expected, $cli['crontab_line']);
		self::assertContains(str_replace(array('[x]', '[y]'),
			array('8.3.1', '/opt/cpanel/ea-php83/root/usr/bin/php'), LAN_CRON_SETUP_PHP_FOUND), $cli['notes']);
		self::assertContains(LAN_CRON_SETUP_PANEL_HOWTO, $cli['notes'], 'the control panel instruction belongs on a Unix command');
		self::assertNotContains(LAN_CRON_SETUP_OPEN_BASEDIR_NOTE, $cli['notes']);
	}

	public function testUnixCommandLineFallsBackToThePath()
	{
		$env = $this->unixEnv(array('php_cli' => null, 'php_cli_pinned' => false));
		$cli = $this->option(cronSetup::options($env, self::TOKEN), 'cli');

		self::assertSame('php -q /home/site/public_html/cron.php token='.self::TOKEN.' >/dev/null 2>&1', $cli['command']);
		self::assertContains(str_replace('[x]', '8.3', LAN_CRON_SETUP_PHP_NOT_FOUND), $cli['notes']);
		self::assertNotContains(str_replace(array('[x]', '[y]'), array('8.3.1', ''), LAN_CRON_SETUP_PHP_FOUND), $cli['notes']);
	}

	public function testOpenBasedirIsReportedOnTheCommandLineOption()
	{
		$env = $this->unixEnv(array('php_cli' => null, 'php_cli_pinned' => false, 'open_basedir' => true));
		$cli = $this->option(cronSetup::options($env, self::TOKEN), 'cli');

		self::assertContains(LAN_CRON_SETUP_OPEN_BASEDIR_NOTE, $cli['notes']);
	}

	public function testShebangOptionReportsTheFileMode()
	{
		$options = cronSetup::options($this->unixEnv(), self::TOKEN);
		$shebang = $this->option($options, 'shebang');

		self::assertSame('/home/site/public_html/cron.php token='.self::TOKEN.' >/dev/null 2>&1', $shebang['command']);
		self::assertSame('* * * * * '.$shebang['command'], $shebang['crontab_line']);
		self::assertSame(LAN_CRON_SETUP_NOT_EXECUTABLE, $shebang['status']);
		self::assertSame('chmod 755 /home/site/public_html/cron.php', $shebang['chmod']);

		$executable = cronSetup::options($this->unixEnv(array('cron_executable' => true, 'cron_mode' => '755')), self::TOKEN);
		$shebang = $this->option($executable, 'shebang');

		self::assertSame(LAN_CRON_SETUP_EXECUTABLE, $shebang['status']);
		self::assertArrayNotHasKey('chmod', $shebang, 'a file that is already executable needs no chmod');
		self::assertSame(array('http', 'cli', 'shebang'), $this->ids($executable),
			'the shell script option is listed whatever the file mode is');
	}

	public function testWindowsOptions()
	{
		$options = cronSetup::options($this->windowsEnv(), self::TOKEN);

		self::assertSame(array('http', 'cli'), $this->ids($options),
			'a shebang cannot run on Windows, so it must not be offered there');

		$url = 'http://example.com/cron.php?token=ab%2Bcd%2Fef0123456789abcdef0123456789abcdef';
		$http = $this->option($options, 'http');

		self::assertSame($url, $http['url']);
		self::assertSame('curl.exe -fsS "'.$url.'"', $http['command']);
		self::assertSame('schtasks /create /sc minute /mo 1 /tn "e107 cron" /tr "curl.exe -fsS \"'.$url.'\""', $http['schtasks']);
		self::assertArrayNotHasKey('crontab_line', $http);
		self::assertArrayNotHasKey('alt_command', $http);
		self::assertContains(LAN_CRON_SETUP_CURL_EXE_NOTE, $http['notes']);

		$cli = $this->option($options, 'cli');

		self::assertSame('"C:\\php\\php.exe" "C:\\inetpub\\wwwroot\\cron.php" token='.self::TOKEN, $cli['command']);
		self::assertSame('schtasks /create /sc minute /mo 1 /tn "e107 cron" /tr "\"C:\\php\\php.exe\" '
			.'\"C:\\inetpub\\wwwroot\\cron.php\" token='.self::TOKEN.'"', $cli['schtasks']);
		self::assertArrayNotHasKey('crontab_line', $cli);
		self::assertContains(LAN_CRON_SETUP_SCHTASKS_ACCOUNT_NOTE, $cli['notes']);
		self::assertNotContains(LAN_CRON_SETUP_PANEL_HOWTO, $cli['notes'], 'crontab -e is no use on Windows');
	}

	public function testTheUrlFollowsTheSiteUrl()
	{
		$env = $this->unixEnv(array('siteurl' => 'http://example.org/e107/', 'https' => false, 'host' => 'example.org'));
		$http = $this->option(cronSetup::options($env, self::TOKEN), 'http');

		self::assertSame('http://example.org/e107/cron.php?token=ab%2Bcd%2Fef0123456789abcdef0123456789abcdef', $http['url']);
	}

	public function testEveryCommandCarriesTheTokenExactlyOnce()
	{
		$fields = array('url', 'command', 'alt_command', 'crontab_line', 'schtasks');
		$envs = array('unix' => $this->unixEnv(), 'windows' => $this->windowsEnv());

		foreach($envs as $name => $env)
		{
			foreach(cronSetup::options($env, self::TOKEN) as $option)
			{
				$needle = ($option['id'] === 'http') ? rawurlencode(self::TOKEN) : self::TOKEN;

				foreach($fields as $field)
				{
					if(!isset($option[$field]))
					{
						continue;
					}

					self::assertSame(1, substr_count($option[$field], $needle),
						"$name $option[id] $field must carry the token exactly once");
				}
			}

			$http = $this->option(cronSetup::options($env, self::TOKEN), 'http');

			self::assertStringNotContainsString(self::TOKEN, $http['url'],
				"$name: the URL must carry the token rawurlencoded");
		}
	}

	public function testCandidatePathsPutAVersionedBinaryFirst()
	{
		$paths = cronSetup::candidatePaths('unix', '8.3.1', '/opt/cpanel/ea-php83/root/usr/bin');

		self::assertSame('/opt/cpanel/ea-php83/root/usr/bin/php8.3', $paths[0]);
		self::assertSame('/usr/bin/php', $paths[count($paths) - 1]);

		self::assertLessThan(array_search('/usr/local/bin/php', $paths, true),
			array_search('/opt/plesk/php/8.3/bin/php', $paths, true),
			'a version-pinned path must be tried before a bare one');

		self::assertContains('/usr/local/bin/ea-php83', $paths);
		self::assertContains('/usr/local/php83/bin/php', $paths);
		self::assertContains('/opt/remi/php83/root/usr/bin/php', $paths);
		self::assertContains('/usr/bin/php8.3', $paths);
		self::assertSame($paths, array_values(array_unique($paths)), 'no path may be offered twice');

		$windows = cronSetup::candidatePaths('windows', '8.3.1', 'C:\\php', 'C:\\other\\php.exe');

		self::assertSame(array('C:\\php\\php.exe', 'C:\\other\\php.exe'), $windows);
	}

	public function testDetectEnvironmentDescribesThisServer()
	{
		$env = cronSetup::detectEnvironment();

		$expected = array(
			'os'              => 'string',
			'panel'           => 'NULL|string',
			'panel_url'       => 'NULL|string',
			'php_version'     => 'string',
			'php_cli'         => 'NULL|string',
			'php_cli_pinned'  => 'boolean',
			'open_basedir'    => 'boolean',
			'cron_executable' => 'boolean',
			'cron_mode'       => 'NULL|string',
			'root'            => 'string',
			'siteurl'         => 'string',
			'https'           => 'boolean',
			'host'            => 'string',
		);

		self::assertSame(array_keys($expected), array_keys($env));

		foreach($expected as $key => $types)
		{
			self::assertContains(gettype($env[$key]), explode('|', $types), "$key is a ".gettype($env[$key]));
		}

		self::assertContains($env['os'], array('unix', 'windows'));
		self::assertSame(PHP_VERSION, $env['php_version']);
		self::assertSame(e_ROOT, $env['root']);
		self::assertStringEndsWith('/', $env['siteurl']);
		self::assertSame($env['https'], stripos($env['siteurl'], 'https://') === 0);

		if($env['panel'] !== null)
		{
			self::assertContains($env['panel'], array('cpanel', 'directadmin', 'plesk'));
			self::assertStringStartsWith('https://'.$env['host'].':', $env['panel_url']);
		}

		if($env['cron_mode'] !== null)
		{
			self::assertRegExp('/^[0-7]{3}$/', $env['cron_mode']);
		}

		if($env['php_cli'] !== null)
		{
			self::assertTrue(is_executable($env['php_cli']), 'a reported binary must be executable');
		}
	}
}
