<?php

/**
 * A new thread's poll is created with the thread, and nothing else.
 *
 * The forum hands a thread's poll to poll::submit_poll(2), which read the id of
 * the poll to write from the request. A new-thread form that named an existing
 * poll therefore rewrote it, whoever had made it and whatever thread or menu it
 * belonged to, while the thread went without one of its own.
 *
 * The member is put in the forum's poll class for every test but the one about
 * that class, so that the poll code runs and the only thing between her and
 * someone else's poll is the rule under test.
 */
class ForumPollCest
{
	/** e_UC_MEMBER and e_UC_NOBODY, which this process does not define. */
	const CLASS_MEMBER = 253;
	const CLASS_NOBODY = 255;

	const CALLER_FILE = 'e107_tests_poll_caller.php';

	/** @var array */
	private $ids;

	/** @var int the site's main poll, made by an administrator */
	private $mainPoll;

	/** @var string the forum's poll class before this Cest changed it */
	private $pollClassWas;

	/** @var string the site's flood switch before this Cest changed it */
	private $antifloodWas;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('poll');
		$I->haveForumCsrfMode(2);

		$this->ids = $I->haveForumStructure();

		$this->mainPoll = $I->haveInDatabase('e107_polls', array(
			'poll_datestamp' => time() - 86400, 'poll_start_datestamp' => 0, 'poll_end_datestamp' => 0,
			'poll_admin_id' => 1, 'poll_title' => 'Keep the dark theme?',
			'poll_options' => 'Keep it'.chr(1).'Drop it'.chr(1), 'poll_votes' => '40'.chr(1).'2'.chr(1),
			'poll_ip' => '', 'poll_type' => 1, 'poll_comment' => 1, 'poll_allow_multiple' => 0,
			'poll_result_type' => 0, 'poll_vote_userclass' => 0, 'poll_storage_method' => 1,
		));

		// Each test starts a thread, and the flood check refuses one that follows
		// the newest thread in the table within seconds, whoever started it.
		$this->antifloodWas = $I->haveSitePref('antiflood1', 0);
		$this->pollClassWas = $I->havePluginPref('forum', 'poll', self::CLASS_MEMBER);

