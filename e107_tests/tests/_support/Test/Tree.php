<?php
namespace Test;

/**
 * The PHP files of the Codeception tree, for the conventions tests that read source.
 */
final class Tree
{
	/**
	 * @param string $directory relative to the Codeception root, '' for all of it
	 * @param array $skippedDirectories names of directories to leave out, at any depth
	 * @return array absolute paths
	 */
	public static function phpFiles($directory, array $skippedDirectories = array())
	{
		$root = rtrim(codecept_root_dir(), '/') . '/' . $directory;
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
