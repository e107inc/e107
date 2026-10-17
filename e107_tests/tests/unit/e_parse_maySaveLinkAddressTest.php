<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * What {@see e_parse::maySaveLinkAddress()} lets a saver store, with no one allowed to post script unless a case grants it.
 */
class e_parse_maySaveLinkAddressTest extends \Test\Unit
{
	use \Test\ScriptAccess;

	/** @var e_parse */
	private $tp;

	protected function _before()
	{
		$this->tp = e107::getParser();
		$this->holdScriptAccess();
	}

	protected function _after()
	{
		$this->restoreScriptAccess();
	}

	/**
	 * @return array addresses a browser can run as script once e107 has stored and printed them
	 */
	public function scriptAddresses()
	{
		return array(
			'javascript'                             => array('javascript:void(0)'),
			'vbscript'                               => array('VBScript:msgbox(1)'),
			'data'                                   => array('data:text/html,<b>e107</b>'),
			'a tab inside the scheme'                => array("java\tscript:void(0)"),
			'a named entity for a tab'               => array('java&Tab;script:void(0)'),
			'a named entity for a line break'        => array('java&NewLine;script:void(0)'),
			'a named entity colon'                   => array('javascript&colon;void(0)'),
			'a padded reference'                     => array('&#0000106;avascript:void(0)'),
			'a padded hex reference'                 => array('&#x0006A;avascript:void(0)'),
			'an upper case hex reference'            => array('&#X6A;avascript:void(0)'),
			'a reference with no semicolon'          => array('&#106avascript:void(0)'),
			'an encoded reference'                   => array('&amp;#106;avascript:void(0)'),
			'a tag inside the scheme'                => array('java<x></x>script:void(0)'),
			'a tag before the scheme'                => array('<x></x>javascript:void(0)'),
			'a reference to a control character'     => array('&#1;javascript:void(0)'),
			'a reference to nothing'                 => array('&#0;javascript:void(0)'),
			'a hex reference to a control character' => array('&#x1f;javascript:void(0)'),
			'a reference to delete'                  => array('&#127;javascript:void(0)'),
			'a reference past the last code point'   => array('&#99999999999;javascript:void(0)'),
			'a carriage return reference'            => array('java&#13;script:void(0)'),
			'a hex carriage return reference'        => array('java&#x0d;script:void(0)'),
			'a carriage return reference, unended'   => array('java&#13script:void(0)'),
			'an empty bbcode pair inside the scheme' => array('java[p][/p]script://%0Avoid(0)'),
			'an empty bbcode pair before the scheme' => array('[h][/h]javascript://%0Avoid(0)'),
			'behind an empty constant'               => array('{e_HANDLER}javascript:void(0)'),
			'a shortcode'                            => array('{USER_EXTENDED=homepage.value.1}'),
			'a shortcode inside the scheme'          => array('java{SITENAME}script:void(0)'),
			'an encoded shortcode'                   => array('&#123;SITEURL&#125;'),
		);
	}

	/**
	 * @return array addresses that run no script, shortcodes after the scheme included
	 */
	public function otherAddresses()
	{
		return array(
			'a page'                       => array('news.php'),
			'a constant'                   => array('{e_PLUGIN}forum/forum.php'),
			'a constant before a page'     => array('{e_BASE}news.php'),
			'a web address'                => array('https://e107.org/'),
			'mail'                         => array('mailto:admin@example.com'),
			'a phone number'               => array('tel:+15550100'),
			'the word in a query'          => array('news.php?javascript:void(0)'),
			'the word in a path'           => array('/javascript:void(0)'),
			'an encoded colon'             => array('javascript%3Avoid(0)'),
			'a shortcode after the scheme' => array('news.php?item={USERID}'),
			'a shortcode in a path'        => array('/{LANGUAGE}/page.php'),
			'the logout token'             => array('index.php?logout&e-token={E_TOKEN}'),
			'nothing'                      => array(''),
		);
	}

	/**
	 * @return array addresses that run script, each as the save of an author who may post script stores it and as a save of it stores it again
	 */
	public function storedScriptAddresses()
	{
		return array(
			'javascript'               => array('javascript:void(0)'),
			'vbscript'                 => array('VBScript:msgbox(1)'),
			'with two slashes'         => array('javascript://%0Avoid(0)'),
			'with quotes'              => array('javascript:alert(&#039;e107&#039;)'),
			'behind an empty constant' => array('{e_HANDLER}javascript:void(0)'),
			'a shortcode'              => array('{USER_EXTENDED=homepage.value.1}'),
		);
	}

	/**
	 * @return array stored addresses that run no script, which a save outside the post_script class stores as one that does
	 */
	public function storedAddressesASaveTurnsIntoScript()
	{
		return array(
			'an empty bbcode pair inside the scheme' => array('java[p][/p]script://%0Avoid(0)'),
			'an empty bbcode pair before the scheme' => array('[h][/h]javascript://%0Avoid(0)'),
			'an empty bbcode pair before the colon'  => array('javascript[p][/p]://%0Avoid(0)'),
		);
	}

