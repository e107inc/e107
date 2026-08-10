<?php

/**
 * @see https://github.com/e107inc/e107/issues/5870
 */
class RssFeedResolverTest extends \Codeception\Test\Unit
{
	/**
	 * Stands in for e107::getRssConfig('legacy') plus core's inline comments
	 * feed, so the ordering rules can be exercised without installed plugins.
	 *
	 * @var array
	 */
	private $legacy = array(
		5 => array('plugin' => null, 'url' => 'comments'),
		1 => array('plugin' => 'news', 'url' => 'news'),
		6 => array('plugin' => 'forum', 'url' => 'forumthreads'),
	);

	protected function _before()
	{
		require_once(e_PLUGIN . 'rss_menu/rss_resolver.php');
	}

	public function testTextKeyResolvesDirectly()
	{
		$result = $this->resolver(array($this->row('news', 'news')))->resolve('news', '');

		$this->assertNotFalse($result);
		$this->assertEquals('news', $result['key']);
		$this->assertEquals('news', $result['row']['rss_url']);
	}

	/**
	 * The compatibility-critical ordering. A site whose only row is an old
	 * numeric import must resolve that row, not be redirected to a text row it
	 * may not have. Rewriting the key before the lookup would miss it.
	 */
	public function testLiteralNumericRowWinsOverAlias()
	{
		$rows = array(
			$this->row('6', 'forum'),
			$this->row('forumthreads', 'forum'),
		);

		$result = $this->resolver($rows)->resolve('6', '');

		$this->assertNotFalse($result);
		$this->assertEquals('6', $result['row']['rss_url'], 'the literal row should be served');
		$this->assertEquals('forumthreads', $result['key'], 'the addon should still receive its text key');
	}

	public function testNumericFallsBackToAliasWhenNoLiteralRow()
	{
		$result = $this->resolver(array($this->row('forumthreads', 'forum')))->resolve('6', '');

		$this->assertNotFalse($result);
		$this->assertEquals('forumthreads', $result['row']['rss_url']);
		$this->assertEquals('forumthreads', $result['key']);
	}

	/**
	 * Ownership is checked against the row, so one plugin cannot rewrite the
	 * feed key of a row that belongs to another.
	 */
	public function testNumericNotCanonicalisedWhenAnotherPluginOwnsRow()
	{
		$result = $this->resolver(array($this->row('6', 'someplugin')))->resolve('6', '');

		$this->assertNotFalse($result);
		$this->assertEquals('6', $result['key']);
	}

	public function testPipeSeparatedPathStillIdentifiesOwner()
	{
		$result = $this->resolver(array($this->row('6', 'forum|extra')))->resolve('6', '');

		$this->assertNotFalse($result);
		$this->assertEquals('forumthreads', $result['key']);
	}

	/**
	 * Core serves comments inline rather than through an addon, so its key has
	 * no owning plugin folder to match against.
	 */
	public function testCoreOwnedKeyCanonicalisesRegardlessOfPath()
	{
		$result = $this->resolver(array($this->row('5', '')))->resolve('5', '');

		$this->assertNotFalse($result);
		$this->assertEquals('comments', $result['key']);
	}

	/**
	 * Declaring the map is the opt-in. An undeclared number is passed through
	 * untouched, so third-party feeds behave exactly as they did before.
	 */
	public function testUndeclaredNumericPassesThroughUntouched()
	{
		$result = $this->resolver(array($this->row('99', 'myplugin')))->resolve('99', '');

		$this->assertNotFalse($result);
		$this->assertEquals('99', $result['key']);
	}

	public function testWildcardRowServesAnyTopicId()
	{
		$result = $this->resolver(array($this->row('forumtopic', 'forum', '*')))->resolve('forumtopic', '42');

		$this->assertNotFalse($result);
		$this->assertEquals('*', $result['row']['rss_topicid']);
		$this->assertEquals('forumtopic', $result['key']);
	}

	public function testExactTopicIdPreferredOverWildcard()
	{
		$rows = array(
			$this->row('news', 'news', '*'),
			$this->row('news', 'news', '3'),
		);

		$result = $this->resolver($rows)->resolve('news', '3');

		$this->assertNotFalse($result);
		$this->assertEquals('3', $result['row']['rss_topicid']);
	}

	public function testUnknownKeyResolvesToFalse()
	{
		$this->assertFalse($this->resolver(array($this->row('news', 'news')))->resolve('nope', ''));
	}

	public function testDeclaredNumericWithNoRowAnywhereResolvesToFalse()
	{
		$this->assertFalse($this->resolver(array())->resolve('6', ''));
	}

	/**
	 * @param array $rows fixture rows the fake lookup serves from
	 * @return rss_feed_resolver
	 */
	private function resolver(array $rows)
	{
		$lookup = function ($feedKey, $topicValue) use ($rows) {
			foreach($rows as $row)
			{
				if((string) $row['rss_url'] !== (string) $feedKey)
				{
					continue;
				}

				if($topicValue !== false && (string) $row['rss_topicid'] !== (string) $topicValue)
				{
					continue;
				}

				return $row;
			}

			return false;
		};

		return new rss_feed_resolver($lookup, $this->legacy);
	}

	/**
	 * @param string $url
	 * @param string $path
	 * @param string $topicid
	 * @return array
	 */
	private function row($url, $path, $topicid = '')
	{
		return array(
			'rss_url'     => $url,
			'rss_path'    => $path,
			'rss_topicid' => $topicid,
			'rss_name'    => 'Feed ' . $url,
			'rss_limit'   => 9,
		);
	}
}
