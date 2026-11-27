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
 * A value worked out from source files, kept in the site's own cache folder until one of them changes.
 */
final class SourceDerived
{
	const FORMAT = 1;

	/** @var string */
	private $prefix;

	/** @var string|null */
	private $directory;

	/** @var callable */
	private $write;

	/** @var array */
	private $memo = array();

	/** @var bool */
	private $pruned = false;

	/**
	 * @param string      $prefix    names this kind of entry in the store
	 * @param string|null $directory where entry files live, trailing separator included; null keeps entries in memory only
	 * @param callable    $write     writes a file whole, as {@see \e107::writeFileAtomic()} does
	 */
	public function __construct($prefix, $directory, $write)
	{
		$this->prefix = (string) $prefix;
		$this->directory = $directory;
		$this->write = $write;
	}

	/**
	 * What $derive makes of the file's contents, worked out again only when the file, or a file it depends on, has changed.
	 *
	 * @param string   $path
	 * @param callable $derive    given the contents and $dependsOn, returns a value var_export() can write back
	 * @param string[] $dependsOn files $derive reads for itself
	 * @return mixed|null null when a file cannot be read
	 */
	public function get($path, $derive, array $dependsOn = array())
	{
		$paths = array($path);
		$stats = array();

		foreach($dependsOn as $file)
		{
			$paths[] = $file;
		}

		foreach($paths as $file)
		{
			$stat = @stat($file);

			if($stat === false)
			{
				return null;
			}

			$stats[] = $stat;
		}

		$key = $paths[0];

		if(isset($this->memo[$key]) && $this->trusted($this->memo[$key], $paths, $stats))
		{
			return $this->memo[$key]['value'];
		}

		$entry = $this->load($key);

		if($entry !== null && $this->trusted($entry, $paths, $stats))
		{
			$this->memo[$key] = $entry;

			return $entry['value'];
		}

		$source = @file_get_contents($path);

		if($source === false)
		{
			return null;
		}

		$files = array();

		foreach($stats as $i => $stat)
		{
			$hash = ($i === 0) ? hash('sha256', $source) : @hash_file('sha256', $paths[$i]);

			$files[] = array('size' => $stat['size'], 'mtime' => $stat['mtime'], 'ino' => $stat['ino'], 'hash' => $hash);
		}

		$known = ($entry !== null && $entry['paths'] === $paths && $this->hashes($entry['files']) === $this->hashes($files));

		$fresh = array(
			'format'   => self::FORMAT,
			'paths'    => $paths,
			'files'    => $files,
			'recorded' => time(),
			'value'    => $known ? $entry['value'] : call_user_func($derive, $source, $dependsOn),
		);

		if(!$known || $entry['files'] !== $files || ($this->settled($fresh) && !$this->settled($entry)))
		{
			$this->store($fresh);
		}

		$this->memo[$key] = $fresh;

		return $fresh['value'];
	}

	/**
	 * Whether the entry may answer without reading the files: each looks the same, and looked so a second before the entry was made.
	 *
	 * @param array $entry
	 * @param array $paths
	 * @param array $stats
	 * @return bool
	 */
	private function trusted(array $entry, array $paths, array $stats)
	{
		if($entry['paths'] !== $paths)
		{
			return false;
		}

		foreach($stats as $i => $stat)
		{
			$file = $entry['files'][$i];

			if($file['size'] !== $stat['size'] || $file['mtime'] !== $stat['mtime'] || $file['ino'] !== $stat['ino'])
			{
				return false;
			}
		}

		return $this->settled($entry);
	}

	/**
	 * Whether every file is older than the second the entry was recorded in, so the entry can be trusted on a stat alone.
	 *
	 * @param array $entry
	 * @return bool
	 */
	private function settled(array $entry)
	{
		foreach($entry['files'] as $file)
		{
			if($file['mtime'] >= $entry['recorded'])
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $files
	 * @return array
	 */
	private function hashes(array $files)
	{
		$hashes = array();

		foreach($files as $file)
		{
			$hashes[] = $file['hash'];
		}

		return $hashes;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function name($key)
	{
		return $this->prefix.'_v'.self::FORMAT.'_'.hash('sha256', $key);
	}

	/**
	 * @param string $key
	 * @return array|null
	 */
	private function load($key)
	{
		if($this->directory === null)
		{
			return null;
		}

		$file = $this->directory.$this->name($key).'.php';
		$entry = null;

		try
		{
			$entry = @include $file;
		}
		catch(\ParseError $e)
		{
			@unlink($file);
		}

		if(!is_array($entry) || !isset($entry['format'], $entry['paths'], $entry['files'], $entry['recorded'])
			|| !array_key_exists('value', $entry) || $entry['format'] !== self::FORMAT
			|| !is_array($entry['paths']) || !isset($entry['paths'][0]) || $entry['paths'][0] !== $key
			|| !is_array($entry['files']) || count($entry['files']) !== count($entry['paths']))
		{
			return null;
		}

		foreach($entry['files'] as $file)
		{
			if(!isset($file['size'], $file['mtime'], $file['ino'], $file['hash']))
			{
				return null;
			}
		}

		return $entry;
	}

	/**
	 * @param array $entry
	 * @return void
	 */
	private function store(array $entry)
	{
		if($this->directory === null || !is_dir($this->directory) || !is_writable($this->directory))
		{
			return;
		}

		call_user_func($this->write, $this->directory.$this->name($entry['paths'][0]).'.php', "<?php\nreturn ".var_export($entry, true).";\n");

		if(!$this->pruned)
		{
			$this->pruned = true;
			$this->prune();
		}
	}

	/**
	 * Delete this prefix's entries in a format no longer read, which no lookup would ever reach again.
	 *
	 * @return void
	 */
	private function prune()
	{
		$current = $this->prefix.'_v'.self::FORMAT.'_';
		$files = glob($this->directory.$this->prefix.'_*.php');

		foreach(is_array($files) ? $files : array() as $file)
		{
			if(strpos(basename($file), $current) !== 0)
			{
				@unlink($file);
			}
		}
	}
}
