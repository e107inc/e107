<?php

/**
 * Proof that Helper\Acceptance::havePluginInstalled() installs.
 *
 * Every front-end page a plugin serves opens with e107::isInstalled(), which
 * reads one core preference and nothing else. A test that drives such a page on
 * a site where the plugin was never installed is turned away for that reason,
 * and a refusal for that reason is indistinguishable from the authorisation
 * refusal a security test means to prove. The helper exists to close that gap,
 * so its own test has to show both halves: the page turned away first, and the
 * same page served afterwards. Drop the first half and nothing separates a
 * working helper from one that does nothing at all.
 *
 * poll is the subject because it is bundled, is in no theme's default install
 * set (see e107_themes/bootstrap5/theme.xml), and gates on nothing else:
 * poll.php redirects to the site root while e107::isInstalled('poll') is false
 * and renders the page once it is true.
 */
class PluginInstallHelperCest
{
	const PLUGIN = 'poll';
	const FRONT_PAGE = '/e107_plugins/poll/poll.php';

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();
	}

	/**
	 * Redirects go unfollowed for every request here, which is what makes a
	 * regression report itself. PhpBrowser follows redirects without a cap, and
	 * an e107 that answers a plugin page with one has a fair chance of answering
	 * the next hop the same way, so a helper that quietly stopped installing
	 * would hang the run instead of failing it. Unfollowed, the status code is
	 * the whole answer.
	 */
	public function aPluginPageIsRefusedUntilTheHelperInstallsThePlugin(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();

		$I->amOnPage(self::FRONT_PAGE);
		$I->seeResponseCodeIsRedirection();

		$I->havePluginInstalled(self::PLUGIN);

		$I->amOnPage(self::FRONT_PAGE);
		$I->seeResponseCodeIs(200);
		// A themed page, not an empty body that happened to answer 200.
		$I->seeInSource('</html>');
	}

	/**
	 * The install has to be undoable and repeatable, or a Cest that installs a
	 * plugin in _before() and tidies up in _after() works once and then fails
	 * for the rest of the run.
	 */
	public function theInstallIsReversibleAndRepeatable(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();

		$I->havePluginInstalled(self::PLUGIN);
		$I->havePluginInstalled(self::PLUGIN);

		$I->seeInDatabase('e107_plugin', array(
			'plugin_path' => self::PLUGIN, 'plugin_installflag' => 1));
		$I->seeNumRecords(0, 'e107_polls');

		$I->dropPluginInstall(self::PLUGIN);

		$I->seeInDatabase('e107_plugin', array(
			'plugin_path' => self::PLUGIN, 'plugin_installflag' => 0));
		$I->dontSeeTableInDatabase('e107_polls');

		$I->amOnPage(self::FRONT_PAGE);
		$I->seeResponseCodeIsRedirection();

		$I->havePluginInstalled(self::PLUGIN);

		$I->seeNumRecords(0, 'e107_polls');
		$I->amOnPage(self::FRONT_PAGE);
		$I->seeResponseCodeIs(200);
	}
}
