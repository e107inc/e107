<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * The installed add-ons e107.org has a newer version of, as the dashboard's last update check found them, kept for the whole site.
 */
class AddonUpdateStatus
{
	const CACHE_TAG = 'Admin_addon_updates';

	/** Minutes an answer is kept: those of the version lists it is worked out from, {@see \e_marketplace::getVersionList()}. */
	const LIFETIME = 720;

	/** Session key the update check sets before it asks e107.org, so each session checks once. */
	const CHECKED = 'addons-update-checked';

	/** @var \ecache */
	private $cache;

	/** @var \e_session */
	private $session;

	/**
	 * @param \ecache $cache
	 * @param \e_session $session
	 */
	public function __construct(\ecache $cache, \e_session $session)
	{
		$this->cache = $cache;
		$this->session = $session;
	}

	/**
	 * @return array|null the rows {@see AddonUpdateStatus::set()} kept, per add-on type and without download links; null when this session is to run the check
	 */
	public function get()
	{
		if($this->session->get(self::CHECKED) !== true)
		{
			return null;
		}

		$stored = $this->cache->retrieve(self::CACHE_TAG, self::LIFETIME, true, true);
		$updates = is_string($stored) ? json_decode($stored, true) : null;

		return is_array($updates) ? $updates : null;
	}

	/**
	 * Keep what a check found, an empty answer included.
	 *
	 * @param array $updates rows per add-on type, as {@see \admin_shortcodes::getUpdateable()} returns them
	 * @return void
	 */
	public function set(array $updates)
	{
		$kept = array();

		foreach($updates as $type => $rows)
		{
			$kept[$type] = array();

			foreach($rows as $row)
			{
				unset($row['modalDownload']);
				$kept[$type][] = $row;
			}
		}

		$this->cache->set(self::CACHE_TAG, json_encode($kept), true, false, true);
	}

	/**
	 * Forget the answer, so the next dashboard view runs the check again.
	 *
	 * @return void
	 */
	public function clear()
	{
		$this->cache->clear(self::CACHE_TAG, true);
	}
}
