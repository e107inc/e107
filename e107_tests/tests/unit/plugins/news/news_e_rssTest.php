<?php

/**
 * What the news feed hands to rss_menu, which prints an item's comment link as its comments element.
 *
 * @see https://github.com/e107inc/e107/discussions/6497
 */
class news_e_rssTest extends \Codeception\Test\Unit
{
	use \Test\CorePrefs;

	/** @var string */
	private $marker;

	/** @var int[] news_id of every row a test planted */
	private $planted = array();

	protected function _before()
	{
		require_once(e_PLUGIN.'news/e_rss.php');

		$this->marker = uniqid('news_e_rssTest', false);
	}

	protected function _after()
	{
		foreach($this->planted as $id)
		{
			e107::getDb()->delete('news', 'news_id = '.(int) $id);
		}
	}

	public function testAnItemOpenToCommentsCarriesTheCommentsLink()
	{
		$id = $this->haveNews('open', false);

		$item = $this->feedItem('open', array('comments_disabled' => 0, 'rss_othernews' => 0));

		$this->assertArrayHasKey('comment', $item, 'An item open to comments carries no comments link.');
		$this->assertSame(SITEURL.'comment.php?comment.news.'.$id, $item['comment']);
	}

	/**
	 * comment.php sends a request for a closed item's comments to the front page, so a link to it leads nowhere.
	 */
	public function testAnItemClosedToCommentsCarriesNoCommentsLink()
	{
		$this->haveNews('closed', true);

		$this->assertArrayNotHasKey('comment', $this->feedItem('closed', array('comments_disabled' => 0, 'rss_othernews' => 0)));
	}

	public function testNoItemCarriesACommentsLinkWhileCommentsAreDisabledSiteWide()
	{
		$this->haveNews('open', false);
		$this->haveNews('closed', true);
		$prefs = array('comments_disabled' => 1, 'rss_othernews' => 0);

		$this->assertArrayNotHasKey('comment', $this->feedItem('open', $prefs));
		$this->assertArrayNotHasKey('comment', $this->feedItem('closed', $prefs));
	}

	/**
	 * rss_othernews is written only by the RSS admin page, so a site that never saved it has no such pref.
	 */
	public function testASiteThatNeverSavedItsRssPrefsListsOnlyMainNews()
	{
		$this->haveNews('open', false);
		$this->haveNews('other', false, '2');

		$items = $this->feed(array('comments_disabled' => 0, 'rss_othernews' => null));

		$this->assertArrayHasKey($this->marker.'-open', $items);
		$this->assertArrayNotHasKey($this->marker.'-other', $items);
	}

	public function testOtherNewsIsListedOnlyWhenTheRssPrefsAskForIt()
	{
		$this->haveNews('other', false, '2');

		$this->assertArrayNotHasKey($this->marker.'-other', $this->feed(array('comments_disabled' => 0, 'rss_othernews' => 0)));
		$this->assertArrayHasKey($this->marker.'-other', $this->feed(array('comments_disabled' => 0, 'rss_othernews' => 1)));
	}

	/**
	 * @param array $prefs core prefs to build the feed under, a null value leaving one unset
	 * @return array[] the items the feed built, keyed by title
	 */
	private function feed(array $prefs)
	{
		$restore = $this->withCorePrefs($prefs);

		try
		{
			$rss = new news_rss();

			return array_column($rss->data(array('url' => 'news', 'id' => '', 'limit' => 999)), null, 'title');
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * @param string $which the suffix the row was planted under
	 * @param array $prefs core prefs to build the feed under
	 * @return array the item the feed built for that row
	 */
	private function feedItem($which, array $prefs)
	{
		$items = $this->feed($prefs);

		$this->assertArrayHasKey($this->marker.'-'.$which, $items, 'The feed did not list the '.$which.' fixture item.');

		return $items[$this->marker.'-'.$which];
	}

	/**
	 * @param string $which a suffix for the title, which is how the item is found in the feed
	 * @param bool $closedToComments stored in news_allow_comments, where 1 means closed
	 * @param string $renderType news_render_type, where 2 is other news
	 * @return int news_id
	 */
	private function haveNews($which, $closedToComments, $renderType = '0')
	{
		$id = e107::getDb()->insert('news', array(
			'news_title'          => $this->marker.'-'.$which,
			'news_body'           => 'body',
			'news_allow_comments' => $closedToComments ? 1 : 0,
			'news_class'          => '0',
			'news_author'         => 1,
			'news_datestamp'      => time() - 60,
			'news_start'          => 0,
			'news_end'            => 0,
			'news_render_type'    => $renderType,
		));

		$this->assertNotEmpty($id, 'Could not seed a news item.');
		$this->planted[] = $id;

		return $id;
	}
}
