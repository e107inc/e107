<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Cache;

/**
 * A small JSON record in a directory, written behind a PHP exit line so that a
 * web server which serves the directory prints nothing for it.
 */
class Stamp
{
	const PREFIX = '<?php exit; ?>';

	/** @var string */
	private $directory;

	/**
	 * @param string $directory
	 *   Where the records live, trailing separator included; e_CACHE on a site.
	 */
	public function __construct($directory)
	{
		$this->directory = (string) $directory;
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @return bool
	 */
	public function write($name, array $data)
	{
		return (bool) @file_put_contents($this->path($name), self::PREFIX.json_encode($data), LOCK_EX);
	}

	/**
	 * @param string $name
	 * @return array|null
	 *   The stored record, or null when there is none or it does not parse.
	 */
	public function read($name)
	{
		$file = $this->path($name);
		clearstatcache(true, $file);

		if(!is_readable($file))
		{
			return null;
		}

		$raw = (string) @file_get_contents($file);

		if(strpos($raw, self::PREFIX) !== 0)
		{
			return null;
		}

		$data = json_decode((string) substr($raw, strlen(self::PREFIX)), true);

		return is_array($data) ? $data : null;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function clear($name)
	{
		@unlink($this->path($name));
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function path($name)
	{
		return $this->directory.preg_replace('#\W#', '', (string) $name).'.php';
	}
}
