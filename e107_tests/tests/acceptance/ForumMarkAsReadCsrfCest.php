<?php

/**
 * Marking a forum read is a write, and it ran off a bare GET.
 *
 * forum.php?f=mfar&id=<forum> calls forumMarkAsRead(), which collects every
 * thread in that forum and its children that is newer than the caller's last
 * visit and hands the list to threadMarkAsRead(), which rewrites
 * user_extended.user_plugin_forum_viewed for whoever holds the session. There
 * is no permission on it and none would help: the row it rewrites is the
 * victim's own, so the forged request needs nothing the victim's browser does
 * not already carry.
 *
 * What it costs is small and irreversible: the unread markers a member relies
 * on to find what has been posted since they were last here, for a whole forum
 * and everything under it, gone with no record that anything happened.
 * Repeatable from an <img> tag on any page the member opens.
 *
 * The route is search-engine-friendly, /forum/markread/<forum>, and route
 * parameters land in $_GET through eRouter::populateRequestParams(). That
 * merges the redirect's own f and id into $_GET without disturbing what the
 * request really carried, so a token in the query string survives the rewrite
 * and both spellings of the URL reach the same guard.
 *
 * @see e107_plugins/forum/forum_class.php  e107forum::threadMarkAsRead()
 */
class ForumMarkAsReadCsrfCest
{
	const FORUM = '/e107_plugins/forum/forum.php';

	const VIEWFORUM = '/e107_plugins/forum/forum_viewforum.php';

	/** A distinctive fragment of LAN_FORUM_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** @var int the forum whose page publishes the marker, one level up */
	private $parentId;

	/** @var int the forum the marker names */
	private $forumId;

	/** @var int */
	private $threadId;

	/** @var int */
	private $memberId;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('forum');

		$category = $I->haveInDatabase('e107_forum',
			$this->forumRow('Markread CSRF category', 'markread-csrf-category', 0, 0));
		$this->parentId = $I->haveInDatabase('e107_forum',
			$this->forumRow('Markread CSRF forum', 'markread-csrf-forum', $category, 0));

		// A child forum, because the new-posts marker is published on the
		// parent's page for each of its children and nowhere else.
		$this->forumId = $I->haveInDatabase('e107_forum',
			$this->forumRow('Markread CSRF subforum', 'markread-csrf-subforum',
				$category, $this->parentId));

		$this->threadId = $I->haveForumThread('Markread CSRF thread', $this->forumId, 1);

		$this->memberId = $I->haveForumMember('markreadcsrfmember');

		$I->logoutFromForum();
		$I->loginToForum('markreadcsrfmember');

		// After signing in, never before: the plugin's own login handler empties
		// this column on every login, so a value seeded first would be gone.
		$I->haveForumThreadsRead($this->memberId, array());
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	public function aTokenlessGetDoesNotClearTheUnreadMarkers(AcceptanceTester $I)
	{
		$I->wantTo('refuse a mark-as-read that arrived without a token');

		$I->amOnPage(self::FORUM.'?f=mfar&id='.$this->forumId);

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_user_extended', array(
			'user_extended_id' => $this->memberId,
			'user_plugin_forum_viewed' => '',
		));
	}

	/**
	 * Presence is all the endpoint tests. Whether the value is the right one is
	 * e_core_session::check()'s half, so assert that half too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('keep the framework refusing a token that does not validate');

		$I->amOnPage(self::FORUM.'?f=mfar&id='.$this->forumId.'&e-token=not-even-close');

		$I->seeInSource('Unauthorized access!');
		$I->seeInDatabase('e107_user_extended', array(
			'user_extended_id' => $this->memberId,
			'user_plugin_forum_viewed' => '',
		));
	}

	/**
	 * The control that matters most. The marker is the only way a member has of
	 * saying "I have seen this forum", so it has to keep working from the page
	 * that publishes it.
	 */
	public function theForumsOwnNewPostsMarkerStillClearsThem(AcceptanceTester $I)
	{
		$I->wantTo('keep the new-posts marker working');

		$I->amOnPage(self::FORUM.'?f=mfar&id='.$this->forumId.$this->publishedToken($I));

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInDatabase('e107_user_extended', array(
			'user_extended_id' => $this->memberId,
			'user_plugin_forum_viewed' => (string) $this->threadId,
		));
	}

	/**
	 * @param string $name
	 * @param string $sef must be unique; forum_sef carries a unique key
	 * @param int $parent category the forum sits in; 0 makes the row a category
	 * @param int $sub forum this one is a child of, 0 for none
	 * @return array
	 */
	private function forumRow($name, $sef, $parent, $sub)
	{
		return array(
			'forum_name' => $name, 'forum_description' => 'fixture',
			'forum_parent' => $parent, 'forum_sub' => $sub, 'forum_moderators' => 0,
			'forum_class' => 0, 'forum_postclass' => 0, 'forum_threadclass' => 0,
			'forum_order' => 1, 'forum_sef' => $sef, 'forum_datestamp' => time(),
		);
	}

	/**
	 * Read the token out of the marker the parent forum publishes, as a
	 * query-string tail.
	 *
	 * Only the token is taken. The path around it is either the SEF route or
	 * the plugin file depending on a site preference, and rebuilding it here
	 * keeps the request the same on both. The tail is empty when the marker
	 * carries no token, so this control still marks the forum read against a
	 * tree without the fix and holds in both states.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function publishedToken(AcceptanceTester $I)
	{
		$I->amOnPage(self::VIEWFORUM.'?id='.$this->parentId);

		$pattern = '#href=\'([^\']*(?:markread/'.$this->forumId
			.'|f=mfar[^\']*id='.$this->forumId.')[^\']*)\'#';

		if (!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The forum published no mark-as-read marker');
		}

		if (!preg_match('#e-token=([^&\'"]+)#', $matches[1], $token))
		{
			return '';
		}

		return '&e-token='.$token[1];
	}
}
