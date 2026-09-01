<?php

/**
 * Regression test for discussion #6110: with news_pagination set to "page",
 * {@see news_front::setPagination()} subtracted from $_GET['page'] before the
 * cast, so PHP 8 threw "Unsupported operand types: string - int" and the news
 * page became a fatal error. A scanner's SQL-injection probe in the query string
 * was enough to trigger it, which is how the reporter found it.
 *
 * The Cest drives the ITEMVIEW arm of the switch, the shape the report carried.
 * The NEWSLIST_LIMIT arm is reachable as well, through ?tag= or ?author= beside
 * ?page=, and is left untested: setActions() runs before setPagination(), and
 * both arms now resolve the page and compute the offset in one place.
 */
class NewsPaginationCest
{
	/** The query string from the scan in #6110, before URL encoding. */
	const SCAN = ')/**/AND/**/(199285712=199285712/**/UNION/**/ALL/**/SELECT/**/NULL,NULL,NULL,\'nxxtxdwfockkdvqtjhryvodbfajtiesx\',NULL--/**/iwbdh9';

	const FATAL = 'Unsupported operand types';
	const NEWEST = 'PaginationNewsNewest';
	const OLDEST = 'PaginationNewsOldest';

	/** The news menus in the sidebar carry titles, so only the item's own text proves the listing rendered it. */
	const NEWEST_BODY = 'PaginationNewsNewestBody';
	const OLDEST_BODY = 'PaginationNewsOldestBody';

	/** @var array the prefs this Cest replaced, captured on the first _before only */
	private $restore = array();

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();

		$replaced = array(
			'news_pagination' => $I->haveSitePref('news_pagination', 'page'),
			'newsposts'       => $I->haveSitePref('newsposts', 1),
		);

		if(empty($this->restore))
		{
			$this->restore = $replaced;
		}

		$this->haveNews($I, self::OLDEST, time() - 60);
		$this->haveNews($I, self::NEWEST, time());
	}

	public function _after(AcceptanceTester $I)
	{
		foreach($this->restore as $name => $value)
		{
			$I->haveSitePref($name, $value === '' ? null : $value);
		}
	}

	public function aScannedPageParameterStillRendersTheNewsPage(AcceptanceTester $I)
	{
		if(PHP_VERSION_ID < 70100)
		{
			$I->markTestSkipped('Before 7.1 the subtraction converts the scan string silently, so the unfixed code renders too.');
		}

		$I->wantTo('answer a scanner with the news page instead of a fatal error');

		$I->amOnPage('/news.php?page=' . rawurlencode(self::SCAN));

		$I->assertSame(200, $I->grabResponseCode());
		$I->dontSee(self::FATAL);
		$I->see(self::NEWEST_BODY);
	}

	public function aNumericPageSelectsThatPageOfNews(AcceptanceTester $I)
	{
		$I->wantTo('reach the second page of news by number');

		$I->amOnPage('/news.php?page=1');
		$I->see(self::NEWEST_BODY);
		$I->dontSee(self::OLDEST_BODY);

		$I->amOnPage('/news.php?page=2');
		$I->see(self::OLDEST_BODY);
		$I->dontSee(self::NEWEST_BODY);
	}

	/**
	 * Eighteen digits fit in an integer and overflow the offset into a float,
	 * which release/v2.3.x interpolates into LIMIT as 1.05E+19.
	 */
	public function AnOversizedPageNumberDoesNotOverflowTheOffset(AcceptanceTester $I)
	{
		$I->wantTo('survive a page number too large to multiply');

		$I->haveSitePref('newsposts', 15);

		$I->amOnPage('/news.php?page=700000000000000000');

		$I->assertSame(200, $I->grabResponseCode());
		$I->dontSee('E+19');
	}

	/**
	 * Seeds the marker in the body and the meta description alike, because the shipped news templates render one or the other.
	 *
	 * @param int $datestamp
	 * @return int news id
	 */
	private function haveNews(AcceptanceTester $I, $title, $datestamp)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title' => $title, 'news_sef' => strtolower($title),
			'news_body' => $title . 'Body', 'news_extended' => '',
			'news_meta_title' => '', 'news_meta_keywords' => '', 'news_meta_description' => $title . 'Body',
			'news_datestamp' => $datestamp, 'news_author' => 1, 'news_category' => 0,
			'news_allow_comments' => 1, 'news_sticky' => 0,
			'news_start' => 0, 'news_end' => 0, 'news_class' => '0', 'news_render_type' => '0',
			'news_summary' => '', 'news_thumbnail' => '',
		));
	}
}
