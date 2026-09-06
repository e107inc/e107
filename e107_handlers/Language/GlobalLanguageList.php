<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Language;

/**
 * The installed plugins that ship a global language file.
 *
 * Derived state: the installed plugins intersected with those carrying
 * languages/English_global.php or languages/English/English_global.php. It is
 * held in the system cache rather than in a preference, so a missing entry is
 * a cache miss answered by a rebuild rather than damage that needs repairing.
 *
 * <code>
 * GlobalLanguageList::loadAll();      // boot: load every installed plugin's global LAN
 * GlobalLanguageList::has('news');    // does news ship one, and is it installed?
 * </code>
 *
 * {@see \e_plugin::clearCache()} calls {@see GlobalLanguageList::invalidate()},
 * and every install, uninstall, refresh, upgrade and folder scan runs through
 * it, so each of those drops this entry.
 */
class GlobalLanguageList
{
	const CACHE_TAG = 'Meta_lan_global';
	const CACHE_TIME = 120;

	private static $memo = null;

	/**
	 * Folder names of the installed plugins that ship a global language file.
	 *
	 * @return string[]
	 */
	public static function plugins()
	{
		if(is_array(self::$memo))
		{
			return self::$memo;
		}

		$cached = \e107::getCache()->retrieve(self::CACHE_TAG, self::CACHE_TIME, true, true);

		if(is_string($cached))
		{
			$list = json_decode($cached, true);

			if(is_array($list))
			{
				self::$memo = array_values($list);

				return self::$memo;
			}
		}

		return self::rebuild();
	}

	/**
	 * Is this plugin folder installed and shipping a global language file?
	 *
	 * @param string $plugin plugin folder name
	 * @return bool
	 */
	public static function has($plugin)
	{
		return in_array((string) $plugin, self::plugins(), true);
	}

	/**
	 * Include every listed plugin's global language file, nested layout first.
	 *
	 * @return void
	 */
	public static function loadAll()
	{
		foreach(self::plugins() as $path)
		{
			if(\e107::plugLan($path, 'global', true) === false)
			{
				\e107::plugLan($path, 'global');
			}
		}
	}

	/**
	 * Drop the list so the next read rebuilds it. Safe to call before boot completes.
	 *
	 * @return void
	 */
	public static function invalidate()
	{
		self::$memo = null;
		\e107::getCache()->clear(self::CACHE_TAG, true);
	}

	/**
	 * Builds its own {@see \e_plugin} because {@see \e_plugin::load()} moves the
	 * shared instance off the plugin its caller is working on. See issue 3531.
	 *
	 * An answer is only cached when both the plugin table and the folder scan
	 * had something to say, so a failed read stays a miss rather than becoming
	 * an empty list held for the whole of {@see GlobalLanguageList::CACHE_TIME}.
	 *
	 * @return string[]
	 */
	private static function rebuild()
	{
		$plug      = new \e_plugin();
		$installed = array_keys($plug->getInstalled());
		$list      = array();

		if(empty($installed) || !$plug->getDetected())
		{
			return $list;
		}

		foreach($installed as $path)
		{
			if($plug->load($path)->hasLanGlobal())
			{
				$list[] = (string) $path;
			}
		}

		\e107::getCache()->set(self::CACHE_TAG, json_encode($list), true, true, true);

		self::$memo = $list;

		return $list;
	}
}
