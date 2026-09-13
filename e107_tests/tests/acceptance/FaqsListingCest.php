<?php

/**
 * The FAQ page reads $action, $id and $idx from a block that cannot run (#6376).
 *
 * The block tests $_GET['elan'] through vartrue(), which takes its argument by
 * reference, so the test creates the key and the empty($_GET) beside it is false
 * for the rest of the request. Deleting the test rather than the block would
 * have brought the block to life, and that is what these tests are here for: a
 * query string PHP parses to no variables at all leaves $_GET empty while
 * e_QUERY still holds the string, so a live block would set $action to something
 * the page dispatches on none of, and the request would render no header, no
 * listing and no view. Both shapes have to end at the listing.
 *
 * What a visitor can reach through this page beyond that is the open question on
 * #6376 and is the maintainer's.
 *
 * @see e107_plugins/faqs/faqs.php
 */
class FaqsListingCest
{
	const PAGE = '/e107_plugins/faqs/faqs.php';

	/** The first question faqs_setup seeds, in the category it gives to everyone. */
	const SEEDED = 'What is FAQs?';

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('faqs');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall('faqs');
	}

	public function aBareRequestRendersTheListing(AcceptanceTester $I)
	{
		$I->wantTo('read the FAQs');

		$I->amOnPage(self::PAGE);

		$this->seeTheListing($I);
	}

	/**
	 * PHP discards a query-string entry that names nothing, so a request can
	 * carry a query string and still leave $_GET empty.
	 */
	public function aQueryStringThatNamesNothingStillRendersTheListing(AcceptanceTester $I)
	{
		$I->wantTo('land on the FAQ list from a link whose query string lost its name');

		$I->amOnPage(self::PAGE.'?=x');

		$this->seeTheListing($I);
	}

	private function seeTheListing(AcceptanceTester $I)
	{
		$I->seeResponseCodeIs(200);
		$I->see(self::SEEDED);
	}
}
