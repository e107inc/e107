<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\User;

use e107\Http\Request;

/**
 * The question a member is asked when a logout link carried no e-token.
 */
class LogoutConfirmationTest extends \Codeception\Test\Unit
{
	const HOME = 'http://example.test/';

	/** @var array */
	private $parsed;

	/** @var array */
	private $framed;

	protected function _before()
	{
		$this->parsed = array();
		$this->framed = array();
	}

	public function testAGuestHasNothingToConfirmAndIsSentHome()
	{
		$response = $this->confirmation(false)->handle(new Request('GET', 'logout'));

		self::assertSame(self::HOME, $response->getLocation());
		self::assertSame(303, $response->getStatusCode());
		self::assertSame(array(), $this->parsed);
	}

	public function testAMemberIsAskedAndNothingIsRenderedBeforeTheThemeHeader()
	{
		$response = $this->confirmation(true)->handle(new Request('GET', 'logout'));

		self::assertSame(200, $response->getStatusCode());
		self::assertNull($response->getLocation());
		self::assertSame('Logout', $response->getPage()->getTitle());
		self::assertSame(array(), $this->parsed);
		self::assertSame(array(), $this->framed);
	}

	public function testTheFormPostsBackWithTheTokenInTheQueryString()
	{
		$this->render(true, 'abc123');

		self::assertSame('Logout', $this->framed[0][0]);
		self::assertSame('logout', $this->framed[0][2]);
		self::assertStringContainsString("<form id='logout-confirm' method='post' action='/e107/index.php?logout&amp;e-token=abc123'>", $this->framed[0][1]);
	}

	public function testAHostileTokenCannotBreakOutOfTheAction()
	{
		$this->render(true, "x' onmouseover='y");

		self::assertStringContainsString("e-token=x%27%20onmouseover%3D%27y'", $this->framed[0][1]);
	}

	public function testOnlyTheTemplateBodyIsParsed()
	{
		$this->render(true);

		self::assertSame('<p>{QUESTION}</p>', $this->parsed[0][0]);
	}

	public function testCancelReturnsToAnOnSiteReferrer()
	{
		$this->render(true, 't', 'http://example.test/news.php?item=1&x=2');

		self::assertSame(array('LOGOUT_CANCEL_URL' => 'http://example.test/news.php?item=1&amp;x=2'), $this->parsed[0][1]);
	}

	public function testCancelGoesHomeForAReferrerOffSite()
	{
		$this->render(true, 't', 'http://elsewhere.test/');

		self::assertSame(array('LOGOUT_CANCEL_URL' => self::HOME), $this->parsed[0][1]);
	}

	public function testCancelGoesHomeWhenThereIsNoReferrer()
	{
		$this->render(true, 't', '');

		self::assertSame(array('LOGOUT_CANCEL_URL' => self::HOME), $this->parsed[0][1]);
	}

	public function testCancelNeverLeadsBackToALogoutLink()
	{
		$this->render(true, 't', 'http://example.test/index.php?logout');

		self::assertSame(array('LOGOUT_CANCEL_URL' => self::HOME), $this->parsed[0][1]);
	}

	public function testNoBraceFromAReferrerReachesThePage()
	{
		$this->render(true, 't', 'http://example.test/news.php?{---CAPTION---}');

		self::assertSame(array('LOGOUT_CANCEL_URL' => 'http://example.test/news.php?%7B---CAPTION---%7D'), $this->parsed[0][1]);
	}

	public function testAReferrerIsNeverParsedAsShortcodes()
	{
		$tp = \e107::getParser();
		$confirmation = new LogoutConfirmation(
			true,
			array('caption' => 'Logout', 'body' => "<a href='{LOGOUT_CANCEL_URL}'>x</a>"),
			'/e107/index.php',
			't',
			self::HOME,
			function () { return true; },
			function ($markup, array $vars) use ($tp) { return $tp->parseTemplate($markup, true, null, new \e_vars($vars)); },
			function ($caption, $text) { return $text; }
		);

		$request = new Request('GET', 'logout', array('Referer' => 'http://example.test/page.php?q={SITENAME}'));
		$html = $confirmation->handle($request)->getPage()->render();

		self::assertStringContainsString("href='http://example.test/page.php?q=%7BSITENAME%7D'", $html);
	}

	/**
	 * @param bool $isMember
	 * @return LogoutConfirmation
	 */
	private function confirmation($isMember)
	{
		$parsed = &$this->parsed;
		$framed = &$this->framed;

		return new LogoutConfirmation(
			$isMember,
			array('caption' => 'Logout', 'body' => '<p>{QUESTION}</p>'),
			'/e107/index.php',
			$this->token,
			self::HOME,
			function ($url) { return strpos($url, self::HOME) === 0; },
			function ($markup, array $vars) use (&$parsed) { $parsed[] = array($markup, $vars); return $markup; },
			function ($caption, $text, $mode) use (&$framed) { $framed[] = array($caption, $text, $mode); return $text; }
		);
	}

	/** @var string */
	private $token = 't';

	/**
	 * @param bool $isMember
	 * @param string $token
	 * @param string $referrer
	 * @return void
	 */
	private function render($isMember, $token = 't', $referrer = '')
	{
		$this->token = $token;
		$request = new Request('GET', 'logout', array('Referer' => $referrer));

		$this->confirmation($isMember)->handle($request)->getPage()->render();
	}
}
