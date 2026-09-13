<?php

/**
 * Issue #5792 leg 2: a custom page whose body carries the [newpage] marker must
 * actually split into separate pages on the front-end, while legacy content that
 * carries TinyMce's default <!-- pagebreak --> comment must stay inert (one
 * page), preserving backwards compatibility for pages saved before the fix.
 *
 * page.php splits on /\[newpage.*?\]/ (pageClass::parsePage), so this exercises
 * the real end-of-line consumer of what the pagebreak button now inserts.
 */
class CpageNewpageCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function newpageMarkerSplitsPagesOnFrontEnd(AcceptanceTester $I)
	{
		$I->wantTo('confirm [newpage] splits a custom page into separate front-end pages');

		$pageId = $this->seedPage($I,
			'[html]<p>SPLITALPHA111</p>[newpage]<p>SPLITBRAVO222</p>[/html]');

		// Page 1 shows only the first page's content.
		$I->amOnPage('/page.php?' . $pageId);
		$I->dontSee('Site Configuration Issue');
		$I->see('SPLITALPHA111');
		$I->dontSee('SPLITBRAVO222');

		// Page 2 shows only the second page's content.
		$I->amOnPage('/page.php?' . $pageId . '.1');
		$I->see('SPLITBRAVO222');
		$I->dontSee('SPLITALPHA111');
	}

	public function legacyPagebreakCommentStaysInert(AcceptanceTester $I)
	{
		$I->wantTo('confirm a stored <!-- pagebreak --> comment does not split a page (BC)');

		$pageId = $this->seedPage($I,
			'[html]<p>INERTALPHA333</p><!-- pagebreak --><p>INERTBRAVO444</p>[/html]');

		// The comment is not a split marker, so the whole body renders as one
		// page, and requesting a second page yields the same single page.
		$I->amOnPage('/page.php?' . $pageId);
		$I->see('INERTALPHA333');
		$I->see('INERTBRAVO444');

		$I->amOnPage('/page.php?' . $pageId . '.1');
		$I->see('INERTALPHA333');
		$I->see('INERTBRAVO444');
	}

	private function seedPage(AcceptanceTester $I, $body)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'        => 'Newpage regression 5792',
			'page_sef'          => '',
			'page_chapter'      => 0,
			'page_text'         => $body,
			'page_author'       => 1,
			'page_datestamp'    => time(),
			'page_class'        => 0,
			'page_password'     => '',
			'page_template'     => 'default',
			'page_order'        => 0,
		));
	}
}
