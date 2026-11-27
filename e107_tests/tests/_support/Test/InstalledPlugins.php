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
 * Bundled plugins a test installs for their tables when the suite's database lacks them, uninstalled again by {@see InstalledPlugins::uninstallInstalledPlugins()}.
 */
trait InstalledPlugins
{
	/** @var string[] the plugins this test installed */
	private $installedPlugins = array();

	/**
	 * @param string $plugin the plugin's folder
	 * @param string $table one of its tables, without the prefix
	 * @return void
	 */
	protected function installPluginFor($plugin, $table)
	{
		if(\e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX.$table."'"))
		{
			return;
		}

		\e107::getPlugin()->install($plugin);
		$this->installedPlugins[] = $plugin;
	}

	/**
	 * @return void
	 */
	protected function uninstallInstalledPlugins()
	{
		foreach($this->installedPlugins as $plugin)
		{
			\e107::getPlugin()->uninstall($plugin, array('delete_tables' => true));
		}

		$this->installedPlugins = array();
	}
}
