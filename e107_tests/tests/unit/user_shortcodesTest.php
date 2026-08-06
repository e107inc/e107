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
	}

	public function _after()
	{
		foreach($this->savedRegistry as $key => $value)
		{
			e107::setRegistry($key, $value);
		}
	}

	/**
	 * Stand in for the user_extended table. The forum plugin owns the
	 * user_plugin_forum_posts column, so it is only there while the plugin is
	 * installed; the shortcode's behaviour does not depend on that, and
	 * neither should this test.
	 *
	 * @param array $counts user_extended_id => user_plugin_forum_posts
	 * @param array $tableCounts logical table name => row count, for count() calls
	 */
	private function haveForumPostCounts(array $counts, array $tableCounts = array())
	{
		$db = e107::getDb();
		$stub = $this->make(get_class($db), array(
			'createQueryBuilder' => function() use ($counts, $tableCounts)
			{
				return new user_shortcodesTestQueryBuilder($counts, $tableCounts);
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
}

/**
 * Fluent stand-in for the query builder, answering only the shapes the user
 * shortcode batch builds: a per-member lookup on user_extended, and a row
 * count on a named table.
 */
class user_shortcodesTestQueryBuilder
{
	private $counts;
	private $tableCounts;
	private $table;
	private $columns;
	private $userId;

	public function __construct(array $counts, array $tableCounts)
	{
		$this->counts = $counts;
		$this->tableCounts = $tableCounts;
	}

	public function select($columns = '*')
	{
		$this->columns = $columns;

		return $this;
	}

	public function from($table, $alias = null)
	{
		$this->table = $table;

		return $this;
	}

	public function where(...$args)
	{
		if(isset($args[0]) && $args[0] === 'user_extended_id')
		{
			$this->userId = (int) $args[1];
		}

		return $this;
	}

	public function count($column = '*')
	{
		return isset($this->tableCounts[$this->table]) ? (int) $this->tableCounts[$this->table] : 0;
	}

	public function fetchOne()
	{
		if($this->table !== 'user_extended' || $this->columns !== 'user_plugin_forum_posts')
		{
			return null;
		}

		return isset($this->counts[$this->userId]) ? $this->counts[$this->userId] : null;
	}
}
