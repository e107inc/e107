<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Presses the legacy newsletter menu's buttons as the CLI user, whose id is 1.
 *
 * @group plugins
 * @see https://github.com/e107inc/e107/issues/6873
 */
class newsletter_legacy_menuTest extends \Test\Unit
{
	use \Test\NewsletterRows;
	use \Test\HtmlAttributes;

	/** @var int a newsletter whose id is its place in the menu at most */
	private $first;

	/** @var int a newsletter created after an issue of the first, so its id is past its place in the menu */
	private $later;

	protected function _before()
	{
		$this->haveNewsletterTable();
	}

	protected function _after()
	{
		$this->dropNewsletterRows();
	}

	public function testSubscribingToALaterNewsletterKeepsItsSubscribersAndAddsTheUser()
	{
		$this->seed(array(4), array(3, 12));

		$page = $this->press('nlSubscribe_'.$this->later);

		self::assertSame(chr(1).'3'.chr(1).'12'.chr(1).'1', $this->storedSubscribers($this->later), "the list was not kept:\n".$page);
		self::assertSame(chr(1).'4', $this->storedSubscribers($this->first));
		self::assertStringContainsString("name='nlUnsubscribe_".$this->later."'", $page, 'the user is not shown as subscribed');
	}

	public function testLeavingALaterNewsletterKeepsTheIdsThatStartWithTheUsersId()
	{
		$this->seed(array(4), array(3, 1, 12, 15));

		$page = $this->press('nlUnsubscribe_'.$this->later);

		self::assertSame(chr(1).'3'.chr(1).'12'.chr(1).'15', $this->storedSubscribers($this->later), "the list was not kept:\n".$page);
		self::assertSame(chr(1).'4', $this->storedSubscribers($this->first));
		self::assertStringContainsString("name='nlSubscribe_".$this->later."'", $page, 'the user is still shown as subscribed');
	}

	public function testSubscribingTwiceKeepsOneEntry()
	{
		$this->seed(array(4), array(1, 12));

		$page = $this->press('nlSubscribe_'.$this->later);

		self::assertSame(chr(1).'1'.chr(1).'12', $this->storedSubscribers($this->later), "the list was not kept:\n".$page);
		self::assertStringContainsString("name='nlUnsubscribe_".$this->later."'", $page, 'the user is not shown as subscribed');
	}

	public function testBothButtonsAskBeforeTheyAreSent()
	{
		$this->seed(array(1), array(3));

		$page = $this->press('');

		self::assertMatchesRegularExpression('#^return jsconfirm\("[^"]+"\)\s*$#', $this->onclick($page, 'nlUnsubscribe_'.$this->first));
		self::assertMatchesRegularExpression('#^return jsconfirm\("[^"]+"\)\s*$#', $this->onclick($page, 'nlSubscribe_'.$this->later));
	}

	/**
	 * @param int[] $first the first newsletter's subscribers
	 * @param int[] $later the later newsletter's subscribers
	 * @return void
	 */
	private function seed(array $first, array $later)
	{
		$this->first = $this->haveNewsletter(0, $first);
		$this->haveNewsletter($this->first, array());
		$this->later = $this->haveNewsletter(0, $later);
	}

	/**
	 * @param string $page
	 * @param string $name
	 * @return string the onclick of the button named $name, as a browser reads it
	 */
	private function onclick($page, $name)
	{
		$onclick = $this->attributesOf($page, "//input[@name='".$name."']/@onclick");
		self::assertCount(1, $onclick, 'no single button named '.$name.":\n".$page);

		return $onclick[0];
	}

	/**
	 * Renders the menu with $button posted, the page standing in for the theme's tablestyle().
	 *
	 * @param string $button the posted button's name; empty to post nothing
	 * @return string the menu's body
	 */
	private function press($button)
	{
		$php = "function tablestyle(\$caption, \$text) { echo \$text; } ";
		$php .= self::newsletterInstalled();
		$php .= "\$_POST = ".var_export($button === '' ? array() : array($button => 'pressed'), true)."; ";
		$php .= "echo e107::getMenu()->renderMenu('newsletter', 'newsletter_legacy_menu', '', true); ";

		return $this->outputOfBootedCli($php);
	}
}
