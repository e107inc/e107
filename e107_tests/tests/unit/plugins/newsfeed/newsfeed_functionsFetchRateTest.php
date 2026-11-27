<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * How often {@see newsfeedClass::getFeed()} asks a feed's host for the feed, and what it serves when the answer is not a feed.
 */
class newsfeed_functionsFetchRateTest extends \Test\Unit
{
	use \Test\InstalledPlugins;
	use \Test\ScriptClassPages;

	const HOST = 'feed.example.test';

	const NAME = 'Fetch rate fixture';

	const INTERVAL = 3600;

	/** @var int how many times the feed host was asked */
	private $fetches = 0;

	/** @var mixed */
	private $savedXml;

	/** @var bool */
	private $savedCacheActive;

	/** @var string|null */
	private $savedPageMD5;

	protected function _before()
	{
		$this->installPluginFor('newsfeed', 'newsfeed');

		if(!class_exists('newsfeedClass'))
		{
			require(e_PLUGIN.'newsfeed/newsfeed_functions.php');
		}

		require_once(e_HANDLER.'xml_class.php');

		$cache = e107::getCache();
		$this->savedXml = e107::getRegistry('core/e107/singleton/xmlClass');
		$this->savedCacheActive = $cache->UserCacheActive;
		$this->savedPageMD5 = $cache->CachePageMD5;

		$this->clearFeedCaches();
	}

	protected function _after()
	{
		$cache = e107::getCache();
		e107::setRegistry('core/e107/singleton/xmlClass', $this->savedXml);
		$cache->UserCacheActive = $this->savedCacheActive;
		$cache->CachePageMD5 = $this->savedPageMD5;

		$this->clearFeedCaches();

		e107::getDb()->createQueryBuilder()->delete('newsfeed')->whereLike('newsfeed_name', self::NAME.'%')->execute();

		$this->uninstallInstalledPlugins();
	}

	public function testOneCopyOfAFeedServesEveryPageAndVisitor()
	{
		$cache = e107::getCache();

		$cache->CachePageMD5 = md5('news.php?extend.1, as a guest');
		$list = $cache->cache_fname(NEWSFEED_LIST_CACHE_TAG);
		$news = $cache->cache_fname(NEWSFEED_NEWS_CACHE_TAG.'1');

		$cache->CachePageMD5 = md5('page.php?id=2, as a member');

		self::assertSame($list, $cache->cache_fname(NEWSFEED_LIST_CACHE_TAG));
		self::assertSame($news, $cache->cache_fname(NEWSFEED_NEWS_CACHE_TAG.'1'));
	}

	public function testAFeedListFromTheCacheServesTheCachedFeed()
	{
		e107::getCache()->UserCacheActive = true;
		$id = $this->haveFeed(time() - 60, $this->lastGood());
		$this->feedClass()->readFeedList();
		e107::getCache()->set(NEWSFEED_NEWS_CACHE_TAG.$id, $this->lastGood(), true);
		$this->hostAnswers(false);

		$feed = $this->feedClass()->getFeed($id);

		self::assertSame(0, $this->fetches);
		self::assertSame('Kept item', $feed['items'][0]['title']);
	}

	public function testAFetchedFeedIsAskedForAgainOnlyAfterItsInterval()
	{
		e107::getCache()->UserCacheActive = true;
		$id = $this->haveFeed(0, '');
		$this->hostAnswers('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Fixture</title>'.
			'<item><title>Fresh item</title><link>https://feed.example.test/1</link></item></channel></rss>');

		$first = $this->feedClass()->getFeed($id);
		$second = $this->feedClass()->getFeed($id);

		self::assertSame(1, $this->fetches);
		self::assertSame('Fresh item', $first['items'][0]['title']);
		self::assertSame($first, $second);
		$stored = $this->storedFeed($id);
		self::assertGreaterThan(0, (int) $stored['newsfeed_timestamp']);

		touch(e107::getCache()->cache_fname(NEWSFEED_NEWS_CACHE_TAG.$id), time() - self::INTERVAL - 60);
		$this->feedClass()->getFeed($id);

		self::assertSame(2, $this->fetches);
	}

