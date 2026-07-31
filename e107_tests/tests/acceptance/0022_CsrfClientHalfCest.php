<?php

/**
 * The CSRF rule introduced for GHSA-72q5-94gw-prww refuses a POST that carries
 * a cookie and no token. That only holds a site together if the token reaches
 * the client, and the client half arrives in two pieces: e_token_injector puts
 * the token in the document, and e107_web/js/core/all.jquery.js carries the
 * $.ajaxPrefilter that attaches it to an AJAX POST.
 *
 * v2.3.10 shipped documents that were given one piece and not the other, and
 * every write issued from those documents was refused. The suite did not see it
 * because every positive test in it scrapes a token out of one page and pastes
 * it into a request the test itself builds, which proves the server accepts a
 * well-formed request but never that a client produces one.
 *
 * PhpBrowser runs no JavaScript, so what these tests pin down is the delivery
 * of the client half rather than its behaviour. That is the layer that broke.
 * The prefilter's own logic is covered by tests/unit/e_token_injectorTest.php.
 */
class CsrfClientHalfCest
{
	/**
	 * A document handed a token must also be handed the code that sends it.
	 * Either alone is useless.
	 */
	private function seeTokenAndSender(AcceptanceTester $I, $where)
	{
		$source = $I->grabPageSource();

		$I->assertStringContainsString('name="e-token"', $source, $where.' must publish a CSRF token');
		$I->assertStringContainsString('js/core/all.jquery.js', $source, $where.' must load the script that sends the token');
	}

	public function frontPageCarriesBothHalves(AcceptanceTester $I)
	{
		$I->amOnPage('/index.php');
		$this->seeTokenAndSender($I, 'the front page');
	}

