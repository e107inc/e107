<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Who moderates a forum when its moderator class sits in a class tree.
 *
 * @group plugins
 */
class forumModeratorClassTreeTest extends \Codeception\Test\Unit
{
	use \Test\ForumRows;

	/** @var array[] every userclass_classes row as it was before the test */
	private $classRows = array();

	/** @var user_class the shared class tree, put back after the test */
	private $savedUserClass;

	/** @var int[] user ids planted, keyed by role */
	private $users = array();

	/** @var int */
	private $categoryId;

	protected function _before()
	{
		$this->haveForumTables();

		require_once(e_PLUGIN.'forum/forum_class.php');

		$db = e107::getDb();
		$db->select('userclass_classes', '*');

		while($row = $db->fetch())
		{
			$this->classRows[] = $row;
		}

		$this->savedUserClass = e107::getUserClass();
		e107::setRegistry('core/e107/singleton/user_class', new user_class());

		$this->categoryId = $this->haveForum('e107help moderator tree category');
	}

	protected function _after()
	{
		$db = e107::getDb();

		$this->dropForumRows();

		foreach($this->users as $id)
		{
			$db->delete('user', 'user_id = '.(int) $id);
		}

		$this->users = array();

		if($this->classRows)
		{
			$db->delete('userclass_classes', 'userclass_id >= 0');

			foreach($this->classRows as $row)
			{
				$db->insert('userclass_classes', $row);
			}
		}

		e107::getCache()->clear_sys(UC_CACHE_TAG);

		if($this->savedUserClass)
		{
			e107::setRegistry('core/e107/singleton/user_class', $this->savedUserClass);
		}
	}

	public function testAClassUnderMembersIsModeratedOnlyByItsOwnMembers()
	{
		$moderators = $this->haveClass('e107help mods under members', e_UC_MEMBER);
		$this->haveUser('member', '');
		$this->haveUser('holder', (string) $moderators);

		$this->assertModeratedBy($moderators, array('holder'));
	}

	public function testMainAdminAtopTheDefaultTreeModeratesWithoutTheAdminsBeneathIt()
	{
		$this->haveDefaultTree();
		$beneath = $this->haveClass('e107help mods beneath admins and mods', e_UC_ADMINMOD);

		$this->haveUser('member', '');
		$this->haveUser('admin', '', 1, '1.2.3');
		$this->haveUser('main admin', '', 1, '0');
		$this->haveUser('admins and mods', (string) e_UC_ADMINMOD);
		$this->haveUser('beneath', (string) $beneath);

		$this->assertModeratedBy(e_UC_MAINADMIN, array('main admin'));
		$this->assertModeratedBy($beneath, array('admins and mods', 'beneath'));
	}

	public function testAMemberOfAChildClassModeratesWhereTheParentClassDoes()
	{
		$parent = $this->haveClass('e107help mods parent', e_UC_PUBLIC);
		$child = $this->haveClass('e107help mods child', $parent);

		$this->haveUser('member', '');
		$this->haveUser('parent', (string) $parent);
		$this->haveUser('child', (string) $child);

		$this->assertModeratedBy($parent, array('parent', 'child'));
		$this->assertModeratedBy($child, array('child'));
	}

	public function testAFlatTreeKeepsItsModerators()
	{
		$everyone = $this->haveClass('e107help mods under everyone', e_UC_PUBLIC);
		$noOne = $this->haveClass('e107help mods under no one', e_UC_NOBODY);

		$this->haveUser('member', '');
		$this->haveUser('admin', '', 1, '1.2.3');
		$this->haveUser('main admin', '', 1, '0');
		$this->haveUser('everyone', (string) $everyone);
		$this->haveUser('no one', (string) $noOne);

		$this->assertModeratedBy($everyone, array('everyone'));
		$this->assertModeratedBy($noOne, array('no one'));
		$this->assertModeratedBy(e_UC_MAINADMIN, array('main admin'));
		$this->assertModeratedBy(e_UC_ADMIN, array('admin', 'main admin'));

		foreach($this->moderatorIds($this->haveModeratedForum(e_UC_PUBLIC)) as $path => $ids)
		{
			self::assertSame(array(), $this->planted($ids),
				$path.' for a forum whose moderator class is still the default, Everyone');
		}
	}

	public function testForcingTheModeratorListReadsItAgain()
	{
		$moderators = $this->haveClass('e107help mods forced', e_UC_PUBLIC);
		$this->haveUser('first', (string) $moderators);

		$forum = new e107forum(true);
		self::assertSame($this->idsOf(array('first')),
			$this->planted(array_keys($forum->forumGetMods($moderators))));

		$this->haveUser('second', (string) $moderators);
		self::assertSame($this->idsOf(array('first', 'second')),
			$this->planted(array_keys($forum->forumGetMods($moderators, true))));
	}

