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
		elseif (self::systemHasGit() && self::appPathIsGitRepo())
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
}