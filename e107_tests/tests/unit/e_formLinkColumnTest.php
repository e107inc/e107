<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An admin list's url and file columns link to their value.
 */
class e_formLinkColumnTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	/**
	 * @return array values a browser runs as script when the link is followed
	 */
	public function scriptAddresses()
	{
		return array(
			'javascript'           => array('javascript:void(0)'),
			'a leading space'      => array(' javascript:void(0)'),
			'data'                 => array('data:text/html,<b>e107</b>'),
			'an entity for colon'  => array('javascript&#58;void(0)'),
		);
	}

	/**
	 * @dataProvider scriptAddresses
	 * @param string $address
	 */
	public function testAUrlColumnLinkRunsNoScript($address)
	{
		$this->assertRunsNoScript($this->column('url', $address));
	}

	/**
	 * @dataProvider scriptAddresses
	 * @param string $address
	 */
	public function testAFileColumnLinkRunsNoScript($address)
	{
		$this->assertRunsNoScript($this->column('file', $address));
	}

	public function testAUrlColumnLinksToAWebAddress()
	{
		self::assertSame("<a href='https://e107.org/?a=1&amp;b=2' title='https://e107.org/?a=1&amp;b=2'>https://e107.org/?a=1&b=2</a>", $this->column('url', 'https://e107.org/?a=1&b=2'));
	}

	public function testAUrlColumnDecodesAStoredEntityOnceInItsLink()
	{
		self::assertSame('https://e107.org/?a=1&b=2', $this->href($this->column('url', 'https://e107.org/?a=1&amp;b=2')));
	}

	public function testAFileColumnLinksToAFileWhoseStoredNameHoldsAnEntity()
	{
		self::assertSame('<a href="https://e107.org/a&amp;b.zip" title="Direct link to a&amp;b.zip" rel="external">a&amp;b.zip</a>', $this->column('file', 'https://e107.org/a&amp;b.zip'));
	}

	/**
	 * @param string $type
	 * @param string $value
	 * @return string the column as a list prints it
	 */
	private function column($type, $value)
	{
		return e107::getForm()->renderValue('link_001', $value, array('title' => 'Link', 'type' => $type));
	}

	/**
	 * @param string $link
	 * @return string the address a browser follows the link to
	 */
	private function href($link)
	{
		self::assertSame(1, preg_match('/ href=([\'"])(.*?)\1/', $link, $match), 'the column is no link: '.$link);

		return html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	/**
	 * @param string $link
	 */
	private function assertRunsNoScript($link)
	{
		$href = preg_replace('/[\x00-\x20\x7F]/', '', $this->href($link));

		self::assertSame(0, preg_match('/^(javascript|vbscript|data):/i', $href), 'the column links to '.$href.': '.$link);
	}
}
