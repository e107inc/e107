<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * A dashboard feed's kept copy: what the dashboard shows, when it asks for another, and what asking leaves behind.
 */
class DashboardFeedTest extends \Test\Unit
{
	/** Three hours and a minute, in seconds: a copy past its lifetime. */
	const EXPIRED = 10860;

	/** Fifteen minutes, in seconds: the wait after a fetch that brought no copy. */
	const RETRY = 900;

	/** Seven days, in seconds: the age past which a copy is not shown. */
	const MAXIMUM_AGE = 604800;

	/** @var \ecache */
	private $cache;

	protected function _before()
	{
		require_once(e_HANDLER.'xml_class.php');

		if(!defined('ADMINFEED'))
		{
			define('ADMINFEED', 'https://feed.invalid/adminfeed');
		}

		if(!defined('ADDONFEED'))
		{
			define('ADDONFEED', 'https://feed.invalid/feed/');
		}

		$this->cache = new \ecache();
		$this->cache->clear('Infopanel_', true);
	}

	protected function _after()
	{
		$this->cache->clear('Infopanel_', true);
	}

	/**
	 * @param string $html
	 * @param int $fetched seconds since the fetch that brought the copy
	 * @param int $checked seconds since the last fetch
	 * @return void
	 */
	private function keep($html, $fetched, $checked)
	{
		$now = time();
		$this->cache->set('Infopanel_core', json_encode(array('html' => $html, 'fetched' => $now - $fetched, 'checked' => $now - $checked)), true, false, true);
	}

	/**
	 * @param int $seconds how far to move the kept times into the past, as if that long had gone by
	 * @return void
	 */
	private function rewind($seconds)
	{
		$entry = json_decode($this->cache->retrieve('Infopanel_core', false, true, true), true);
		$entry['fetched'] -= $seconds;
		$entry['checked'] -= $seconds;
		$this->cache->set('Infopanel_core', json_encode($entry), true, false, true);
	}

	/**
	 * @param callable $answer stands in for xmlClass::getRemoteFile()
	 * @return \xmlClass
	 */
	private function xml($answer)
	{
		return $this->construct('xmlClass', array(), array('getRemoteFile' => $answer));
	}

	/**
	 * @param string $type
	 * @param \xmlClass|null $xml
	 * @return DashboardFeed
	 */
	private function feed($type = 'core', $xml = null)
	{
		return new DashboardFeed($type, $this->cache, ($xml === null) ? $this->make('xmlClass') : $xml, \e107::getParser());
	}

	/**
	 * @param string ...$items
	 * @return string an RSS feed of the given items
	 */
	private static function rss(...$items)
	{
		return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>-</title>'.implode('', $items).'</channel></rss>';
	}

	/**
	 * @param string $title
	 * @return string an RSS item with every field filled in
	 */
	private static function item($title)
	{
		return '<item><title>'.$title.'</title><link>https://example.com/</link><pubDate>Fri, 09 Oct 2026 00:00:00 +0000</pubDate><description>-</description></item>';
	}

	/**
	 * @param string $html
	 * @return void
	 */
	private function assertKeptAndNotDue($html)
	{
		$feed = $this->feed();

		$this->assertSame($html, $feed->copy());
		$this->assertFalse($feed->isDue());
	}

	/**
	 * @param string $html
	 * @return void
	 */
	private function assertKeptAndDue($html)
	{
		$feed = $this->feed();

		$this->assertSame($html, $feed->copy());
		$this->assertTrue($feed->isDue());
	}

	/**
	 * @param \xmlClass $xml
	 * @return string
	 */
	private function renew($xml)
	{
		return $this->feed('core', $xml)->renew();
	}

	/**
	 * @return \xmlClass one whose fetch fails
	 */
	private function failing()
	{
		return $this->xml(function ()
		{
			return false;
		});
	}

	public function testAnUnknownFeedIsRefused()
	{
		$this->expectException(\InvalidArgumentException::class);

		$this->feed('news');
	}

	public function testNothingKeptIsDueAndShowsNothing()
	{
		$this->assertKeptAndDue('');
	}

	public function testACopyIsShownAndNotDueWithinThreeHours()
	{
		$this->keep('<p>kept</p>', 10740, 10740);

		$this->assertKeptAndNotDue('<p>kept</p>');
	}

	public function testACopyIsStillShownOnceDue()
	{
		$this->keep('<p>kept</p>', self::EXPIRED, self::EXPIRED);

		$this->assertKeptAndDue('<p>kept</p>');
	}

	/**
	 * Such as the bare HTML the add-on feeds kept before, or an entry without the time of the fetch that brought its copy.
	 */
	public function testAnEntryInAnotherShapeCountsAsNothingKept()
	{
		foreach(array('<div>composed before</div>', json_encode(array('html' => '<p>kept</p>', 'checked' => time()))) as $stored)
		{
			$this->cache->set('Infopanel_core', $stored, true, false, true);

			$this->assertKeptAndDue('');
		}
	}

