<?php

/**
 * Moderation has to be confined to the forum it was granted in.
 *
 * Three separate faults let a moderator of one forum reach every thread on the
 * site, and each of the routes below was demonstrated against a running install
 * before these tests were written:
 *
 *  - forumGetMods() memoised one moderator list on the forum object whatever
 *    userclass it was asked about. The page primed that memo before any write
 *    was authorised, so every later permission question answered for the forum
 *    named in the URL.
 *  - ajaxModerate() derived permission from whichever of thread/post arrived
 *    last and then acted on the thread regardless.
 *  - forum_thread_moderate() took its target out of the POST field name and
 *    checked nothing at all, leaning on a MODERATOR constant its caller had
 *    computed for a different forum.
 *
 * The positive control is not decoration. Without it a fix that refused every
 * moderation request would turn this whole file green.
 */
class ForumModerationCest
{
	/** @var array */
	private $ids;

	/** @var int */
	private $moda;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->moda = $I->haveForumMember('modonlya', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('modonlya');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The control. Moderation must still work where it was granted, or every
	 * refusal below proves nothing.
	 */
	public function aModeratorCanStillLockAThreadInTheirOwnForum(AcceptanceTester $I)
	{
		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_active' => 1));

		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'lock',
			'thread' => $this->ids['threadA'],
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_active' => 0));
	}

	/**
	 * The AJAX route. Authorised from forum A's page, acting on a thread in B.
	 */
	public function aModeratorCannotLockAThreadInAForumTheyDoNotModerate(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'lock',
			'thread' => $this->ids['threadB'],
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadB'], 'thread_active' => 1));
	}

	/**
	 * The same route asked to destroy rather than lock. Asserted on the row
	 * rather than the response, because a refusal that still deleted would
	 * otherwise read as a pass.
	 */
	public function aModeratorCannotDeleteAThreadInAForumTheyDoNotModerate(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'delete',
			'thread' => $this->ids['threadB'],
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadB']));
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postB']));
	}

	/**
	 * Supplying a post in a forum you do moderate alongside a thread in one you
	 * do not. This is the combination the code's own comment warned about and
	 * then implemented backwards.
	 */
	public function aPostInYourForumDoesNotAuthoriseAThreadInAnother(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'delete',
			'thread' => $this->ids['threadB'],
			'post'   => $this->ids['postA'],
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadB']));
	}

	/**
	 * Deleting a post in a forum you do not moderate.
	 */
	public function aModeratorCannotDeleteAPostInAForumTheyDoNotModerate(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'deletepost',
			'post'   => $this->ids['postB'],
		));

		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postB']));
	}

	/**
	 * The non-AJAX route, where the target id is carried in the field name and
	 * the handler never looked at which forum it belonged to.
	 */
	public function aModeratorCannotDeleteAThreadElsewhereByNamingItInAField(AcceptanceTester $I)
	{
		$token = $I->grabForumToken('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->sendPostRequest('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA'], array(
			'deleteThread_'.$this->ids['threadB'].'_x' => 1,
			'e-token' => $token,
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadB']));
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postB']));
	}

	/**
	 * Same route, same field trick, asking to lock instead of delete.
	 */
	public function aModeratorCannotLockAThreadElsewhereByNamingItInAField(AcceptanceTester $I)
	{
		$token = $I->grabForumToken('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->sendPostRequest('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA'], array(
			'lock_'.$this->ids['threadB'].'_x' => 1,
			'e-token' => $token,
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadB'], 'thread_active' => 1));
	}

	/**
	 * The field-name route must still work for a thread the caller does
	 * moderate, so the two tests above are not passing because the whole route
	 * stopped functioning.
	 */
	public function theFieldNameRouteStillWorksInYourOwnForum(AcceptanceTester $I)
	{
		$token = $I->grabForumToken('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->sendPostRequest('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA'], array(
			'lock_'.$this->ids['threadA'].'_x' => 1,
			'e-token' => $token,
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_active' => 0));
	}

	/**
	 * An action with no id used to read an undefined variable and then call
	 * postDelete() with it anyway. Nothing was deleted, but the PHP warning
	 * printed ahead of the JSON, so the client's parse threw and the UI did
	 * nothing at all rather than reporting the error.
	 */
	public function aModerationRequestWithNoIdIsRefusedCleanly(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array('action' => 'deletepost'));

		$I->dontSee('Warning');
		$I->dontSee('Undefined');
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postA']));
	}

	/**
	 * The forum page called ajaxModerate() for any AJAX request at all as long
	 * as the viewer was a moderator, and ajaxModerate() always ends by printing
	 * JSON and exiting. So a moderator's poll vote, rating or plugin widget on a
	 * forum page was swallowed and answered with a forum error, while the same
	 * click by an ordinary member went through. It reads as a permissions fault
	 * and is not one.
	 */
	public function anUnrelatedAjaxRequestIsNotSwallowedByModeration(AcceptanceTester $I)
	{
		$this->moderateByAjax($I, $this->ids['forumA'], array(
			'action' => 'somethingelse',
			'thread' => $this->ids['threadA'],
		));

		$I->dontSeeInSource('"status":"error"');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $forumId the forum whose page the request is made from
	 * @param array $fields
	 */
	private function moderateByAjax(AcceptanceTester $I, $forumId, array $fields)
	{
		$page = '/e107_plugins/forum/forum_viewforum.php?id='.$forumId;

		$fields['e-token'] = $I->grabForumToken($page);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, $fields);
	}
}
