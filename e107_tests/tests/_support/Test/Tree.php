<?php
namespace Test;

/**
 * The PHP files of the Codeception tree and of the tree e107 ships, with the token look-ups a conventions test reads them by.
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

	/**
	 * The shipped PHP files a convention can reach: the pages at the installation root and the trees named, minus anything vendored.
	 *
	 * @param array $trees directory names under e_ROOT
	 * @return string[] absolute paths
	 */
	public static function shippedPhpFiles(array $trees)
	{
		$paths = glob(e_ROOT . '*.php');

		foreach ($trees as $tree)
		{
			if (!is_dir(e_ROOT . $tree))
			{
				continue;
			}

			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(e_ROOT . $tree, \RecursiveDirectoryIterator::SKIP_DOTS));

			foreach ($files as $file)
			{
				if (substr($file->getFilename(), -4) === '.php')
				{
					$paths[] = $file->getPathname();
				}
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

		return $found;
	}

	/**
	 * Whether $tokens[$i] calls one of $names as a plain function, rather than naming a method, a static, a declaration or a string.
	 *
	 * @param array $tokens from token_get_all()
	 * @param int $i
	 * @param array $names function names, unqualified
	 * @return bool
	 */
	public static function isFunctionCall(array $tokens, $i, array $names)
	{
		$token = $tokens[$i];
		$callable = is_array($token) && ($token[0] === T_STRING
			|| (defined('T_NAME_FULLY_QUALIFIED') && $token[0] === T_NAME_FULLY_QUALIFIED));

		if (!$callable || !in_array(ltrim($token[1], '\\'), $names, true))
		{
			return false;
		}

		return self::significantToken($tokens, $i, 1) === '('
			&& !in_array(self::significantToken($tokens, $i, -1), array('->', '?->', '::', 'function'), true);
	}

	/**
	 * @param array $tokens from token_get_all()
	 * @param int $i
	 * @param int $direction 1 forwards, -1 backwards
	 * @return string the nearest token in $direction that is not whitespace or a comment, '' at either end
	 */
	public static function significantToken(array $tokens, $i, $direction)
	{
		for ($j = $i + $direction; isset($tokens[$j]); $j += $direction)
		{
			$token = $tokens[$j];

			if (!is_array($token))
			{
				return $token;
			}

			if (!in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				return $token[1];
			}
		}

		return '';
	}
}
