<?php

/**
 * Issue #5694: a visitor who hits a class-restricted page while logged out should
 * be returned to that page after logging in, instead of being dumped on the front
 * page. The destination travels as a signed (JWT) token in a hidden field + cookie.
 */
class LoginDestinationCest
{
	/** @var int */
	private $pageId;

	public function _before(AcceptanceTester $I)
	{
		// A custom page only logged-in members (e_UC_MEMBER = 253) may view.
		$this->pageId = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Members Only Destination Page',
			'page_text'      => 'Members-only destination content',
			'page_class'     => '253',
			'page_author'    => 1,
			'page_datestamp' => time(),
		));
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function returnsToRequestedPageAfterLogin(AcceptanceTester $I)
	{
		$I->wantTo('Return to a class-restricted page after logging in (issue #5694)');

		$pageUrl = '/page.php?id=' . $this->pageId;

		// As a logged-out guest, the page is restricted and offers a way in.
		$I->amOnPage($pageUrl);
		$I->see('Log in to view this page');
		$I->dontSee('Members-only destination content');

		// The login form carries the signed destination as a hidden field.
		$I->amOnPage('/login.php');
		$I->seeElement('input', array('name' => '__logindest'));

		// Log in (the Main Admin is also a member, so class 253 admits them).
		$I->fillField('username', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('userpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('userlogin');

		// We should land back on the originally requested page, now authorised.
		$I->seeInCurrentUrl($pageUrl);
		$I->see('Members-only destination content');
		$I->dontSee('Log in to view this page');
	}
}
