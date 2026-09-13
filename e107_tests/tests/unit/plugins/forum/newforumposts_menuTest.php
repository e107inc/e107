<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The New Forum Posts menu, rendered over a forum that has something to show.
 *
 * The menu file defines its class and renders on the spot, so it is driven in
 * a subprocess, the way forum_templateIconsTest drives the forum templates. That
 * also keeps the measurement to the diagnostics this render raises rather than
 * whatever else the shuffled suite has left in the parent process.
 */
class newforumposts_menuTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ForumRows;

	/** @var string */
	private $threadName;

	protected function _before()
	{
		$this->haveForumTables();

		$category = $this->haveForum('e107help menu probe category');
		$forumId = $this->haveForum('e107help menu probe forum', $category);

		$this->threadName = 'e107help menu probe thread '.time();
		$threadId = $this->haveForumThread($this->threadName, $forumId);
		$this->haveForumPost('e107help menu probe post', $threadId, $forumId);
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	/**
	 * The three totals the menu hands its template are counted up from nothing:
	 * the first ++ and the first += in the loop are what creates them, so every
	 * render that had a post to show raised three warnings before doing so.
	 */
	public function testCountingTheTotalsRaisesNothing()
	{
		$php = "e107::getConfig()->setPref('plug_installed/forum', '2.0'); ";
		$php .= "e107::getCache()->clear('nfpCache_default'); ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/newforumposts_menu.php')."'); ";

		list($output, ) = $this->runInBootedCli("error_reporting(E_ALL); ".$php);

		$printed = implode("\n", $output);

		self::assertStringContainsString($this->threadName, $printed,
			"the menu rendered nothing, so nothing was measured:\n".$printed);

		self::assertDoesNotMatchRegularExpression('/Undefined variable \$?total_(topics|views|replies)/i', $printed,
			"the menu counts into variables it never declared:\n".$printed);
	}
}
