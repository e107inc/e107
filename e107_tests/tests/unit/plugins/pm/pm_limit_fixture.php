<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once(__DIR__.'/pm_fixture.php');

/**
 * What the limit tests share: the plugin's stored preferences and class limit rows, kept and put back around each test, and booted children that have the plugin installed and run as the main admin, a member of the member and admin classes.
 */
abstract class pm_limit_fixture extends pm_fixture
{
	/** @var callable[] put back the stored rows a child may have saved */
	private $restore = array();

	protected function _before()
	{
		parent::_before();

		$this->restore[] = $this->keepStoredCorePrefs();
		$this->restore[] = $this->keepStoredPrefs('plugin_pm', new e_plugin_pref('pm', '', false));
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		foreach($this->restore as $restore)
		{
			$restore();
		}

		parent::_after();
	}

	/**
	 * Stores these as the plugin's preferences, and nothing else.
	 *
	 * @param array $prefs
	 * @return void
	 */
	protected function storePrefs(array $prefs)
	{
		e107::getDb()->createQueryBuilder()->replace('core')
			->values(array('e107_name' => 'plugin_pm', 'e107_value' => e107::serialize($prefs, 'json')))
			->execute();
	}

	/**
	 * Adds a class limit row as the Limits page stores it, with no inbox limits.
	 *
	 * @param int $class
	 * @param int $outboxCount
	 * @param int $outboxSize in KB
	 * @return int the row's gen_id
	 */
	protected function addLimit($class, $outboxCount, $outboxSize)
	{
		return $this->addGenericRow(array(
			'gen_type' => 'pm_limit', 'gen_datestamp' => $class, 'gen_user_id' => 0, 'gen_ip' => (string) $outboxCount, 'gen_intdata' => 0, 'gen_chardata' => (string) $outboxSize,
		));
	}

	/**
	 * Loads one of the plugin's pages once $seed has run.
	 *
	 * @param string $script path under the app root, e.g. 'e107_plugins/pm/pm.php'
	 * @param string $query the page's query string
	 * @param string $seed PHP to run before the page, e.g. setting $_POST
	 * @param array $constants name => value, defined before class2.php could
	 * @return string everything the page wrote
	 */
	protected function renderPage($script, $query, $seed, array $constants = array())
	{
		putenv('QUERY_STRING='.$query);

		$page = $this->renderInBootedCli($script, $this->installed().$seed, self::BUFFERED, $constants);

		self::assertStringNotContainsString('Fatal error', $page, "The page died:\n".$page);

		return $page;
	}

	/**
	 * @param array $post
	 * @return string PHP that sets $_POST to it
	 */
	protected static function posting(array $post)
	{
		return '$_POST = '.var_export($post, true).'; ';
	}

	/**
	 * @return string PHP that makes a child the main admin of a site with the plugin installed
	 */
	protected function installed()
	{
		$php = "e107::getUser()->setData(e107::getDb()->retrieve('user', '*', 'user_id = 1')); ";
		$php .= "e107::getConfig()->setPref('plug_installed/pm', '1.0'); ";
		$php .= "e107::plugLan('pm', 'global'); ";

		return $php;
	}
}