	/**
	 * As when the server's clock is set back after a fetch.
	 */
	public function testACopyFetchedAfterNowIsShownAndDue()
	{
		$this->keep('<p>kept</p>', -86400, -86400);

		$this->assertKeptAndDue('<p>kept</p>');
	}

	/**
	 * As when the server's clock is set back after a fetch and the next fetch fails.
	 */
	public function testAFailedFetchAfterTheClockWentBackIsTriedAgainAfterFifteenMinutes()
	{
		$this->keep('<p>kept</p>', -86400, -86400);

		$this->assertSame('<p>kept</p>', $this->renew($this->failing()));
		$this->assertKeptAndNotDue('<p>kept</p>');

		$this->rewind(self::RETRY);
		$this->assertKeptAndDue('<p>kept</p>');

		$this->rewind(self::MAXIMUM_AGE);
		$this->assertKeptAndDue('');
	}

	public function testAFreshCopyIsRenewedWithoutAFetch()
	{
		$this->keep('<p>kept</p>', 60, 60);

		$this->assertSame('<p>kept</p>', $this->renew($this->make('xmlClass', array('getRemoteFile' => \Codeception\Stub\Expected::never()))));
	}

	public function testAMissingCopyIsFetchedAndKept()
	{
		$html = $this->renew($this->xml(function ()
		{
			return self::rss(self::item('First'), self::item('Second'));
		}));

		$this->assertStringContainsString('>First</a>', $html);
		$this->assertStringContainsString('>Second</a>', $html);
		$this->assertKeptAndNotDue($html);
	}

	/**
	 * @return array the feed's name, then the address it is fetched from
	 */
	public function addresses()
	{
		return array(
			'core'   => array('core', 'ADMINFEED', ''),
			'plugin' => array('plugin', 'ADDONFEED', '?limit=3&type=plugin'),
			'theme'  => array('theme', 'ADDONFEED', '?limit=3&type=theme'),
		);
	}

	/**
	 * @dataProvider addresses
	 * @param string $type
	 * @param string $constant
	 * @param string $query
	 */
	public function testEachFeedIsFetchedFromItsOwnAddress($type, $constant, $query)
	{
		$asked = array();

		$this->feed($type, $this->xml(function ($address) use (&$asked)
		{
			$asked[] = $address;

			return false;
		}))->renew();

		$this->assertSame(array(constant($constant).$query), $asked);
	}

	public function testAFeedOfOneItemIsShown()
	{
		$html = $this->renew($this->xml(function ()
		{
			return self::rss(self::item('Only'));
		}));

		$this->assertStringContainsString('>Only</a>', $html);
		$this->assertSame(1, substr_count($html, 'class="media"'));
	}

	/**
	 * An item's title is optional in RSS.
	 */
	public function testAFeedOfOneUntitledItemIsShown()
	{
		$html = $this->renew($this->xml(function ()
		{
			return self::rss('<item><link>https://example.com/untitled</link><description>Untitled</description></item>');
		}));

		$this->assertStringContainsString('href="https://example.com/untitled"', $html);
		$this->assertStringContainsString('Untitled', $html);
		$this->assertSame(1, substr_count($html, 'class="media"'));
	}

	/**
	 * @return array an item's field, then the same field left empty
	 */
	public function emptyNewsFields()
	{
		return array(
			'title'       => array('<title>Item</title>', '<title></title>'),
			'pubDate'     => array('<pubDate>Fri, 09 Oct 2026 00:00:00 +0000</pubDate>', '<pubDate/>'),
			'description' => array('<description>-</description>', '<description/>'),
			'link'        => array('<link>https://example.com/</link>', '<link/>'),
		);
	}

	/**
	 * The parser gives an empty element as an empty array rather than as text.
	 *
	 * @dataProvider emptyNewsFields
	 * @param string $filled
	 * @param string $empty
	 */
	public function testAnEmptyFieldOfANewsItemIsShownEmpty($filled, $empty)
	{
		$html = $this->renew($this->xml(function () use ($filled, $empty)
		{
			return self::rss(str_replace($filled, $empty, self::item('Item')));
		}));

		$this->assertSame(1, substr_count($html, 'class="media"'));
		$this->assertStringNotContainsString('Array', $html);
	}

	public function testAnUntitledSecondItemIsShownAfterTheFirst()
	{
		$html = $this->renew($this->xml(function ()
		{
			return self::rss(self::item('First'), str_replace('<title>Second</title>', '<title/>', self::item('Second')));
		}));

		$this->assertStringContainsString('>First</a>', $html);
		$this->assertSame(2, substr_count($html, 'class="media"'));
		$this->assertStringNotContainsString('Array', $html);
	}

