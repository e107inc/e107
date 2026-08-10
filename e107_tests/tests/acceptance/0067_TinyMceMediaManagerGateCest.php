<?php

/**
 * The third file in e107_plugins/tinymce4/plugins/e107/ asked nobody who was
 * calling. dialog.php has had a USER + post_html gate for as long as it has
 * existed and parser.php has one now; mediamanager.php required class2.php and
 * then issued an unconditional
 *
 *   header('Location: '.e_ADMIN_ABS.'image.php?...&for='.$_SESSION['media_category'].'...')
 *
 * to any caller at all. The target is admin-gated, so this is not a disclosure
 * of media; it is a free oracle for the admin directory name, which an install
 * is entitled to rename precisely so that it is not guessable, and it wrote a
 * session value into a URL with no encoding.
 *
 * REDIRECTS ARE NOT FOLLOWED HERE. The evidence is the Location header, and
 * chasing it would land on an admin page whose own refusal would then be what
 * was measured.
 */
class TinyMceMediaManagerGateCest
{
	const MEDIAMANAGER = '/e107_plugins/tinymce4/plugins/e107/mediamanager.php';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\OutputEncodingFixture::PROBE_FILE, \Helper\OutputEncodingFixture::probeSource());
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');
		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=cleanup');
		$I->deleteAppFile(\Helper\OutputEncodingFixture::PROBE_FILE);
	}

	public function aVisitorIsNotToldWhereTheAdminDirectoryIs(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an unauthenticated caller at the TinyMce media manager');

		$I->resetAllCookies();
		$I->amOnPage(self::MEDIAMANAGER);

		$I->seeNoRedirectTo('image.php');
	}

	/**
	 * A member with no post_html class is the second half of the same question:
	 * having an account is not the same as being allowed to use the editor.
	 */
	public function aMemberWhoMayNotPostHtmlIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a member with no post_html class at the media manager');

		$this->loginAsMember($I);
		$I->amOnPage(self::MEDIAMANAGER);

		$I->seeNoRedirectTo('image.php');
	}

	/**
	 * Positive control. The endpoint exists so the editor's media button works
	 * for whoever may use the editor, and a main administrator may.
	 */
	public function anAdministratorIsStillRedirectedToTheMediaDialog(AcceptanceTester $I)
	{
		$I->wantTo('Still send an administrator on to the media dialog');

		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		$I->amOnPage(self::MEDIAMANAGER);

		$I->seeRedirectTo('image.php?mode=main&action=dialog');
		$I->seeRedirectTo('bbcode=img');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function loginAsMember(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=member');
		$I->see('P8_OK member');

		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\OutputEncodingFixture::MEMBER_NAME);
		$I->fillField('userpass', \Helper\OutputEncodingFixture::MEMBER_PASS);
		$I->click('userlogin');
		$I->stopFollowingRedirects();
	}
}
