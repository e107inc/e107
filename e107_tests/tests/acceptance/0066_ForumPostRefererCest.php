<?php

/**
 * The same sink as email.php:252, three more times, in a plugin that ships in
 * the same tarball. e107_plugins/forum/forum_post.php wrote
 *
 *   <a class='btn ...' href='".$_SERVER['HTTP_REFERER']."' >Cancel</a>
 *
 * on the split-thread confirmation, on the move-thread confirmation and in the
 * duplicate-post message. Browsers do not percent-encode the apostrophe in a
 * URL, and a referring page can opt into
 * <meta name="referrer" content="unsafe-url">, so the full path and query of a
 * hostile page survive a cross-origin navigation and
 * https://evil/?a=x' onmouseover='alert(1) closes the attribute with no space
 * and no angle bracket. The victim is a moderator.
 *
 * Encoding alone would be settling. The same header also decides where the
 * Cancel button sends the moderator, which makes each of these an off-site jump
 * the site itself offers, so the destination is checked as well as encoded.
 * Both properties are asserted below.
 *
 * The split-thread page is the one driven here. All three sites call the same
 * helper, so one page exercises the fix; the move page is driven too because it
 * is one more request and it proves the helper is actually wired to both.
 */
class ForumPostRefererCest
{
	const PAYLOAD = "https://evil.example.invalid/?a=ENCFORUMREF' onmouseover='alert(1)";
	const PAYLOAD_RAW = "ENCFORUMREF' onmouseover=";

	/** @var array */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();

		$this->ids = $I->haveForumStructure();

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		// getperms('0') satisfies MODERATOR for every forum
		// (forum_post.php:83), which is what these confirmation pages need.
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteHeader('Referer');
		$I->dropForumProbe();
	}

	/**
	 * The split-thread confirmation page, rendered with a hostile Referer.
	 */
	public function theSplitThreadCancelLinkDoesNotCarryTheRawReferer(AcceptanceTester $I)
	{
		$I->wantTo('Keep a hostile Referer out of the split-thread Cancel link');

		$I->haveHttpHeader('Referer', self::PAYLOAD);
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=split&id='
			.$this->ids['threadA'].'&post='.$this->ids['postA']);

		// The page is the page under test, not a redirect or an error.
		$I->seeInSource('forum-post-split');

		$I->dontSeeInSource(self::PAYLOAD_RAW);
		// And not merely encoded: an off-site Cancel destination is the second
		// half of the same defect.
		$I->dontSeeInSource('evil.example.invalid');
	}

	/**
	 * The move-thread confirmation page, which is the second of the three sites.
	 */
	public function theMoveThreadCancelLinkDoesNotCarryTheRawReferer(AcceptanceTester $I)
	{
		$I->wantTo('Keep a hostile Referer out of the move-thread Cancel link');

		$I->haveHttpHeader('Referer', self::PAYLOAD);
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=move&id='.$this->ids['threadA']);

		$I->seeInSource('forum-post-move');

		$I->dontSeeInSource(self::PAYLOAD_RAW);
		$I->dontSeeInSource('evil.example.invalid');
	}

	/**
	 * Positive control. The Cancel link still has to be a link, and an on-site
	 * Referer still has to be where it goes, or the fix has replaced a hole with
	 * a broken button.
	 */
	public function anOnSiteRefererStillReachesTheCancelLink(AcceptanceTester $I)
	{
		$I->wantTo('Still send Cancel back to the on-site page the moderator came from');

		$I->haveHttpHeader('Referer', '/e107_plugins/forum/forum.php?p8=back');
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=split&id='
			.$this->ids['threadA'].'&post='.$this->ids['postA']);

		$I->seeInSource("href='/e107_plugins/forum/forum.php?p8=back'");
	}
}
