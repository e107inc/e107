<?php
/*
* e107 website system
*
* Copyright (C) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Aggregates what plugins declare in their e_rss.php addons
*
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Reads the e_rss.php addon of every installed plugin.
 *
 * The e_rss contract is this plugin's, not core's: nothing outside rss_menu
 * consumes it, and the feed descriptions addons return are labelled with this
 * plugin's own admin LAN constants.
 */
class rss_addons
{
	/**
	 * Every feed the installed plugins declare, flattened across plugins.
	 *
	 * Each entry is the array the addon's config() returned, with 'path' set to
	 * the declaring plugin folder:
	 *
	 *   array(
	 *     array(
	 *       'name'        => (string) admin-facing feed name,
	 *       'url'         => (string) feed key, stored in rss_url,
	 *       'topic_id'    => (string) category id, '' for the base feed,
	 *       'description' => (string) admin-facing description ('text' is an
	 *                        accepted older spelling of the same field),
	 *       'class'       => (string) userclass permitted to see the feed,
	 *       'limit'       => (string) default item count,
	 *       'path'        => (string) declaring plugin folder, set here and
	 *                        overwriting anything the addon supplied,
	 *     ),
	 *     ...
	 *   )
	 *
	 * {@see e107::getAddonConfig()} does this scan generically, but it cannot be
	 * used here: a v1.x addon declares its feeds by assigning to a global
	 * $eplug_rss_feed at include time rather than returning them, and that
	 * variable is only reachable from the scope that ran the include.
	 *
	 * ADMIN AREA ONLY. Addons label their feeds with admin language constants,
	 * and not only this plugin's: news names its feed with core's ADLAN_0. The
	 * pack loaded below covers this plugin's, so a front end caller still takes
	 * a fatal undefined constant. {@see rss_addons::legacyKeys()} carries plugin
	 * ownership without calling config() and is safe anywhere.
	 *
	 * @return array
	 */
	public static function feeds()
	{
		static $v1feeds = array(); // include_once only runs the file the first time

		$ret = array();
		$elist = e107::getPref('e_rss_list');

		if(empty($elist))
		{
			return $ret;
		}

		self::loadLan();

		foreach(array_keys($elist) as $plugin)
		{
			$filepath = e_PLUGIN.$plugin.'/e_rss.php';

			if(!is_readable($filepath))
			{
				continue;
			}

			$eplug_rss_feed = array();

			include_once($filepath);

			if(!empty($eplug_rss_feed))
			{
				$v1feeds[$plugin] = $eplug_rss_feed;
			}

			$feeds = e107::callMethod($plugin.'_rss', 'config');

			if(empty($feeds))
			{
				$feeds = isset($v1feeds[$plugin]) ? $v1feeds[$plugin] : array();
			}

			foreach($feeds as $feed)
			{
				$feed['path'] = $plugin;
				$ret[] = $feed;
			}
		}

		return $ret;
	}

	/**
	 * The numeric feed keys plugins still answer to, merged across plugins.
	 *
	 *   array(
	 *     6 => array(
	 *       'plugin' => (string) declaring plugin folder,
	 *       'url'    => (string) canonical text key to hand data(),
	 *     ),
	 *     ...
	 *   )
	 *
	 * Feeds were keyed by number before v0.7.6. The numeric namespace is global
	 * and finite, so the first plugin to claim a key keeps it and a later claim
	 * is dropped with a debug message naming both. It exists to keep pre-2009
	 * feed URLs resolving and is not a supported way to key a new feed.
	 *
	 * @return array
	 */
	public static function legacyKeys()
	{
		$ret = array();

		foreach(e107::getAddonConfig('e_rss', '', 'legacy') as $plugin => $keys)
		{
			if(!is_array($keys))
			{
				continue;
			}

			foreach($keys as $old => $canonical)
			{
				if(isset($ret[$old]))
				{
					e107::getMessage()->addDebug("Legacy RSS feed key <b>".$old."</b> is already claimed by <b>".$ret[$old]['plugin']."</b>, so <b>".$plugin."</b>'s claim on it is ignored. Legacy keys are first-come, first-served; give the feed a text key instead.");
					continue;
				}

				$ret[$old] = array('plugin' => $plugin, 'url' => $canonical);
			}
		}

		return $ret;
	}

	/**
	 * Addon config() methods label their feeds with this plugin's admin LAN
	 * constants, so that pack has to be loaded before any of them is called.
	 * Without it the first undefined constant is fatal on PHP 8.
	 */
	private static function loadLan()
	{
		$lan = e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php';

		if(is_readable($lan))
		{
			e107::includeLan($lan);
		}
	}
}
