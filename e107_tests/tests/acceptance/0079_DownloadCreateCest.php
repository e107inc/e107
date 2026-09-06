<?php

/**
 * Admin > Downloads > Create, the form discussion #6104 reported saving nothing.
 *
 * The unit tests settle the rule: a typed write resolves a null against the
 * column it is going into. What they cannot answer is whether an administrator
 * can actually create a download, and that is the question that went unasked
 * for six weeks. Nothing in this suite posted submit_download or opened
 * admin_download.php at all, so the row silently stopped landing and every job
 * stayed green.
 *
 * Editing goes through the same form: e_admin_ui::EditPage() defers to
 * CreatePage(), which this plugin overrides, and create_download() loads the row
 * itself when the action is edit. Both post submit_download. The edit case is
 * covered here too, though it passes either way: submit_download() writes an
 * update through set() rather than a typed write, so its null lands on the
 * server as SQL NULL and is coerced to '' by e107's non-strict sql_mode.
 */
class DownloadCreateCest
{
	const CAT_CREATE = '/e107_plugins/download/admin_download.php?mode=cat&action=create';
	const CREATE = '/e107_plugins/download/admin_download.php?mode=main&action=create';
	const LIST_PAGE = '/e107_plugins/download/admin_download.php?mode=main&action=list';

	/**
	 * download_name carries a UNIQUE key and the harness database outlives a
	 * run, so a fixed name would let the row left by the previous run satisfy
	 * the assertion while this run's insert failed on the duplicate.
	 *
	 * @var string
	 */
	private $downloadName;

	public function _before(AcceptanceTester $I)
	{
		$this->downloadName = 'Acceptance 6104 '.uniqid();

		$I->havePluginInstalled('download');
		$I->loginAsAdmin();
	}

	/**
	 * A download needs a category to belong to, and the create form refuses to
	 * render its fields without one.
	 */
	private function haveADownloadCategory(AcceptanceTester $I)
	{
		$I->amOnPage(self::CAT_CREATE);
		$I->assertSame(200, $I->grabResponseCode());

		$I->fillField('download_category_name', 'Acceptance 6104 category');
		$I->click('#etrigger-submit');
	}

	public function aDownloadCreatedThroughTheAdminFormIsSaved(AcceptanceTester $I)
	{
		$I->wantTo('create a download and find it in Manage afterwards');

		$this->haveADownloadCategory($I);

		$I->amOnPage(self::CREATE);
		$I->assertSame(200, $I->grabResponseCode());
		$I->seeElement('input[name="submit_download"]');

		// The case #6104 was reported on, and the one this test exists to cover:
		// the thumbnail field is drawn only where e_FILE."downloadthumbs" exists,
		// so on an ordinary site the form posts no download_thumb at all and the
		// value reaches a `text NOT NULL` column as a null. If this assertion
		// ever fails, the form has changed and the covered case has moved.
		$I->dontSeeElement('[name="download_thumb"]');

		$I->submitForm('#myform', array(
			'download_name'        => $this->downloadName,
			'download_description' => 'Regression cover for discussion #6104.',
		), 'submit_download');

		$I->amOnPage(self::LIST_PAGE);
		$I->see($this->downloadName);
	}

	/**
	 * The other half of the same form, which nothing covered either.
	 *
	 * The row's edit link is followed rather than grabbed and handed to
	 * amOnUrl(), which resets the browser's base URL and so loses the session on
	 * a site served from a subdirectory, the shape CI installs.
	 */
	public function ADownloadEditedThroughTheAdminFormIsSaved(AcceptanceTester $I)
	{
		$I->wantTo('rename a download through the edit form');

		$this->haveADownloadCategory($I);

		$I->amOnPage(self::CREATE);
		$I->submitForm('#myform', array(
			'download_name'        => $this->downloadName,
			'download_description' => 'Regression cover for discussion #6104.',
		), 'submit_download');

		$I->amOnPage(self::LIST_PAGE);
		$I->click("//tr[contains(., '".$this->downloadName."')]//a[contains(@href, 'action=edit')]");
		$I->assertSame(200, $I->grabResponseCode());
		$I->seeInField('download_name', $this->downloadName);

		$renamed = $this->downloadName.' renamed';
		$I->submitForm('#myform', array('download_name' => $renamed), 'submit_download');

		$I->amOnPage(self::LIST_PAGE);
		$I->see($renamed);
	}
}
