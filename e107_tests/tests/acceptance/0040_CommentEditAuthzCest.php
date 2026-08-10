<?php

/**
 * GHSA-5w63-63rh-99q6 / CVE-2026-43934: rewriting somebody else's comment.
 *
 * The published fix put an author predicate on comment::updateComment(), which
 * is the method comment.php's AJAX route calls. The route that shipped before
 * AJAX existed was left alone. comment.php's editsubmit branch hands the request
 * to comment::enter_comment(), and that method carries an UPDATE of its own
 * whose only predicate is a comment id.
 *
 * Which comment id is not the one the form submitted. enter_comment()
 * re-derives it from the request line: either from ?comment=edit&comment_id=N,
 * or by taking the segment after "edit" out of a dotted e_QUERY. The target is
 * therefore chosen by the URL of any POST that carries editsubmit, and nothing
 * between the request and the UPDATE compares it against the caller.
 *
 * That UPDATE is reached from every legacy caller of enter_comment(), so this is
 * one method and six routes: comment.php, page.php, user.php and the download,
 * faqs and news plugins.
 *
 * The same write ignores the two rules the front end states and the server never
 * did: comment_lock, a moderator's "no further edits" on one comment, and
 * allowCommentEdit, the preference a fresh install ships off. Both are covered
 * here, on every spelling that reaches the write.
 *
 * Every assertion reads the stored row back. The route answers 200 and renders
 * the comment page whether or not the write landed, so nothing in the response
 * tells a refusal apart from a successful rewrite.
 */
class CommentEditAuthzCest
{
	/**
	 * Registered in Extension\WorkspaceCleanup so a crashed run does not leave
	 * it in the docroot.
	 */
	const PROBE_FILE = 'e107_tests_comment_authz_probe.php';

	/** e_session::TOKEN_CHECK_ENFORCE. Pinned; see _before(). */
	const CSRF_TOKEN_ENFORCE = 2;

	const MEMBER_PASS = 'Password1234';

	/** Text of the comment under test, as seeded. */
	const ORIGINAL = 'Alice original comment text';

	/** Text of the anonymous comment under test, as seeded. */
	const ORIGINAL_ANON = 'Anonymous original comment text';

	/** Text of the comment a moderator has locked, as seeded. */
	const ORIGINAL_LOCKED = 'Alice locked comment text';

	/** @var int */
	private $newsId;

	/**
	 * A second item, which carries the locked comment.
	 *
	 * compose_comment() reads comment_lock off the last comment it renders and
	 * treats it as a lock on the whole thread, so a locked comment anywhere on
	 * the item under test replaces the comment form with "Comments are locked"
	 * and the page stops publishing a token. The routes under test take their
	 * target from the request line and never check which item it belongs to, so
	 * the locked row is seeded out of the way.
	 *
	 * @var int
	 */
	private $lockedNewsId;

	/** @var int */
	private $alice;

	/** @var int */
	private $mallory;

	/** @var int */
	private $moderator;

	/** @var int comment_id of a comment authored by alice */
	private $aliceComment;

	/** @var int comment_id of a comment with comment_author_id 0 */
	private $anonComment;

	/** @var int comment_id of a comment authored by alice with comment_lock 1 */
	private $lockedComment;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		// Every request from the container arrives from the bridge address, and
		// e107 bans an address once it has been seen enough times in a window.
		$this->probe($I, 'act=flood');

		// Pin the CSRF mode rather than inherit it. Unset resolves to the
		// recommended browser check, which wants a Sec-Fetch-Site header
		// PhpBrowser never sends, so the POSTs below would be refused before
		// authorisation was ever considered and every refusal here would pass
		// for the wrong reason.
		$this->probe($I, 'act=pref&k=csrf_enforce&v='.self::CSRF_TOKEN_ENFORCE);

		// The AJAX edit route is behind this preference, and a fresh install
		// ships it off.
		$this->probe($I, 'act=pref&k=allowCommentEdit&v=1');

