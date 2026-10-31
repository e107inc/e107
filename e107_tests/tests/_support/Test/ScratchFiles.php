<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace Test;

/**
 * Throwaway folders under the system temp dir, for a test that needs real files outside the app.
 */
trait ScratchFiles
{
	/** @var string[] */
	private $scratchDirs = array();

	/**
	 * A fresh, empty directory, removed by {@see ScratchFiles::removeScratch()}.
	 *
	 * @param string $label
	 * @return string with a trailing slash
	 */
	protected function makeScratchDir($label)
	{
		$dir = sys_get_temp_dir().'/e107_'.$label.'_'.uniqid('', true).'/';
		mkdir($dir, 0777, true);
		$this->scratchDirs[] = $dir;

		return $dir;
	}

	/**
	 * Write a file, making the folders above it.
	 *
	 * @param string $path
	 * @param string $contents
	 * @return string $path
	 */
	protected function writeScratchFile($path, $contents)
	{
		if(!is_dir(dirname($path)))
		{
			mkdir(dirname($path), 0777, true);
		}

		file_put_contents($path, $contents);

		return $path;
	}

	/**
	 * Remove every directory {@see ScratchFiles::makeScratchDir()} made; call it from _after().
	 *
	 * @return void
	 */
	protected function removeScratch()
	{
		foreach($this->scratchDirs as $dir)
		{
			self::removeTree($dir);
		}

		$this->scratchDirs = array();
	}

	/**
	 * @param string $dir
	 * @return void
	 */
	protected static function removeTree($dir)
	{
		if(!is_dir($dir))
		{
			return;
		}

		@chmod($dir, 0755);

		foreach(array_diff(scandir($dir), array('.', '..')) as $entry)
		{
			$path = rtrim($dir, '/').'/'.$entry;

			if(is_dir($path) && !is_link($path))
			{
				self::removeTree($path);
			}
			else
			{
				@unlink($path);
			}
		}

		@rmdir($dir);
	}
}