	public function testAnEditAsksForTheFeedAgainInEveryLanguage()
	{
		e107::getCache()->UserCacheActive = false;
		$id = $this->haveFeed(time(), $this->lastGood());
		e107::getCache()->set(NEWSFEED_NEWS_CACHE_TAG.$id, $this->lastGood(), true);
		e107::getCache()->set($this->otherLanguageTag(NEWSFEED_NEWS_CACHE_TAG).$id, $this->lastGood(), true);

		$this->editFeed($id, 'https://'.self::HOST.'/rss.xml');
		$this->hostAnswers(false);
		$feed = $this->feedClass()->getFeed($id);

		self::assertFalse(e107::getCache()->retrieve($this->otherLanguageTag(NEWSFEED_NEWS_CACHE_TAG).$id, false, true), 'the copy for another language survived the edit');
		self::assertSame(1, $this->fetches);
		self::assertSame('Kept item', $feed['items'][0]['title']);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function changesToTheFeedList()
	{
		return array(
			'a feed created' => array('create'),
			'a feed copied'  => array('copy'),
			'a feed deleted' => array('delete'),
		);
	}

	/**
	 * @dataProvider changesToTheFeedList
	 * @param string $change
	 */
	public function testAChangeToTheFeedListAsksForTheListAgainInEveryLanguage($change)
	{
		$id = $this->haveFeed(time(), $this->lastGood());
		e107::getCache()->set(NEWSFEED_LIST_CACHE_TAG, 'Stale list', true);
		e107::getCache()->set($this->otherLanguageTag(NEWSFEED_LIST_CACHE_TAG), 'Stale list', true);

		$page = $this->changeFeedList($change, $id);

		self::assertFalse(e107::getCache()->retrieve(NEWSFEED_LIST_CACHE_TAG, false, true), "the list survived:\n".$page);
		self::assertFalse(e107::getCache()->retrieve($this->otherLanguageTag(NEWSFEED_LIST_CACHE_TAG), false, true), 'the list for another language survived');
	}

	public function testAnEditToTheAddressDropsTheCopyFromTheOldOne()
	{
		e107::getCache()->UserCacheActive = false;
		$id = $this->haveFeed(time(), $this->lastGood());

		$this->editFeed($id, 'https://'.self::HOST.'/moved.xml');
		$this->hostAnswers(false);

		self::assertFalse($this->feedClass()->getFeed($id));
		self::assertSame('', $this->storedFeed($id)['newsfeed_data']);
	}

	public function testAFailedFetchServesTheLastGoodCopyAndWaitsTheInterval()
	{
		e107::getCache()->UserCacheActive = false;
		$stale = time() - (2 * self::INTERVAL);
		$id = $this->haveFeed($stale, $this->lastGood());
		$this->hostAnswers(false);

		$feed = $this->feedClass()->getFeed($id);

		self::assertSame(1, $this->fetches);
		self::assertSame('Kept item', $feed['items'][0]['title']);
		$this->assertStoredUnchanged($id, $stale);

		e107::getDb()->createQueryBuilder()->update('newsfeed')->set('newsfeed_data', '')
			->where('newsfeed_id', $id)->execute();

		$again = $this->feedClass()->getFeed($id);

		self::assertSame(1, $this->fetches);
		self::assertSame($feed, $again);
	}

	public function testAFailedFirstFetchWaitsTheIntervalToo()
	{
		e107::getCache()->UserCacheActive = false;
		$id = $this->haveFeed(0, '');
		$this->hostAnswers(false);

		self::assertFalse($this->feedClass()->getFeed($id));
		self::assertFalse($this->feedClass()->getFeed($id));
		self::assertSame(1, $this->fetches);
	}

	public function testAViewDuringAFetchServesTheLastGoodCopyWithoutFetching()
	{
		e107::getCache()->UserCacheActive = false;
		$id = $this->haveFeed(time() - (2 * self::INTERVAL), $this->lastGood());
		$meanwhile = null;
		$this->hostAnswers(false, function () use (&$meanwhile, $id)
		{
			$meanwhile = $this->feedClass()->getFeed($id);
		});

		$this->feedClass()->getFeed($id);

		self::assertSame(1, $this->fetches);
		self::assertSame('Kept item', $meanwhile['items'][0]['title']);
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function answersThatAreNotAFeed()
	{
		return array(
			'a flood warning page' => array('<!DOCTYPE html><html><head><title>Slow down</title></head><body><p>Flood protection</p></body></html>'),
			'a feed cut off before its first item' => array('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Fixture</title>'),
		);
	}

	/**
	 * @dataProvider answersThatAreNotAFeed
	 * @param string $body
	 */
	public function testAnAnswerThatIsNotAFeedLeavesTheLastGoodCopy($body)
	{
		e107::getCache()->UserCacheActive = false;
		$stale = time() - (2 * self::INTERVAL);
		$id = $this->haveFeed($stale, $this->lastGood());
		$this->hostAnswers($body);

		$feed = $this->feedClass()->getFeed($id);

		self::assertSame(1, $this->fetches);
		self::assertSame('Kept item', $feed['items'][0]['title']);
		$this->assertStoredUnchanged($id, $stale);
	}

	/**
	 * @return array<string, array{0: string, 1: string|false}>
	 */
	public function copiesThatNameMagpieRSS()
	{
		return array(
			'a copy stored by version 1, its host down' => array('O:9:"MagpieRSS":1:{s:5:"items";a:0:{}}', false),
			'a feed whose own text names MagpieRSS'     => array('', '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Fixture</title>'.
				'<item><title>MagpieRSS turns twenty</title><link>https://feed.example.test/1</link></item></channel></rss>'),
		);
	}

	/**
	 * @dataProvider copiesThatNameMagpieRSS
	 * @param string $stored
	 * @param string|false $body
	 */
	public function testAFeedShownTwiceOnAPageIsAskedForOnceWhenItsCopyNamesMagpieRSS($stored, $body)
	{
		e107::getCache()->UserCacheActive = false;
		$id = $this->haveFeed(0, $stored);
		$this->hostAnswers($body);
		$view = $this->feedClass();

		$view->getFeed($id);
		$view->getFeed($id);

		self::assertSame(1, $this->fetches);
	}

	/**
	 * @return newsfeedClass a fresh instance, as the next page view would make
	 */
	private function feedClass()
	{
		return new newsfeedClass();
	}

	/**
	 * @param string|false $body what the feed host answers, false for no answer
	 * @param callable|null $during run once while the first fetch is in flight, as another visitor's view would be
	 * @return void
	 */
	private function hostAnswers($body, $during = null)
	{
		$fetches = &$this->fetches;

		e107::setRegistry('core/e107/singleton/xmlClass', $this->make('xmlClass', array(
			'errors'        => ($body === false) ? 'Curl error: 6, Could not resolve host: '.self::HOST : '',
			'getRemoteFile' => function () use (&$fetches, &$during, $body)
			{
				$fetches++;

				if($during !== null)
				{
					$view = $during;
					$during = null;
					$view();
				}

				return $body;
			},
		)));
	}

	/**
	 * @param int $id
	 * @param string $url the address to save
	 * @return void
	 */
	private function editFeed($id, $url)
	{
		$page = $this->openAsScriptClass(e_UC_MAINADMIN, 'e107_plugins/newsfeed/admin_config.php', 'mode=main&action=edit&id='.$id,
			$this->feedForm(self::NAME.', renamed', $url) + array('etrigger_submit' => 'update'));

		self::assertSame(self::NAME.', renamed', e107::getDb()->createQueryBuilder()->select(array('newsfeed_name'))
			->from('newsfeed')->where('newsfeed_id', $id)->fetchOne(), "the edit was not saved:\n".$page);
	}

	/**
	 * @param string $change 'create', 'copy' or 'delete'
	 * @param int $id the feed a copy or delete selects
	 * @return string the page
	 */
	private function changeFeedList($change, $id)
	{
		$posts = array(
			'create' => array('mode=main&action=create', $this->feedForm(self::NAME.', created', 'https://'.self::HOST.'/new.xml') + array('etrigger_submit' => 'create')),
			'copy'   => array('mode=main&action=list', array('e-multiselect' => array($id), 'etrigger_batch' => 'copy', 'e__execute_batch' => 1)),
			'delete' => array('mode=main&action=list', array('etrigger_delete' => array($id => $id), 'etrigger_delete_confirm' => 1)),
		);

		return $this->openAsScriptClass(e_UC_MAINADMIN, 'e107_plugins/newsfeed/admin_config.php', $posts[$change][0], $posts[$change][1]);
	}

	/**
	 * @param string $name
	 * @param string $url
	 * @return array the form the admin area posts for a feed
	 */
	private function feedForm($name, $url)
	{
		return array(
			'newsfeed_name'        => $name,
			'newsfeed_url'         => $url,
			'newsfeed_description' => 'Fixture feed',
			'newsfeed_image'       => 'default',
			'newsfeed_showmenu'    => 5,
			'newsfeed_showmain'    => 10,
			'newsfeed_updateint'   => self::INTERVAL,
			'newsfeed_active'      => 1,
		);
	}

	/**
	 * @param int $timestamp
	 * @param string $data
	 * @return int
	 */
	private function haveFeed($timestamp, $data)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('newsfeed')->insertGetId(array(
			'newsfeed_name'        => self::NAME,
			'newsfeed_url'         => 'https://'.self::HOST.'/rss.xml',
			'newsfeed_data'        => $data,
			'newsfeed_timestamp'   => $timestamp,
			'newsfeed_description' => 'Fixture feed',
			'newsfeed_image'       => 'default::5::10',
			'newsfeed_active'      => 1,
			'newsfeed_updateint'   => self::INTERVAL,
		));

		return $id;
	}

	/**
	 * @param int $id
	 * @return array<string, int|string>
	 */
	private function storedFeed($id)
	{
		return e107::getDb()->createQueryBuilder()->select(array('newsfeed_data', 'newsfeed_timestamp'))
			->from('newsfeed')->where('newsfeed_id', $id)->fetchRow();
	}

	/**
	 * @param int $id
	 * @param int $timestamp
	 * @return void
	 */
	private function assertStoredUnchanged($id, $timestamp)
	{
		$stored = $this->storedFeed($id);

		self::assertSame($this->lastGood(), $stored['newsfeed_data']);
		self::assertSame($timestamp, (int) $stored['newsfeed_timestamp']);
	}

	/**
	 * @return string
	 */
	private function lastGood()
	{
		return e107::serialize(array(
			'channel' => array('title' => 'Fixture'),
			'items'   => array(array('title' => 'Kept item', 'link' => 'https://feed.example.test/0')),
		), 'json');
	}

	/**
	 * @param string $tag one of the plugin's cache tags, which end in the page's language
	 * @return string the same tag on a page in another language
	 */
	private function otherLanguageTag($tag)
	{
		return substr($tag, 0, -strlen(e_LAN.'_')).'Klingon_';
	}

	/**
	 * @return void
	 */
	private function clearFeedCaches()
	{
		e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);
		e107::getCache()->clear(NEWSFEED_NEWS_CACHE_TAG);
		e107::getCache()->clear($this->otherLanguageTag(NEWSFEED_LIST_CACHE_TAG));
		e107::getCache()->clear($this->otherLanguageTag(NEWSFEED_NEWS_CACHE_TAG));
	}
}
