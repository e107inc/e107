<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class MagpieRSSTest extends \Test\Unit
{
	/** @var string[] */
	private $deprecations = array();

	protected function _before()
	{
		require_once(e_HANDLER.'magpie_rss.php');
	}

	/**
	 * Parse a feed while collecting any E_DEPRECATED notices raised by MagpieRSS.
	 *
	 * @param string $source
	 * @return MagpieRSS
	 */
	private function parse($source)
	{
		$this->deprecations = array();
		$deprecations = &$this->deprecations;

		set_error_handler(function ($errno, $errstr) use (&$deprecations)
		{
			$deprecations[] = $errstr;
			return true;
		}, E_DEPRECATED);

		try
		{
			$rss = new MagpieRSS($source);
		}
		finally
		{
			restore_error_handler();
		}

		return $rss;
	}

	public function testAValidFeedParsesWithoutAnError()
	{
		$rss = new MagpieRSS('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0"><channel><title>Channel</title><description>About</description>
			<item><title>Item</title></item></channel></rss>');

		$this->assertSame('', $rss->ERROR);
		$this->assertSame('Channel', $rss->channel['title']);
		$this->assertCount(1, $rss->items);
	}

	public function testAnRssFeedParsesWithoutDeprecations()
	{
		$rss = $this->parse('<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0"><channel><title>Channel</title><description>About</description>
			<item><title>First</title></item><item><title>Second</title></item></channel></rss>');

		$this->assertSame(array(), $this->deprecations);
		$this->assertSame('', $rss->ERROR);
		$this->assertSame(RSS, $rss->feed_type);
		$this->assertSame('Channel', $rss->channel['title']);
		$this->assertCount(2, $rss->items);
		$this->assertSame('Second', $rss->items[1]['title']);
	}

	public function testAnAtomFeedParsesWithoutDeprecations()
	{
		$rss = $this->parse('<?xml version="1.0" encoding="utf-8"?>
			<feed xmlns="http://www.w3.org/2005/Atom" version="1.0"><title>Atom Channel</title>
			<tagline>About</tagline>
			<entry><title>Entry</title><id>1</id><modified>2026-01-01</modified></entry></feed>');

		$this->assertSame(array(), $this->deprecations);
		$this->assertSame('', $rss->ERROR);
		$this->assertSame(ATOM, $rss->feed_type);
		$this->assertSame('Atom Channel', $rss->channel['title']);
		$this->assertCount(1, $rss->items);
	}

	public function testTheDetectedSourceEncodingIsStoredInTheDeclaredProperty()
	{
		$rss = $this->parse("<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
			<rss version=\"2.0\"><channel><title>Caf\xe9</title><description>About</description>
			<item><title>Item</title></item></channel></rss>");

		$this->assertSame(array(), $this->deprecations);
		$this->assertSame('', $rss->ERROR);
		$this->assertSame('Café', $rss->channel['title']);

		// PHP 5 lets libxml2 detect the encoding and never reads the XML prolog.
		if(PHP_MAJOR_VERSION !== 5)
		{
			$this->assertSame('ISO-8859-1', $rss->_source_encoding);
		}
		$this->assertFalse(property_exists($rss, 'source_encoding'));
	}
}
