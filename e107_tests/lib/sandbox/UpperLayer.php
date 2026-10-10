<?php

namespace Sandbox;

/**
 * The paths a sandbox's upper layer changed against the layers under it, less the ones a test may leave.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class UpperLayer
{
	/** Written by install.php and e_file::blockScriptExecution(), so they belong to the installer rather than to a test. */
	const INSTALL_ARTEFACTS = array('.htaccess', 'e107.htaccess', 'e107_system/e107Install.log', 'e107_media/.htaccess');

	/** Where a suite process lists, one JSON line per test, what its tests left behind; read by the runner. */
	const VIOLATIONS = 'sandbox-violations.jsonl';

	/** Written by the harness itself for each sandbox. */
	const HARNESS_FILES = array('e107_config.php');

	/** @var string */
	private $upper;

	/** @var string */
	private $start;

	/** @var string */
	private $merged;

	/** @var string[] */
	private $siteFolders;

	/**
	 * @param string $upper the overlay's upper directory
	 * @param string $start the tree the overlay started from: its one lower directory, or its lower layers merged read-only
	 * @param string $merged where the overlay is mounted
	 * @param string[] $siteFolders the names under e107_system/ and e107_media/ the site may fill
	 */
	public function __construct($upper, $start, $merged, array $siteFolders)
	{
		$this->upper = rtrim($upper, '/');
		$this->start = rtrim($start, '/');
		$this->merged = rtrim($merged, '/');
		$this->siteFolders = $siteFolders;
	}

	/**
	 * @param string[] $allowed further paths, relative to the app root, that may be left along with everything beneath them
	 * @return array<string,string[]> 'new' paths, 'modified' and 'deleted' worktree paths; a new directory is listed once, with a trailing slash
	 */
	public function violations(array $allowed)
	{
		$found = array('new' => array(), 'modified' => array(), 'deleted' => array());
		$this->walk('', array_flip($allowed), $found);

		return $found;
	}

	private function walk($dir, array $allowed, array &$found)
	{
		$entries = @scandir($this->upper.'/'.$dir);

		foreach ($entries ?: array() as $name)
		{
			$path = $dir.$name;

			if ($name === '.' || $name === '..' || $this->isAllowed($path, $allowed))
			{
				continue;
			}

			$upper = $this->upper.'/'.$path;
			$lower = self::exists($this->start.'/'.$path) ? $this->start.'/'.$path : null;

			if (filetype($upper) === 'char')
			{
				continue;
			}

			if (is_dir($upper) && !is_link($upper))
			{
				if ($lower === null)
				{
					$found['new'][] = $path.'/';
					continue;
				}
				$this->walk($path.'/', $allowed, $found);
				$this->hidden($path.'/', $allowed, $found);
				continue;
			}

			if ($lower === null)
			{
				$found['new'][] = $path;
			}
			elseif (!self::sameFile($upper, $lower))
			{
				$found['modified'][] = $path;
			}
		}

		if ($dir === '')
		{
			$this->hidden('', $allowed, $found);
		}
	}

	/** Lower entries of a directory the merged view no longer shows: deleted, or under a directory that was replaced. */
	private function hidden($dir, array $allowed, array &$found)
	{
		foreach (@scandir($this->start.'/'.$dir) ?: array() as $name)
		{
			$path = $dir.$name;

			if ($name !== '.' && $name !== '..' && !$this->isAllowed($path, $allowed) && !self::exists($this->merged.'/'.$path))
			{
				$found['deleted'][] = $path;
			}
		}
	}

	private function isAllowed($path, array $allowed)
	{
		if (in_array($path, self::INSTALL_ARTEFACTS, true) || in_array($path, self::HARNESS_FILES, true))
		{
			return true;
		}

		$parts = explode('/', $path, 3);

		if (count($parts) >= 2 && ($parts[0] === 'e107_system' || $parts[0] === 'e107_media') && in_array($parts[1], $this->siteFolders, true))
		{
			return true;
		}

		for ($prefix = $path; $prefix !== '.' && $prefix !== ''; $prefix = dirname($prefix))
		{
			if (isset($allowed[$prefix]))
			{
				return true;
			}
		}

		return false;
	}

	private static function exists($path)
	{
		return file_exists($path) || is_link($path);
	}

	private static function sameFile($a, $b)
	{
		if (is_link($a) || is_link($b))
		{
			return is_link($a) && is_link($b) && readlink($a) === readlink($b);
		}

		return is_file($b) && filesize($a) === filesize($b) && sha1_file($a) === sha1_file($b);
	}
}
