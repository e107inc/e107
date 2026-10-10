<?php
spl_autoload_register(function($class_name) {
	$candidate_path = __DIR__ . "/$class_name.php";
	if (file_exists($candidate_path))
	{
		include_once($candidate_path);
	}
});

/**
 * What `e107-tests graft` puts in place of an older tree's own factory: that tree's helpers prepare through its E107Preparer, in place.
 */
class PreparerFactory
{
	/** @var Preparer|null */
	private static $instance;

	/**
	 * For a tree whose bootstrap names the app path before APP_PATH exists; {@see create()} then hands back the same preparer.
	 *
	 * @param string $appPath
	 * @return Preparer
	 */
	public static function createForPath($appPath)
	{
		return self::$instance = new E107Preparer($appPath);
	}

	/**
	 * @return Preparer
	 */
	public static function create()
	{
		return self::$instance ?: self::$instance = new E107Preparer(APP_PATH);
	}
}
