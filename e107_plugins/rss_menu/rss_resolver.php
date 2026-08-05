<?php
/*
* e107 website system
*
* Copyright (C) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* RSS feed key resolution
*
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN.'rss_menu/rss_addons.php');

/**
 * Resolves a requested feed key to a row in the rss table.
 *
 * Feeds were keyed by number before v0.7.6, so a request may arrive with a key
 * no current feed uses. Each plugin declares the numbers it still answers to in
 * the legacy() method of its e_rss.php.
 *
 * @see rss_addons::legacyKeys()
 */
class rss_feed_resolver
{
	/**
	 * Signature: function($feedKey, $topicValue) => array|false
	 * @var callable
	 */
	private $lookup;

	/** @var array|null old numeric key => array('plugin' => folder|null, 'url' => text key) */
	private $legacy;

	/**
	 * @param callable   $lookup fetches one rss row, or false when none matches
	 * @param array|null $legacy overrides the aggregated map; for tests
	 */
	public function __construct($lookup, $legacy = null)
	{
		$this->lookup = $lookup;
		$this->legacy = $legacy;
	}

	/**
	 * @param string $feedKey rss_url as requested
	 * @param string $topicId topic id from the request, empty when absent
	 * @return array|false array('row' => array, 'key' => string), or false when
	 *                     no feed matches
	 */
	public function resolve($feedKey, $topicId)
	{
		$row = $this->fetch($feedKey, $topicId ? $topicId : false);

		if(empty($row) && is_numeric($feedKey))
		{	// No feed is keyed by this number; ask whoever answers to it now.
			$keys = $this->legacyKeys();

			if(isset($keys[$feedKey]))
			{
				$feedKey = $keys[$feedKey]['url'];
				$row = $this->fetch($feedKey, $topicId ? $topicId : false);
			}
		}

		if(empty($row))
		{	// A wildcard row stands in for any topic id.
			$row = $this->fetch($feedKey, $topicId ? '*' : false);
		}

		if(empty($row))
		{
			return false;
		}

		return array('row' => $row, 'key' => $this->canonicalKey($feedKey, $row));
	}

	/**
	 * Hands the addon the text key it implements, so a plugin that declares its
	 * old numbers only has to support one spelling. Only applied where that
	 * addon owns the row, so one plugin cannot rewrite another's feed key.
	 *
	 * @param string $feedKey
	 * @param array  $row
	 * @return string
	 */
	private function canonicalKey($feedKey, $row)
	{
		if(!is_numeric($feedKey))
		{
			return $feedKey;
		}

		$keys = $this->legacyKeys();

		if(!isset($keys[$feedKey]))
		{
			return $feedKey;
		}

		$owner = $keys[$feedKey];
		$path = explode('|', (string) varset($row['rss_path'], ''));

		if($owner['plugin'] === null || $owner['plugin'] === $path[0])
		{
			return $owner['url'];
		}

		return $feedKey;
	}

	/**
	 * @return array
	 */
	private function legacyKeys()
	{
		if($this->legacy === null)
		{
			// Built on demand, so a modern text key never pays for it. 'comments'
			// is core's, served inline by rss.php rather than by an addon.
			$this->legacy = array(5 => array('plugin' => null, 'url' => 'comments'))
				+ rss_addons::legacyKeys();
		}

		return $this->legacy;
	}

	/**
	 * @param string $feedKey
	 * @param string|false $topicValue
	 * @return array|false
	 */
	private function fetch($feedKey, $topicValue)
	{
		return call_user_func($this->lookup, $feedKey, $topicValue);
	}
}