		// ANON, which is what getCommentPermissions() grants a caller with no
		// account. Without it enter_comment() turns a guest away for a reason
		// that has nothing to do with who owns the comment.
		$this->probe($I, 'act=pref&k=anon_post&v=1');

		$this->newsId = $this->haveNews($I, 'Comment authz fixture', 'comment-authz-fixture');
		$this->lockedNewsId = $this->haveNews($I, 'Comment authz lock fixture', 'comment-authz-lock-fixture');

		$this->alice = $this->haveMember($I, 'authzalice');
		$this->mallory = $this->haveMember($I, 'authzmallory');
		// Not a main admin: user_perms '0' short-circuits every permission test
		// in e107, so it would make a moderator control pass without a moderator.
		$this->moderator = $this->haveMember($I, 'authzmod', 1, 'B');

		$this->aliceComment = $this->haveComment($I, self::ORIGINAL, $this->alice, 'authzalice');
		$this->anonComment = $this->haveComment($I, self::ORIGINAL_ANON, 0, 'Guest');
		$this->lockedComment = $this->haveComment($I, self::ORIGINAL_LOCKED, $this->alice, 'authzalice', 1, $this->lockedNewsId);

		// A POST to this route answers 302 as often as 200, and an e107 that
		// cannot serve a request answers with a relative Location of install.php,
		// so following redirects can loop rather than fail.
		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=prefdel&k=csrf_enforce');
		$this->probe($I, 'act=pref&k=allowCommentEdit&v=0');
		$this->probe($I, 'act=pref&k=anon_post&v=0');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	// -----------------------------------------------------------------
	// the legacy editsubmit route
	// -----------------------------------------------------------------

