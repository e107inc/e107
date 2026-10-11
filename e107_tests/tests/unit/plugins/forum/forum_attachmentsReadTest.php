<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Who may read a post attachment, by the rule of the forum the post is in. See issue #6282.
 *
 * @group plugins
 */
class forum_attachmentsReadTest extends \Test\Unit
{
	use \Test\ForumRows;

	/** @var string[] files and directories this test made, removed deepest first */
	private $litter = array();

	protected function _before()
	{
		require_once(APP_PATH.'/e107_plugins/forum/forum_attachments.php');

		$this->haveForumTables();
	}

	protected function _after()
	{
		foreach($this->litter as $path)
		{
			is_dir($path) ? rmdir($path) : unlink($path);
		}

		$this->litter = array();
		$this->dropForumRows();
	}

	public function testAnAttachmentInAPublicForumIsEveryones()
	{
		$this->assertTrue($this->attachmentIn(e_UC_PUBLIC)->admitsEveryone());
	}

	public function testAnAttachmentInAForumHiddenFromGuestsIsReadByMembersOnly()
	{
		$attachment = $this->attachmentIn(-e_UC_GUEST);

		$this->assertFalse($attachment->admitsEveryone(), 'A forum that shuts guests out is not every caller\'s, so its files are never served without asking who is calling.');
		$this->assertTrue($attachment->mayRead(array(e_UC_PUBLIC, e_UC_MEMBER)), 'A member is outside the excluded class and may read it.');
		$this->assertFalse($attachment->mayRead(array(e_UC_PUBLIC, e_UC_GUEST)), 'A guest is the excluded class.');
	}

	public function testAPublicForumInACategoryHiddenFromGuestsIsReadByMembersOnly()
	{
		$attachment = $this->attachmentIn(e_UC_PUBLIC, -e_UC_GUEST);

		$this->assertFalse($attachment->admitsEveryone(), 'A forum grants nothing its category withholds.');
		$this->assertTrue($attachment->mayRead(array(e_UC_PUBLIC, e_UC_MEMBER)));
		$this->assertFalse($attachment->mayRead(array(e_UC_PUBLIC, e_UC_GUEST)));
	}

	/**
	 * @param int $class the forum's forum_class
	 * @param int $categoryClass the forum_class of the category it sits in
	 * @return forum_attachments an image attached to a post in that forum
	 */
	private function attachmentIn($class, $categoryClass = e_UC_PUBLIC)
	{
		$forum = $this->haveForum('uc probe forum', $this->haveForum('uc probe category', 0, $categoryClass), $class);
		$post = $this->haveForumPost('probe', $this->haveForumThread('uc probe thread', $forum), $forum);

		$paths = forum_attachments::paths();
		$dir = $paths[0].'user_1/';
		foreach(array($paths[0], $dir) as $made)
		{
			if(!is_dir($made))
			{
				mkdir($made, 0755, true);
				array_unshift($this->litter, $made);
			}
		}

		$name = 'uc_probe_'.uniqid().'.png';
		file_put_contents($dir.$name, 'probe');
		array_unshift($this->litter, $dir.$name);

		e107::getDb()->createQueryBuilder()->update('forum_post')
			->set('post_attachments', e107::serialize(array('img' => array($name))))
			->where('post_id', $post)->execute();

		return new forum_attachments($dir.$name);
	}
}
