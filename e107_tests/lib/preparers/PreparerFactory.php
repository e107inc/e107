<?php
spl_autoload_register(function($class_name) {
	$candidate_path = __DIR__ . "/$class_name.php";
	if (file_exists($candidate_path))
	{
		include_once($candidate_path);
	}
});

class PreparerFactory
{
	/** @var Preparer|null */
	private static $instance;

	/**
	 * Create a Preparer for the given app path. Called during bootstrap,
	 * before APP_PATH is defined.
	 *
	 * @param string $appPath
	 * @return Preparer
	 */
	public static function createForPath($appPath)
	{
		if (self::$instance !== null)
		{
			return self::$instance;
		}

		if (self::systemIsSlow())
		{
			self::$instance = self::createFromName('E107Preparer');
		}
		elseif (self::pathHasUsableGit($appPath))
		{
			self::$instance = new GitPreparer($appPath);
		}
		else
		{
			self::$instance = self::createFromName('E107Preparer');
		}

		codecept_debug('Instantiating Preparer: ' . get_class(self::$instance));
		return self::$instance;
	}

	/**
	 * @return Preparer
	 */
	public static function create()
	{
		if (self::$instance !== null)
		{
			return self::$instance;
		}
		return self::createForPath(APP_PATH);
	}

	/**
	 * @param string $class_name
	 * @return Preparer
	 */
	public static function createFromName($class_name)
	{
		return new $class_name();
	}

	private static function systemIsSlow()
	{
		return self::systemIsWindows();
	}

	private static function systemIsWindows()
	{
		return strtolower(substr(php_uname('s'), 0, 3)) === 'win';
	}

	private static function systemHasGit()
	{
		return stripos(shell_exec('git --version'), 'git version') !== false;
	}

	/**
	 * Returns true only if git can actually operate on the given path.
	 * Catches worktrees whose .git file points outside the current
	 * filesystem view (e.g. a Docker container with a host-worktree
	 * bind-mount).
	 *
	 * @param string $path
	 * @return bool
	 */
	private static function pathHasUsableGit($path)
	{
		if (!file_exists($path . '/.git'))
		{
			return false;
		}
		if (!self::systemHasGit())
		{
			return false;
		}
		$cmd = 'git -C ' . escapeshellarg($path) . ' rev-parse --git-dir 2>/dev/null';
		$rc = 0;
		$out = [];
		@exec($cmd, $out, $rc);
		return $rc === 0;
	}
}
