<?php

/**
 * File Inspector polls a progress endpoint every 500ms while a scan runs, and
 * starts the scan from a control inside the page. Three things are asserted
 * here: the poll is only answered for somebody who may run the scan, the
 * scan's options reach the server in the request body rather than in the link
 * that commits to the work, and nothing from the query string is written back
 * into the run page.
 *
 * REDIRECTS ARE NOT FOLLOWED for the poll. The evidence is the Location header;
 * chasing it lands on the admin login, whose own body would then be what is
 * measured.
 */
class FileInspectorProgressGateCest
{
	const PROGRESS = '/e107_admin/fileinspector.php?action=progress&scan=DEADBEEF';
	const SETUP = '/e107_admin/fileinspector.php?mode=main&action=setup';
	const RUN = '/e107_admin/fileinspector.php?mode=main&action=run';
	const ADMIN_DIR = 'e107_admin';
	const PAYLOAD = '"><img src=x onerror=alert(document.domain)>';

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
	}

	public function aVisitorIsNotServedScanProgress(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an unauthenticated caller at the File Inspector progress endpoint');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage(self::PROGRESS);

		$I->seeRedirectTo(self::ADMIN_DIR);
		$I->seeResponseCodeIs(301);
	}

	/**
	 * Positive control. The endpoint exists so the progress bar can move, and an
	 * administrator gets an answer: 100, because no scan of theirs is running.
	 */
	public function anAdministratorIsServedScanProgress(AcceptanceTester $I)
	{
		$I->wantTo('Answer the progress endpoint for an administrator');

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->loginAsAdmin();

		$I->stopFollowingRedirects();
		$I->amOnPage(self::PROGRESS);

		$I->seeNoRedirectTo(self::ADMIN_DIR);
		$I->see('100');
	}

	/**
	 * The scan is started by the form, so a bare GET of the same address does not
	 * run it, and a run page reached without the options sends the operator back
	 * to choose them rather than offering a button that would scan with defaults.
	 */
	public function theScanIsNotStartedByFollowingALink(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to start a File Inspector scan from a plain GET');

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->loginAsAdmin();

		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_admin/fileinspector.php?mode=main&action=begin&core=all');
		$I->seeRedirectTo('fileinspector.php');
		$I->seeNoRedirectTo('action=begin');

		$I->amOnPage('/e107_admin/fileinspector.php?mode=main&action=run');
		$I->seeRedirectTo('action=setup');
	}

	/**
	 * The options are chosen on a POST form and carried into the run page as
	 * hidden fields, so neither the address bar nor the link that starts the scan
	 * ever holds them.
	 */
	public function theScanOptionsDoNotTravelInAUrl(AcceptanceTester $I)
	{
		$I->wantTo('Keep the File Inspector scan options out of every URL');

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->loginAsAdmin();

		$I->amOnPage(self::SETUP);
		$I->seeElement('form#scanform[method=post]');

		$I->submitForm('#scanform', array('core' => 'all', 'type' => 'list'));

		$I->seeElement('form#runit input[type=hidden][name=core][value=all]');
		$I->seeElement('form#runit input[type=hidden][name=type][value=list]');
		$I->seeInSource('action=begin"');
		$I->dontSeeInSource('action=begin&');
		$I->dontSeeInCurrentUrl('core=all');
	}

	/**
	 * GHSA-396r-g8m8-w9xx. The poll URL used to carry the scan identifier straight
	 * out of the query string through filter_var(), which sanitises nothing, so a
	 * double quote in it closed data-progress and the rest was parsed as markup.
	 *
	 * Both routes to the run page are asked. The crafted link is the attack, and
	 * it no longer renders the page at all. The POST is what proves the sink: the
	 * run page does render, with the crafted identifier sitting in the query
	 * string it is rendering from, and still writes none of it out.
	 *
	 * Everything after the POST is asserted against the response source, as every
	 * other sendPostRequest() caller in this suite does. _request() sends the
	 * request without reloading the page, so the DOM crawler still holds whatever
	 * amOnPage() last fetched, and seeElement() here would be answered by the
	 * setup tab. theScanOptionsDoNotTravelInAUrl() covers the same fields through
	 * the DOM, because submitForm() does reload.
	 */
	public function aCraftedScanIdentifierIsNotReflected(AcceptanceTester $I)
	{
		$I->wantTo('Keep a crafted File Inspector scan identifier out of the run page');

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->loginAsAdmin();

		$I->amOnPage(self::RUN.'&scan='.urlencode(self::PAYLOAD));

		$I->dontSeeInSource(self::PAYLOAD);

		$I->sendPostRequest(self::RUN.'&scan='.urlencode(self::PAYLOAD), array(
			'core'    => 'all',
			'type'    => 'list',
			'e-token' => $I->grabFreshAdminToken(self::SETUP),
		));

		$I->seeInSource("id='runit'");
		$I->seeInSource("name='core' value='all'");
		$I->dontSeeInSource(self::PAYLOAD);
		$I->seeInSource('?action=progress"');
		$I->dontSeeInSource('action=progress&');
	}
}
