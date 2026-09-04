<?php

/**
 * The v1 forum upgrade sweeps six deprecated files out of its own plugin
 * directory, and it is meant to do so once.
 *
 * forumUpgrade::__construct() calls getUpdateInfo(), which loads
 * $_SESSION['forumUpgrade'] into $this->updateInfo, and then sweeps when that
 * property is empty. Empty means no upgrade is in progress, so the sweep
 * belongs to the load that starts an upgrade attempt and to no other: step1()
 * writes the session on that same render, and every later request the upgrade
 * makes, including each AJAX tick of a long migration, finds the property
 * populated.
 *
 * The condition used to name a local $updateInfo that is never assigned, so it
 * was always true and the sweep ran on every request the page served. These
 * cases pin both halves: an upgrade that has not started still sweeps, and one
 * that has started no longer touches the plugin directory.
 *
 * The stand-in is one of the six real names, since removeDeprecatedFiles()
 * unlinks by name, and no e107 release ships it. It boots e107 and refuses any
 * caller that cannot present this run's secret, so its presence in the docroot
 * grants nothing to anybody who finds it.
 *
 * @see e107_plugins/forum/forum_update.php  forumUpgrade::removeDeprecatedFiles()
 */
class ForumUpgradeSweepCest
{
	const UPDATE = '/e107_plugins/forum/forum_update.php';

	/** One of the six names forumUpgrade::removeDeprecatedFiles() unlinks. */
	const DEPRECATED = 'e107_plugins/forum/forum_update_check.php';

	/** What the stand-in answers a caller holding this run's secret. */
	const SURVIVED = 'forum-deprecated-file-survived';

	/** @var string */
	private $token;

	/** @var string */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('forum');

		$this->secret = md5(uniqid('forum-sweep', true).mt_rand());

		$I->loginAsAdmin();
		$this->token = $I->grabForumToken('/e107_plugins/forum/forum.php');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::DEPRECATED);
		$I->dropPluginProbe();
	}

	/**
	 * The stand-in is a file in the docroot for the length of the run, so it
	 * has to be worth nothing to a caller who is not this test.
	 */
	public function theStandInRefusesACallerWithoutTheSecret(AcceptanceTester $I)
	{
		$I->wantTo('keep the deprecated-file stand-in useless to anyone else');

		$I->writeAppFile(self::DEPRECATED, $this->standInSource());

		$I->amOnPage('/'.self::DEPRECATED);

		$I->seeResponseCodeIs(403);
		$I->dontSeeInSource(self::SURVIVED);
	}

	/**
	 * The half of the guard that has to keep working: nothing in the session
	 * means no upgrade in progress, so the leftovers still go.
	 */
	public function aFreshUpgradeSweepsThePluginDirectory(AcceptanceTester $I)
	{
		$I->wantTo('sweep the deprecated files when an upgrade starts');

		$I->writeAppFile(self::DEPRECATED, $this->standInSource());
		$this->seeTheStandInIsThere($I);

		$I->amOnPage($this->freshStart());

		$I->amOnPage($this->standIn());
		$I->seeResponseCodeIs(404);
		$I->dontSeeInSource(self::SURVIVED);
	}

	/**
	 * The half the typo destroyed. The first load has already swept and has
	 * already written $_SESSION['forumUpgrade'] through step1(), so the second
	 * one is a resumed upgrade and must leave the plugin directory alone.
	 *
	 * The stand-in is written after the upgrade has started, which is what a
	 * site restoring a file it still needs looks like, and what an unfixed
	 * tree deletes again on the very next request.
	 */
	public function aResumedUpgradeLeavesThePluginDirectoryAlone(AcceptanceTester $I)
	{
		$I->wantTo('stop the upgrade re-sweeping on every request it serves');

		$I->amOnPage($this->freshStart());

		$I->writeAppFile(self::DEPRECATED, $this->standInSource());
		$this->seeTheStandInIsThere($I);

		$I->amOnPage(self::UPDATE.'?e-token='.$this->token);

		$this->seeTheStandInIsThere($I);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeTheStandInIsThere(AcceptanceTester $I)
	{
		$I->amOnPage($this->standIn());
		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SURVIVED);
	}

	/**
	 * The stand-in, asked the one question it answers.
	 *
	 * @return string
	 */
	private function standIn()
	{
		return '/'.self::DEPRECATED.'?s='.$this->secret;
	}

	/**
	 * An empty reset clears $_SESSION['forumUpgrade'] before the constructor
	 * reads it, which is what a site opening the upgrade page for the first
	 * time looks like whatever ran before it.
	 *
	 * @return string
	 */
	private function freshStart()
	{
		return self::UPDATE.'?reset=&e-token='.$this->token;
	}

	/**
	 * @return string
	 */
	private function standInSource()
	{
		$source = <<<'PHP'
<?php
require_once(__DIR__.'/../../class2.php');

if(!isset($_GET['s']) || !hash_equals('%SECRET%', (string) $_GET['s']))
{
	http_response_code(403);
	echo 'forum-deprecated-file-refused';
	exit;
}

echo '%SURVIVED%';
PHP;

		return str_replace(
			array('%SECRET%', '%SURVIVED%'),
			array($this->secret, self::SURVIVED),
			$source
		);
	}
}
