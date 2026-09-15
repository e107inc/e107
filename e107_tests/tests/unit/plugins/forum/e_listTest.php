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
 * The forum's section of the list_new pages, which names whoever started each
 * thread. A guest starts one under a name of their own, held in
 * thread_user_anon, and there is no profile to link it to.
 */
class e_listTest extends \Codeception\Test\Unit
{
	use \Test\ForumRows;

	const GUEST = 'e107help probe guest';

	/** @var int */
	private $forumId;

	/** @var string */
	private $threadName;

	protected function _before()
	{
		$this->haveForumTables();

		$category = $this->haveForum('e107help probe category');
		$this->forumId = $this->haveForum('e107help probe forum', $category);

		$this->threadName = 'e107help probe thread '.time();
		$this->haveForumThread($this->threadName, $this->forumId, 0, self::GUEST);
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	/**
	 * The column is thread_user_anon, and the row read thread_anon, which no
	 * query selected either. Every thread therefore took the profile-link
	 * branch, and a guest has no profile, so the cell was a link with nothing
	 * in it.
	 */
	public function testAThreadOpenedByAGuestIsAttributedToTheNameTheGuestGave()
	{
		require_once(e_PLUGIN.'list_new/list_class.php');
		require_once(e_PLUGIN.'forum/e_list.php');

		$rc = new listclass();
		$rc->mode = 'recent_page';
		$rc->list_pref = array(
			'recent_page_icon_use'       => '1',
			'recent_page_icon_default'   => '1',
			'recent_page_char_heading'   => '',
			'recent_page_char_postfix'   => '',
			'recent_page_datestyle'      => '%d %b',
			'recent_page_datestyletoday' => '%H:%M',
		);
		$rc->settings = array(
			'section'  => 'forum',
			'caption'  => 'Forum',
			'open'     => '1',
			'icon'     => '',
			'amount'   => '20',
			'author'   => '1',
			'category' => '1',
			'date'     => '1',
		);

		$list = new list_forum($rc);
		$data = $list->getListData();

		self::assertIsArray($data['records'], 'the seeded thread is the most recent, so the section has records');

		$record = null;
		foreach($data['records'] as $candidate)
		{
			if(strpos($candidate['heading'], $this->threadName) !== false)
			{
				$record = $candidate;
			}
		}

		self::assertNotNull($record, 'the seeded thread is missing from the list the forum section returned');
		self::assertSame(self::GUEST, $record['author']);
	}
}
