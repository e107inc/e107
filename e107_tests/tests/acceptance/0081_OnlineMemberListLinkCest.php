<?php

/**
 * Where the online plugin's member links send a visitor.
 *
 * {@see e_online::goOnline()} assembled MEMBER_LIST by hand, as
 * SITEURL."user.php?id.{$id}", and online.php built its newest-member link the
 * same way, as e_BASE."user.php?id.{$id}", while every shortcode rendering a
 * profile link beside them goes through {@see eUrl::create()} on the
 * user/profile/view route. On a site with rewritten profile URLs the two
 * disagree, so those links pointed somewhere the rest of the site did not.
 *
 * The suite installs with url_config/user on core, the legacy profile, and
 * there {@see core_user_url::create()} answers user.php?id.N and reads no
 * parameter but id. The route is then the same one the hand-built strings
 * produced, so a run left on that profile can only tell fix from bug by the
 * prefix. Each test switches the module to core/rewrite and puts the old value
 * back afterwards: that is the other profile Admin > URLs offers for it, its
 * catch-all rule is the first thing in the tree that reads the name parameter,
 * and it is the configuration the report describes.
 *
 * MEMBER_LIST is a constant defined during the class2.php bootstrap of every
 * request, so a probe in the docroot reaches it without a menu having to be
 * placed in a layout; the online menu ships unassigned (menu_location 0 in
 * e107_core/xml/default_install.xml).
 *
 * online.php needs two things before it will render its newest-member line: a
 * template that assigns its blocks unconditionally, because online.php blanks
 * them before requiring one and the core template's own defaults are guarded by
 * !isset() (that blanking is #6108, fixed separately), and a second member,
 * because the line sits behind a $total_members > 1 gate. The fixture supplies
 * both, in the shape 0055_ForumNamesOutsideTheFeedCest established, including
 * asking the running site for its theme directory rather than naming one: this
 * suite installs more than once and the last install decides the theme.
 */
class OnlineMemberListLinkCest
{
	const PROBE_FILE = 'e107_tests_online_memberlist_probe.php';

	/**
	 * Seeded a minute into the future, so it sorts first under online.php's
	 * `user_join DESC`, which has no secondary key; a bare time() could tie
	 * with a row another Cest seeded in the same second.
	 */
	const NEWEST_NAME = 'e107testsnewest';

	const PROFILE_PREF = 'url_config/user';

	/** The rewritten user profile, as url_locations offers it to Admin > URLs. */
	const REWRITE_PROFILE = 'core/rewrite';

	/** @var int */
	private $newestId;

	/** @var string|null the active theme's online template, as _before() resolved it */
	private $templateFile;

	/** @var string */
	private $newestLink;

	/** @var string|null the url_config/user value the site was found on */
	private $previousProfile;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		$this->templateFile = $I->grabActiveThemeDir().'online_template.php';
		$I->writeAppFile($this->templateFile, $this->templateSource());

		$this->newestId = $I->haveForumMember(self::NEWEST_NAME, '253', time() + 60);

		$found = $I->haveSitePref(self::PROFILE_PREF, self::REWRITE_PROFILE);
		if($this->previousProfile === null)
		{
			$this->previousProfile = $found;
		}

