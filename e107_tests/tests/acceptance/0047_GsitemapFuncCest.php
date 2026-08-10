<?php

/**
 * P6 item 4. gsitemap.php:43 dispatches on the query string.
 *
 *   $obj = e107::getAddon($_GET['plug'], 'e_gsitemap');
 *   if($items = e107::callMethod($obj, $_GET['func']))
 *
 * e107::callMethod() filters on method_exists() and nothing else, so every
 * public method of every installed plugin's e_gsitemap class is invocable by an
 * anonymous caller. What a plugin publishes as a sitemap is
 * e_gsitemap::config(), which names the methods meant to be reachable that way;
 * news names allPosts and nothing else, and forum names nothing at all because
 * it has no config().
 *
 * The reachable-by-accident methods are not inert. forum_gsitemap::import()
 * (e107_plugins/forum/e_gsitemap.php:24-38) reads every row of the forum table
 * with no userclass predicate, and gsitemap.php:98-135 never looks at the
 * 'class' key it returns, so a class-restricted forum's name and URL come back
 * in an anonymous response.
 *
 * That last point answers the question of whether the ignored 'class' key is in
 * scope: while func is attacker chosen it is an anonymous disclosure, and an
 * allow-list closes it by construction, because import() is an admin-side
 * importer that no allow-list would name.
 */
class GsitemapFuncCest
{
	const RESET_FILE = 'e107_tests_p6_gsitemap_reset.php';

	/** @var string */
	private $restrictedForum;

	/** @var string */
	private $restrictedSef;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::RESET_FILE, $this->resetSource());
		$I->amOnPage('/'.self::RESET_FILE);
		$I->seeInSource('RESET_DONE');

		$I->havePluginInstalled('gsitemap');

		$suffix = uniqid('', false);
		$this->restrictedForum = 'P6 Restricted Forum '.$suffix;
		$this->restrictedSef = 'p6-restricted-'.$suffix;

		// forum_class 254 is Admin, and forum_parent must not be 0 or
		// forum_gsitemap::import() skips the row.
		$I->haveForum($this->restrictedForum, $this->restrictedSef, 1, 254, 5, 254);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall('gsitemap');
		$I->dropPluginProbe();
		$I->deleteAppFile(self::RESET_FILE);
	}

	/**
	 * news_gsitemap::config() names allPosts. import() is the admin importer and
	 * is named by nothing.
	 */
	public function anUnadvertisedAddonMethodIsNotInvocable(AcceptanceTester $I)
	{
		$I->wantTo('refuse a gsitemap func that the addon never advertised');

		$I->amOnPage('/gsitemap.php?plug=news&func=import');

		$this->seeARefusalRatherThanACrash($I);
		$I->dontSeeInSource('<urlset');
	}

	/**
	 * A crash is not a refusal.
	 *
	 * news_gsitemap::import() reaches for ADLAN_0, an admin language constant
	 * that is defined nowhere on the front end, so on PHP 8 the dispatch dies
	 * before it can render anything. That is not the endpoint declining to run
	 * the method, it is the method running and failing, and on PHP 5.6 and 7 the
	 * same line is a notice and the response is served. Without this assertion
	 * the refusal tests here would pass on PHP 8 for the wrong reason and keep
	 * passing after a fix that was never applied.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeARefusalRatherThanACrash(AcceptanceTester $I)
	{
		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');
	}

	/**
	 * forum has no config() at all, so no func on it is reachable, and the
	 * method that is reachable today hands out forums nobody may see.
	 */
	public function aForumAddonMethodIsNotInvocableAndLeaksNoRestrictedForum(AcceptanceTester $I)
	{
		$I->wantTo('refuse a gsitemap func on a plugin that advertises none, and leak no restricted forum');

		$I->amOnPage('/gsitemap.php?plug=forum&func=import');

		$this->seeARefusalRatherThanACrash($I);
		$I->dontSeeInSource('<urlset');
		$I->dontSeeInSource($this->restrictedForum);
		$I->dontSeeInSource($this->restrictedSef);
	}

	/**
	 * A method name that does not exist has always returned false. This is the
	 * control for the two tests above: it shows what a refusal looks like, so
	 * "no urlset" cannot be mistaken for a page that never ran.
	 */
	public function anUnknownFuncIsRefusedWithoutAFatal(AcceptanceTester $I)
	{
		$I->wantTo('keep an unknown func harmless');

		$I->amOnPage('/gsitemap.php?plug=news&func=noSuchMethod');

		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('<urlset');
		$I->dontSeeInSource('Fatal error');
	}

	/**
	 * Positive control. An allow-list that named nothing would satisfy every
	 * refusal above and take the dynamic sitemaps with it.
	 */
	public function theAdvertisedDynamicSitemapStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('keep serving the dynamic sitemap the news addon advertises');

		$I->amOnPage('/gsitemap.php?plug=news&func=allPosts');

		$I->seeResponseCodeIs(200);
		$I->seeInSource('<urlset');
		$I->seeInSource('<loc>');
	}

	/**
	 * Positive control for the rest of the endpoint: the stored sitemap must
	 * still be served.
	 */
	public function theStoredSitemapStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('keep serving the sitemap built from the gsitemap table');

		$I->amOnPage('/gsitemap.php');

		$I->seeResponseCodeIs(200);
		$I->seeInSource('<urlset');
	}

	/**
	 * @return string
	 */
	private function resetSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0036_GsitemapFuncCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');
// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
echo 'RESET_DONE';
PHP;
	}
}
