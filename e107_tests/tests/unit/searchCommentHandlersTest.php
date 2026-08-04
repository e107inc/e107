<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the comment search handlers named by the search_prefs
 * 'comments_handlers' pref, which is never pruned when a plugin is
 * uninstalled or deleted. Loading a handler that its plugin no longer backs
 * used to fatal on PHP 8, both in admin and on the front-end.
 *
 * @see https://github.com/e107inc/e107/issues/5267
 */
class searchCommentHandlersTest extends \Codeception\Test\Unit
{
	/**
	 * The news handler ships with e107 and stands in for a handler that is
	 * always usable, so a test can tell "the table skipped one row" apart from
	 * "the table never rendered".
	 */
	const SEED_CONTROL_HANDLER = "e107::getConfig('search')->setPref('comments_handlers/news', array('id' => 0, 'class' => '0', 'dir' => 'core')); ";

	/** Marker proving the subprocess got past booting e107. */
	const BOOTED = 'E107-BOOTED';

	/** @var string path of the file holding the stored search_prefs row */
	private $prefBackup;

	/**
	 * The admin search page saves search_prefs whenever it finds a handler or
	 * a default to add, so the row is put back after every test.
	 *
	 * Both halves run in their own subprocess. This process is inside a
	 * transaction for the life of the suite, so it cannot see what a
	 * subprocess wrote, and restoring from here would write back a stale
	 * snapshot.
	 */
	protected function _before()
	{
		$this->prefBackup = tempnam(sys_get_temp_dir(), 'e107_search_prefs_');

		$this->runIsolated(
			"\$stored = e107::getDb()->createQueryBuilder()->select('e107_value')->from('core')->where('e107_name', 'search_prefs')->fetchOne(); " .
			"file_put_contents('" . addslashes($this->prefBackup) . "', (string) \$stored); "
		);
	}

	protected function _after()
	{
		$this->runIsolated(
			"\$stored = file_get_contents('" . addslashes($this->prefBackup) . "'); " .
			"\$qb = e107::getDb()->createQueryBuilder(); " .
			"if(\$stored === '') { \$qb->delete('core')->where('e107_name', 'search_prefs')->execute(); } " .
			"else { \$qb->replace('core')->values(array('e107_name' => 'search_prefs', 'e107_value' => \$stored))->execute(); } " .
			"foreach(glob(e_BASE . 'e107_system/*/cache/content/S_Config_*.cache.php') ?: array() as \$cacheFile) { @unlink(\$cacheFile); } "
		);

		@unlink($this->prefBackup);
	}

	/**
	 * Run PHP in a subprocess, so a fatal is observable instead of killing
	 * the test run, and so pref changes cannot leak into other tests.
	 *
	 * @param string $code PHP to run after class2.php has been loaded
	 * @return array {out: string, exit: int}
	 */
	private function runIsolated($code)
	{
		// APP_PATH, not this file's location: with a deploy-based preparer the
		// application under test runs from an isolated git worktree, and only
		// that copy has the e107_config.php the suite generated.
		$e107Root = APP_PATH;

		// Boot e107 the way the suite's own bootstrap does. Without the cli
		// flag, class2.php takes the browser path, tears down every output
		// buffer and starts its own, and a command-line run can then finish
		// having emitted nothing at all. The marker goes to stderr so that it
		// survives whatever happens to the output buffers, and any buffer
		// still open at the end is flushed rather than dropped.
		$php = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$php .= "\$_E107 = array('cli' => true); ";
		$php .= "require_once('" . addslashes($e107Root . '/class2.php') . "'); ";
		$php .= "fwrite(STDERR, '" . self::BOOTED . "'); ";
		$php .= $code;
		$php .= "while(ob_get_level() > 0) { @ob_end_flush(); } ";

		$output = array();
		$exitCode = 0;
		exec(sprintf('php -r %s 2>&1', escapeshellarg($php)), $output, $exitCode);

		return array('out' => implode("\n", $output), 'exit' => $exitCode);
	}