	public function adminPageCarriesBothHalves(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);
		$I->amOnPage('/e107_admin/cpage.php');
		$this->seeTokenAndSender($I, 'an admin page');
	}

	/**
	 * The menu manager renders its working area in an iframe, and that document
	 * goes through neither header: it declares USER_AREA, so the admin header is
	 * skipped, and it parses no layout, so the front-end header is skipped too.
	 * It was therefore given a token and no way to send it, and every menu the
	 * manager added, moved or deleted came back "Unauthorized access!" on a
	 * completely fresh browser.
	 *
	 * The controls that issue those writes live in this document, not in the
	 * page that frames it, so it is this document that has to carry the sender.
	 */
	public function menuManagerIframeCarriesBothHalves(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);

		// Follow the manager to whichever layout it frames, rather than naming
		// one here: the layouts on offer belong to the installed theme.
		$I->amOnPage('/e107_admin/menus.php');

		if(!preg_match('/<iframe[^>]+src=[\'"]([^\'"]+)[\'"]/i', $I->grabPageSource(), $iframe))
		{
			throw new \RuntimeException('The menu manager did not render its iframe');
		}

		$I->amOnUrl($iframe[1]);
		$this->seeTokenAndSender($I, 'the menu manager iframe');
	}

	/**
	 * The request shape the menu manager's delete control sends. It is refused
	 * without a token, so the token has to be reachable from that document.
	 */
	public function menuManagerDeleteIsAcceptedWithItsToken(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);

		$token = $this->grabToken($I, '/e107_admin/menus.php');

		$I->sendPostRequest('/e107_admin/menus.php', array(
			'removeid' => 'remove-64-1',
			'area'     => 'remove',
			'mode'     => 'delete',
			'e-token'  => $token,
		));

		$I->dontSee('Unauthorized access!');
	}

	/**
	 * Core asset URLs have to change when e107 does.
	 *
	 * e107's own e107.htaccess sets a one-month Expires and Cache-Control:
	 * public on .js, so a script URL that survives an upgrade unchanged means a
	 * returning visitor goes on running the previous release's copy. That is how
	 * a server which had begun to require a CSRF token ended up talking to a
	 * script with no code to send one, and it would happen again for any future
	 * fix that lands in a .js file.
	 *
	 * Stylesheets always carried a cache-busting query; scripts carried none,
	 * because url() was called with a second argument that is not === true.
	 */
	public function sameOriginAssetUrlsAreCacheBusted(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);

		foreach(array('/index.php', '/e107_admin/admin.php') as $page)
		{
			$I->amOnPage($page);
			$source = $I->grabPageSource();

			preg_match_all('/<script[^>]+src=[\'"]([^\'"]+)[\'"]/i', $source, $scripts);

			$checked = 0;

			foreach($scripts[1] as $src)
			{
				// A CDN copy is versioned in its own path and is not ours to bust.
				if(strpos($src, '//') === 0 || preg_match('~^https?://~i', $src))
				{
					continue;
				}

				$checked++;
				$I->assertStringContainsString('?', $src, 'Same-origin script needs a cache-busting query on '.$page.': '.$src);
			}

			$I->assertGreaterThan(0, $checked, $page.' should serve at least one same-origin script');
		}
	}

	/**
	 * The error page used to publish a token that could never validate.
	 *
	 * error.php defines e_TOKEN_DISABLE, which stopped getFormToken() minting the
	 * session's first token, so it returned md5(null). The injector then stamped
	 * that constant into the meta tag and into every form on a fully themed error
	 * page. A visitor whose first request of a session was a dead link got
	 * refused on the theme's login box, search or comment form, and this lands on
	 * the invalid-token branch, so csrf_enforce does not soften it.
	 */
	public function errorPageServesAUsableToken(AcceptanceTester $I)
	{
		$I->resetCookie('PHPSESSID');

		$token = $this->grabToken($I, '/error.php?404');

		$I->assertNotSame(md5(''), $token, 'the error page must mint a real token, not md5 of nothing');

		$I->sendPostRequest('/index.php', array('e-token' => $token));
		$I->dontSee('Unauthorized access!');
	}

	/**
	 * Drag-and-drop upload runs on Dropzone, which drives its own
	 * XMLHttpRequest, so the $.ajaxPrefilter never sees it and the token has to
	 * be written into the init. Its sibling uploader on the same endpoint
	 * (plupload, in mediaManager.js) was given one; this one was missed, which
	 * left every admin image and media field unable to accept a dropped file.
	 */
	public function dropzoneUploadCarriesAToken(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);
		$I->amOnPage('/e107_admin/newspost.php?mode=main&action=create');

		$source = $I->grabPageSource();

		$I->assertStringContainsString('dropzone({', $source, 'the news form should offer a drop target');
		$I->assertStringContainsString("params: {'e-token'", $source, 'the Dropzone init must carry a token');
	}

	/**
	 * An AJAX reply echoes and exits, so it never reaches the buffer flush where
	 * pages are given their token. Admin list fragments carry a whole form, and
	 * dropping one into the page replaced a tokenised form with an untokenised
	 * one: filter a list, then use Filter or a batch action, and the write was
	 * refused.
	 */
	public function ajaxListFragmentsCarryATokenisedForm(AcceptanceTester $I)
	{
		$this->loginAsAdmin($I);

		foreach(array('/e107_admin/users.php?mode=main&action=list', '/e107_admin/cpage.php?mode=page&action=list') as $page)
		{
			$I->amOnPage($page.'&ajax_used=1');
			$source = $I->grabPageSource();

			$I->assertStringContainsString('<form', $source, $page.' should return a form fragment');
			$I->assertStringContainsString('name="e-token"', $source, $page.' fragment must carry a token');
		}
	}

	private function grabToken(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);
		$source = $I->grabPageSource();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*(?:value|content)=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('Could not locate an e-token on '.$page);
		}

		return $matches[1];
	}

	private function loginAsAdmin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', 'admin');
		$I->fillField('authpass', 'admin');
		$I->click('authsubmit');

		// A failed sign-in re-renders the login page, and that page carries a
		// token, all.jquery.js and a form, so every check below would pass
		// against it without ever reaching what it names. Nothing here is worth
		// anything unless this holds.
		$I->dontSeeElement('input[name=authpass]');
	}
}
