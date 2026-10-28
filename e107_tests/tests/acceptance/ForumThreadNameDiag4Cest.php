<?php

/**
 * A thread's name is stored one way, whichever form wrote it, and printed as text.
 */
class ForumThreadNameDiag4Cest
{
	/** A subject carrying markup, a quote and an ampersand. */
	const SUBJECT = 'Notes <b class="x">bold</b> & "more"';

	/** A name none of these forms stored: an '&' raw and encoded, and sequences the split and move forms decode. */
	const LEGACY_NAME = 'Tom & Jerry, AT&amp;T, 100%25 %3Cb%3Esure%3C/b%3E';

	/** A name as an edit stored it before edits were converted. */
	const STORED_MARKUP = 'Stored <name-markup>as typed</name-markup>';

	/** A name carrying inline, table, image and link bbcode, and a mention of a code nothing closes. */
	const STORED_BBCODE = '[b]bbcode probe[/b] [table][tr][td]table probe[/td][/tr][/table] [img]image-probe.png[/img] [link=https://example.com/link-probe]link probe[/link] [img] lone probe';

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int */
	private $member;

	/** @var int a thread the member started */
	private $thread;

	/** @var int the member's opening post in it */
	private $openingPost;

	/** @var int the administrator's reply in it, so a topic split from it is theirs */
	private $reply;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');

		$this->ids = $I->haveForumStructure();
		$I->haveSitePrefUntilAfter('antiflood1', 0);

