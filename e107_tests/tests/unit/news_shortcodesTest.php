<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class news_shortcodesTest extends \Test\Unit
{
	const NEWS_ID = 64910;

	/** @var news_shortcodes */
	private $sc;

	/** @var mixed */
	private $savedCommentsIcon;

	/** @var int */
	private $commentId;

	protected function _before()
	{
		$this->savedCommentsIcon = e107::getConfig()->get('comments_icon');

		$this->commentId = e107::getDb()->createQueryBuilder()->insert('comments')->insertGetId(array(
			'comment_item_id'     => self::NEWS_ID,
			'comment_type'        => '0',
			'comment_author_id'   => 1,
			'comment_author_name' => 'admin',
			'comment_datestamp'   => 1700000000,
			'comment_comment'     => 'first',
			'comment_blocked'     => 0,
		));

		require_once(e_CORE.'shortcodes/batch/news_shortcodes.php');
		$this->sc = new news_shortcodes();
	}

	protected function _after()
	{
		e107::getConfig()->set('comments_icon', $this->savedCommentsIcon);
		e107::getDb()->createQueryBuilder()->delete('comments')->where('comment_id', $this->commentId)->execute();
	}

	/**
	 * The previous and next links lead to the visible news items either side of the one shown.
	 */
	public function testTheNavigationFindsTheNeighbouringNewsItems()
	{
		$ids = array();

		foreach(array('earlier' => 1100000000, 'hidden' => 1100000050, 'current' => 1100000100, 'later' => 1100000200) as $name => $datestamp)
		{
			$ids[$name] = (int) e107::getDb()->createQueryBuilder()->insert('news')->insertGetId(array(
				'news_title'            => 'nav '.$name,
				'news_body'             => '',
				'news_extended'         => '',
				'news_meta_description' => '',
				'news_summary'          => '',
				'news_thumbnail'        => '',
				'news_datestamp'        => $datestamp,
				'news_class'            => ($name === 'hidden') ? (string) e_UC_NOBODY : '0',
				'news_render_type'      => ($name === 'later') ? '1,4' : '0',
			));
		}

		try
		{
			$this->sc->setScVar('news_item', array('news_id' => $ids['current'], 'news_datestamp' => 1100000100));
			$nav = new ReflectionMethod($this->sc, 'getNavQuery');
			$nav->setAccessible(true);

			$previous = $nav->invoke($this->sc, 'previous');
			$next = $nav->invoke($this->sc, 'next');

			$this->assertSame('nav earlier', $previous['news_title'], 'an item only nobody may see is passed over');
			$this->assertSame('nav later', $next['news_title'], 'an item rendered in the list as well as elsewhere counts');
		}
		finally
		{
			e107::getDb()->createQueryBuilder()->delete('news')->whereIn('news_id', array_values($ids))->execute();
		}
	}

	public function testNewsCommentsLinkRendersWithoutLastVisit()
	{
		e107::getConfig()->set('comments_icon', 1);

		$this->sc->setScVar('news_item', array(
			'news_id'             => self::NEWS_ID,
			'news_title'          => 'Commented item',
			'news_sef'            => 'commented-item',
			'news_comment_total'  => 1,
			'news_allow_comments' => 0,
		));
		$this->sc->setScVar('param', array('current_action' => 'list'));

		$result = $this->sc->sc_newscomments();

		$this->assertStringContainsString('>1</a>', $result);
	}
}
