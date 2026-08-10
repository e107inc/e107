<?php

/**
 * A subscription is not a licence to keep reading.
 *
 * trackEmail() selected every forum_track row for the thread with a LEFT JOIN
 * onto user and mailed the post body to each address. It asked nothing about
 * the forum the thread sits in, so three populations kept receiving the full
 * text of posts they can no longer open: subscriptions taken out before
 * acquisition was gated (PR #5861), subscriptions to forums whose class was
 * tightened afterwards, and subscribers since removed from the class. The LEFT
 * JOIN also yielded a recipient with no address at all when the subscriber's
 * account was gone. forum.php?f=track, the tracked-topics listing, read the
 * same rows and showed the thread name, the forum name and who posted in it
 * last to the same lapsed subscriber.
 *
 * Measured at the queue rather than at the transport. Above five recipients
 * e107MailManager::sendEmails() stops sending and writes the recipient list to
 * mail_recipients, which is the one place the exact set is recorded and
 * readable. That is why every fixture thread has more than five allowed
 * subscribers: the queue has to be reached both before the fix and after it, or
 * the set would be measured on one side only.
 *
 * Four forums, because the view permission has four shapes and a suite that
 * only covers one of them goes green while the others leak:
 *
 *  - D is restricted by its own class, under a public category.
 *  - E is public in its own right and sits under a restricted category, which
 *    is how a staff area is normally built and the leg e107 code habitually
 *    forgets.
 *  - M is granted to e_UC_MEMBER, which is what most sites actually run.
 *  - N is granted to e_UC_NEWUSER, which is answered by the join date and not
 *    only by user_class.
 *
 * Three of the six allowed readers are members of D's class outright and three
 * hold a child class that inherits it. FIND_IN_SET on user_class, which is what
 * notify::send() does, would see the first three and miss the second three, so
 * the positive control is also the guard on the hierarchy.
 */
class ForumTrackDeliveryCest
{
	/** Sits under no other class; forum D is granted to this one. */
	const CLASS_STAFF = 202;

	/** Sits under CLASS_STAFF, so its members hold CLASS_STAFF too. */
	const CLASS_SENIOR = 203;

	/** e_UC_MEMBER. */
	const CLASS_MEMBER = 253;

	/** e_UC_NEWUSER, held by join date rather than by user_class alone. */
	const CLASS_NEWUSER = 247;

	/** user_new_period, in days. */
	const NEW_USER_DAYS = 30;

	/** USER_EMAIL_BOUNCED. */
	const BAN_BOUNCED = 3;

	/** A subscriber whose account is gone. */
	const ORPHAN_USER = 999001;

	/** @var array */
	private $ids;

	/** @var int the forum only staff may read */
	private $forumD;

	/** @var int */
	private $threadD;

	/** @var int a public forum under a category only staff may read */
	private $forumE;

	/** @var int */
	private $threadE;

	/** @var int the forum granted to e_UC_MEMBER */
	private $forumM;

	/** @var int */
	private $threadM;

	/** @var int the forum granted to e_UC_NEWUSER */
	private $forumN;

	/** @var int */
	private $threadN;

	/** @var string[] addresses of the subscribers who may still read D and E */
	private $readers = array();

	/** @var string[] the three of those who hold the class by inheritance */
	private $inheritors = array();

	/** @var int no longer in the class D is granted to */
	private $lapsed;

