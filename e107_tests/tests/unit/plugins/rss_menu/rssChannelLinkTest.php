<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The news feed names the site by its primary address in every format rss.php serves.
 *
 * @see https://github.com/e107inc/e107/issues/6639
 */
class rssChannelLinkTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const OPEN = '@@SITEURL=';

	const CLOSE = '@@';

	public function testAHostLessSiteUrlGivesEveryFeedSiteurlInstead()
	{
		foreach($this->addressesNamedUnder('/') as $type => $named)
		{
			self::assertNotEmpty(parse_url($named['SITEURL'], PHP_URL_HOST), 'SITEURL in the child is the Site URL it booted with, which must name a host, or a host-less Site URL passes this test unfixed');
			self::assertNotEmpty($named['site'], "feed type $type names the site nowhere");
			self::assertSame(array_fill(0, count($named['site']), $named['SITEURL']), $named['site'], "feed type $type");
		}
	}

	public function testASiteUrlThatNamesAHostIsTheAddressEveryFeedGives()
	{
		foreach($this->addressesNamedUnder('https://example.test/') as $type => $named)
		{
			self::assertNotEmpty($named['site'], "feed type $type names the site nowhere");
			self::assertSame(array_fill(0, count($named['site']), 'https://example.test/'), $named['site'], "feed type $type");
		}
	}

	public function testTheAtomFeedDeclaresTheAtomNamespace()
	{
		$feed = $this->feed(4, 'https://example.test/');

		self::assertSame('http://www.w3.org/2005/Atom', $feed['document']->documentElement->namespaceURI);
	}

	/**
	 * @param string $siteUrl the siteurl preference the feeds are built under
	 * @return array feed type => array('site' => every address the channel names the site by, 'SITEURL' => SITEURL in the child)
	 */
	private function addressesNamedUnder($siteUrl)
	{
		$where = array(
			1 => '/rss/channel/link',
			2 => '/rss/channel/link | /rss/channel/image/link',
			3 => '/rdf:RDF/rss1:channel/@rdf:about | /rdf:RDF/rss1:channel/rss1:link',
			4 => "/*[local-name()='feed']/*[local-name()='id']",
		);

		$named = array();

		foreach($where as $type => $query)
		{
			$feed = $this->feed($type, $siteUrl);
			$xpath = new DOMXPath($feed['document']);
			$xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
			$xpath->registerNamespace('rss1', 'http://purl.org/rss/1.0/');

			$named[$type] = array('site' => array(), 'SITEURL' => $feed['SITEURL']);

			foreach($xpath->query($query) as $node)
			{
				$named[$type]['site'][] = trim($node->nodeValue);
			}
		}

		return $named;
	}

	/**
	 * @param int $type the feed format, as rss.php's type parameter
	 * @param string $siteUrl
	 * @return array array('document' => DOMDocument, 'SITEURL' => string)
	 */
	private function feed($type, $siteUrl)
	{
		$get = array('cat' => 'news', 'type' => (string) $type, 'topic' => '');
		$php = "\$GLOBALS['pref']['siteurl'] = ".var_export($siteUrl, true)."; ";
		$php .= "e107::getConfig()->set('siteurl', ".var_export($siteUrl, true)."); ";
		$php .= "echo '".self::OPEN."', SITEURL, '".self::CLOSE."'; ";
		$php .= "require(e_PLUGIN.'rss_menu/rss.php'); ";

		list($output, $status) = $this->bootPluginInCli('rss_menu', '1.4', $get, $php);

		$printed = implode("\n", $output);
		$matches = array();
		$pattern = '/'.preg_quote(self::OPEN, '/').'(.*?)'.preg_quote(self::CLOSE, '/').'.*?(<\?xml.*<\/(?:rss|rdf:RDF|feed)>)/s';

		self::assertSame(0, $status, "the child did not return:\n".$printed);
		self::assertSame(1, preg_match($pattern, $printed, $matches), "the child served no feed of type $type:\n".$printed);

		$document = new DOMDocument();

		self::assertTrue(@$document->loadXML($matches[2]), "feed type $type is not well-formed XML:\n".$matches[2]);

		return array('document' => $document, 'SITEURL' => $matches[1]);
	}
}