		$this->member = $I->haveForumMember('namemember');
		$this->thread = $I->haveForumThread('Fixture Named Thread', $this->ids['forumA'], $this->member);
		$this->openingPost = $I->haveForumPost('Opening post of the named thread', $this->thread, $this->ids['forumA'], $this->member);
		$this->reply = $I->haveForumPost('A reply in the named thread', $this->thread, $this->ids['forumA'], 1);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	/**
	 * Editing a thread's opening post stores its subject as starting the thread would have.
	 */
	public function anEditedSubjectIsStoredAsANewThreadsSubjectIs(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');
		$this->renameThroughTheEditForm($I);

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->thread, 'thread_name' => $this->startThread($I)));
	}

	/**
	 * Saving an opening post with its subject left as the edit form offers it keeps the name as it was stored.
	 */
	public function anUntouchedSubjectKeepsTheStoredName(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');

		$this->seeABodyEditKeepTheName($I);
	}

	public function aModeratorsUntouchedSubjectKeepsTheStoredName(AcceptanceTester $I)
	{
		$this->loginAsModerator($I);

		$this->seeABodyEditKeepTheName($I);
	}

	/**
	 * A topic split away under the title the split form offers is named as the thread it left.
	 */
	public function aTopicSplitUnderTheOfferedTitleKeepsTheName(AcceptanceTester $I)
	{
		$this->haveStoredName($I, self::LEGACY_NAME);
		$this->loginAsModerator($I);

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=split&id='.$this->thread.'&post='.$this->reply);
		$I->submitForm('form.forum-horizontal', array(), 'split_thread');

		$splitOff = $I->grabFromDatabase('e107_forum_post', 'post_thread', array('post_id' => $this->reply));
		$I->assertNotEquals($this->thread, $splitOff);
		$this->seeLegacyNameOn($I, $splitOff);
	}

	/**
	 * A thread moved and renamed to the title the move form offers keeps its name.
	 */
	public function aThreadMovedUnderTheOfferedTitleKeepsTheName(AcceptanceTester $I)
	{
		$this->loginAsModeratorOfBothForums($I);
		$this->haveStoredName($I, self::LEGACY_NAME);

		$this->moveThroughTheMoveForm($I, array());

		$this->seeLegacyNameOn($I, $this->thread);
	}

	/**
	 * A thread moved under a title the moderator typed is named as starting a thread would have named it.
	 */
	public function aThreadMovedUnderANewTitleIsNamedAsANewThreadIs(AcceptanceTester $I)
	{
		$this->loginAsModeratorOfBothForums($I);

		$this->moveThroughTheMoveForm($I, array('newtitle' => self::SUBJECT));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->thread, 'thread_name' => $this->startThread($I)));
	}

	/**
	 * A move posted to another form's address moves nothing, so no thread takes the name that form offered.
	 */
	public function aMovePostedToAnotherFormMovesNothing(AcceptanceTester $I)
	{
		$this->loginAsModeratorOfBothForums($I);

		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=split&id='.$this->ids['threadA'].'&post='.$this->reply, array(
			'e-token'       => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->thread),
			'move_thread'   => 1,
			'forum_move'    => $this->ids['forumB'],
			'rename_thread' => 'rename',
			'newtitle'      => 'Fixture Named Thread',
		));

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_id' => $this->ids['threadA'], 'thread_forum_id' => $this->ids['forumA'], 'thread_name' => 'Fixture Thread A',
		));
	}

	/**
	 * A topic split away by a moderator is named as starting a thread would have named it.
	 */
	public function aSplitTopicIsNamedAsANewThreadIs(AcceptanceTester $I)
	{
		$this->loginAsModerator($I);

		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=split&id='.$this->thread.'&post='.$this->reply, array(
			'e-token'          => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->thread),
			'split_thread'     => 1,
			'forum_split'      => '',
			'new_thread_title' => self::SUBJECT,
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_user' => 1, 'thread_name' => $this->startThread($I)));
	}

	public function theTopicPagePrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_viewtopic.php?id='.$this->thread);
	}

	/**
	 * The name's inline bbcode is rendered, every other code it closes is stripped to its text, and one it leaves open prints as typed.
	 */
	public function theTopicPageRendersANamesInlineBbcodeAndStripsTheRest(AcceptanceTester $I)
	{
		$this->haveStoredName($I, self::STORED_BBCODE);
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->thread);

		$I->see('bbcode probe', 'strong.bbcode');
		$I->seeInSource('bbcode probe</strong> table probe image-probe.png link probe [img] lone probe');
		$I->dontSeeElement('img[src*="image-probe"]');
		$I->dontSeeElement('a[href*="link-probe"]');
	}

	/**
	 * The site's profanity filter applies to a name as it does to the rest of a visitor's text.
	 */
	public function theTrackedTopicsListFiltersAStoredName(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('profanity_filter', 1);
		$I->haveSitePrefUntilAfter('profanity_words', 'darn');
		$I->haveSitePrefUntilAfter('profanity_replace', '[censored]');
		$this->haveStoredName($I, 'darn spammers');
		$I->haveForumSubscriber($this->member, $this->thread);
		$I->loginToForum('namemember');

		$I->amOnPage('/e107_plugins/forum/forum.php?f=track');

		$I->seeInSource('[censored] spammers');
		$I->dontSeeInSource('darn spammers');
	}

	public function theReplyFormPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');

		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->thread);
	}

	public function aReplyPreviewPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');
		$this->haveStoredName($I, self::STORED_MARKUP);

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->thread);
		$I->submitForm('#dataform', array('post' => 'A reply to preview'), 'fpreview');

		$this->seePrintedAsText($I);
	}

	/**
	 * With the forum's redirect off, as it is on a new install, a posted reply is answered with a page captioned with the name.
	 */
	public function theReplyPostedPagePrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');
		$this->haveStoredName($I, self::STORED_MARKUP);

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->thread);
		$I->submitForm('#dataform', array('post' => 'A reply to post'), 'reply');
		$I->seeInDatabase('e107_forum_post', array('post_thread' => $this->thread, 'post_entry' => 'A reply to post'));
		$I->seeCurrentUrlMatches('~/forum_post\.php~');

		$this->seePrintedAsText($I);
	}

	/**
	 * The who's online page names the thread a member's last request named, whatever URL form it took.
	 */
	public function theOnlinePagePrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('track_online', 1);
		$I->haveSitePref('antiflood1', 1);
		$this->haveStoredName($I, self::STORED_MARKUP);

		$I->loginToForum('namemember');
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?'.$this->thread);
		$I->startFollowingRedirects();
		$I->seeInDatabase('e107_online', array('online_user_id' => $this->member.'.namemember', 'online_location like' => '%forum_viewtopic.php?'.$this->thread));
		$I->logoutFromForum();

		$I->amOnPage('/online.php');

		$this->seePrintedAsText($I);
	}

	public function theReportFormPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');

		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_post.php?f=report&id='.$this->thread.'&post='.$this->reply);
	}

	public function theMoveFormPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$this->loginAsModerator($I);

		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_post.php?f=move&id='.$this->thread.'&post='.$this->openingPost);
	}

	public function theTrackedTopicsListPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->haveForumSubscriber($this->member, $this->thread);
		$I->loginToForum('namemember');

		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum.php?f=track');
	}

	public function theForumStatisticsPrintAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_stats.php');
	}

	public function thePrintViewPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/print.php?plugin:forum.'.$this->thread);
	}

	public function aMembersPostListPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');

		$this->seeStoredNameAsText($I, '/userposts.php?0.forums.'.$this->member);
	}

	public function theMostActiveTopicsPrintAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/top.php?0.active.forum.10');
	}

	public function theNewPostsListPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->loginToForum('namemember');

		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum.php?new');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page
	 */
	private function seeStoredNameAsText(AcceptanceTester $I, $page)
	{
		$this->haveStoredName($I, self::STORED_MARKUP);
		$I->amOnPage($page);

		$this->seePrintedAsText($I);
	}

	/**
	 * The name is on the page, and nowhere on it as markup.
	 *
	 * @param AcceptanceTester $I
	 */
	private function seePrintedAsText(AcceptanceTester $I)
	{
		$I->dontSeeInSource('<name-markup>');
		$I->seeInSource('&lt;name-markup');
	}

	/**
	 * Start a thread named {@see ForumThreadNameDiag4Cest::SUBJECT} through the new-thread form.
	 *
	 * @param AcceptanceTester $I
	 * @return string the name it was stored under
	 */
	private function startThread(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA']);
		$I->submitForm('#dataform', array('subject' => self::SUBJECT, 'post' => 'A thread started with the same subject'), 'newthread');

		$name = $I->grabFromDatabase('e107_forum_thread', 'thread_name', array(
			'thread_name like' => 'Notes%', 'thread_user !=' => 1, 'thread_datestamp >' => time() - 600,
		));
		$I->assertSame(0, substr_count($name, '<b'), $name);

		return $name;
	}

	/**
	 * Rename the member's thread to {@see ForumThreadNameDiag4Cest::SUBJECT} through the edit form.
	 *
	 * @param AcceptanceTester $I
	 * @return string the name it was stored under
	 */
	private function renameThroughTheEditForm(AcceptanceTester $I)
	{
		$I->amOnPage($this->editForm());
		$I->submitForm('#dataform', array('subject' => self::SUBJECT, 'post' => 'Opening post, edited'), 'update_thread');
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->openingPost, 'post_entry' => 'Opening post, edited'));

		return $I->grabFromDatabase('e107_forum_thread', 'thread_name', array('thread_id' => $this->thread));
	}

	/**
	 * Save the member's opening post with only its body changed, and see the thread keep its stored name.
	 *
	 * @param AcceptanceTester $I
	 */
	private function seeABodyEditKeepTheName(AcceptanceTester $I)
	{
		$this->haveStoredName($I, self::LEGACY_NAME);

		$I->amOnPage($this->editForm());
		$I->submitForm('#dataform', array('post' => 'Opening post, body edited'), 'update_thread');
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->openingPost, 'post_entry' => 'Opening post, body edited'));

		$this->seeLegacyNameOn($I, $this->thread);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name stored as it is, as no form of this version would store it
	 */
	private function haveStoredName(AcceptanceTester $I, $name)
	{
		$I->updateInDatabase('e107_forum_thread', array('thread_name' => $name), array('thread_id' => $this->thread));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $thread
	 */
	private function seeLegacyNameOn(AcceptanceTester $I, $thread)
	{
		$I->assertSame(self::LEGACY_NAME, $I->grabFromDatabase('e107_forum_thread', 'thread_name', array('thread_id' => $thread)));
	}

	/**
	 * Sign in as a moderator of the member's forum who may not post HTML.
	 *
	 * @param AcceptanceTester $I
	 */
	private function loginAsModerator(AcceptanceTester $I)
	{
		$I->haveForumMember('namemoderator', '253,'.\Helper\ForumFixture::CLASS_MOD_A);
		$I->loginToForum('namemoderator');
	}

	/**
	 * Sign in as a moderator of both fixture forums, so a thread can be moved from one to the other.
	 *
	 * @param AcceptanceTester $I
	 */
	private function loginAsModeratorOfBothForums(AcceptanceTester $I)
	{
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveForumMember('namemoderator', '253,'.\Helper\ForumFixture::CLASS_MOD_A.','.\Helper\ForumFixture::CLASS_MOD_B);
		$I->purgeForumPermCache();
		$I->loginToForum('namemoderator');
	}

	/**
	 * Move the member's thread into the second forum through the move form, renaming it.
	 *
	 * @param AcceptanceTester $I
	 * @param array $fields filled in beyond the destination and the rename choice
	 */
	private function moveThroughTheMoveForm(AcceptanceTester $I, array $fields)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=move&id='.$this->thread.'&post='.$this->openingPost);
		$I->submitForm('form.forum-horizontal', array_merge(array('forum_move' => $this->ids['forumB'], 'rename_thread' => 'rename'), $fields), 'move_thread');

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->thread, 'thread_forum_id' => $this->ids['forumB']));
	}

	/**
	 * @return string the edit form of the member's opening post
	 */
	private function editForm()
	{
		return '/e107_plugins/forum/forum_post.php?f=edit&id='.$this->thread.'&post='.$this->openingPost;
	}
}
