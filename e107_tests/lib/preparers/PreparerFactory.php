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
	/**
	 * @return Preparer
	 */
	public static function create()
	{
		if (self::systemIsSlow())
		{
			return self::createFromName('E107Preparer');
		}
		elseif (self::systemHasGit() && self::appPathIsUsableGitRepo())
		{
			return self::createFromName('GitPreparer');
		}
		return self::createFromName('E107Preparer');
	}

	/**
	 * @param $class_name
	 * @return Preparer
	 */
	public static function createFromName($class_name)
	{
		codecept_debug('Instantiating Preparer: ' . $class_name);
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

	private static function appPathIsGitRepo()
	{
		return file_exists(APP_PATH."/.git");
	}

	/**
	 * Returns true only if git can actually operate on APP_PATH. This catches
	 * cases where a `.git` entry exists but the underlying gitdir is unreachable
	 * — most commonly a worktree (`.git` is a file pointing outside APP_PATH) that
	 * the parent repo isn't mounted into.
	 *
	 * @return bool
	 */
	private static function appPathIsUsableGitRepo()
	{
		if (!self::appPathIsGitRepo())
		{
			return false;
		}
		$cmd = 'git -C ' . escapeshellarg(APP_PATH) . ' rev-parse --git-dir 2>/dev/null';
		$rc = 0;
		$out = [];
		@exec($cmd, $out, $rc);
		return $rc === 0;
	}
}