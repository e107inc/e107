<?php

namespace Helper;

/**
 * Journal of what this run wrote into the app root, kept on disk under tests/_output so a run that dies is healed on the next start.
 *
 * Inert until {@see AppFileRegistry::enable()} is called, which Extension\WorkspaceGuard does when the app runs in place.
 * Keep this class in PHP 5.6 syntax: release/v2.3.x runs the same file.
 */
class AppFileRegistry
{
	const JOURNAL = 'app-writes.jsonl';
	const BACKUPS = 'app-backup';

	const SCOPE_SUITE = 'suite';
	const SCOPE_TEST = 'test';

	/** @var bool */
	private static $enabled = false;

	/** @var string what a write is filed under until the scope is changed */
	private static $scope = self::SCOPE_SUITE;

	/** @var array<string,true> paths already journaled by this run */
	private static $seen = array();

	/** @var array<string,true> paths a reap has taken back since they were last written */
	private static $reaped = array();

	public static function enable()
	{
		self::$enabled = true;
	}

	/**
	 * Whether $relative_path was written and has since been reaped, so a caller holding "I already wrote that" has to write it again.
	 *
	 * @param string $relative_path
	 * @return bool
	 */
	public static function wasReaped($relative_path)
	{
		return isset(self::$reaped[$relative_path]);
	}

	/** @param string $scope one of the SCOPE_ constants */
	public static function scope($scope)
	{
		self::$scope = $scope;
	}

	/**
	 * Back up whatever is at $relative_path now, so it comes back whether or not the test that moves it lives to put it back.
	 *
	 * @param string $relative_path path relative to the app root
	 * @return void
	 */
	public static function park($relative_path)
	{
		if (!self::$enabled || isset(self::$seen[$relative_path]))
		{
			return;
		}

		$target = APP_PATH.'/'.$relative_path;

		if (!file_exists($target) || is_dir($target))
		{
			return;
		}

		$backup = self::backupDir().'/'.sha1($relative_path);

		if (!@copy($target, $backup))
		{
			throw new \RuntimeException('AppFileRegistry: could not back up '.$relative_path.' to '.$backup
				.', so the test that moves it could not have put it back');
		}

		self::append(array('p' => $relative_path, 'r' => basename($backup)));
		self::$seen[$relative_path] = true;
	}

	/**
	 * Record a path this run made, with the topmost directory that had to be created for it.
	 *
	 * @param string $relative_path path relative to the app root
	 * @param string[] $created_dirs absolute paths the deployer created, deepest first
	 */
	public static function didWrite($relative_path, array $created_dirs = array())
	{
		if (!self::$enabled)
		{
			return;
		}

		unset(self::$reaped[$relative_path]);

		if (!isset(self::$seen[$relative_path]))
		{
			self::append(array('p' => $relative_path));
			self::$seen[$relative_path] = true;
		}

		if (empty($created_dirs))
		{
			return;
		}

		$outermost = self::relativeTo(end($created_dirs));

		if ($outermost === null || isset(self::$seen[$outermost]))
		{
			return;
		}

		self::append(array('p' => $outermost, 'd' => 1));
		self::$seen[$outermost] = true;
	}

	/** Take back everything filed under $scope, innermost first. */
	public static function reap($scope, \Deployer $deployer)
	{
		$keep = array();
		$undo = array();

		foreach (self::read() as $entry)
		{
			if ($entry['s'] === $scope)
			{
				$undo[] = $entry;
				unset(self::$seen[$entry['p']]);
				continue;
			}
			$keep[] = $entry;
		}

		self::undo(array_reverse($undo), $deployer);
		self::write($keep);

		if ($scope === self::SCOPE_TEST)
		{
			self::$scope = self::SCOPE_SUITE;
		}
	}

	/** Replay a journal an earlier run died holding, then start a clean one. */
	public static function recover(\Deployer $deployer)
	{
		$stale = self::read();

		if (empty($stale))
		{
			return;
		}

		codecept_debug(sprintf('AppFileRegistry: replaying %d path(s) from a run that did not finish',
			count($stale)));

		self::$seen = array();
		self::undo(array_reverse($stale), $deployer);
		self::write(array());
	}

	/** @param array[] $entries */
	private static function undo(array $entries, \Deployer $deployer)
	{
		foreach ($entries as $entry)
		{
			if (isset($entry['r']))
			{
				self::restore($entry, $deployer);
				continue;
			}

			try
			{
				$deployer->removeAppPaths(array($entry['p']));
				self::$reaped[$entry['p']] = true;
			}
			catch (\Exception $e)
			{
				codecept_debug('AppFileRegistry: could not remove '.$entry['p'].': '.$e->getMessage());
			}
		}
	}

	private static function restore(array $entry, \Deployer $deployer)
	{
		$backup = self::backupDir().'/'.$entry['r'];

		if (!is_file($backup))
		{
			return;
		}

		try
		{
			$deployer->writeAppFile($entry['p'], file_get_contents($backup));
		}
		catch (\Exception $e)
		{
			codecept_debug('AppFileRegistry: could not restore '.$entry['p'].': '.$e->getMessage());
		}

		@unlink($backup);
	}

	private static function append(array $entry)
	{
		$entry['s'] = self::$scope;
		@file_put_contents(self::journal(), self::line($entry), FILE_APPEND | LOCK_EX);
	}

	/** @return array[] */
	private static function read()
	{
		$journal = self::journal();

		if (!is_file($journal))
		{
			return array();
		}

		$entries = array();

		foreach (file($journal, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
		{
			$entry = json_decode($line, true);

			if (is_array($entry) && isset($entry['p'], $entry['s']))
			{
				$entries[] = $entry;
			}
		}

		return $entries;
	}

	/** @param array[] $entries */
	private static function write(array $entries)
	{
		if (empty($entries))
		{
			@unlink(self::journal());
			return;
		}

		$lines = '';

		foreach ($entries as $entry)
		{
			$lines .= self::line($entry);
		}

		@file_put_contents(self::journal(), $lines, LOCK_EX);
	}

	/** One journal line; slashes and non-ASCII bytes stay literal so bin/e107-tests can read the paths back with sed. */
	private static function line(array $entry)
	{
		return json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
	}

	/**
	 * @param string $absolute
	 * @return string|null null when the path is not inside the app root
	 */
	private static function relativeTo($absolute)
	{
		$root = APP_PATH.'/';

		if (strpos($absolute, $root) !== 0)
		{
			return null;
		}

		return (string) substr($absolute, strlen($root));
	}

	private static function journal()
	{
		return codecept_output_dir().self::JOURNAL;
	}

	private static function backupDir()
	{
		$dir = codecept_output_dir().self::BACKUPS;

		if (!is_dir($dir))
		{
			@mkdir($dir, 0777, true);
		}

		return $dir;
	}
}