	/**
	 * @return array addresses that run no script as posted or as saved once, but that later saves of the form turn into javascript://%0Avoid(0)
	 */
	public function addressesSavesTurnIntoScript()
	{
		return array(
			'nested empty bbcode pairs inside the scheme'  => array('java[[p][/p]p][/[p][/p]p]script://%0Avoid(0)'),
			'nested empty heading pairs inside the scheme' => array('java[[h][/h]h][/[h][/h]h]script://%0Avoid(0)'),
			'three nested empty bbcode pairs'              => array('java[[[p][/p]p][/[p][/p]p]p][/[[p][/p]p][/[p][/p]p]p]script://%0Avoid(0)'),
			'a tag inside an empty bbcode pair'            => array('java[<x></x>p][/p]script://%0Avoid(0)'),
		);
	}

	/**
	 * @dataProvider scriptAddresses
	 * @param string $address
	 */
	public function testAnAddressThatCanRunScriptNeedsThePostScriptClass($address)
	{
		self::assertFalse($this->tp->maySaveLinkAddress($address), 'outside the class');
		self::assertFalse($this->tp->maySaveLinkAddress($address, 'news.php'), 'outside the class, in place of another address');

		$this->tp->setScriptAccess(e_UC_PUBLIC);
		self::assertTrue($this->tp->maySaveLinkAddress($address), 'inside the class');
	}

	/**
	 * @dataProvider otherAddresses
	 * @param string $address
	 */
	public function testAnAddressThatRunsNoScriptMayBeSavedOutsideThePostScriptClass($address)
	{
		self::assertTrue($this->tp->maySaveLinkAddress($address));
	}

	/**
	 * @dataProvider storedScriptAddresses
	 * @param string $address
	 */
	public function testTheStoredAddressSentBackUnchangedMayBeSavedOutsideThePostScriptClass($address)
	{
		self::assertTrue($this->tp->maySaveLinkAddress($address, $address));
	}

	/**
	 * @dataProvider storedAddressesASaveTurnsIntoScript
	 * @param string $address
	 */
	public function testAStoredAddressSentBackUnchangedIsJudgedAsTheSaveWouldStoreIt($address)
	{
		self::assertFalse($this->tp->maySaveLinkAddress($address, $address), 'outside the class');

		$this->tp->setScriptAccess(e_UC_PUBLIC);
		self::assertTrue($this->tp->maySaveLinkAddress($address, $address), 'inside the class');
	}

	public function testAStoredAddressSentBackUnchangedIsJudgedAsASaveWithoutThePostHtmlClassWouldStoreIt()
	{
		$restore = $this->withCorePrefs(array('post_html' => e_UC_NOBODY));

		try
		{
			self::assertFalse($this->tp->maySaveLinkAddress('java<b></b>script://%0Avoid(0)', 'java<b></b>script://%0Avoid(0)'));
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * @dataProvider addressesSavesTurnIntoScript
	 * @param string $address
	 */
	public function testNoSaveOfTheFormSentBackUnchangedStoresAnAddressThatRunsScript($address)
	{
		$stored = null;

		for ($save = 0; $save < 5 && $this->tp->maySaveLinkAddress($address, $stored); $save++)
		{
			$stored = $address = $this->tp->toDB($address);
		}

		self::assertNotSame('javascript://%0Avoid(0)', $stored, 'saved '.$save.' times');
	}

	public function testAStoredAddressStillCountsAsUnchangedWithItsLineBreaksSentAsCrlf()
	{
		self::assertTrue($this->tp->maySaveLinkAddress("javascript:void(0)\r\n//e107", "javascript:void(0)\n//e107"));
	}

	public function testAnAddressInPlaceOfNothingStoredIsJudged()
	{
		self::assertFalse($this->tp->maySaveLinkAddress('javascript:void(0)', ''));
	}

	public function testEveryAddressOfAListIsJudgedAgainstTheOneStoredUnderItsKey()
	{
		$stored = array('home' => 'javascript:void(0)', 'news' => 'news.php');

		self::assertTrue($this->tp->maySaveLinkAddress(array('home' => 'javascript:void(0)', 'news' => 'news.php?item=1'), $stored), 'a changed address that runs no script');
		self::assertFalse($this->tp->maySaveLinkAddress(array('home' => 'news.php', 'news' => 'javascript:void(0)'), $stored), 'a stored address moved to another key');
		self::assertFalse($this->tp->maySaveLinkAddress(array('home' => 'news.php', 'extra' => array('javascript:void(0)')), $stored), 'a nested address');
		self::assertFalse($this->tp->maySaveLinkAddress(array('javascript:void(0)'), 'javascript:void(0)'), 'a list in place of the stored address');
	}
}
