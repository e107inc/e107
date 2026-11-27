<?php

/**
 * Which `has-error` marks core makes on a tab or panel out of sight reach the Save guard in e107_web/js/core/all.jquery.js (#6557).
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class AdminSaveHasErrorGuardCest
{
	const NEWS_EDIT = '/e107_admin/newspost.php?mode=main&action=edit&id=';
	const NEWS_SAVE = '#etrigger-submit';
	const ADVANCED_TAB = "a[href='#tab-2']";
	const TITLE = 'input#news-title';
	const META_TITLE = 'input#news-meta-title';
	const META_DESCRIPTION = 'textarea#news-meta-description';

	const PREFS = '/e107_admin/prefs.php';
	const SITE_URL = 'input#siteurl';
	const EMAIL_PANEL = "a.e-nav-pane[href='#core-prefs-email']";
	const EMAIL_SAVE = '#updateprefs-email';

	/** With " | e107" added, past seo_title_limit's default of 100, so the meta title is marked on load. */
	const LONG_TITLE = 'A news title written long enough that the search engine title built from it runs past the counter';

	/** Past seo_description_limit's default of 180. */
	const LONG_DESCRIPTION = 'A meta description that has been written well past the length search engines show, so the counted box that holds it is drawn already marked by the form handler before the admin has typed a single character.';

	const TIMEOUT = 10;

	public function _before(\WebDriverTester $I)
	{
		$I->amSignedInAs();
	}

	public function theMetaTitleCoreMarksOnTheHiddenSeoTabDoesNotHoldTheSaveBack(\WebDriverTester $I)
	{
		$I->wantTo('save a news item whose meta title core marked on a tab I never opened');
		$this->openNewsItem($I, $this->haveNewsItem($I, self::LONG_TITLE, ''));
		$I->dontSeeElement(self::META_TITLE);
		$this->recordWhatTheSubmitDoes($I);

		$I->click(self::NEWS_SAVE);

		$this->seeTheSaveSent($I);
	}

	public function aMetaDescriptionOverItsLimitHoldsTheSaveBackInFrontOfTheAdmin(\WebDriverTester $I)
	{
		$I->wantTo('see which field holds a news Save back when the server drew it marked on a hidden tab');
		$this->openNewsItem($I, $this->haveNewsItem($I, self::LONG_TITLE, self::LONG_DESCRIPTION));
		$I->seeElementInDOM(self::META_DESCRIPTION.'.has-error');
		$I->dontSeeElement(self::META_DESCRIPTION);
		$this->recordWhatTheSubmitDoes($I);

		$I->click(self::NEWS_SAVE);

		$I->assertSame('cancelled', $this->whatTheSubmitDid($I), 'a description over its limit was sent');
		$this->seeFocusOn($I, self::META_DESCRIPTION);
		$I->dontSeeElementInDOM(self::META_TITLE.'.has-error');
	}

	public function marksOnTwoHiddenTabsAreShownOneSaveAtATime(\WebDriverTester $I)
	{
		$I->wantTo('clear the title, move to Advanced, and save with the title and the meta title both marked out of sight');
		$this->openNewsItem($I, $this->haveNewsItem($I, self::LONG_TITLE, ''));
		$I->fillField(self::TITLE, '');
		$I->click(self::ADVANCED_TAB);
		$I->waitForElementNotVisible(self::TITLE, self::TIMEOUT);
		$I->seeElementInDOM(self::TITLE.'.has-error');
		$I->dontSeeElement(self::META_TITLE);
		$this->recordWhatTheSubmitDoes($I);

		$I->click(self::NEWS_SAVE);

		$I->assertSame('cancelled', $this->whatTheSubmitDid($I), 'a news item with no title was sent');
		$this->seeFocusOn($I, self::TITLE);

		$I->fillField(self::TITLE, self::LONG_TITLE);
		$this->recordWhatTheSubmitDoes($I);

		$I->click(self::NEWS_SAVE);

		$this->seeTheSaveSent($I);
	}

	public function anInvalidSiteUrlLeftInAHiddenPreferencesPanelIsNotSaved(\WebDriverTester $I)
	{
		$I->wantTo('type a bad Site URL, move to the Email panel and press its Save');
		$I->amOnPage(self::PREFS);
		$I->waitForElementVisible(self::SITE_URL, self::TIMEOUT);

		$I->fillField(self::SITE_URL, 'foo');
		$I->click(self::EMAIL_PANEL);
		$I->waitForElementVisible(self::EMAIL_SAVE, self::TIMEOUT);
		$I->waitForElementNotVisible(self::SITE_URL, self::TIMEOUT);
		$I->seeElementInDOM(self::SITE_URL.'.has-error');
		$this->recordWhatTheSubmitDoes($I);

		$I->click(self::EMAIL_SAVE);

		$I->assertSame('cancelled', $this->whatTheSubmitDid($I), 'a Site URL that fails its pattern was sent');
	}

	/**
	 * @return int the news_id of an item the admin can edit, with an empty meta title
	 */
	private function haveNewsItem(\WebDriverTester $I, $title, $metaDescription)
	{
		return (int) $I->haveInDatabase('e107_news', \Test\Rows::news(array(
			'news_title'            => $title,
			'news_body'             => 'fixture',
			'news_meta_description' => $metaDescription,
			'news_datestamp'        => time() - 3600,
			'news_author'           => 1,
		)));
	}

	/**
	 * Returns once {@see eHelper::syncSEOTitle()} has marked the meta title, which it does on the window's load event.
	 */
	private function openNewsItem(\WebDriverTester $I, $id)
	{
		$I->expectOutboundRequest('cdnjs.cloudflare.com');
		$I->amOnPage(self::NEWS_EDIT.$id);
		$I->waitForElement(self::META_TITLE.'.has-error', self::TIMEOUT);
	}

	/**
	 * Keeps the outcome in sessionStorage, which outlives a sent form's navigation.
	 */
	private function recordWhatTheSubmitDoes(\WebDriverTester $I)
	{
		$I->executeJS('
			sessionStorage.removeItem("e107SubmitOutcome");

			if(!window.e107SubmitRecorder) {
				window.e107SubmitRecorder = true;
				window.addEventListener("submit", function (event) {
					setTimeout(function () {
						sessionStorage.setItem("e107SubmitOutcome", event.defaultPrevented ? "cancelled" : "sent");
					}, 0);
				}, true);
			}
		');
	}

	/**
	 * @return string|false "cancelled", "sent", or false when no submit was seen
	 */
	private function whatTheSubmitDid(\WebDriverTester $I)
	{
		return \Test\Poll::until(function () use ($I)
		{
			try
			{
				return $I->executeJS('return sessionStorage.getItem("e107SubmitOutcome");');
			}
			catch(\Facebook\WebDriver\Exception\WebDriverException $e)
			{
				return false;
			}
		}, self::TIMEOUT);
	}

	private function seeTheSaveSent(\WebDriverTester $I)
	{
		$I->assertSame('sent', $this->whatTheSubmitDid($I), 'the Save was held back');
	}

	private function seeFocusOn(\WebDriverTester $I, $selector)
	{
		$I->seeElement($selector);
		$I->assertTrue((bool) $I->executeJS('return document.activeElement === document.querySelector("'.$selector.'");'),
			'the Save was held back without the field that held it being handed focus');
	}
}
