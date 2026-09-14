<?php

/**
 * @see https://github.com/e107inc/e107/issues/5880
 */
class e_rssTest extends \Test\Unit
{
	/** @var rss_menu_rss */
	private $addon;

	/** @var string */
	private $marker;

	/** @var int */
	private $newsId;

	/** @var int */
	private $commentId;

	/** @var string */
	private $host;

	protected function _before()
	{
		require_once(e_PLUGIN . 'rss_menu/e_rss.php');

		$this->addon = new rss_menu_rss();
		$this->marker = uniqid('e_rssTest', false);
		$this->host = varset($_SERVER['HTTP_HOST'], '');

		$this->seed();
	}

	protected function _after()
	{
		$_SERVER['HTTP_HOST'] = $this->host;

		$db = e107::getDb();
		$db->createQueryBuilder()->delete('comments')->where('comment_id', $this->commentId)->execute();
		$db->createQueryBuilder()->delete('news')->where('news_id', $this->newsId)->execute();
	}

	/**
	 * The feed used to build its links from the request host, so a site behind a
	 * proxy, or reached by an alias, published links to whichever name the
	 * request happened to arrive under.
	 */
	public function testItemLinksComeFromTheSiteUrlAndNotTheRequestHost()
	{
		$_SERVER['HTTP_HOST'] = 'rogue.example.net';

		$item = $this->seededItem($this->addon->data(array('url' => 'comments', 'id' => '', 'limit' => 9)));

		$this::assertSame(SITEURL . 'comment.php?comment.news.' . $this->newsId, $item['link']);
		$this::assertStringNotContainsString('rogue.example.net', $item['link']);
	}

	/**
	 * rssCreate maps an addon's rows onto the feed's items and reads the item
	 * date from 'datestamp'. A row spelling it any other way is silently dated
	 * to the moment the feed was built.
	 */
	public function testItemsCarryTheCommentDateUnderTheKeyTheFeedReads()
	{
		$item = $this->seededItem($this->addon->data(array('url' => 'comments', 'id' => '', 'limit' => 9)));

		$this::assertArrayHasKey('datestamp', $item);
		$this::assertNotEmpty($item['datestamp']);
	}

	/**
	 * The author came from comment_author, a column the 2.0 schema split into
	 * comment_author_id and comment_author_name, so every item carried an empty
	 * author and the feed never named anybody.
	 */
	public function testItemsNameTheCommentAuthor()
	{
		$item = $this->seededItem($this->addon->data(array('url' => 'comments', 'id' => '', 'limit' => 9)));

		$this::assertSame('admin', $item['author']);
	}

	public function testTheLegacyKeyIsDeclaredHereRatherThanHeldByTheResolver()
	{
		$this::assertSame(array(5 => 'comments'), $this->addon->legacy());
	}

	/**
	 * @param array $items what data() returned
	 * @return array the item for the seeded comment
	 */
	private function seededItem($items)
	{
		foreach($items as $item)
		{
			if($item['title'] === $this->marker)
			{
				return $item;
			}
		}

		$this::fail('the seeded comment is missing from the feed');
	}

	/**
	 * A published, unrestricted news item and one comment on it.
	 */
	private function seed()
	{
		$db = e107::getDb();

		$this->newsId = $db->createQueryBuilder()->insert('news')->insertGetId(array(
			'news_title'            => $this->marker,
			'news_sef'              => '',
			'news_body'             => 'body',
			'news_extended'         => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => time() - 3600,
			'news_author'           => 1,
			'news_category'         => 0,
			'news_start'            => 0,
			'news_end'              => 0,
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_summary'          => '',
			'news_thumbnail'        => '',
		));

		$this->commentId = $db->createQueryBuilder()->insert('comments')->insertGetId(array(
			'comment_pid'          => 0,
			'comment_item_id'      => $this->newsId,
			'comment_subject'      => $this->marker,
			'comment_author_id'    => 1,
			'comment_author_name'  => 'admin',
			'comment_author_email' => '',
			'comment_datestamp'    => time(),
			'comment_comment'      => 'comment body',
			'comment_blocked'      => 0,
			'comment_ip'           => '',
			'comment_type'         => '0',
			'comment_lock'         => 0,
			'comment_share'        => 0,
		));

		$this::assertNotEmpty($this->newsId, 'could not seed a news item: '.$db->getLastErrorText());
		$this::assertNotEmpty($this->commentId, 'could not seed a comment: '.$db->getLastErrorText());
	}
}