	/** @var int a member whose probationary period is long over */
	private $veteran;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);
		$I->haveForumMailDryRun(true);
		$I->haveSitePref('user_new_period', self::NEW_USER_DAYS);
		$I->resetForumMailQueue();

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');
		$I->haveUserClass(self::CLASS_SENIOR, 'fixture_senior', self::CLASS_STAFF,
			self::CLASS_STAFF.','.self::CLASS_SENIOR);

		$this->ids = $I->haveForumStructure();

		// Readable and postable by staff only, under the public category so the
		// parent leg of the permission is granted to everyone and the forum's
		// own class is what decides.
		$this->forumD = $I->haveForum('Fixture Forum D', 'fixture-forum-d',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 4, self::CLASS_STAFF);
		$this->threadD = $I->haveForumThread('Fixture Thread D', $this->forumD, 1);
		$I->haveForumPost('Opening post in D', $this->threadD, $this->forumD, 1);

		// The mirror image: the forum itself is public and the category above it
		// is not, so only the parent leg refuses anybody.
		$categoryE = $I->haveForum('Fixture Category E', 'fixture-category-e',
			0, 0, 5, self::CLASS_STAFF);
		$this->forumE = $I->haveForum('Fixture Forum E', 'fixture-forum-e',
			$categoryE, \Helper\ForumFixture::CLASS_MOD_B, 6, 0);
		$this->threadE = $I->haveForumThread('Fixture Thread E', $this->forumE, 1);
		$I->haveForumPost('Opening post in E', $this->threadE, $this->forumE, 1);

		$this->forumM = $I->haveForum('Fixture Forum M', 'fixture-forum-m',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_B, 7, self::CLASS_MEMBER);
		$this->threadM = $I->haveForumThread('Fixture Thread M', $this->forumM, 1);
		$I->haveForumPost('Opening post in M', $this->threadM, $this->forumM, 1);

		$this->forumN = $I->haveForum('Fixture Forum N', 'fixture-forum-n',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 8, self::CLASS_NEWUSER);
		$this->threadN = $I->haveForumThread('Fixture Thread N', $this->forumN, 1);
		$I->haveForumPost('Opening post in N', $this->threadN, $this->forumN, 1);

		$I->haveForumMember('trackposter', '253,'.self::CLASS_STAFF);

		$this->readers = array();
		$this->inheritors = array();

		foreach (array(1, 2, 3) as $n)
		{
			$this->readers[] = $this->subscribe($I, 'trackreader'.$n, '253,'.self::CLASS_STAFF);
		}

		foreach (array(4, 5, 6) as $n)
		{
			$address = $this->subscribe($I, 'trackreader'.$n, '253,'.self::CLASS_SENIOR);
			$this->readers[] = $address;
			$this->inheritors[] = $address;
		}

		// Still subscribed, no longer in the class D and E are granted to. Kept
		// out of the public forum A so the orphan count there stays readable,
		// and given a subscription in the public forum B so the tracked-topics
		// listing has something of theirs to render.
		$this->lapsed = $I->haveForumMember('tracklapsed', '253');
		$I->haveForumSubscriber($this->lapsed, $this->ids['threadB']);
		$I->haveForumSubscriber($this->lapsed, $this->threadD);
		$I->haveForumSubscriber($this->lapsed, $this->threadE);
		$I->haveForumSubscriber($this->lapsed, $this->threadM);

		// Two accounts forum M is granted to that the class list cannot find:
		// one carrying no user_class at all, one whose address has bounced.
		$I->haveForumSubscriber($I->haveForumMember('tracknoclass', ''), $this->threadM);
		$I->haveForumSubscriber(
			$I->haveForumMember('trackbounced', '253', null, self::BAN_BOUNCED), $this->threadM);

		// A member out of the probationary period, so N is closed to them.
		$this->veteran = $I->haveForumMember('trackveteran', '253',
			time() - ((self::NEW_USER_DAYS + 370) * 86400));
		$I->haveForumSubscriber($this->veteran, $this->threadN);

		// And a subscription belonging to an account that no longer exists.
		$I->haveForumSubscriber(self::ORPHAN_USER, $this->threadD);
		$I->haveForumSubscriber(self::ORPHAN_USER, $this->ids['threadA']);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('trackposter');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumMailDryRun(false);
		$I->haveSitePref('user_new_period', null);
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The fixture has to keep the lapsed subscriber out of the forum, or the
	 * refusals below are satisfied by a restriction that was never there. Both
	 * halves, so "denied" is told apart from "the page never rendered".
	 */
	public function theLapsedSubscriberCannotOpenTheForumTheyAreSubscribedTo(AcceptanceTester $I)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$this->threadD;

		$I->amOnPage($page);
		$I->see('Opening post in D');

		$I->logoutFromForum();
		$I->loginToForum('tracklapsed');

		$I->amOnPage($page);
		$I->dontSee('Opening post in D');

		$I->seeInDatabase('e107_forum_track', array(
			'track_userid' => $this->lapsed, 'track_thread' => $this->threadD,
		));
	}

	/**
	 * The positive control. A fix that mailed nobody would satisfy every
	 * assertion in this file except this one and the next.
	 */
	public function everySubscriberWhoCanStillReadIsMailedTheReply(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply the subscribers are entitled to', $this->forumD, $this->threadD);

		foreach ($this->readers as $address)
		{
			$I->seeInDatabase('e107_mail_recipients', array('mail_recipient_email' => $address));
		}

		$I->seeInDatabase('e107_mail_content', array(
			'mail_title' => 'FORUM TRACKING',
			'mail_body like' => '%a reply the subscribers are entitled to%',
		));
	}

	/**
	 * The half of the control that a flat FIND_IN_SET would fail. These three
	 * were never given the forum's class; they hold it because the class they
	 * were given sits under it, which is what USERCLASS_LIST resolves for a
	 * signed-in caller and what delivery has to resolve for everybody else.
	 */
	public function aMemberOfAChildClassIsMailedAForumGrantedToItsParent(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply the senior staff are entitled to', $this->forumD, $this->threadD);

		foreach ($this->inheritors as $address)
		{
			$I->seeInDatabase('e107_mail_recipients', array('mail_recipient_email' => $address));
		}
	}

	/**
	 * The defect. The subscription is still there, the forum is not readable,
	 * and the body of the reply went out anyway.
	 */
	public function aSubscriberWhoLostReadAccessIsNotMailedTheReply(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply that is none of the lapsed subscriber business',
			$this->forumD, $this->threadD);

		$I->seeInDatabase('e107_mail_content', array(
			'mail_title' => 'FORUM TRACKING',
			'mail_body like' => '%none of the lapsed subscriber business%',
		));

		$I->dontSeeInDatabase('e107_mail_recipients', array(
			'mail_recipient_email' => 'tracklapsed@example.com',
		));
	}

	/**
	 * The same refusal through the other leg. Forum E is public; the category
	 * above it is not, and _getForumPermList() requires both. A predicate that
	 * reads only the forum's own class passes every other test in this file and
	 * still serves the full text of a private area to anyone who once
	 * subscribed to it.
	 */
	public function aSubscriberBelowARestrictedCategoryIsNotMailedTheReply(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply from inside the restricted category', $this->forumE, $this->threadE);

		$I->seeInDatabase('e107_mail_content', array(
			'mail_title' => 'FORUM TRACKING',
			'mail_body like' => '%a reply from inside the restricted category%',
		));

		foreach ($this->readers as $address)
		{
			$I->seeInDatabase('e107_mail_recipients', array('mail_recipient_email' => $address));
		}

		$I->dontSeeInDatabase('e107_mail_recipients', array(
			'mail_recipient_email' => 'tracklapsed@example.com',
		));
	}

	/**
	 * The LEFT JOIN, on its own terms. A track row naming a deleted account
	 * produced a recipient with a NULL address, counted among the recipients of
	 * the mail and rejected by the queue as "Empty Recipient Email".
	 *
	 * Asserted on the public forum, because that is the only place the join is
	 * load bearing: anywhere the class predicate is more than TRUE, a row with
	 * no user answers NULL and is refused by the predicate instead, so a join
	 * relaxed back to LEFT would go unnoticed.
	 */
	public function anOrphanedSubscriptionNamesNoRecipient(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply with nobody missing from its audience',
			$this->ids['forumA'], $this->ids['threadA']);

		$I->seeInDatabase('e107_mail_content', array(
			'mail_title' => 'FORUM TRACKING',
			'mail_total_count' => count($this->readers),
		));

		$I->seeNumRecords(count($this->readers), 'e107_mail_recipients', array(
			'mail_detail_id' => $this->grabTrackMailId($I),
		));
	}

	/**
	 * Refusing to mail a subscription is not a reason to destroy it. What a
	 * subscription entitles its holder to moves whenever a forum's class or a
	 * member's does, and it moves back; a delete taken on the delivery
	 * predicate would take a reversible permission change and make it permanent
	 * for every subscriber it touched.
	 */
	public function aRefusedSubscriptionSurvivesAndIsHonouredWhenAccessReturns(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply sent while the subscriber is locked out',
			$this->forumD, $this->threadD);

		$I->dontSeeInDatabase('e107_mail_recipients', array(
			'mail_recipient_email' => 'tracklapsed@example.com',
		));

		$I->seeInDatabase('e107_forum_track', array(
			'track_userid' => $this->lapsed, 'track_thread' => $this->threadD,
		));
		$I->seeInDatabase('e107_forum_track', array(
			'track_userid' => self::ORPHAN_USER, 'track_thread' => $this->threadD,
		));

		$I->updateInDatabase('e107_user', array('user_class' => '253,'.self::CLASS_STAFF),
			array('user_id' => $this->lapsed));
		$I->resetForumMailQueue();

		$this->reply($I, 'a reply sent once the subscriber is back in the class',
			$this->forumD, $this->threadD);

		$I->seeInDatabase('e107_mail_recipients', array(
			'mail_recipient_email' => 'tracklapsed@example.com',
		));
	}

	/**
	 * The branch most sites run on. Forum M is granted to e_UC_MEMBER, which
	 * _setClassList() hands to every account whatever user_class says and
	 * whatever state the account is in, so the predicate has to answer it from
	 * the existence of the row and not from the class list: a member with no
	 * user_class at all reads it, and so does one whose address has bounced.
	 *
	 * Deliverability is not readability. Whether a bounced or banned address is
	 * worth writing to is e107MailManager's question, and answering it here
	 * would make delivery depend on which class the forum happens to use.
	 */
	public function aMembersOnlyForumIsMailedToEveryAccountThatMayReadIt(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply every member is entitled to', $this->forumM, $this->threadM);

		foreach ($this->readers as $address)
		{
			$I->seeInDatabase('e107_mail_recipients', array('mail_recipient_email' => $address));
		}

		foreach (array('tracklapsed', 'tracknoclass', 'trackbounced') as $name)
		{
			$I->seeInDatabase('e107_mail_recipients', array(
				'mail_recipient_email' => $name.'@example.com',
			));
		}
	}

	/**
	 * e_UC_NEWUSER is held by join date as well as by user_class
	 * (e_user_model::isNewUser()), so a predicate that only reads user_class
	 * mails a probationary forum to whoever the site denies it to and to nobody
	 * else it grants it to.
	 */
	public function aNewUsersForumMailsOnlyThoseStillInTheProbationaryPeriod(AcceptanceTester $I)
	{
		$this->reply($I, 'a reply for the new arrivals', $this->forumN, $this->threadN);

		foreach ($this->readers as $address)
		{
			$I->seeInDatabase('e107_mail_recipients', array('mail_recipient_email' => $address));
		}

		$I->dontSeeInDatabase('e107_mail_recipients', array(
			'mail_recipient_email' => 'trackveteran@example.com',
		));

		$I->seeInDatabase('e107_forum_track', array(
			'track_userid' => $this->veteran, 'track_thread' => $this->threadN,
		));
	}

	/**
	 * The other route to the same rows. forum.php?f=track lists the caller's own
	 * subscriptions and renders the thread name, the forum name and who posted
	 * in it last, none of which the caller may still see. For a private staff
	 * area the titles and the last poster are the sensitive part, and this is
	 * where a stale row is visible to the person holding it whether or not
	 * anyone ever replies again.
	 */
	public function theTrackedTopicsListingHidesAForumTheSubscriberMayNoLongerRead(AcceptanceTester $I)
	{
		$page = '/e107_plugins/forum/forum.php?f=track';

		$I->logoutFromForum();
		$I->loginToForum('trackreader1');

		$I->amOnPage($page);
		$I->see('Fixture Thread D');
		$I->see('Fixture Forum D');

		$I->logoutFromForum();
		$I->loginToForum('tracklapsed');

		$I->amOnPage($page);
		$I->see('Fixture Thread B');
		$I->dontSee('Fixture Thread D');
		$I->dontSee('Fixture Forum D');
		$I->dontSee('Fixture Thread E');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string $classes
	 * @return string the member's address
	 */
	private function subscribe(AcceptanceTester $I, $name, $classes)
	{
		$userId = $I->haveForumMember($name, $classes);

		foreach (array($this->ids['threadA'], $this->threadD, $this->threadE,
			$this->threadM, $this->threadN) as $threadId)
		{
			$I->haveForumSubscriber($userId, $threadId);
		}

		return $name.'@example.com';
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int mail_source_id of the tracking mail this test queued
	 */
	private function grabTrackMailId(AcceptanceTester $I)
	{
		return (int) $I->grabFromDatabase('e107_mail_content', 'mail_source_id', array(
			'mail_title' => 'FORUM TRACKING',
		));
	}

	/**
	 * Post a reply the way the topic page does, which is the route that calls
	 * trackEmail().
	 *
	 * @param AcceptanceTester $I
	 * @param string $text unique per test; isDuplicatePost() matches on the body
	 * @param int $forumId
	 * @param int $threadId
	 */
	private function reply(AcceptanceTester $I, $text, $forumId, $threadId)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$threadId;

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'  => 'quickreply',
			'insert'  => 0,
			'post'    => $forumId,
			'thread'  => $threadId,
			'text'    => $text,
			'e-token' => $I->grabForumToken($page),
		));

		$I->seeInDatabase('e107_forum_post', array(
			'post_thread' => $threadId,
			'post_entry like' => '%'.$text.'%',
		));
	}
}