	/**
	 * Guard against a silent boot failure being read as "the page rendered and
	 * simply did not contain that row".
	 *
	 * @param array $result as returned by runIsolated()
	 */
	private function assertBooted($result)
	{
		$this->assertStringContainsString(self::BOOTED, $result['out'],
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n" . $result['out']);
	}

	/**
	 * Render the admin search page with the given prefs seeded in memory.
	 *
	 * @param string $seed PHP setting up prefs before the page is loaded
	 * @return array {out: string, exit: int}
	 */
	private function renderAdminSearchPage($seed)
	{
		return $this->runIsolated($seed . "require_once('" . addslashes(APP_PATH . '/e107_admin/search.php') . "'); ");
	}

	/**
	 * A pref entry pointing at a plugin that is no longer installed. Core only
	 * auto-loads a plugin's global LAN while it is installed, so requiring the
	 * handler used to hit an undefined constant.
	 */
	public function testAdminSearchPageSurvivesHandlerOfUninstalledPlugin()
	{
		$result = $this->renderAdminSearchPage(
			"e107::getConfig()->removePref('plug_installed/poll'); " .
			"e107::getConfig('search')->setPref('comments_handlers/poll', array('id' => 4, 'class' => '0', 'dir' => 'poll')); "
		);

		$this->assertBooted($result);
		$this->assertStringNotContainsString('Undefined constant', $result['out'],
			"A stale comments_handlers pref must not pull in a language constant the page has not loaded (#5267).\n" . $result['out']);
		$this->assertSame(0, $result['exit'],
			"The admin search page must not fatal on a stale comments_handlers pref (#5267).\n" . $result['out']);
	}

	/**
	 * The handler table is the sole UI for these prefs, so what it lists is
	 * part of the fix: handlers of uninstalled plugins are not configurable
	 * and used to render as untitled rows.
	 */
	public function testAdminSearchPageOmitsHandlerOfUninstalledPlugin()
	{
		$result = $this->renderAdminSearchPage(
			self::SEED_CONTROL_HANDLER .
			"e107::getConfig()->removePref('plug_installed/poll'); " .
			"e107::getConfig('search')->setPref('comments_handlers/poll', array('id' => 4, 'class' => '0', 'dir' => 'poll')); "
		);

		$this->assertBooted($result);
		$this->assertStringContainsString('comments_handlers[news]', $result['out'],
			"The handler table should still render its usable rows.\n" . $result['out']);
		$this->assertStringNotContainsString('comments_handlers[poll]', $result['out'],
			"A handler whose plugin is not installed should not be listed (#5267).\n" . $result['out']);
	}

	/**
	 * The mirror of the test above: the skip must be narrow enough that an
	 * installed plugin keeps its row, titled from its own language file.
	 */
	public function testAdminSearchPageListsHandlerOfInstalledPlugin()
	{
		$result = $this->renderAdminSearchPage(
			"e107::getConfig()->setPref('plug_installed/poll', '1.0'); " .
			"e107::getConfig('search')->setPref('comments_handlers/poll', array('id' => 4, 'class' => '0', 'dir' => 'poll')); "
		);

		$this->assertBooted($result);
		$this->assertStringContainsString('comments_handlers[poll]', $result['out'],
			"An installed plugin's comment handler must still be listed.\n" . $result['out']);
		$this->assertStringContainsString('>Poll<', $result['out'],
			"The handler must be titled from the plugin's own global LAN.\n" . $result['out']);
	}

	/**
	 * A 0.7-era entry for a plugin whose folder is long gone. There is nothing
	 * to configure, and the row used to render with an empty title.
	 */
	public function testAdminSearchPageOmitsHandlerWithNoFile()
	{
		$result = $this->renderAdminSearchPage(
			self::SEED_CONTROL_HANDLER .
			"e107::getConfig('search')->setPref('comments_handlers/content', array('id' => 5, 'class' => '0', 'dir' => 'content')); "
		);

		$this->assertBooted($result);
		$this->assertStringNotContainsString('comments_handlers[content]', $result['out'],
			"A handler whose file no longer exists should not be listed (#5267).\n" . $result['out']);
		$this->assertSame(0, $result['exit'], $result['out']);
	}

	/**
	 * The entry shipped for the download plugin claims 'core' as its directory
	 * and is named only by its key, so the installation check has to follow
	 * the key rather than the directory.
	 *
	 * @see https://github.com/e107inc/e107/issues/2003
	 */
	public function testAdminSearchPageOmitsCoreEntryOfUninstalledPlugin()
	{
		$result = $this->renderAdminSearchPage(
			"e107::getConfig()->removePref('plug_installed/download'); " .
			"e107::getConfig('search')->setPref('comments_handlers/download', array('id' => 2, 'class' => '0', 'dir' => 'core')); "
		);

		$this->assertBooted($result);
		$this->assertStringNotContainsString('comments_handlers[download]', $result['out'],
			"A core-flagged entry for an uninstalled plugin must stay hidden (#2003).\n" . $result['out']);

		$installed = $this->renderAdminSearchPage(
			"e107::getConfig()->setPref('plug_installed/download', '1.0'); " .
			"e107::getConfig('search')->setPref('comments_handlers/download', array('id' => 2, 'class' => '0', 'dir' => 'core')); "
		);

		$this->assertStringContainsString('comments_handlers[download]', $installed['out'],
			"The same entry must be listed once the plugin is installed.\n" . $installed['out']);
	}

	/**
	 * news, page and user ship with installRequired="false", so their handlers
	 * must survive even when the plug_installed pref has no entry for them.
	 */
	public function testAdminSearchPageKeepsHandlersThatNeedNoInstall()
	{
		$result = $this->renderAdminSearchPage(
			self::SEED_CONTROL_HANDLER .
			"e107::getConfig()->removePref('plug_installed/news'); " .
			"e107::getConfig()->removePref('plug_installed/page'); " .
			"e107::getConfig()->removePref('plug_installed/user'); " .
			"e107::getConfig('search')->setPref('comments_handlers/page', array('id' => 'page', 'class' => '0', 'dir' => 'core')); " .
			"e107::getConfig('search')->setPref('comments_handlers/user', array('id' => 'profile', 'class' => '0', 'dir' => 'core')); "
		);

		$this->assertBooted($result);

		foreach(array('news', 'page', 'user') as $key)
		{
			$this->assertStringContainsString('comments_handlers[' . $key . ']', $result['out'],
				"Handlers that do not require an install must never be hidden.\n" . $result['out']);
		}
	}

	/**
	 * A site that has never saved its search settings has no search_prefs row
	 * at all, and one with no search-capable plugin installed never grows a
	 * plug_handlers key, which used to reach count() with a null.
	 */
	public function testAdminSearchPageSurvivesWithNoStoredSearchPrefs()
	{
		$result = $this->renderAdminSearchPage(
			"e107::getDb()->createQueryBuilder()->delete('core')->where('e107_name', 'search_prefs')->execute(); " .
			"e107::getConfig()->removePref('plug_installed'); " .
			"foreach(glob(e_BASE . 'e107_system/*/cache/content/S_Config_*.cache.php') ?: array() as \$cacheFile) { @unlink(\$cacheFile); } "
		);

		$this->assertBooted($result);
		$this->assertStringNotContainsString('Fatal error', $result['out'],
			"The admin search page must render before any search pref has been saved (#5267).\n" . $result['out']);
		$this->assertSame(0, $result['exit'], $result['out']);
	}

	/**
	 * The front-end advanced search pane had no readability check at all, so a
	 * pref for a deleted plugin fataled the public search page.
	 */
	public function testAdvancedCommentSearchSkipsHandlerWithNoFile()
	{
		$result = $this->runIsolated(
			"e107::coreLan('search'); " .
			"require_once(e_HANDLER . 'userclass_class.php'); " .
			"require_once(e_HANDLER . 'search_class.php'); " .
			"\$search_prefs = array('comments_handlers' => array('content' => array('id' => 5, 'class' => '0', 'dir' => 'content'))); " .
			"require_once(e_HANDLER . 'search/advanced_comment.php'); " .
			"echo 'REACHED-END'; "
		);

		$this->assertBooted($result);
		$this->assertStringContainsString('REACHED-END', $result['out'],
			"The advanced comment search pane must skip handlers it cannot load (#5267).\n" . $result['out']);
		$this->assertSame(0, $result['exit'], $result['out']);
	}

	/**
	 * With every handler filtered out, the comment search used to build
	 * "comment_type IN ()" from an undefined variable.
	 */
	public function testCommentSearchWithNoUsableHandlers()
	{
		$result = $this->runIsolated(
			"e107::coreLan('search'); " .
			"require_once(e_HANDLER . 'userclass_class.php'); " .
			"require_once(e_HANDLER . 'search_class.php'); " .
			"\$tp = e107::getParser(); \$sch = new e_search('example'); \$text = ''; " .
			"\$search_prefs = array('comments_handlers' => array('content' => array('id' => 5, 'class' => '0', 'dir' => 'content'))); " .
			"require_once(e_HANDLER . 'search/search_comment.php'); " .
			"echo 'REACHED-END'; "
		);

		$this->assertBooted($result);
		$this->assertStringContainsString('REACHED-END', $result['out'],
			"A comment search with no usable handlers must return no results rather than fatal (#5267).\n" . $result['out']);
		$this->assertStringNotContainsString('IN ()', $result['out'],
			"An empty handler list must not reach the query builder.\n" . $result['out']);
		$this->assertSame(0, $result['exit'], $result['out']);
	}

	/**
	 * Defence in depth: the handler is also required directly by
	 * e107plugin::manage_search(), where no page has loaded the plugin's LAN.
	 */
	public function testPollHandlerLoadsItsOwnLanguage()
	{
		$result = $this->runIsolated(
			"e107::getConfig()->removePref('plug_installed/poll'); " .
			"require_once(e_PLUGIN . 'poll/search/search_comments.php'); " .
			"echo 'TITLE=' . \$comments_title; "
		);

		$this->assertBooted($result);
		$this->assertStringNotContainsString('Undefined constant', $result['out'],
			"poll/search/search_comments.php must load its own global LAN (#5267).\n" . $result['out']);
		$this->assertStringContainsString('TITLE=Poll', $result['out'],
			"The handler must still be titled when the plugin is not installed.\n" . $result['out']);
		$this->assertSame(0, $result['exit'], $result['out']);
	}
}