		$this->newestLink = $this->grabNewestLink($I);
	}

	public function _after(AcceptanceTester $I)
	{
		try
		{
			if($this->previousProfile !== null)
			{
				$I->haveSitePref(self::PROFILE_PREF, $this->previousProfile);
			}
		}
		finally
		{
			$I->dropForumProbe();
			if($this->templateFile !== null) { $I->deleteAppFile($this->templateFile); }
			$I->deleteAppFile(self::PROBE_FILE);
		}
	}

	/**
	 * The requesting member is recorded online before the list is selected, so
	 * the probe's own request puts the admin in MEMBER_LIST.
	 */
	public function theMemberListLinksThroughTheUrlHandler(AcceptanceTester $I)
	{
		$I->wantTo('build the online member list through the URL handler');

		$I->loginAsAdmin();
		$I->amOnPage('/'.self::PROBE_FILE);

		$I->seeInSource("<a href='".$this->grabProbeValue($I, 'HANDLER')."'>");
	}

	/**
	 * Positive control. Emptying the list, or dropping the anchor text, would
	 * satisfy the test above by producing nothing for it to disagree with.
	 */
	public function theMemberListStillNamesTheMember(AcceptanceTester $I)
	{
		$I->wantTo('still name the member in the online member list');

		$I->loginAsAdmin();
		$I->amOnPage('/'.self::PROBE_FILE);

		$I->seeInSource('>'.$this->grabProbeValue($I, 'MEMBER_NAME').'</a>');
	}

	/**
	 * goOnline() builds MEMBER_LIST under no USER gate, so its links are what
	 * this change alters for a signed-out visitor: they were absolute, from
	 * SITEURL, and are now whatever the handler answers. A member has to be
	 * online for the list to hold anything, hence the sign-in before the
	 * cookies go.
	 */
	public function theMemberListLinksThroughTheUrlHandlerForAGuest(AcceptanceTester $I)
	{
		$I->wantTo('build the online member list through the URL handler for a guest');

		$I->loginAsAdmin();
		$I->amOnPage('/'.self::PROBE_FILE);
		$handler = $this->grabProbeValue($I, 'HANDLER');

		$I->resetAllCookies();
		$I->amOnPage('/'.self::PROBE_FILE);

		$I->seeInSource("<a href='".$handler."'>");
	}

	/**
	 * The other half of the same fix, on the page rather than in the handler.
	 */
	public function theNewestMemberLinksThroughTheUrlHandler(AcceptanceTester $I)
	{
		$I->wantTo('link the newest member through the URL handler on online.php');

		$I->loginAsAdmin();
		$I->amOnPage('/online.php');

		$I->seeInSource("<a href='".$this->newestLink."'>".self::NEWEST_NAME."</a>");
	}

	/**
	 * A guard rather than a reproduction. The newest member has always been
	 * plain text for a guest: the USER ternary that decides it is the same
	 * before this change and after, and only the href inside its other arm
	 * moved. This is what fails if a later change lifts the create() call out
	 * of the arm that renders the anchor.
	 */
	public function theNewestMemberIsPlainTextForAGuest(AcceptanceTester $I)
	{
		$I->wantTo('leave the newest member unlinked for a guest on online.php');

		$I->resetAllCookies();
		$I->amOnPage('/online.php');

		$I->seeInSource(self::NEWEST_NAME);
		$I->dontSeeInSource("<a href='".$this->newestLink."'>".self::NEWEST_NAME."</a>");
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * What the URL handler answers for the seeded member, asked of a fresh
	 * process so the page under test cannot be the thing that answers it.
	 *
	 * The assertion is this fixture's own contract rather than the subject's:
	 * every failure mode of the profile switch is silent, from an undeletable
	 * rule cache to a preference write that lost its merge race, and each one
	 * would leave all of these tests green against the legacy route on both
	 * sides of every comparison.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabNewestLink(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=link&id='.$this->newestId);
		$link = $this->grabProbeValue($I, 'LINK');

		$I->assertSame(false, strpos($link, 'user.php?id.'),
			'The site must be on the '.self::REWRITE_PROFILE.' user URL profile for these '
			.'tests to mean anything, and it answered "'.$link.'" instead.');

		return $link;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name the probe key, without its PROBE_ prefix
	 * @return string
	 */
	private function grabProbeValue(AcceptanceTester $I, $name)
	{
		$body = $I->grabResponseBody();

		$found = array();
		if(!preg_match('/^PROBE_'.$name.'=(.*)$/m', $body, $found))
		{
			throw new RuntimeException('The online probe emitted no PROBE_'.$name.': '
				.trim(strip_tags($body)));
		}

		return rtrim($found[1], "\r");
	}

	/**
	 * @return string
	 */
	private function templateSource()
	{
		return "<?php\n"
			."if (!defined('e107_INIT')) { exit; }\n"
			."\$ONLINE_TABLE_START = \"<div id='fixture-online'>\";\n"
			."\$ONLINE_TABLE = \"<div>{ONLINE_TABLE_USERNAME}</div>\";\n"
			."\$ONLINE_TABLE_END = \"</div>\";\n"
			."\$ONLINE_TABLE_MISC = \"{ONLINE_TABLE_MEMBERS_NEWEST}\";\n";
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$newest = self::NEWEST_NAME;

		return <<<PHP
<?php
// Fixture for 0081_OnlineMemberListLinkCest. Removed again in the Cest's _after().
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(varset(\$_GET['act']) === 'link')
{
	\$params = array('id' => (int) varset(\$_GET['id']), 'name' => '{$newest}');
	echo 'PROBE_LINK='.e107::getUrl()->create('user/profile/view', \$params)."\\n";
	exit;
}

\$user = e107::getUser();
\$uparams = array('id' => \$user->getId(), 'name' => \$user->getName());

echo 'PROBE_HANDLER='.e107::getUrl()->create('user/profile/view', \$uparams)."\\n";
echo 'PROBE_MEMBER_NAME='.\$user->getName()."\\n";
echo 'PROBE_MEMBER_LIST='.defset('MEMBER_LIST')."\\n";
PHP;
	}
}
