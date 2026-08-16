<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression cover for the user shortcode batch, which caches site-wide
 * totals in the registry. Anything computed alongside a cached total has to
 * survive a cache hit, because the second row a member list renders always
 * takes the hit path.
 */

class user_shortcodesTest extends \Codeception\Test\Unit
{
	/** @var user_shortcodes */
	private $sc;

	/** @var array registry entries this test overwrites, restored in _after() */
	private $savedRegistry = array();

	/** @var array user_extended_id => user_plugin_forum_posts, served by the stub db */
	private $forumPostCounts = array();

	/** @var array table name => row count, served by the stub db */
	private $tableCounts = array();

	/** @var array|null the plug_installed pref as found, restored in _after() */
	private $savedInstalled = null;

	/** @var string the real db class, noted before {@see installStubDb()} replaces the instance */
	private $dbClass;

	public function _before()
	{
		require_once(e_CORE."shortcodes/batch/user_shortcodes.php");

		try
		{
			$this->sc = $this->make('user_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$this->sc->__construct();

		foreach(array('total_forumposts', 'core/e107/singleton/db') as $key)
		{
			$this->savedRegistry[$key] = e107::getRegistry($key);
		}

		$this->savedInstalled = e107::getConfig()->get('plug_installed');
		$this->dbClass = get_class(e107::getDb());
	}

	public function _after()
	{
		foreach($this->savedRegistry as $key => $value)
		{
			e107::setRegistry($key, $value);
		}

		e107::getConfig()->set('plug_installed', $this->savedInstalled);
	}

	/**
	 * Let e107::isInstalled('forum') answer yes without an install.
	 */
	private function haveForumInstalled()
	{
		e107::getConfig()->setPref('plug_installed/forum', '2.0');
	}

	/**
	 * Stand in for the user_extended table. The forum plugin owns the
	 * user_plugin_forum_posts column, so it is only there while the plugin is
	 * installed; the shortcode's behaviour does not depend on that, and
	 * neither should this test.
	 *
	 * @param array $counts user_extended_id => user_plugin_forum_posts
	 */
	private function haveForumPostCounts(array $counts)
	{
		$this->forumPostCounts = $counts;
		$this->installStubDb();
	}

	/**
	 * Stand in for $sql->count(), so a test can say how many rows each forum
	 * table holds without writing any.
	 *
	 * @param array $counts table name => row count
	 */
	private function haveTableCounts(array $counts)
	{
		$this->tableCounts = $counts;
		$this->installStubDb();
	}

	private function installStubDb()
	{
		$forumPostCounts = $this->forumPostCounts;
		$tableCounts = $this->tableCounts;

		$stub = $this->make($this->dbClass, array(
			'count' => function($table, $fields = '(*)', $where = '') use ($tableCounts)
			{
				return isset($tableCounts[$table]) ? $tableCounts[$table] : 0;
			},
			'retrieve' => function($table, $fields = null, $where = null) use ($forumPostCounts)
			{
				if($table !== 'user_extended' || $fields !== 'user_plugin_forum_posts')
				{
					return false;
				}

				if(!preg_match('/user_extended_id\s*=\s*(\d+)/', (string) $where, $m))
				{
					return false;
				}

				return isset($forumPostCounts[$m[1]]) ? $forumPostCounts[$m[1]] : false;
			},
		));

		e107::setRegistry('core/e107/singleton/db', $stub);
	}

	/**
	 * USER_FORUMPER divides one member's post tally by the site-wide total.
	 * Only the total belongs in the registry: the tally changes with every
	 * row, so it has to be read on every call, cache hit or miss.
	 *
	 * Before the fix the tally was assigned inside the cache-miss block and
	 * read outside it, so every member after the first on a page divided an
	 * undefined value and rendered 0%.
	 */
	public function testUserForumPerIsRecomputedForEveryUser()
	{
		$this->haveForumPostCounts(array(90001 => 25, 90002 => 5));

		// Stand in for an earlier caller on the same page, e.g. {TOTAL_FORUMPOSTS}
		// or the forum's own profile addon, which both fill this key.
		e107::setRegistry('total_forumposts', 100);

		$this->sc->setVars(array('user_id' => 90001));
		$first = $this->sc->sc_user_forumper();

		$this->sc->setVars(array('user_id' => 90002));
		$second = $this->sc->sc_user_forumper();

		$this->assertEquals(25, $first,
			'25 of 100 forum posts is 25%.');
		$this->assertEquals(5, $second,
			'The second member on the page must get their own percentage, not a stale or empty one.');
	}

	/**
	 * A member list renders many rows through one batch object. Every row
	 * after the first takes the cache-hit path, so all of them have to come
	 * out right, not just the one that happened to fill the cache.
	 */
	public function testUserForumPerIsRightForEveryRowOfAMemberList()
	{
		$this->haveForumPostCounts(array(90003 => 8, 90004 => 2, 90005 => 40));
		e107::setRegistry('total_forumposts', 200);

		$rendered = array();

		foreach(array(90003, 90004, 90005) as $userId)
		{
			$this->sc->setVars(array('user_id' => $userId));
			$rendered[$userId] = $this->sc->sc_user_forumper();
		}

		$this->assertEquals(4, $rendered[90003], '8 of 200 forum posts is 4%.');
		$this->assertEquals(1, $rendered[90004], '2 of 200 forum posts is 1%.');
		$this->assertEquals(20, $rendered[90005], '40 of 200 forum posts is 20%.');
	}

	/**
	 * {TOTAL_FORUMPOSTS} names forum posts, and the tally it is compared
	 * against, user_extended.user_plugin_forum_posts, is stepped once per row
	 * written to forum_post. So the total has to come from forum_post too,
	 * not from forum_thread, which holds one row per topic.
	 */
	public function testTotalForumPostsCountsPostsNotTopics()
	{
		$this->haveForumInstalled();
		$this->haveTableCounts(array('forum_post' => 42, 'forum_thread' => 7));
		e107::setRegistry('total_forumposts', null);

		$this->assertEquals(42, $this->sc->sc_total_forumposts(),
			'{TOTAL_FORUMPOSTS} must report posts, not topics.');
	}

	/**
	 * Both shortcodes cache under the registry key total_forumposts, so
	 * whichever renders first decides what the key means for the other. They
	 * have to mean the same thing, or the percentage silently depends on
	 * template order.
	 */
	public function testTotalForumPostsLeavesUserForumPerADenominatorItAgreesWith()
	{
		$this->haveForumInstalled();
		$this->haveForumPostCounts(array(90006 => 21));
		$this->haveTableCounts(array('forum_post' => 42, 'forum_thread' => 7));
		e107::setRegistry('total_forumposts', null);

		// {TOTAL_FORUMPOSTS} first, as a template listing site totals above a
		// member table would place it.
		$total = $this->sc->sc_total_forumposts();

		$this->sc->setVars(array('user_id' => 90006));
		$percentage = $this->sc->sc_user_forumper();

		$this->assertEquals(42, $total);
		$this->assertEquals(50, $percentage,
			'21 of the 42 posts {TOTAL_FORUMPOSTS} just reported is 50%.');
	}

	/**
	 * And the other way round, to show the answer no longer turns on which
	 * shortcode the template happens to reach first.
	 */
	public function testUserForumPerIsTheSameWhicheverShortcodeWarmsTheRegistry()
	{
		$this->haveForumInstalled();
		$this->haveForumPostCounts(array(90007 => 21));
		$this->haveTableCounts(array('forum_post' => 42, 'forum_thread' => 7));

		$this->sc->setVars(array('user_id' => 90007));

		e107::setRegistry('total_forumposts', null);
		$perFirst = $this->sc->sc_user_forumper();

		e107::setRegistry('total_forumposts', null);
		$this->sc->sc_total_forumposts();
		$perSecond = $this->sc->sc_user_forumper();

		$this->assertEquals($perFirst, $perSecond,
			'Template order must not change the percentage.');
	}
}
