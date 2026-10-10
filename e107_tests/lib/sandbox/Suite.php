<?php

namespace Sandbox;

/**
 * What the runner needs to know about one Codeception suite, read from its own configuration.
 */
class Suite
{
	/** The group of the test whose site every other test of its suite starts from. */
	const SITE_TEMPLATE_GROUP = 'site-template';

	/** The most workers a suite that drives a browser runs on unless told otherwise. */
	const BROWSER_JOBS = 4;

	/** @var string */
	private $name;

	/** @var string */
	private $root;

	/** @var array */
	private $settings;

	/**
	 * @param string $name
	 * @param string $root the Codeception project directory, which test paths are relative to
	 * @param array $settings the suite's settings as Codeception resolves them
	 */
	public function __construct($name, $root, array $settings)
	{
		$this->name = $name;
		$this->root = rtrim($root, '/');
		$this->settings = $settings;
	}

	/** @return string */
	public function name()
	{
		return $this->name;
	}

	/** @return string */
	public function root()
	{
		return $this->root;
	}

	/** @return bool whether the suite drives the site over HTTP, so each sandbox needs a host of its own */
	public function servesHttp()
	{
		return $this->enables('modules', array('PhpBrowser', 'WebDriver'));
	}

	/**
	 * @param int $available the workers the env can run at once
	 * @return int the workers the suite runs on unless told otherwise
	 */
	public function defaultJobs($available)
	{
		return $this->enables('modules', array('WebDriver')) ? min($available, self::BROWSER_JOBS) : $available;
	}

	/** @return bool whether the suite was written for these sandboxes, so a test file never leans on what another left behind; an older tree's suite runs whole, in one process */
	public function isolatesFiles()
	{
		return $this->enables('extensions', array('Extension\SandboxGuard'));
	}

	/** @return bool whether the suite's tests can declare what they make e107 reach outside the stack; an older tree's attempts are refused and listed, but fail nothing */
	public function declaresOutbound()
	{
		return $this->enables('extensions', array('Extension\OutboundLedger'));
	}

	/** @return string[] the suite's test files, relative to the project directory */
	public function files()
	{
		$dir = rtrim($this->settings['path'], '/');
		$files = array();
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file)
		{
			if (preg_match('/(Cest|Test)\.php$/', $file->getFilename()))
			{
				$files[] = (string) substr($file->getPathname(), strlen($this->root) + 1);
			}
		}
		sort($files);

		return $files;
	}

	/**
	 * @param string $name a test file or directory as codecept takes one: relative to the suite or to the project, `.php` optional, a `:test` filter allowed
	 * @return string|null the file or directory relative to the project directory, or null if $name names none
	 */
	public function locate($name)
	{
		list($path) = self::split($name);
		$path = rtrim($path, '/');
		foreach (array(rtrim($this->settings['path'], '/'), $this->root) as $dir)
		{
			foreach (array($path, "$path.php") as $candidate)
			{
				if ($path !== '' && file_exists("$dir/$candidate"))
				{
					return (string) substr("$dir/$candidate", strlen($this->root) + 1);
				}
			}
		}

		return null;
	}

	/**
	 * @param string $name a test name as codecept takes one
	 * @return string[] the file or directory it names, and its `:test` filter or ''
	 */
	public static function split($name)
	{
		$path = preg_replace('/:[^\/]*$/', '', $name);

		return array($path, (string) substr($name, strlen($path)));
	}

	/** @return bool whether a test of this suite builds the site the others start from */
	public function hasSiteTemplate()
	{
		foreach ($this->files() as $file)
		{
			if (preg_match('/@group\s+'.self::SITE_TEMPLATE_GROUP.'\b/', file_get_contents("{$this->root}/$file")))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $kind modules or extensions
	 * @param string[] $names
	 * @return bool whether the suite enables any of $names
	 */
	private function enables($kind, array $names)
	{
		foreach ($this->settings[$kind]['enabled'] as $entry)
		{
			$name = is_array($entry) ? key($entry) : $entry;
			if (in_array(ltrim($name, '\\'), $names, true))
			{
				return true;
			}
		}

		return false;
	}
}