	/**
	 * Asserts the planted users the forum plugin counts as moderators of a
	 * forum moderated by $classId, on every path that asks, and that they are
	 * the planted users whose own class list holds $classId.
	 *
	 * @param int $classId
	 * @param string[] $expected roles
	 * @return void
	 */
	private function assertModeratedBy($classId, array $expected)
	{
		$expected = $this->idsOf($expected);
		self::assertSame($expected, $this->holdersAmongPlanted($classId),
			'premise: the planted users holding class '.$classId.' by their own class list');

		$forum = $this->haveModeratedForum($classId);

		foreach($this->moderatorIds($forum) as $path => $ids)
		{
			self::assertSame($expected, $this->planted($ids), $path.' for a forum moderated by class '.$classId);
		}
	}

	/**
	 * @param array $forum forum, thread and post ids
	 * @return array[] user ids per path that decides moderation
	 */
	private function moderatorIds(array $forum)
	{
		$byForum = new e107forum(true);
		$byThread = new e107forum(true);
		$byPost = new e107forum(true);
		$listed = new e107forum(true);

		return array(
			'getModeratorUserIdsByForumId()'  => $byForum->getModeratorUserIdsByForumId($forum['forum']),
			'getModeratorUserIdsByThreadId()' => $byThread->getModeratorUserIdsByThreadId($forum['thread']),
			'getModeratorUserIdsByPostId()'   => $byPost->getModeratorUserIdsByPostId($forum['post']),
			'forumGetMods()'                  => array_keys($listed->forumGetMods($forum['moderators'])),
		);
	}

	/**
	 * @param array $ids
	 * @return int[] the planted users among $ids, sorted
	 */
	private function planted(array $ids)
	{
		$planted = array_values(array_intersect($this->users, array_map('intval', $ids)));
		sort($planted);

		return $planted;
	}

	/**
	 * @param int $classId
	 * @return int[] sorted
	 */
	private function holdersAmongPlanted($classId)
	{
		$holders = array();

		foreach($this->users as $id)
		{
			$user = e107::getObject('e_system_user');
			$user->load($id);

			if(in_array((int) $classId, array_map('intval', $user->getClassList()), true))
			{
				$holders[] = $id;
			}
		}

		sort($holders);

		return $holders;
	}

	/**
	 * @param string[] $roles
	 * @return int[] sorted
	 */
	private function idsOf(array $roles)
	{
		$ids = array();

		foreach($roles as $role)
		{
			$ids[] = $this->users[$role];
		}

		sort($ids);

		return $ids;
	}

	/**
	 * The class form's create, then what userclass2.php runs after it.
	 *
	 * @param string $name
	 * @param int $parent
	 * @return int the new class id
	 */
	private function haveClass($name, $parent)
	{
		$admin = new user_class_admin();
		$id = (int) $admin->findNewClassID();

		self::assertTrue($admin->add_new_class(array(
			'userclass_id'          => $id,
			'userclass_name'        => $name,
			'userclass_description' => $name,
			'userclass_type'        => UC_TYPE_STD,
			'userclass_editclass'   => e_UC_MAINADMIN,
			'userclass_visibility'  => e_UC_MAINADMIN,
			'userclass_parent'      => (int) $parent,
		)), 'the fixture could not create class '.$name);

		$this->rebuildTree($admin);

		return $id;
	}

	/**
	 * Options > Create a default tree.
	 *
	 * @return void
	 */
	private function haveDefaultTree()
	{
		e107::coreLan('userclass2', true);

		$admin = new user_class_admin();
		$admin->set_default_structure();
		$this->rebuildTree($admin);
	}

	/**
	 * @param user_class_admin $admin
	 * @return void
	 */
	private function rebuildTree($admin)
	{
		e107::getUserClass()->clearCache();
		$admin->calc_tree();
		$admin->save_tree();
		e107::getUserClass()->clearCache();
		e107::getUserClass()->readTree(true);
	}

	/**
	 * @param string $role
	 * @param string $classes user_class as stored
	 * @param int $admin
	 * @param string $perms
	 * @return void
	 */
	private function haveUser($role, $classes, $admin = 0, $perms = '')
	{
		$name = 'e107help mod tree '.$role.' '.mt_rand(100000, 999999);

		$id = e107::getDb()->insert('user', array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_email'     => str_replace(' ', '.', $name).'@example.com',
			'user_password'  => md5($name),
			'user_join'      => 1262304000,
			'user_ban'       => 0,
			'user_class'     => $classes,
			'user_admin'     => (int) $admin,
			'user_perms'     => $perms,
		));

		self::assertNotEmpty($id, 'the fixture could not plant user '.$role);

		$this->users[$role] = (int) $id;
	}

	/**
	 * @param int $classId the forum's moderator class
	 * @return array forum, thread, post and moderators
	 */
	private function haveModeratedForum($classId)
	{
		$forumId = $this->haveForum('e107help moderated by '.$classId, $this->categoryId);

		e107::getDb()->update('forum', 'forum_moderators = '.(int) $classId.' WHERE forum_id = '.(int) $forumId);

		$threadId = $this->haveForumThread('e107help moderated thread', $forumId);

		return array(
			'forum'      => $forumId,
			'thread'     => $threadId,
			'post'       => $this->haveForumPost('e107help moderated post', $threadId, $forumId),
			'moderators' => (int) $classId,
		);
	}
}
