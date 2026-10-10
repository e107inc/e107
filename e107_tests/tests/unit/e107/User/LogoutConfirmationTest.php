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
class LogoutConfirmationTest extends \Test\Unit
{
	const HOME = 'http://example.test/';

	/** @var array */
	private $parsed;

	/** @var string */
	private $html;

	protected function _before()
	{
		$this->parsed = array();
		$this->html = '';
	}

	public function testAGuestHasNothingToConfirmAndIsSentHome()
	{
		$response = $this->respond(false);

		self::assertTrue($response->isRedirect());
		self::assertSame(self::HOME, $response->getHeaderLine('Location'));
		self::assertSame(303, $response->getStatusCode());
		self::assertSame(array(), $this->parsed);
	}

	public function testAMemberIsAskedAndNothingIsRenderedBeforeTheThemeHeader()
	{
		$response = $this->respond(true);

		self::assertSame(200, $response->getStatusCode());
		self::assertFalse($response->isRedirect());
		self::assertSame('Logout', $response->getPage()->getTitle());
		self::assertSame(array(), $this->parsed);
	}

	public function testTheThemeDrawsTheQuestionInOneLogoutBox()
	{
		$page = $this->respond(true)->getPage();

		self::assertSame('Logout', $page->getCaption());
		self::assertSame('logout', $page->getMode());
	}

	public function testTheFormPostsBackWithTheTokenInTheQueryString()
	{
		$this->render(true, 'abc123');

		self::assertSame("<form id='logout-confirm' method='post' action='/e107/index.php?logout&amp;e-token=abc123'><p>{QUESTION}</p></form>", $this->html);
	}

	public function testAHostileTokenCannotBreakOutOfTheAction()
	{
		$this->render(true, "x' onmouseover='y");

		self::assertStringContainsString("e-token=x%27%20onmouseover%3D%27y'", $this->html);
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
		$parse = function ($markup, array $vars) use ($tp) { return $tp->parseTemplate($markup, true, null, new \e_vars($vars)); };

		$this->renderWith($this->confirmation(true, "<a href='{LOGOUT_CANCEL_URL}'>x</a>", $parse), 'http://example.test/page.php?q={SITENAME}');

		self::assertStringContainsString("href='http://example.test/page.php?q=%7BSITENAME%7D'", $this->html);
	}

	/**
	 * @param bool $isMember
	 * @param string $body the template body
	 * @param callable|null $parse records each call and returns the markup unparsed when null
	 * @return LogoutConfirmation
	 */
	private function confirmation($isMember, $body = '<p>{QUESTION}</p>', $parse = null)
	{
		$parsed = &$this->parsed;
		$record = function ($markup, array $vars) use (&$parsed) { $parsed[] = array($markup, $vars); return $markup; };

		return new LogoutConfirmation(
			$isMember,
			array('caption' => 'Logout', 'body' => $body),
			'/e107/index.php',
			$this->token,
			self::HOME,
			function ($url) { return strpos($url, self::HOME) === 0; },
			$parse ?: $record
		);
	}

	/**
	 * @param bool $isMember
	 * @return \e107\Http\Response
	 */
	private function respond($isMember)
	{
		return $this->confirmation($isMember)->handle(new Request('GET', 'logout'));
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
		$this->renderWith($this->confirmation($isMember), $referrer);
	}

	/**
	 * @param LogoutConfirmation $confirmation
	 * @param string $referrer
	 * @return void
	 */
	private function renderWith(LogoutConfirmation $confirmation, $referrer)
	{
		$request = new Request('GET', 'logout', array('Referer' => $referrer));

		$this->html = $confirmation->handle($request)->getPage()->render();
	}
}