		$I->haveForumMember('pollmember');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('pollmember');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->havePluginPref('forum', 'poll', $this->pollClassWas === '' ? null : $this->pollClassWas);
		$I->haveSitePref('antiflood1', $this->antifloodWas === '' ? null : $this->antifloodWas);
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The second assertion is the control: the thread still gets a poll of its
	 * own, so a fix that refused the poll altogether fails here too.
	 */
	public function aNewThreadCannotRewriteAnExistingPoll(AcceptanceTester $I)
	{
		$threadId = $this->startThread($I, 'A thread naming a poll', array(
			'poll_id' => $this->mainPoll, 'pollUserclass' => self::CLASS_NOBODY,
		));

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the dark theme?',
			'poll_type' => 1, 'poll_vote_userclass' => 0,
		));
		$I->seeInDatabase('e107_polls', array(
			'poll_datestamp' => $threadId, 'poll_type' => 2, 'poll_title' => 'Lunch?',
		));
	}

	/**
	 * The other half of the rule. The poll administration page edits a poll by
	 * naming it, so a fix that ignored the id everywhere would turn every edit
	 * into a second poll and close the one being edited.
	 */
	public function anAdministratorStillEditsAPollInPlace(AcceptanceTester $I)
	{
		$I->logoutFromForum();
		$I->loginAsAdmin();
		$I->amOnPage('/e107_plugins/poll/admin_config.php?mode=create');
		$polls = $I->grabNumRecords('e107_polls');

		$I->sendPostRequest('/e107_plugins/poll/admin_config.php', array(
			'e-token'        => $I->grabToken(),
			'submit'         => 1,
			'poll_id'        => $this->mainPoll,
			'poll_title'     => 'Keep the light theme?',
			'poll_option'    => array('Keep it', 'Drop it'),
			'pollUserclass'  => 0,
			'multipleChoice' => 0,
			'showResults'    => 0,
			'storageMethod'  => 1,
			'poll_comment'   => 1,
		));

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the light theme?',
			'poll_type' => 1, 'poll_vote_userclass' => 0,
		));
		$I->seeNumRecords($polls, 'e107_polls');
	}

	/**
	 * A page that calls {@see poll::submit_poll()} without a mode writes the poll the request names only for a poll administrator.
	 */
	public function aCallerNamingNoModeCannotLetAMemberRewriteAPoll(AcceptanceTester $I)
	{
		$I->haveProbe(self::CALLER_FILE, $this->callerSource());
		$polls = $I->grabNumRecords('e107_polls');

		$this->submitThroughCaller($I, 'Lunch?');

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the dark theme?',
			'poll_type' => 1, 'poll_vote_userclass' => 0,
		));
		$I->seeNumRecords($polls, 'e107_polls');

		$I->logoutFromForum();
		$I->loginAsAdmin();
		$this->submitThroughCaller($I, 'Keep the light theme?');

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the light theme?',
		));
	}

	/**
	 * The poll plugin's own administration permission is what admits a caller of {@see poll::submit_poll()} without a mode, not any other.
	 */
	public function aCallerNamingNoModeAdmitsOnlyThePollPluginsAdministrators(AcceptanceTester $I)
	{
		$I->haveProbe(self::CALLER_FILE, $this->callerSource());

		$this->loginAsPluginAdmin($I, 'forumadmin', 'forum');
		$this->submitThroughCaller($I, 'Lunch?');

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the dark theme?', 'poll_vote_userclass' => 0,
		));

		$this->loginAsPluginAdmin($I, 'polladmin', 'poll');
		$this->submitThroughCaller($I, 'Keep the light theme?');

		$I->seeInDatabase('e107_polls', array(
			'poll_id' => $this->mainPoll, 'poll_title' => 'Keep the light theme?',
		));
	}

	/**
	 * The forum's poll setting decides who may attach a poll, and the form and
	 * the preview have always asked it. Saving the thread has to ask it too. The
	 * first test is the control: with the member in the class, the poll is made.
	 */
	public function aMemberOutsideTheForumsPollClassCannotAttachAPoll(AcceptanceTester $I)
	{
		$I->havePluginPref('forum', 'poll', self::CLASS_NOBODY);

		$threadId = $this->startThread($I, 'A thread where polls are off', array());

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $threadId));
		$I->dontSeeInDatabase('e107_polls', array('poll_datestamp' => $threadId));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $subject unique per test, which is how the thread is found again
	 * @param array $extra fields added to, or replacing, an ordinary submission
	 * @return int the new thread's id
	 */
	private function startThread(AcceptanceTester $I, $subject, array $extra)
	{
		$page = '/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA'];

		$I->sendPostRequest($page, array_merge(array(
			'e-token'     => $I->grabForumToken($page),
			'subject'     => $subject,
			'post'        => 'The body of '.$subject,
			'newthread'   => 'Post thread',
			'action'      => 'nt',
			'poll_title'  => 'Lunch?',
			'poll_option' => array('Soup', 'Salad'),
		), $extra));

		return (int) $I->grabFromDatabase('e107_forum_thread', 'thread_id', array('thread_name' => $subject));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 */
	private function submitThroughCaller(AcceptanceTester $I, $title)
	{
		$token = $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA']);

		$I->sendPostRequest('/'.self::CALLER_FILE, array(
			'e-token'       => $token,
			'poll_id'       => $this->mainPoll,
			'poll_title'    => $title,
			'poll_option'   => array('Soup', 'Salad'),
			'pollUserclass' => self::CLASS_NOBODY,
		));
		$I->seeInSource('PROBE_OK');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name login name, and the password
	 * @param string $plugin the one plugin the administrator may administer
	 */
	private function loginAsPluginAdmin(AcceptanceTester $I, $name, $plugin)
	{
		$pluginId = $I->grabFromDatabase('e107_plugin', 'plugin_id', array('plugin_path' => $plugin));

		$I->logoutFromForum();
		$I->haveMember($name, $name, array('user_admin' => 1, 'user_perms' => 'P'.$pluginId));
		$I->loginAsAdmin($name, $name);
	}

	/**
	 * @return string a page outside core that hands its form to the poll plugin
	 */
	private function callerSource()
	{
		return <<<'PHP'
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(e_PLUGIN.'poll/poll_class.php');
$poll = new poll;
$poll->submit_poll();
echo "PROBE_OK\n";
PHP;
	}
}
