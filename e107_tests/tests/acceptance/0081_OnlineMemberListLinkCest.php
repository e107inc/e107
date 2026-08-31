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
 * Each test asserts that the rendered link agrees with what the URL handler
 * produces for the same member, not that it equals a literal URL. That holds
 * whatever URL profile the site is on, which is the property reported broken.
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

	/** Seeded with a join date ahead of every other row, so it is the newest member. */
	const NEWEST_NAME = 'e107testsnewest';

	/** @var string|null the active theme's online template, as _before() resolved it */
	private $templateFile;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		$this->templateFile = $I->grabActiveThemeDir().'online_template.php';
		$I->writeAppFile($this->templateFile, $this->templateSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?do=cleanup');
		if($this->templateFile !== null) { $I->deleteAppFile($this->templateFile); }
		$I->deleteAppFile(self::PROBE_FILE);
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
	 * The other half of the same fix, on the page rather than in the handler.
	 */
	public function theNewestMemberLinksThroughTheUrlHandler(AcceptanceTester $I)
	{
		$I->wantTo('link the newest member through the URL handler on online.php');

		$I->loginAsAdmin();
		$I->amOnPage('/'.self::PROBE_FILE.'?do=seed');
		$link = $this->grabProbeValue($I, 'NEWEST_LINK');

		$I->amOnPage('/online.php');

		$I->seeInSource("<a href='".$link."'>".self::NEWEST_NAME."</a>");
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
		if(!preg_match('/^PROBE_'.$name.'=(.+)$/m', $body, $found))
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
header('Content-Type: text/plain');

\$sql = e107::getDb();
\$do = isset(\$_GET['do']) ? \$_GET['do'] : 'read';

if(\$do === 'seed')
{
	\$sql->delete('user', "user_loginname='{$newest}'");
	\$id = \$sql->insert('user', array(
		'user_name'      => '{$newest}',
		'user_loginname' => '{$newest}',
		'user_email'     => '{$newest}@example.com',
		'user_password'  => '',
		'user_join'      => time() + 60,
		'user_class'     => '',
		'user_admin'     => 0,
		'user_perms'     => '',
		'user_ban'       => 0,
		'user_xup'       => '',
		'user_prefs'     => '',
		'user_signature' => '',
		'user_realm'     => '',
	));
	\$params = array('id' => \$id, 'name' => '{$newest}');
	echo 'PROBE_NEWEST_LINK='.e107::getUrl()->create('user/profile/view', \$params)."\\n";
	exit;
}

if(\$do === 'cleanup')
{
	\$sql->delete('user', "user_loginname='{$newest}'");
	echo "PROBE_CLEANUP=ok\\n";
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
