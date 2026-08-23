<?php

/**
 * The v1 forum upgrade runs its migration steps off a GET.
 *
 * forum_update.php is gated by getperms('P') and e_ADMIN/auth.php, and then
 * dispatches on $_GET['mode'] through call_user_func('step'.intval(...).'_ajax')
 * as soon as e_AJAX_REQUEST is true. That constant is not a method gate:
 * e107_class.php falls back to isset($_REQUEST['ajax_used']), and $_REQUEST
 * carries the query string, so ?ajax_used=1&mode=8 satisfies it from an <img>
 * tag on somebody else's page with no header of any kind.
 *
 * Behind the three modes that exist are real writes. Mode 6 migrates every v1
 * thread into forum_thread and forum_post, mode 8 recalculates and rewrites
 * every forum's last-post column, and mode 10 moves post attachments on disk
 * and rewrites post_entry and post_attachments for each post it touches. The
 * administrator holds the permission, which in cross-site request forgery is
 * the attacker's weapon rather than the defender's wall.
 *
 * The AJAX branch is not the only doorway. Merely reaching forum_update.php at
 * all constructs forumUpgrade, whose constructor sweeps six deprecated files
 * out of e107_plugins/forum/ with unlink(), so a bare tokenless GET to the file
 * destroys part of the plugin directory before any mode is looked at. The
 * guard therefore sits above the constructor and covers every request the file
 * serves, whatever method it arrived by, with the step forms naming the token
 * in their action so that a POST the administrator made still gets through.
 *
 * The control drives exactly what the upgrade page drives: e-progress reads
 * data-progress off the button and issues a GET to it, so the page has to
 * publish the token there for the upgrade to keep working at all.
 *
 * @see e107_handlers/e107_class.php  define('e_AJAX_REQUEST', isset($_REQUEST['ajax_used']))
 */
class ForumUpgradeCsrfCest
{
	const UPDATE = '/e107_plugins/forum/forum_update.php';

	/** A distinctive fragment of FORLAN_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** What every one of these steps answers with once it has finished its work. */
	const COMPLETE = '100';

	/** Left in forum_lastpost_info, where only step 8 rewrites it. */
	const UNTOUCHED = 'fixture-untouched';

	/** csrf_enforce log-only, the one mode that lets a tokenless POST through. */
	const CSRF_LOG = 1;

	/** One of the six names forumUpgrade::removeDeprecatedFiles() unlinks. */
	const DEPRECATED = 'e107_plugins/forum/forum_update_check.php';

	/** What the seeded deprecated file answers with while it still exists. */
	const SURVIVED = 'forum-deprecated-file-survived';

	/** @var int */
	private $forumId;

	/** @var int */
	private $threadId;

	/** @var int */
	private $lastpost;

	/** @var string */
	private $token;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('forum');

		$this->lastpost = time() - 3600;

		$category = $I->haveInDatabase('e107_forum', $this->forumRow(
			'Upgrade CSRF category', 'upgrade-csrf-category', 0));
		$this->forumId = $I->haveInDatabase('e107_forum', $this->forumRow(
			'Upgrade CSRF forum', 'upgrade-csrf-forum', $category));

		$this->threadId = $I->haveForumThread('Upgrade CSRF thread', $this->forumId, 1,
			1, $this->lastpost, $this->lastpost);

		$I->loginAsAdmin();
		$this->token = $I->grabForumToken('/e107_plugins/forum/forum.php');

