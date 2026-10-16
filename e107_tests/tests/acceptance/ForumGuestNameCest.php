<?php

/**
 * A guest's name is stored as their subject is, and printed as text wherever a page names the guest.
 */
class ForumGuestNameCest
{
	/** A name with markup in it, which still fits the 30-character columns once converted. */
	const TYPED = 'Ann <b>Lee</b> "Co"';

	/** A name as a guest stored it before names were converted. */
	const STORED = '<guest-x>typed</guest-x>';

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var bool whether a case configured List New Items */
	private $listConfigured = false;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$I->haveSitePrefUntilAfter('antiflood1', 0);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();

		if($this->listConfigured)
		{
			$I->havePluginPrefs('list_new', array());
			$I->dropPluginProbe();
		}

		$I->dropForumProbe();
	}

	public function aGuestsNameIsStoredAsTheirSubjectIs(AcceptanceTester $I)
	{
		$name = $this->startThreadAsGuest($I);

		$I->seeInDatabase('e107_forum_thread', array('thread_name' => $name, 'thread_user_anon' => $name, 'thread_lastuser_anon' => $name));
		$I->seeInDatabase('e107_forum_post', array('post_entry' => 'A thread a guest started', 'post_user_anon' => $name));
	}

	public function aGuestsQuickReplyStoresTheNameAsTheirSubjectIs(AcceptanceTester $I)
	{
		$name = $this->startThreadAsGuest($I);
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'   => 'quickreply',
			'thread'   => $this->ids['threadA'],
			'post'     => $this->ids['forumA'],
			'text'     => 'A quick reply from a guest',
			'insert'   => 0,
			'anonname' => self::TYPED,
			'e-token'  => $token,
		));

		$I->seeInSource('"status":"ok"');
		$I->seeInDatabase('e107_forum_post', array('post_thread' => $this->ids['threadA'], 'post_user_anon' => $name));
		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_lastuser_anon' => $name));
		$I->seeInDatabase('e107_forum', array('forum_id' => $this->ids['forumA'], 'forum_lastpost_user_anon' => $name));
	}

	/**
	 * The posting form's preview under a theme without Bootstrap, whose template prints the name typed.
	 */
	public function aPreviewUnderALegacyThemePrintsTheNameAsText(AcceptanceTester $I)
	{
		$I->haveThemeFixture('tpstate3_plain');
		$I->haveSiteTheme('tpstate3_plain');
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA']);
		$I->submitForm('#dataform', array('anonname' => self::STORED, 'subject' => 'Previewed', 'post' => 'A previewed post'), 'fpreview');

		$I->dontSeeInSource('<guest-x');
		$I->seeInSource('&lt;guest-x');
	}

	public function theForumIndexPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum.php');
	}

	public function theThreadListPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);
	}

	public function theMostActiveTopicsPrintAStoredNameAsText(AcceptanceTester $I)
	{
		$this->seeStoredNameAsText($I, '/top.php?0.active.forum.10');
	}

	public function theListingPrintsAStoredNameAsText(AcceptanceTester $I)
	{
		$I->havePluginInstalled('list_new');
		$I->havePluginPrefs('list_new', $this->listPrefs());
		$this->listConfigured = true;

		$this->seeStoredNameAsText($I, '/e107_plugins/list_new/list.php');
	}

	/**
	 * Start a thread as a guest, with {@see ForumGuestNameCest::TYPED} as both its subject and the guest's name.
	 *
	 * @param AcceptanceTester $I
	 * @return string the subject as it was stored
	 */
	private function startThreadAsGuest(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA']);
		$I->submitForm('#dataform', array('anonname' => self::TYPED, 'subject' => self::TYPED, 'post' => 'A thread a guest started'), 'newthread');

		$name = $I->grabFromDatabase('e107_forum_thread', 'thread_name', array('thread_name like' => 'Ann Lee%', 'thread_forum_id' => $this->ids['forumA']));
		$I->assertStringNotContainsString('<', $name);

		return $name;
	}

	/**
	 * A guest started a thread in forum A and posted in it last, under a name stored as typed, and $page names them.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page
	 */
	private function seeStoredNameAsText(AcceptanceTester $I, $page)
	{
		$thread = $I->haveForumThread('A guest thread', $this->ids['forumA'], 0);
		$I->haveForumPost('A guest post', $thread, $this->ids['forumA'], 0);
		$I->updateInDatabase('e107_forum_thread', array(
			'thread_user_anon' => self::STORED, 'thread_lastuser_anon' => self::STORED,
			'thread_views' => 100000, 'thread_total_replies' => 1, 'thread_lastpost' => time(),
		), array('thread_id' => $thread));
		$I->updateInDatabase('e107_forum', array(
			'forum_lastpost_user' => 0, 'forum_lastpost_user_anon' => self::STORED, 'forum_lastpost_info' => time().'.'.$thread,
		), array('forum_id' => $this->ids['forumA']));

		$I->amOnPage($page);

		$I->dontSeeInSource('<guest-x');
		$I->seeInSource('&lt;guest-x');
	}

	/**
	 * List New Items' recent page with the forum section and its authors, the least it renders on PHP 8.
	 *
	 * @return array
	 */
	private function listPrefs()
	{
		return array(
			'recent_page_welcometext'   => '',
			'recent_page_colomn'        => '1',
			'forum_recent_page_caption' => 'Fixture forum',
			'forum_recent_page_display' => '1',
			'forum_recent_page_open'    => '1',
			'forum_recent_page_author'  => '1',
			'forum_recent_page_amount'  => '20',
			'forum_recent_page_order'   => '1',
		);
	}
}
