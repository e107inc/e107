<?php
namespace Test;

/**
 * The PHP files of the Codeception tree and of the installation it tests, for the conventions tests that read source.
 */
final class Tree
{
	/** The trees core ships its own PHP in; e107_tests is this class's other half and anything vendored is somebody else's. */
	private static $coreTrees = array('e107_admin', 'e107_core', 'e107_handlers', 'e107_plugins');

	/** A theme answers to the one framework its own theme.xml loads, so a rule written for core's markup stops at this tree. */
	private static $themeTree = 'e107_themes';

	/**
	 * @param string $directory relative to the Codeception root, '' for all of it
	 * @param array $skippedDirectories names of directories to leave out, at any depth
	 * @return array absolute paths
	 */
	public static function phpFiles($directory, array $skippedDirectories = array())
	{
		return self::phpFilesUnder(rtrim(codecept_root_dir(), '/') . '/' . $directory, $skippedDirectories);
	}

	/**
	 * The installation's own PHP sources: the pages at its root and the trees core ships code in, the bundled themes included, minus anything vendored.
	 *
	 * @return array absolute paths
	 */
	public static function appPhpFiles()
	{
		return self::phpFilesInTrees(array_merge(self::$coreTrees, array(self::$themeTree)));
	}

	/**
	 * The same, less the theme tree, for a rule core's own markup must follow and a bundled theme need not.
	 *
	 * @return array absolute paths
	 */
	public static function corePhpFiles()
	{
		return self::phpFilesInTrees(self::$coreTrees);
	}

	/**
	 * @param array $trees names of directories under the installation root
	 * @return array absolute paths, the root's own pages included
	 */
	private static function phpFilesInTrees(array $trees)
	{
		$paths = glob(e_ROOT . '*.php');

		foreach ($trees as $tree)
		{
			if (is_dir(e_ROOT . $tree))
			{
				$paths = array_merge($paths, self::phpFilesUnder(e_ROOT . $tree));
			}
		}

		$found = array();

		foreach ($paths as $path)
		{
			if (strpos(str_replace('\\', '/', $path), '/vendor/') === false)
			{
				$found[] = $path;
			}
		}

		sort($found);

		return $found;
	}

	/**
	 * @param string $root absolute
	 * @param array $skippedDirectories names of directories to leave out, at any depth
	 * @return array absolute paths
	 */
	private static function phpFilesUnder($root, array $skippedDirectories = array())
	{
		$directories = new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS);
		$kept = new \RecursiveCallbackFilterIterator($directories, function ($current, $key, $iterator) use ($skippedDirectories)
		{
			return !($iterator->hasChildren() && in_array($current->getFilename(), $skippedDirectories, true));
		});

		$files = array();

		foreach (new \RecursiveIteratorIterator($kept) as $file)
		{
			if (substr($file->getFilename(), -4) === '.php')
			{
				$files[] = $file->getPathname();
			}
		}

		sort($files);

		return $files;
	}

	/**
	 * @param string $path
	 * @return string $path relative to the Codeception root
	 */
	public static function relativePath($path)
	{
		$root = codecept_root_dir();

		return strpos($path, $root) === 0 ? (string) substr($path, strlen($root)) : $path;
	}
}