		// Step 8 is what fills in the totals its AJAX half divides by, so the
		// page has to be put on it before any request here is the one a real
		// upgrade makes.
		$I->amOnPage($this->step8());
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->deleteAppFile(self::DEPRECATED);
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The doorway that needs no mode at all. forumUpgrade::__construct() calls
	 * removeDeprecatedFiles(), which unlink()s six names out of the forum's own
	 * plugin directory, and it runs on the way to rendering the page rather
	 * than on the way to running a step.
	 */
	public function aTokenlessGetDoesNotSweepThePluginDirectory(AcceptanceTester $I)
	{
		$I->wantTo('refuse the upgrade page before its constructor deletes plugin files');

		$I->writeAppFile(self::DEPRECATED, $this->deprecatedFileSource());

		$I->amOnPage(self::UPDATE);

		$I->amOnPage('/'.self::DEPRECATED);
		$I->seeInSource(self::SURVIVED);

		$I->amOnPage(self::UPDATE);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * forumUpdateLastpost() rewrites forum_lastpost_info, forum_lastpost_user
	 * and forum_lastpost_user_anon for every forum it reaches, which on a live
	 * board is every forum there is.
	 */
	public function aTokenlessGetDoesNotRecalculateTheLastPosts(AcceptanceTester $I)
	{
		$I->wantTo('refuse the lastpost step when it arrives without a token');

		$I->amOnPage(self::UPDATE.'?ajax_used=1&mode=8');

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->forumId,
			'forum_lastpost_info' => self::UNTOUCHED,
		));
	}

	/**
	 * The dispatch answers a POST as readily as a GET. e_AJAX_REQUEST is
	 * isset($_REQUEST['ajax_used']) and $_REQUEST carries the body, so the mode
	 * can ride in the query string with nothing but ajax_used posted to it.
	 *
	 * Which is only reachable at all under csrf_enforce 1: every other mode but
	 * off has class2.php answer a tokenless POST with 403 while this file is
	 * still requiring it, and log-only records the missing token and carries on.
	 * That is the mode where the endpoint's own guard is the only thing left,
	 * so that is the mode this drives.
	 */
	public function aTokenlessPostDoesNotRecalculateTheLastPosts(AcceptanceTester $I)
	{
		$I->wantTo('refuse the lastpost step when it arrives as a tokenless POST');

		$I->haveForumCsrfMode(self::CSRF_LOG);
		$I->amOnPage($this->step8());

		$I->sendPostRequest(self::UPDATE.'?mode=8', array('ajax_used' => 1));

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->forumId,
			'forum_lastpost_info' => self::UNTOUCHED,
		));
	}

	/**
	 * migrateThread() copies v1 rows into forum_thread and forum_post.
	 */
	public function aTokenlessGetDoesNotMigrateThreads(AcceptanceTester $I)
	{
		$I->wantTo('refuse the thread migration step when it arrives without a token');

		$I->amOnPage(self::UPDATE.'?ajax_used=1&mode=6');

		$I->seeInSource(self::REFUSED);
		$I->dontSeeInSource(self::COMPLETE);
	}

	/**
	 * moveAttachment() moves files on disk and the step then rewrites
	 * post_entry and post_attachments for every post it touched.
	 */
	public function aTokenlessGetDoesNotMigrateAttachments(AcceptanceTester $I)
	{
		$I->wantTo('refuse the attachment migration step when it arrives without a token');

		$I->amOnPage(self::UPDATE.'?ajax_used=1&mode=10');

		$I->seeInSource(self::REFUSED);
		$I->dontSeeInSource(self::COMPLETE);
	}

	/**
	 * Presence is all the endpoint tests. Whether the value is the right one is
	 * e_core_session::check()'s half, so assert that half too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('keep the framework refusing a token that does not validate');

		$I->amOnPage(self::UPDATE.'?e-token=not-even-close&ajax_used=1&mode=8');

		$I->seeInSource('Unauthorized access!');
		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->forumId,
			'forum_lastpost_info' => self::UNTOUCHED,
		));
	}

	/**
	 * The control that matters most: the upgrade has to still run. This is the
	 * request e-progress makes on the administrator's behalf, with the token
	 * read off the button the page published rather than minted here.
	 */
	public function theUpgradePagesOwnProgressButtonStillRecalculates(AcceptanceTester $I)
	{
		$I->wantTo('keep the upgrade page able to run its own step');

		$I->amOnPage(self::UPDATE.'?ajax_used=1&mode=8'.$this->progressToken($I));

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->forumId,
			'forum_lastpost_info' => $this->lastpost.'.'.$this->threadId,
		));
	}

	/**
	 * Read back the url the page offers e-progress, as a query-string tail.
	 *
	 * Empty when the page publishes no token, so this control still drives the
	 * step against a tree without the fix and holds in both states.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function progressToken(AcceptanceTester $I)
	{
		$I->amOnPage($this->step8());

		if (!preg_match('#data-progress="([^"]*)"#', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The upgrade page published no progress url');
		}

		if (!preg_match('#e-token=([^&"]+)#', $matches[1], $token))
		{
			return '';
		}

		return '&e-token='.$token[1];
	}

	/**
	 * The url that rewinds the upgrade to step 8, carrying the token the page
	 * now demands of everything it serves.
	 *
	 * @return string
	 */
	private function step8()
	{
		return self::UPDATE.'?reset=8&e-token='.$this->token;
	}

	/**
	 * A stand-in for the deprecated file, which ships in no e107 release and
	 * only exists on a site that has been upgraded from the v1 forum.
	 *
	 * @return string
	 */
	private function deprecatedFileSource()
	{
		return "<?php\necho '".self::SURVIVED."';\n";
	}

	/**
	 * @param string $name
	 * @param string $sef must be unique; forum_sef carries a unique key
	 * @param int $parent
	 * @return array
	 */
	private function forumRow($name, $sef, $parent)
	{
		return array(
			'forum_name' => $name, 'forum_description' => 'fixture',
			'forum_parent' => $parent, 'forum_sub' => 0, 'forum_moderators' => 0,
			'forum_class' => 0, 'forum_postclass' => 0, 'forum_threadclass' => 0,
			'forum_order' => 1, 'forum_sef' => $sef, 'forum_datestamp' => time(),
			'forum_lastpost_info' => self::UNTOUCHED,
		);
	}
}
