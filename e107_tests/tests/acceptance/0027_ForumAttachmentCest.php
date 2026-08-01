<?php

/**
 * Deleting a forum post must not delete anything else.
 *
 * post_attachments is written straight out of $_POST['post_attachments_json']
 * with no validation of any kind, and postDeleteAttachments() concatenated each
 * stored entry onto the poster's attachment directory and unlinked the result.
 * A member could post with a relative path, delete their own post (a supported
 * action, and before the ownership fix an unrestricted one), and have e107
 * remove any file the web user could reach, e107_config.php included.
 *
 * The whole chain was demonstrated against a running install first: an ordinary
 * reply through the real form, then a delete through the real control, and a
 * file at the docroot root was gone.
 *
 * The precondition matters and cost a false negative on the first attempt. The
 * traversal only resolves when the poster's own attachment directory exists,
 * because the operating system walks `user_000002/../..` a component at a time.
 * It exists as soon as that member has ever attached a file, so the fixture
 * creates it the way an upload would. Without that step the unlink fails for a
 * reason that has nothing to do with the defect, and the bug reads as absent.
 */
class ForumAttachmentCest
{
	const CANARY = 'e107_tests_forum_canary.txt';

	/** @var array */
	private $ids;

	/** @var int */
	private $alice;

	/** @var string */
	private $attachmentDir;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->alice = $I->haveForumMember('attachalice');

		$this->attachmentDir = $I->haveForumAttachmentDir($this->alice);
		$I->writeAppFile(self::CANARY, 'a file the forum has no business deleting');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->deleteAppFile(self::CANARY);
		$I->dropForumProbe();
	}

	/**
	 * The canary has to be reachable to begin with, or its later absence proves
	 * nothing about what the delete did.
	 */
	public function theCanaryExistsBeforeAnythingIsDeleted(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::CANARY);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * The live chain, in one test: a stored traversal, then the member removing
	 * their own post through the ordinary route.
	 */
	public function deletingAPostDoesNotDeleteFilesOutsideItsAttachmentDirectory(AcceptanceTester $I)
	{
		$postId = $I->haveForumPostWithAttachments(
			'carrier', $this->ids['threadA'], $this->ids['forumA'], $this->alice,
			array('file' => array('../../../../../../'.self::CANARY))
		);

		$I->loginToForum('attachalice');
		$this->deletePost($I, $postId);

		$I->dontSeeInDatabase('e107_forum_post', array('post_id' => $postId));

		$I->amOnPage('/'.self::CANARY);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * The image list is a second entry point into the same loop.
	 */
	public function theImageListIsConfinedTheSameWay(AcceptanceTester $I)
	{
		$postId = $I->haveForumPostWithAttachments(
			'carrier', $this->ids['threadA'], $this->ids['forumA'], $this->alice,
			array('img' => array('../../../../../../'.self::CANARY))
		);

		$I->loginToForum('attachalice');
		$this->deletePost($I, $postId);

		$I->amOnPage('/'.self::CANARY);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * A path that never reaches the sink cannot be exploited later either, so
	 * the write side drops it too. Asserted on what was stored.
	 */
	public function aPostedAttachmentPathIsNotStored(AcceptanceTester $I)
	{
		$I->loginToForum('attachalice');

		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$I->sendPostRequest($page, array(
			'post' => 'an ordinary looking reply',
			'reply' => 'Submit',
			'post_attachments_json' => json_encode(array('file' => array('../../../../../../'.self::CANARY))),
			'e-token' => $token,
		));

		// The reply itself has to land, or the assertion below is satisfied by
		// there being no post at all rather than by the path being dropped.
		$I->seeInDatabase('e107_forum_post', array('post_entry like' => '%an ordinary looking reply%'));

		$I->dontSeeInDatabase('e107_forum_post', array(
			'post_attachments like' => '%'.self::CANARY.'%',
		));
	}

	/**
	 * Uploads store an array per entry, and only sendFile() ever accounted for
	 * that shape. Here the array was concatenated onto the directory, so a real
	 * attachment was "deleted" as a file literally named Array while the actual
	 * upload was orphaned and its record then cleared, leaving nothing to find
	 * it by. The delete must reach the file the record names.
	 */
	public function anAttachmentStoredAsAnArrayIsStillFound(AcceptanceTester $I)
	{
		$upload = $this->attachmentDir.'upload.txt';
		$I->writeAppFile($upload, 'the attachment this post actually carries');

		// It must be there to begin with, or its later absence means nothing.
		$I->amOnPage('/'.$upload);
		$I->seeResponseCodeIs(200);

		$postId = $I->haveForumPostWithAttachments(
			'carrier', $this->ids['threadA'], $this->ids['forumA'], $this->alice,
			array('file' => array(array('file' => 'upload.txt', 'name' => 'upload.txt', 'size' => 12)))
		);

		$I->loginToForum('attachalice');
		$this->deletePost($I, $postId);

		$I->dontSeeInDatabase('e107_forum_post', array('post_id' => $postId));

		$I->amOnPage('/'.$upload);
		$I->seeResponseCodeIs(404);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $postId
	 */
	private function deletePost(AcceptanceTester $I, $postId)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'  => 'deletepost',
			'post'    => $postId,
			'e-token' => $token,
		));
	}
}
