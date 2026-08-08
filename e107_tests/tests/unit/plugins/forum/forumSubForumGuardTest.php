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
 * forumGetForumList() returns 'subs' keyed on the forum a sub-forum hangs
 * under, so the key exists only for the forums that have one. The index page
 * asks whether a board has sub-forums by reading that key.
 *
 * forum.php defines its class and then renders the page, so the row-rendering
 * method cannot be reached without rendering a page. It is driven here in a
 * subprocess, the way e_fileOutboundRequestTest drives class2.php, so nothing
 * the front page touches is left behind in this one.
 */
class forumSubForumGuardTest extends \Codeception\Test\Unit
{
	const MARKER = 'e107help sub-forum guard probe';

	/** @var bool whether this test installed the forum plugin for its tables */
	private $forumInstalled = false;

	protected function _before()
	{
		// The dump ships the forum tables, but e107Test uninstalls the plugin
		// after borrowing its routes, which drops them. Whether they are still
		// there when this runs is down to the shuffle, so put them back and
		// give them up again the same way.
		//
		// Asked of the server rather than through isTable(), which answers from
		// a list the connection cached the first time anything asked, long
		// before the drop.
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."forum'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumInstalled = true;
		}
	}

	protected function _after()
	{
		e107::getDb()->delete('forum', "forum_description = '".self::MARKER."'");

		if($this->forumInstalled)
		{
			e107::getPlugin()->uninstall('forum');
			$this->forumInstalled = false;
		}
	}

	/**
	 * forum_sql.php declares four tables, and installing the plugin has to
	 * produce all four.
	 *
	 * The installer used to record each failed CREATE TABLE and carry on, so on
	 * a server whose InnoDB has no FULLTEXT this plugin reported itself
	 * installed with only forum_track: `forum` was refused with 1071 for a
	 * unique key over varchar(250) at utf8mb4, and forum_thread and forum_post
	 * with 1214 for their FULLTEXT indexes. Nothing said so until the first
	 * query against a missing table died with 1146, which is what this test
	 * used to fail on, several steps away from the cause.
	 */
	public function testInstallingTheForumCreatesEveryTableItDeclares()
	{
		$sql = e107::getDb();

		foreach(array('forum', 'forum_thread', 'forum_post', 'forum_track') as $table)
		{
			self::assertNotEmpty(
				$sql->gen("SHOW TABLES LIKE '".MPREFIX.$table."'"),
				$table." is declared in forum_sql.php, so installing the plugin must create it"
			);
		}
	}

	/**
	 * @param string $name
	 * @param string $sef
	 * @param int $parent 0 for a category
	 * @param int $sub the forum this hangs under, 0 for a board
	 * @param int $order
	 * @return int forum id
	 */
	private function haveForum($name, $sef, $parent, $sub, $order)
	{
		$id = e107::getDb()->insert('forum', array(
			'forum_name'        => $name,
			'forum_description' => self::MARKER,
			'forum_parent'      => $parent,
			'forum_sub'         => $sub,
			'forum_moderators'  => 0,
			'forum_class'       => e_UC_PUBLIC,
			'forum_postclass'   => e_UC_PUBLIC,
			'forum_threadclass' => e_UC_PUBLIC,
			'forum_order'       => $order,
			'forum_sef'         => $sef,
			'forum_datestamp'   => time(),
		));

		self::assertNotEmpty($id,
			'could not write the forum row this test needs: '.e107::getDb()->getLastErrorText());

		return (int) $id;
	}

	/**
	 * @return string everything rendering the forum index wrote
	 */
	private function renderForumIndex()
	{
		$php = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$php .= "\$_E107 = array('cli' => true); ";
		$php .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";
		$php .= "e107::getConfig()->setPref('plug_installed/forum', '2.0'); ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/forum.php')."'); ";

		$output = array();
		$status = 0;
		exec(sprintf('timeout 60 php -r %s 2>&1', escapeshellarg($php)), $output, $status);

		self::assertNotSame(124, $status, 'the subprocess wedged, so nothing was measured');

		return implode("\n", $output);
	}

	/**
	 * @param string $out
	 * @return array the diagnostics forum.php itself raised
	 */
	private function complaintsFrom($out)
	{
		$found = array();

		foreach(explode("\n", $out) as $line)
		{
			if(strpos($line, 'forum/forum.php') === false)
			{
				continue;
			}

			if(preg_match('/^(PHP )?(Warning|Notice|Fatal error|Parse error):/', $line))
			{
				$found[trim($line)] = true;
			}
		}

		return array_keys($found);
	}

	/**
	 * A site with one board carrying sub-forums and one without. The second
	 * board has no entry in the sub-forum list, and asking for it anyway is a
	 * warning on every such board, on every render of the index.
	 */
	public function testTheIndexDoesNotAskForASubForumListThatIsNotThere()
	{
		$category = $this->haveForum('E107HELP Guard Category', 'e107help-guard-cat', 0, 0, 1);
		$withSubs = $this->haveForum('E107HELP Guard Has Subs', 'e107help-guard-a', $category, 0, 2);
		$this->haveForum('E107HELP Guard No Subs', 'e107help-guard-b', $category, 0, 3);
		$this->haveForum('E107HELP Guard Sub', 'e107help-guard-sub', $category, $withSubs, 4);

		$out = $this->renderForumIndex();

		self::assertStringContainsString('E107HELP Guard Has Subs', $out,
			'precondition: the board with sub-forums has to reach the page');
		self::assertStringContainsString('E107HELP Guard No Subs', $out,
			'precondition: the board without sub-forums has to reach the page');
		self::assertStringContainsString('E107HELP Guard Sub', $out,
			'precondition: the sub-forum has to be listed under its board');

		self::assertSame(array(), $this->complaintsFrom($out),
			'rendering the forum index must raise nothing');
	}
}
