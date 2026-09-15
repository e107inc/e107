<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Storage;

/**
 * The media and system folder pair one site hash names under e107_media/ and e107_system/.
 *
 * A relative path here is root-prefixed: 'media/images/a.jpg' or 'system/temp/b.zip'. The
 * files a site regenerates on its own, the system cache tree and the index.html and .htaccess
 * placeholders the bootstrap writes, are not files() and are what tidy() deletes.
 */
final class SiteFolder
{
	const MEDIA = 'media';

	const SYSTEM = 'system';

	/** @var string */
	private $hash;

	/** @var string[] root name => absolute directory */
	private $roots;

	/**
	 * @param string $hash
	 * @param string $mediaBase directory holding every site's media folder
	 * @param string $systemBase directory holding every site's system folder
	 */
	public function __construct($hash, $mediaBase, $systemBase)
	{
		$this->hash = (string) $hash;
		$this->roots = array(
			self::MEDIA => rtrim($mediaBase, '/\\').'/'.$this->hash,
			self::SYSTEM => rtrim($systemBase, '/\\').'/'.$this->hash,
		);
	}

	/**
	 * @return string
	 */
	public function hash()
	{
		return $this->hash;
	}

	/**
	 * @return bool whether this hash is the one two empty values give, which no real site derives
	 */
	public function isKnownBad()
	{
		return $this->hash === \e107::getInstance()->makeSiteHash('', '');
	}

	/**
	 * @param string $relative root-prefixed
	 * @return string absolute path
	 */
	public function path($relative)
	{
		$parts = explode('/', $relative, 2);

		if(!isset($this->roots[$parts[0]]))
		{
			throw new \InvalidArgumentException("'$relative' names neither the media nor the system root");
		}

		return $this->roots[$parts[0]].(isset($parts[1]) && $parts[1] !== '' ? '/'.$parts[1] : '');
	}

	/**
	 * @return bool whether either root is a directory
	 */
	public function exists()
	{
		foreach($this->roots as $dir)
		{
			if(is_dir($dir))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string[] root-prefixed relative paths of every file and symbolic link the site does not regenerate, sorted
	 */
	public function files()
	{
		$files = array();

		foreach($this->roots as $root => $dir)
		{
			foreach(self::leaves($dir) as $leaf)
			{
				$relative = $root.'/'.$leaf;
				if(!self::isRegenerable($relative))
				{
					$files[] = $relative;
				}
			}
		}

		sort($files);

		return $files;
	}

	/**
	 * @return bool whether files() would list anything; the walk stops at the first such file
	 */
	public function holdsFiles()
	{
		foreach($this->roots as $root => $dir)
		{
			foreach(self::leaves($dir) as $leaf)
			{
				if(!self::isRegenerable($root.'/'.$leaf))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return array 'files' => how many files() lists, 'bytes' => their size, 'newest' => the newest one's Unix time or 0
	 */
	public function summary()
	{
		$summary = array('files' => 0, 'bytes' => 0, 'newest' => 0);

		foreach($this->files() as $relative)
		{
			$path = $this->path($relative);
			$summary['files']++;
			$summary['bytes'] += (int) @filesize($path);
			$summary['newest'] = max($summary['newest'], (int) @filemtime($path));
		}

		return $summary;
	}

	/**
	 * Deletes what the site regenerates and every directory left empty, the roots included.
	 *
	 * @return bool whether neither root remains
	 */
	public function tidy()
	{
		foreach($this->roots as $root => $dir)
		{
			if(is_dir($dir))
			{
				self::tidyTree($dir, $root);
			}
		}

		return !$this->exists();
	}

	/**
	 * @param string $relative root-prefixed
	 * @return bool
	 */
	private static function isRegenerable($relative)
	{
		$name = basename($relative);

		return strpos($relative, self::SYSTEM.'/cache/') === 0
			|| $name === 'index.html'
			|| ($name === '.htaccess' && strpos($relative, self::SYSTEM.'/') === 0);
	}

	/**
	 * @param string $dir
	 * @return \Generator|string[] paths relative to $dir of every file and symbolic link below it, as the walk finds them; a symbolic link to a directory is listed, not entered
	 */
	private static function leaves($dir)
	{
		if(!is_dir($dir))
		{
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach($iterator as $path => $info)
		{
			if($info->isLink() || $info->isFile())
			{
				yield (string) substr($path, strlen($dir) + 1);
			}
		}
	}

	/**
	 * @param string $dir
	 * @param string $root
	 * @return void
	 */
	private static function tidyTree($dir, $root)
	{
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach($iterator as $path => $info)
		{
			if($info->isDir() && !$info->isLink())
			{
				@rmdir($path);
			}
			elseif(self::isRegenerable($root.'/'.substr($path, strlen($dir) + 1)))
			{
				@unlink($path);
			}
		}

		@rmdir($dir);
	}
}