	/**
	 * The one in the advisory, through the route the advisory did not close.
	 * Mallory holds an ordinary member session and never sees an edit control
	 * for this comment; the URL is the whole of the exploit.
	 */
	public function aMemberCannotEditAnotherMembersCommentThroughTheLegacyRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmallory', $this->mallory);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->aliceComment), 'REWRITTEN BY MALLORY');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * The second derivation. enter_comment() reads ?comment=edit&comment_id=N
	 * before it looks at e_QUERY, and that is the form the front-end edit link
	 * uses on any page whose query string already contains an ampersand, so it
	 * is not a theoretical spelling.
	 */
	public function aMemberCannotEditAnotherMembersCommentThroughTheQueryStringForm(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmallory', $this->mallory);

		$url = '/comment.php?comment.news.'.$this->newsId
			.'&comment=edit&comment_id='.$this->aliceComment;

		$this->postLegacyEdit($I, $url, 'REWRITTEN BY MALLORY VIA GET');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * No account at all, only the session and the token any visitor is handed on
	 * their first page view.
	 */
	public function aGuestCannotEditAMembersComment(AcceptanceTester $I)
	{
		$this->beAGuest($I);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->aliceComment), 'REWRITTEN BY A GUEST');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * An anonymous comment stores comment_author_id 0, which is also what USERID
	 * is for a caller with no account. An author predicate that says nothing
	 * about being signed in therefore hands every guest every anonymous comment
	 * on the site, which is the same pseudo-identity the forum's self-service
	 * routes had. e107 offers no interface for editing an anonymous comment: both
	 * the edit shortcode and form_comment() require USER.
	 */
	public function aGuestCannotEditAnAnonymousComment(AcceptanceTester $I)
	{
		$this->beAGuest($I);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->anonComment), 'REWRITTEN BY ANOTHER GUEST');

		$this->seeCommentIs($I, $this->anonComment, self::ORIGINAL_ANON);
		$this->seeNoOtherCommentChanged($I, $this->anonComment);
	}

	/**
	 * The guest actor's own positive control, and the only one this file has.
	 * Every guest refusal here passes through four gates the member refusals do
	 * not share: anon_post, comment.php's ANON-or-USER door, getCommentPermissions()
	 * and the token a guest session is minted. If any of them ever closes, the
	 * guest tests go on passing with the row unchanged and nothing notices, so
	 * prove the same actor can still reach the write section of enter_comment().
	 */
	public function aGuestCanStillPostAnAnonymousComment(AcceptanceTester $I)
	{
		$this->beAGuest($I);

		$I->sendPostRequest('/comment.php?comment.news.'.$this->newsId, array(
			'commentsubmit' => 'Post',
			'comment'       => 'GUEST CONTROL COMMENT',
			'subject'       => '',
			'author_name'   => 'authzguest',
			'table'         => 'news',
			'itemid'        => $this->newsId,
			'e-token'       => $this->grabCommentToken($I),
		));

		$I->seeResponseCodeIs(200);
		$I->seeInDatabase('e107_comments', array(
			'comment_item_id'   => $this->newsId,
			'comment_author_id' => 0,
			'comment_comment'   => 'GUEST CONTROL COMMENT',
		));
	}

	/**
	 * comment_lock is a moderator's "no further edits" on one comment. The admin
	 * comment manager offers it as a filterable, batchable field and
	 * sc_comment_edit() refuses to render the edit link once it is set, so the
	 * author is exactly the party it exists to stop.
	 */
	public function theAuthorCannotEditACommentAModeratorHasLocked(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzalice', $this->alice);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->lockedComment), 'REWRITTEN PAST THE LOCK');

		$this->seeCommentIs($I, $this->lockedComment, self::ORIGINAL_LOCKED);
		$this->seeNoOtherCommentChanged($I, $this->lockedComment);
	}

	/**
	 * The lock's control. It is the author it stops, not the moderator who set
	 * it, who reaches the same row through the admin comment manager anyway.
	 */
	public function aModeratorCanStillEditALockedComment(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmod', $this->moderator, true);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->lockedComment), 'EDITED PAST THE LOCK');

		$this->seeCommentStartsWith($I, $this->lockedComment, 'EDITED PAST THE LOCK');
		$this->seeNoOtherCommentChanged($I, $this->lockedComment);
	}

	/**
	 * allowCommentEdit is the preference that says whether this site lets people
	 * edit comments at all, and a fresh install ships it off. comment.php tests
	 * it before the AJAX route and nothing tests it before this one, so with the
	 * preference off a hand-written URL was the whole of the difference.
	 */
	public function theAuthorCannotEditWhenCommentEditingIsSwitchedOff(AcceptanceTester $I)
	{
		$this->switchCommentEditingOff($I);
		$this->loginAs($I, 'authzalice', $this->alice);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->aliceComment), 'REWRITTEN WITH EDITING OFF');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * The control that matters most. A refusal that also refuses the author is
	 * not a fix, and every assertion above would pass against one.
	 */
	public function theAuthorCanStillEditTheirOwnCommentThroughTheLegacyRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzalice', $this->alice);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->aliceComment), 'EDITED BY ALICE');

		$this->seeCommentStartsWith($I, $this->aliceComment, 'EDITED BY ALICE');
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * The other control. A comment moderator holds 'B', which is what this class
	 * has always meant by a moderator: it is the permission deleteComment() and
	 * approveComment() ask for, and the permission the admin comment manager
	 * rewrites comment text under.
	 */
	public function aModeratorCanStillEditAnybodysCommentThroughTheLegacyRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmod', $this->moderator, true);

		$this->postLegacyEdit($I, $this->dottedEditUrl($this->aliceComment), 'EDITED BY A MODERATOR');

		$this->seeCommentStartsWith($I, $this->aliceComment, 'EDITED BY A MODERATOR');
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	// -----------------------------------------------------------------
	// the AJAX route, which the published fix did cover
	// -----------------------------------------------------------------

	/**
	 * Already closed upstream. Kept so the predicate cannot be lifted out of
	 * updateComment() while the extracted rule is being moved around.
	 */
	public function aMemberCannotEditAnotherMembersCommentThroughTheAjaxRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmallory', $this->mallory);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'itemid'  => $this->aliceComment,
			'comment' => 'REWRITTEN BY MALLORY OVER AJAX',
			'e-token' => $this->grabCommentToken($I),
		));

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
	}

	public function theAuthorCanStillEditTheirOwnCommentThroughTheAjaxRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzalice', $this->alice);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'itemid'  => $this->aliceComment,
			'comment' => 'EDITED BY ALICE OVER AJAX',
			'e-token' => $this->grabCommentToken($I),
		));

		$this->seeCommentIs($I, $this->aliceComment, 'EDITED BY ALICE OVER AJAX');
	}

	public function aModeratorCanStillEditAnybodysCommentThroughTheAjaxRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmod', $this->moderator, true);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'itemid'  => $this->aliceComment,
			'comment' => 'EDITED BY A MODERATOR OVER AJAX',
			'e-token' => $this->grabCommentToken($I),
		));

		$this->seeCommentIs($I, $this->aliceComment, 'EDITED BY A MODERATOR OVER AJAX');
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * The twin of the legacy guest case, on the route the published fix did
	 * cover. It covered it only against a signed-in stranger: the predicate it
	 * added was comment_author_id = USERID, which is 0 for a caller with no
	 * account and matches every anonymous comment on the site. comment.php lets
	 * a guest in here whenever anon_post is on.
	 */
	public function aGuestCannotEditAnAnonymousCommentThroughTheAjaxRoute(AcceptanceTester $I)
	{
		$this->beAGuest($I);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'itemid'  => $this->anonComment,
			'comment' => 'REWRITTEN BY A GUEST OVER AJAX',
			'e-token' => $this->grabCommentToken($I),
		));

		$this->seeCommentIs($I, $this->anonComment, self::ORIGINAL_ANON);
		$this->seeNoOtherCommentChanged($I, $this->anonComment);
	}

	public function theAuthorCannotEditALockedCommentThroughTheAjaxRoute(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzalice', $this->alice);

		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=edit', array(
			'itemid'  => $this->lockedComment,
			'comment' => 'REWRITTEN PAST THE LOCK OVER AJAX',
			'e-token' => $this->grabCommentToken($I),
		));

		$this->seeCommentIs($I, $this->lockedComment, self::ORIGINAL_LOCKED);
		$this->seeNoOtherCommentChanged($I, $this->lockedComment);
	}

	// -----------------------------------------------------------------
	// the third spelling: an AJAX POST that never asks for mode=edit
	// -----------------------------------------------------------------

	/**
	 * comment.php gates the AJAX edit route on allowCommentEdit, but only the
	 * mode=edit branch. Any AJAX POST carrying a comment falls through to the
	 * render branch and calls enter_comment(), which re-derives its target from
	 * ?comment=edit&comment_id=N whatever the mode says. That reaches the legacy
	 * UPDATE without passing the preference gate one line above it.
	 */
	public function aMemberCannotEditAnotherMembersCommentThroughTheSubmitSpelling(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzmallory', $this->mallory);

		$this->postSubmitSpelling($I, $this->aliceComment, 'REWRITTEN VIA THE SUBMIT SPELLING');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * The same spelling with the preference off, which is the state a fresh
	 * install ships and the one comment.php's own gate cannot see.
	 */
	public function theAuthorCannotEditThroughTheSubmitSpellingWhenCommentEditingIsSwitchedOff(AcceptanceTester $I)
	{
		$this->switchCommentEditingOff($I);
		$this->loginAs($I, 'authzalice', $this->alice);

		$this->postSubmitSpelling($I, $this->aliceComment, 'REWRITTEN VIA THE SUBMIT SPELLING WITH EDITING OFF');

		$this->seeCommentIs($I, $this->aliceComment, self::ORIGINAL);
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	/**
	 * This spelling's control. comment.php answers 200 with a JSON error body
	 * whether the write landed or not, and the AJAX door at the top of the file
	 * exits with an empty 200 as well, so only a write that does land proves the
	 * two refusals above reached the code they claim to be refused by.
	 */
	public function theAuthorCanStillEditTheirOwnCommentThroughTheSubmitSpelling(AcceptanceTester $I)
	{
		$this->loginAs($I, 'authzalice', $this->alice);

		$this->postSubmitSpelling($I, $this->aliceComment, 'EDITED BY ALICE VIA THE SUBMIT SPELLING');

		$this->seeCommentStartsWith($I, $this->aliceComment, 'EDITED BY ALICE VIA THE SUBMIT SPELLING');
		$this->seeNoOtherCommentChanged($I, $this->aliceComment);
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * @param int $commentId
	 * @return string
	 */
	private function dottedEditUrl($commentId)
	{
		return '/comment.php?comment.news.'.$this->newsId.'.edit.'.$commentId;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $url
	 * @param string $text replacement comment body
	 */
	private function postLegacyEdit(AcceptanceTester $I, $url, $text)
	{
		$token = $this->grabCommentToken($I);

		// editpid is deliberately absent. comment.php reads it, but only to
		// decide where to redirect afterwards; enter_comment() never looks at it
		// and re-derives the target from the request line instead.
		$I->sendPostRequest($url, array(
			'editsubmit'  => 'Update',
			'comment'     => $text,
			'subject'     => '',
			'author_name' => '',
			'table'       => 'news',
			'itemid'      => $this->newsId,
			'e-token'     => $token,
		));

		// Tell "the server considered this request and refused the write" apart
		// from "the server never got that far". Every way comment.php turns a
		// POST away before enter_comment() is a redirect, and stopFollowingRedirects()
		// would leave the row unchanged and the assertion green either way.
		// Without editpid this route renders the comment page, so a request that
		// reached the edit branch answers 200 whether or not the write landed.
		$I->seeResponseCodeIs(200);
	}

	/**
	 * An AJAX POST that asks for mode=submit and carries ?comment=edit. It skips
	 * comment.php's allowCommentEdit gate, which only guards mode=edit, and
	 * lands on the same UPDATE inside enter_comment().
	 *
	 * @param AcceptanceTester $I
	 * @param int $commentId
	 * @param string $text replacement comment body
	 */
	private function postSubmitSpelling(AcceptanceTester $I, $commentId, $text)
	{
		$token = $this->grabCommentToken($I);

		$url = '/comment.php?ajax_used=1&mode=submit&comment=edit&comment_id='.$commentId;

		$I->sendAjaxPostRequest($url, array(
			'comment'       => $text,
			'subject'       => '',
			'author_name'   => '',
			'table'         => 'news',
			'itemid'        => $this->newsId,
			'pid'           => 0,
			'comment_share' => 0,
			'e-token'       => $token,
		));
	}

	/**
	 * Take the site to the state a fresh install ships in: comment editing off.
	 * _before() turns it on because the AJAX route is behind it.
	 *
	 * @param AcceptanceTester $I
	 */
	private function switchCommentEditingOff(AcceptanceTester $I)
	{
		$this->probe($I, 'act=pref&k=allowCommentEdit&v=0');
	}

	/**
	 * Assert every seeded comment except the one under test still reads as
	 * seeded. An UPDATE that lost its comment_id predicate would rewrite the
	 * whole table, and an assertion that only inspects the target row would call
	 * that a pass.
	 *
	 * @param AcceptanceTester $I
	 * @param int $target the comment this test aimed at
	 */
	private function seeNoOtherCommentChanged(AcceptanceTester $I, $target)
	{
		$seeded = array(
			$this->aliceComment  => self::ORIGINAL,
			$this->anonComment   => self::ORIGINAL_ANON,
			$this->lockedComment => self::ORIGINAL_LOCKED,
		);

		unset($seeded[$target]);

		foreach ($seeded as $id => $text)
		{
			$this->seeCommentIs($I, $id, $text);
		}
	}

	/**
	 * The token the comment form publishes, which is what a real client sends.
	 * Grab it after signing in: e107 regenerates the session id on login, which
	 * retires a token minted for the guest session.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabCommentToken(AcceptanceTester $I)
	{
		$I->amOnPage('/comment.php?comment.news.'.$this->newsId);

		$source = $I->grabPageSource();

		// Take the token from the comment form and not merely from the page. A
		// theme publishes a token in its login menu too, which a guest is served
		// and a member is not, so a page that failed to serve the comment form
		// at all would still hand a guest a usable token and the test would go on
		// as though the route it is about had answered.
		$form = strpos($source, "id='e-comment-form'");

		if ($form === false)
		{
			throw new \RuntimeException('The comment page served no comment form');
		}

		if (!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', substr($source, $form), $m))
		{
			throw new \RuntimeException('No e-token published in the comment form');
		}

		return $m[1];
	}

	/**
	 * Drop any session and confirm the application agrees this caller is nobody,
	 * and that it would let a nobody comment at all.
	 *
	 * Without the second half a refusal below could be e107 turning away an
	 * anonymous comment on a site that does not accept them, which looks exactly
	 * like the authorisation refusal the test believes it has proved.
	 *
	 * @param AcceptanceTester $I
	 */
	private function beAGuest(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$body = $this->probe($I, 'act=whoami');

		if (strpos($body, "USERID=0\n") === false || strpos($body, "ANON=1\n") === false
			|| strpos($body, "MODERATOR=0\n") === false)
		{
			throw new \RuntimeException('Not an anonymous caller who may comment: '.trim(strip_tags($body)));
		}
	}

	/**
	 * Sign in through the front end, then have the application say who it thinks
	 * the caller is.
	 *
	 * The second half is not ceremony. Every refusal in this file is a row that
	 * did not change, and a request that silently arrived as a guest produces the
	 * same row, so a login that quietly failed would turn each of them into a
	 * pass for the wrong reason.
	 *
	 * The moderator half is checked too. Both moderator cases here are positive
	 * controls, so a fixture that quietly stopped holding 'B' would fail them and
	 * read as the moderator escape being broken rather than as the fixture.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $userId
	 * @param bool $moderator whether this actor is expected to hold getperms('B')
	 */
	private function loginAs(AcceptanceTester $I, $name, $userId, $moderator = false)
	{
		$I->resetAllCookies();

		$I->amOnPage('/login.php');
		$I->fillField('username', $name);
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');

		$body = $this->probe($I, 'act=whoami');

		if (strpos($body, 'USERID='.$userId."\n") === false)
		{
			throw new \RuntimeException('Could not sign in as "'.$name.'": '.trim(strip_tags($body)));
		}

		if (strpos($body, 'MODERATOR='.($moderator ? '1' : '0')."\n") === false)
		{
			throw new \RuntimeException('"'.$name.'" is not the kind of actor this test needs: '.trim(strip_tags($body)));
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $commentId
	 * @param string $expected exact stored bytes
	 */
	private function seeCommentIs(AcceptanceTester $I, $commentId, $expected)
	{
		$I->assertSame($expected, $this->grabComment($I, $commentId));
	}

	/**
	 * enter_comment() appends an "edited" marker carrying the time of the edit,
	 * so a successful legacy edit cannot be matched byte for byte.
	 *
	 * @param AcceptanceTester $I
	 * @param int $commentId
	 * @param string $expected
	 */
	private function seeCommentStartsWith(AcceptanceTester $I, $commentId, $expected)
	{
		$I->assertStringStartsWith($expected, $this->grabComment($I, $commentId));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $commentId
	 * @return string
	 */
	private function grabComment(AcceptanceTester $I, $commentId)
	{
		return (string) $I->grabFromDatabase(
			'e107_comments', 'comment_comment', array('comment_id' => $commentId));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param string $sef
	 * @return int news id
	 */
	private function haveNews(AcceptanceTester $I, $title, $sef)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title' => $title, 'news_sef' => $sef,
			'news_body' => 'fixture', 'news_extended' => '',
			'news_meta_title' => '', 'news_meta_keywords' => '', 'news_meta_description' => '',
			'news_datestamp' => time() - 3600, 'news_author' => 1, 'news_category' => 0,
			// 0 means comments are allowed. comment.php requires it.
			'news_allow_comments' => 0,
			'news_start' => 0, 'news_end' => 0, 'news_class' => '0', 'news_render_type' => '0',
			'news_summary' => '', 'news_thumbnail' => '',
		));
	}

	/**
	 * A member who can actually sign in.
	 *
	 * The password is stored as a plain md5: UserHandler::getHashType() reads any
	 * 32 character hash as PASSWORD_E107_MD5 and CheckPassword() accepts it
	 * whatever the site's configured encoding is, so the plaintext is known here.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $admin
	 * @param string $perms
	 * @return int user id
	 */
	private function haveMember(AcceptanceTester $I, $name, $admin = 0, $perms = '')
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name' => $name, 'user_loginname' => $name, 'user_login' => $name,
			'user_password' => md5(self::MEMBER_PASS),
			'user_email' => $name.'@example.com',
			'user_join' => time(), 'user_ban' => 0,
			'user_lastvisit' => time() - 86400, 'user_currentvisit' => time() - 86400,
			'user_class' => '253',
			'user_admin' => $admin, 'user_perms' => $perms,
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $text
	 * @param int $authorId
	 * @param string $authorName
	 * @param int $lock comment_lock, the moderator control sc_comment_edit() honours
	 * @param int|null $itemId the item this comment belongs to; defaults to the one under test
	 * @return int comment id
	 */
	private function haveComment(AcceptanceTester $I, $text, $authorId, $authorName, $lock = 0, $itemId = null)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_pid' => 0, 'comment_item_id' => $itemId === null ? $this->newsId : $itemId,
			'comment_subject' => 'Comment authz fixture',
			'comment_author_id' => $authorId, 'comment_author_name' => $authorName,
			'comment_author_email' => '', 'comment_datestamp' => time() - 1800,
			'comment_comment' => $text, 'comment_blocked' => 0, 'comment_ip' => '127.0.0.1',
			// getCommentType('news') is 0, and that is what enter_comment() stores.
			'comment_type' => '0', 'comment_lock' => $lock, 'comment_share' => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		$body = $I->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Comment authz probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * Core preferences live serialised inside a single e107_core row, so no
	 * database assertion can read or write one. Boot the application instead.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for CommentEditAuthzCest. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$key = isset($_GET['k']) ? preg_replace('/[^\w]/', '', $_GET['k']) : '';

switch($act)
{
	case 'flood':
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
		echo "PROBE_OK flood\n";
		break;

	case 'pref':
		$value = isset($_GET['v']) ? $_GET['v'] : '';
		$config = e107::getConfig('core');
		$config->set($key, is_numeric($value) ? (int) $value : $value);
		$config->save(false, true, false);
		echo "PROBE_OK pref ".$key."\n";
		break;

	case 'prefdel':
		$config = e107::getConfig('core');
		$config->remove($key);
		$config->save(false, true, false);
		echo "PROBE_OK prefdel ".$key."\n";
		break;

	case 'whoami':
		echo "PROBE_OK whoami\n";
		echo "USERID=".USERID."\n";
		echo "ANON=".(deftrue('ANON') ? 1 : 0)."\n";
		echo "ADMIN=".(deftrue('ADMIN') ? 1 : 0)."\n";
		echo "MODERATOR=".(getperms('B') ? 1 : 0)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