	public function testAnEmptyFieldOfAnAddonIsShownEmpty()
	{
		$html = $this->feed('plugin', $this->xml(function ()
		{
			$plugin = '<plugin name="Stub" version="1.0" author="-" icon="https://example.com/i.png"><description/></plugin>';

			return '<?xml version="1.0" encoding="UTF-8"?><e107>'.$plugin.$plugin.'</e107>';
		}))->renew();

		$this->assertSame(2, substr_count($html, 'class="media"'));
		$this->assertStringNotContainsString('Array', $html);
	}

	public function testAFailedFetchKeepsTheExpiredCopyAndIsTriedAgainAfterFifteenMinutes()
	{
		$this->keep('<p>kept</p>', self::EXPIRED, self::EXPIRED);

		$this->assertSame('<p>kept</p>', $this->renew($this->failing()));
		$this->assertKeptAndNotDue('<p>kept</p>');

		$this->rewind(self::RETRY - 60);
		$this->assertKeptAndNotDue('<p>kept</p>');

		$this->rewind(60);
		$this->assertKeptAndDue('<p>kept</p>');
	}

	public function testAnAnswerWithNoItemsKeepsTheExpiredCopyAndIsTriedAgainAfterFifteenMinutes()
	{
		$this->keep('<p>kept</p>', self::EXPIRED, self::EXPIRED);

		$this->assertSame('<p>kept</p>', $this->renew($this->xml(function ()
		{
			return self::rss();
		})));
		$this->assertKeptAndNotDue('<p>kept</p>');

		$this->rewind(self::RETRY);
		$this->assertKeptAndDue('<p>kept</p>');
	}

	public function testAFailedFirstFetchKeepsNothingAndIsTriedAgainAfterFifteenMinutes()
	{
		$this->assertSame('', $this->renew($this->failing()));
		$this->assertKeptAndNotDue('');

		$this->rewind(self::RETRY - 60);
		$this->assertKeptAndNotDue('');

		$this->rewind(60);
		$this->assertKeptAndDue('');
	}

	public function testAFetchThatDiesStillBacksOff()
	{
		$this->keep('<p>kept</p>', self::EXPIRED, self::EXPIRED);

		try
		{
			$this->renew($this->xml(function ()
			{
				throw new \RuntimeException('died during the fetch');
			}));
			$this->fail('the fetch did not die');
		}
		catch(\RuntimeException $e)
		{
			$this->assertSame('died during the fetch', $e->getMessage());
		}

		$this->assertKeptAndNotDue('<p>kept</p>');
	}

	public function testARequestArrivingDuringAFetchAnswersWithTheKeptCopy()
	{
		$this->keep('<p>kept</p>', self::EXPIRED, self::EXPIRED);
		$meanwhile = null;
		$idle = $this->make('xmlClass', array('getRemoteFile' => \Codeception\Stub\Expected::never()));

		$html = $this->renew($this->xml(function () use ($idle, &$meanwhile)
		{
			$meanwhile = $this->feed('core', $idle)->renew();

			return self::rss(self::item('Renewed'));
		}));

		$this->assertSame('<p>kept</p>', $meanwhile);
		$this->assertStringContainsString('>Renewed</a>', $html);
		$this->assertKeptAndNotDue($html);
	}

	/**
	 * Past that age it is shown as no copy would be, however recently a fetch failed to replace it.
	 */
	public function testACopyIsNotShownOnceItsFetchIsMoreThanSevenDaysOld()
	{
		$this->keep('<p>kept</p>', self::MAXIMUM_AGE - 60, 60);
		$this->assertKeptAndNotDue('<p>kept</p>');

		$this->keep('<p>kept</p>', self::MAXIMUM_AGE + 60, 60);
		$this->assertKeptAndNotDue('');

		$this->keep('<p>kept</p>', self::MAXIMUM_AGE + 60, self::RETRY);
		$this->assertKeptAndDue('');
	}

	/**
	 * The link's text is in the language of whoever is looking, so it is added on each request rather than kept.
	 */
	public function testAnAddonCopyGainsItsLinkToMoreWhenShown()
	{
		$xml = $this->xml(function ()
		{
			$plugin = '<plugin name="Stub" version="1.0" author="-" icon="https://example.com/i.png" thumbnail="https://example.com/i.png"><description>-</description></plugin>';

			return '<?xml version="1.0" encoding="UTF-8"?><e107>'.$plugin.$plugin.'</e107>';
		});

		$html = $this->feed('plugin', $xml)->renew();
		$entry = json_decode($this->cache->retrieve('Infopanel_plugin', false, true, true), true);

		$this->assertStringStartsWith($entry['html'], $html);
		$this->assertStringNotContainsString(LAN_MORE, $entry['html']);
		$this->assertStringEndsWith("<div class='right'><a href='".e_ADMIN_ABS."plugin.php?mode=online'>".LAN_MORE."</a></div>", $html);
	}
}
