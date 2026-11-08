<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Media;

/**
 * The media library items that admit only some callers, read from an index in the system cache so that a public item costs no query.
 *
 * @internal
 */
final class RestrictedMedia
{
	const CACHE_TAG = 'Media_restricted';

	const LIFETIME_MINUTES = 1440;

	/** @var \ecache */
	private $cache;

	/** @var \e_db */
	private $db;

	/** @var string absolute path of the system cache directory, '' when it does not exist */
	private $directory;

	/** @var array|null folded media_url => media_userclass */
	private $index;

	/**
	 * @param \ecache $cache
	 * @param \e_db $db
	 */
	public function __construct(\ecache $cache, \e_db $db)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->directory = (string) realpath(dirname($cache->cache_fname(self::CACHE_TAG, true)));
	}

	/**
	 * Drops the index of every language's media table, so the next lookup reads the library again; safe to call from a shutdown function.
	 *
	 * @return void
	 */
	public function forget()
	{
		$this->index = null;

		if($this->directory !== '')
		{
			$this->cache->delete($this->directory, self::CACHE_TAG.'*.cache.php', true);
		}
	}

	/**
	 * @param string $key media_url, as {@see \e_parse::createConstants()} spells it
	 * @return string|null the media_userclass that gates $key, e_UC_NOBODY when the library cannot be read, or null when $key admits everyone
	 */
	public function classOf($key)
	{
		$index = $this->index();

		if($index === null)
		{
			return (string) e_UC_NOBODY;
		}

		$key = self::fold($key);

		return isset($index[$key]) ? $index[$key] : null;
	}

	/**
	 * @return array|null folded media_url => media_userclass, or null when the library cannot be read
	 */
	private function index()
	{
		if($this->index !== null)
		{
			return $this->index;
		}

		$tag = self::CACHE_TAG.'_'.$this->db->hasLanguage('core_media');
		$this->index = $this->cached($tag);

		if($this->index !== null)
		{
			return $this->index;
		}

		$lock = @fopen($this->cache->cache_fname($tag, true).'.lock', 'c');

		if($lock !== false)
		{
			flock($lock, LOCK_EX);
		}

		$this->index = $this->cached($tag);

		if($this->index === null)
		{
			$this->index = $this->build($tag);
		}

		if($lock !== false)
		{
			flock($lock, LOCK_UN);
			fclose($lock);
		}

		return $this->index;
	}

	/**
	 * @param string $tag
	 * @return array|null the index the system cache holds under $tag, or null when it holds none that parses
	 */
	private function cached($tag)
	{
		$cached = json_decode((string) $this->cache->retrieve_sys($tag, self::LIFETIME_MINUTES, true), true);

		return is_array($cached) ? $cached : null;
	}

	/**
	 * @param string $tag
	 * @return array|null the index read from the library and stored under $tag, or null when the library cannot be read
	 */
	private function build($tag)
	{
		if($this->db->select('core_media', 'media_url, media_userclass') === false)
		{
			return null;
		}

		$index = array();

		while($row = $this->db->fetch())
		{
			$userclass = trim((string) $row['media_userclass']);

			if(!self::admitsEveryone($userclass))
			{
				$index[self::fold($row['media_url'])] = $userclass;
			}
		}

		$json = json_encode($index);

		if($json !== false)
		{
			$this->cache->set_sys($tag, $json, true);
		}

		return $index;
	}

	/**
	 * @param string $key
	 * @return string $key lowercased the same way whichever entry point booted e107
	 */
	private static function fold($key)
	{
		return function_exists('mb_strtolower') ? mb_strtolower((string) $key, 'UTF-8') : \eShims::strtolower($key);
	}

	/**
	 * @param string $userclass trimmed media_userclass
	 * @return bool whether it admits everyone: the column default '' or any entry naming e_UC_PUBLIC
	 */
	private static function admitsEveryone($userclass)
	{
		if($userclass === '')
		{
			return true;
		}

		foreach(explode(',', $userclass) as $class)
		{
			$class = trim($class);

			if(is_numeric($class) && (int) $class === e_UC_PUBLIC)
			{
				return true;
			}
		}

		return false;
	}
}
