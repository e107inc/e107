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
 * An edit used to be written as it arrived, so a member could put markup into
 * their own post by editing it. The forum's upgrade counts the edited posts
 * that hold markup their author or last editor may not post and reports the number, and
 * changes nothing stored: a row cannot show whether its author held post_html
 * when it was written.
 */
class forumRawEditTest extends \Test\Unit
{

	/** @var bool whether this test installed the forum plugin for its tables */
	private $forumInstalled = false;

	const PARENT_CLASS = 234;

	const CHILD_CLASS = 235;

	/** @var int[] */
	private $users = array();

	/** @var int[] */
	private $posts = array();

	/** @var mixed */
	private $checked;

	protected function _before()
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."forum'"))
		{
			e107::getPlugin()->install('forum');
			$this->forumInstalled = true;
		}

		require_once(e_PLUGIN.'forum/forum_setup.php');
		$this->checked = e107::getPlugConfig('forum')->get('edit_body_checked');
		e107::getPlugConfig('forum')->remove('edit_body_checked')->save(false, true, false);
	}

	protected function _after()
	{
		foreach($this->posts as $post)
		{
			e107::getDb()->createQueryBuilder()->delete('forum_post')->where('post_id', (int) $post)->execute();
		}

		foreach($this->users as $user)
		{
			e107::getDb()->createQueryBuilder()->delete('user')->where('user_id', (int) $user)->execute();
		}

		e107::getPlugConfig('forum')->set('edit_body_checked', $this->checked)->save(false, true, false);

		if($this->forumInstalled)
		{
			e107::getPlugin()->uninstall('forum');
			$this->forumInstalled = false;
		}
	}

	public function testTheUpgradeCountsEditedPostsWithMarkupAndChangesNone()
	{
		$setup = new forum_setup();
		$baseline = $setup->reportRawEdits();
		e107::getPlugConfig('forum')->remove('edit_body_checked')->save(false, true, false);
		$member = $this->haveUser('rawedit_member', 0);
		$admin = $this->haveUser('rawedit_admin', 1);
		$rows = array(
			$this->havePost('Mine <details open ontoggle=x>d</details> end', $member, $member),
			$this->havePost('Admin <b>bold</b>', $admin, $admin),
			$this->havePost('Member <b>x</b>', $member, $admin),
			$this->havePost('Typed &lt;b&gt;', $member, $member),
			$this->havePost('Gone <b>x</b>', 987654321, 987654321),
			$this->havePost('Admin <b>edited by a moderator</b>', $admin, $member),
		);
		$before = array_map(array($this, 'entry'), $rows);

		$count = $setup->reportRawEdits();

		self::assertSame(4, $count - $baseline);
		self::assertStringContainsString('Some may be legitimate HTML, written while their authors could still post it.', json_encode(e107::getMessage()->getAll('default', true)));
		self::assertSame($before, array_map(array($this, 'entry'), $rows));
		self::assertNotEmpty(e107::getPlugConfig('forum')->get('edit_body_checked'));
	}

	public function testAnAuthorWhoGetsTheHtmlClassThroughAParentKeepsTheirMarkupOnEdit()
	{
		$this->withInheritedHtmlClass(function ()
		{
			$author = $this->haveUser('rawedit_inherits', 0, (string) self::CHILD_CLASS);

			self::assertStringContainsString('<b>kept</b>', e107::getParser()->toDB('Mine <b>kept</b>', false, false, false, $author));
		});
	}

	public function testAnAuthorWhoGetsTheHtmlClassThroughAParentIsNotCounted()
	{
		$this->withInheritedHtmlClass(function ()
		{
			$setup = new forum_setup();
			$baseline = $setup->reportRawEdits();
			e107::getPlugConfig('forum')->remove('edit_body_checked')->save(false, true, false);
			$author = $this->haveUser('rawedit_inherits', 0, (string) self::CHILD_CLASS);
			$this->havePost('Mine <b>kept</b>', $author, $author);

			self::assertSame(0, $setup->reportRawEdits() - $baseline);
		});
	}

	/**
	 * Run $run with post_html set to a class its members may also hold through a child class.
	 *
	 * @param callable $run
	 */
	private function withInheritedHtmlClass($run)
	{
		$config = e107::getConfig();
		$was = array('post_html' => $config->get('post_html'), 'post_script' => $config->get('post_script'));
		$tree = e107::getUserClass()->class_tree;

		try
		{
			foreach(array(self::PARENT_CLASS => (string) self::PARENT_CLASS, self::CHILD_CLASS => self::CHILD_CLASS . ',' . self::PARENT_CLASS) as $id => $accum)
			{
				e107::getDb()->createQueryBuilder()->insert('userclass_classes')->values(array(
					'userclass_id' => $id, 'userclass_name' => 'RAWEDIT' . $id, 'userclass_description' => '', 'userclass_editclass' => e_UC_MAINADMIN,
					'userclass_parent' => e_UC_PUBLIC, 'userclass_accum' => $accum, 'userclass_visibility' => e_UC_PUBLIC, 'userclass_type' => 0,
					'userclass_icon' => '', 'userclass_perms' => '',
				))->execute();
			}

			$this->rereadClasses();
			$config->set('post_html', self::PARENT_CLASS)->set('post_script', self::PARENT_CLASS);
			$run();
		}
		finally
		{
			$config->set('post_html', $was['post_html'])->set('post_script', $was['post_script']);
			e107::getDb()->createQueryBuilder()->delete('userclass_classes')->where('userclass_id', 'IN', array(self::PARENT_CLASS, self::CHILD_CLASS))->execute();
			e107::getCache()->clear_sys(UC_CACHE_TAG);
			e107::getUserClass()->class_tree = $tree;

			foreach($this->users as $user)
			{
				e107::setRegistry('core/e107/user/' . $user, null);
			}
		}
	}

	private function rereadClasses()
	{
		e107::getCache()->clear_sys(UC_CACHE_TAG);
		e107::getUserClass()->readTree(true);
	}

	/**
	 * @param string $name
	 * @param int $admin
	 * @param string $classes the user's own classes
	 * @return int user id
	 */
	private function haveUser($name, $admin, $classes = '')
	{
		$sql = e107::getDb();
		$sql->createQueryBuilder()->insert('user')->values(array(
			'user_name' => $name, 'user_loginname' => $name, 'user_password' => '', 'user_email' => $name.'@example.com',
			'user_join' => time(), 'user_class' => $classes, 'user_admin' => $admin, 'user_perms' => $admin ? '0' : '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		))->execute();
		$id = $sql->lastInsertId();

		self::assertNotEmpty($id, 'could not write the user row this test needs: '.$sql->getLastErrorText());
		$this->users[] = (int) $id;

		return (int) $id;
	}

	/**
	 * @param string $entry as stored
	 * @param int $author
	 * @param int $editor who last edited it
	 * @return int post id
	 */
	private function havePost($entry, $author, $editor)
	{
		$sql = e107::getDb();
		$sql->createQueryBuilder()->insert('forum_post')->values(array(
			'post_entry' => $entry, 'post_thread' => 0, 'post_forum' => 0, 'post_status' => 0,
			'post_datestamp' => time() - 3600, 'post_user' => $author, 'post_ip' => '127.0.0.1',
			'post_edit_datestamp' => time() - 60, 'post_edit_user' => $editor,
		))->execute();
		$id = $sql->lastInsertId();

		self::assertNotEmpty($id, 'could not write the post row this test needs: '.$sql->getLastErrorText());
		$this->posts[] = (int) $id;

		return (int) $id;
	}

	/**
	 * @param int $post
	 * @return string
	 */
	private function entry($post)
	{
		return (string) e107::getDb()->createQueryBuilder()->select('post_entry')->from('forum_post')->where('post_id', (int) $post)->fetchOne();
	}
}
