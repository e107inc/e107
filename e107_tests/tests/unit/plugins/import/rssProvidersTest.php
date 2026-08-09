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
 * The providers that inherit rss_import::saveImages() and the preference that
 * gates it.
 *
 * Both override init() and both render their own fields, so a preference read
 * by the inherited method is reachable only if the subclass renders the field
 * that posts it and takes the value that init() reads. Neither did.
 */
class rssProvidersTest extends \Test\Unit
{

	protected function _before()
	{
		require_once(e_PLUGIN.'import/providers/blogger_import_class.php');
		require_once(e_PLUGIN.'import/providers/livejournal_import_class.php');

		$_POST = array();
	}

	protected function _after()
	{
		$_POST = array();
	}

	/**
	 * @return array
	 */
	public function providerProvider()
	{
		return array(
			'blogger'     => array('blogger_import', 'bloggerUrl'),
			'livejournal' => array('livejournal_import', 'siteUrl'),
		);
	}

	/**
	 * @dataProvider providerProvider
	 * @param string $class
	 * @param string $urlField
	 */
	public function testTheProviderOffersTheImageSwitch($class, $urlField)
	{
		$provider = new $class();
		$html = '';

		foreach ($provider->config() as $row)
		{
			$html .= $row['html'];
		}

		self::assertStringContainsString('rss_saveimages', $html,
			'A provider inheriting saveImages() has to render the field that turns it on');
	}

	/**
	 * @dataProvider providerProvider
	 * @param string $class
	 * @param string $urlField
	 */
	public function testTheProviderReadsTheImageSwitch($class, $urlField)
	{
		$_POST = array($urlField => 'http://blog.example.net', 'rss_saveimages' => 1);

		$provider = new $class();
		$provider->init();

		self::assertTrue($provider->importImages,
			'The preference the inherited saveImages() reads has to be set here too');
	}

	/**
	 * @dataProvider providerProvider
	 * @param string $class
	 * @param string $urlField
	 */
	public function testTheProviderLeavesTheImageSwitchOffWhenNotAsked($class, $urlField)
	{
		$_POST = array($urlField => 'http://blog.example.net');

		$provider = new $class();
		$provider->init();

		self::assertFalse($provider->importImages);
	}

	/**
	 * The field a provider echoes back is its own, and it is escaped.
	 */
	public function testLiveJournalEchoesItsOwnFieldAndEscapesIt()
	{
		$_POST = array('siteUrl' => "http://x.livejournal.com/'><script>", 'bloggerUrl' => 'not this one');

		$provider = new livejournal_import();
		$config = $provider->config();

		self::assertStringNotContainsString('not this one', $config[0]['html'],
			'The field named siteUrl has to echo siteUrl');
		self::assertStringNotContainsString('<script>', $config[0]['html']);
		self::assertStringContainsString('x.livejournal.com', $config[0]['html']);
	}
}
