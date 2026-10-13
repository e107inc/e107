<?php

/**
 * An HTML comment in a page's body stays one comment when the page prints, whatever entities its text holds.
 */
class PageBodyCommentCest
{
	/** An administrator who holds the Pages permission and nothing else. */
	const PAGES_ADMIN = 'cmtq7dpages';

	const PAGES_ADMIN_PASS = 'Cmtq7dPagesPass';

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => '5.'));
	}

	public function aCommentHidesWhatItHolds(AcceptanceTester $I)
	{
		$page = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Comment cmtq7d',
			'page_sef'       => 'comment-cmtq7d',
			'page_chapter'   => 0,
			'page_text'      => 'Body',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
			'page_fields'    => '',
		));

		$I->amSignedInAs(self::PAGES_ADMIN);
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->submitForm('#core-page-form', array(
			'page_text' => '<p>Before<!-- --&gt;<b id="cmtq7d" onclick="cmtq7d()">Hidden</b> --> after</p>',
		), 'etrigger_submit');

		$I->amOnPage('/page.php?id='.$page);
		$I->dontSeeElement('#cmtq7d');
		$I->see('after');
	}
}
