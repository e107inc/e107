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
 * The site folders present under a site's media and system base directories besides its own.
 */
final class SiteFolderScan
{
	const HASH_PATTERN = '/^[a-f0-9]{10}$/';

	/** @var string */
	private $mediaBase;

	/** @var string */
	private $systemBase;

	/** @var string */
	private $activeHash;

	/**
	 * @param string $mediaBase directory holding every site's media folder
	 * @param string $systemBase directory holding every site's system folder
	 * @param string $activeHash the hash this site resolves to
	 */
	public function __construct($mediaBase, $systemBase, $activeHash)
	{
		$this->mediaBase = $mediaBase;
		$this->systemBase = $systemBase;
		$this->activeHash = (string) $activeHash;
	}

	/**
	 * @return SiteFolderScan|null one over the directories the running site's media and system folders sit in, or null when either folder does not end in the site's hash, overrides included
	 */
	public static function ofThisSite()
	{
		$e107 = \e107::getInstance();
		$hash = $e107->getSitePath();
		$bases = array();

		foreach(array('MEDIA', 'SYSTEM') as $folder)
		{
			$path = rtrim($e107->get_override_rel($folder), '/\\');

			if($hash === '' || basename($path) !== $hash || realpath(dirname($path)) === false)
			{
				return null;
			}

			$bases[] = realpath(dirname($path));
		}

		return new self($bases[0], $bases[1], $hash);
	}

	/**
	 * @return SiteFolder the pair this site uses
	 */
	public function active()
	{
		return $this->folder($this->activeHash);
	}

	/**
	 * @return SiteFolder the pair named by the hash of two empty values, whether or not it exists
	 */
	public function knownBad()
	{
		return $this->folder(\e107::getInstance()->makeSiteHash('', ''));
	}

	/**
	 * @return SiteFolder[] hash => pair, for every hash-named directory under either base other than the active one; the known-bad pair first, then newest first
	 */
	public function candidates()
	{
		$hashes = array();

		foreach(array($this->mediaBase, $this->systemBase) as $base)
		{
			foreach(self::hashDirectoriesUnder($base) as $hash)
			{
				$hashes[$hash] = true;
			}
		}

		unset($hashes[$this->activeHash]);

		$folders = array();
		$order = array();

		foreach(array_keys($hashes) as $hash)
		{
			$folder = $this->folder($hash);
			$summary = $folder->summary();
			$folders[$hash] = $folder;
			$order[$hash] = sprintf('%d%020d%s', $folder->isKnownBad() ? 0 : 1, PHP_INT_MAX - $summary['newest'], $hash);
		}

		uksort($folders, function($a, $b) use ($order)
		{
			return strcmp($order[$a], $order[$b]);
		});

		return $folders;
	}

	/**
	 * @param string $hash
	 * @return SiteFolder
	 */
	private function folder($hash)
	{
		return new SiteFolder($hash, $this->mediaBase, $this->systemBase);
	}

	/**
	 * @param string $base
	 * @return string[] hash-named directories directly under $base, symbolic links left out
	 */
	private static function hashDirectoriesUnder($base)
	{
		if(!is_dir($base))
		{
			return array();
		}

		$found = array();

		foreach(scandir($base) as $entry)
		{
			$path = rtrim($base, '/\\').'/'.$entry;

			if(preg_match(self::HASH_PATTERN, $entry) && is_dir($path) && !is_link($path))
			{
				$found[] = $entry;
			}
		}

		return $found;
	}
}
