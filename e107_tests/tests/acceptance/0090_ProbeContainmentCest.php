<?php

/**
 * A fixture in the docroot answers this run and nobody else.
 *
 * The acceptance suite drops around thirty PHP files into the app root and
 * takes them away again. Each one boots e107 and then does what the query
 * string asks: rewrites core preferences, empties the online and banlist
 * tables, installs and uninstalls plugins, deletes users, writes files into
 * the media tree. Extension\WorkspaceCleanup removes them on the way in as
 * well as on the way out, so an ordinary run leaves nothing behind, but a run
 * that is killed does, and the docroot is a live site.
 *
 * Helper\ProbeGuard closes that window. Every fixture that boots e107 in the
 * app root reserves a line for the guard, writeAppFile() substitutes it on the
 * way through, and a caller that cannot show the secret this run minted is
 * answered 403 having done nothing.
 *
 * The forum fixture is the one measured here because it is shared, because it
 * is one of the probes that was reachable, and because what it does is easy to
 * ask the database about: its uninstall drops four forum tables, a userclass
 * and two extended fields. The last case is the one that covers the probes
 * this Cest never names: writeAppFile() refuses a fixture that reserved no
 * room for the guard, so a probe written later cannot ship without one.
 *
 * @see \Helper\ProbeGuard
 * @see \Helper\ForumFixture
 * @see \Extension\WorkspaceCleanup
 */
class ProbeContainmentCest
{
	/** A fixture with the bootstrap and no room reserved for the guard. */
	const UNGUARDED_FILE = 'e107_tests_containment_unguarded.php';

	/** What a probe answers a caller it does not recognise. */
	const REFUSED = 'E107_TEST_PROBE_FORBIDDEN';

	/** An action the probe's switch does not recognise, so nothing is done. */
	const NOOP = 'act=';

	/** The action a passer-by would want, and the state it would leave. */
	const UNINSTALL = 'act=uninstall';

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumProbe();
		$this->show($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$this->show($I);
	}

	/**
	 * The positive control. Without it the refusals below would pass just as
	 * well against a probe that was not there at all.
	 */
	public function theSuitesOwnRequestStillReachesTheProbe(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl(\Helper\ProbeGuard::query().'&'.self::NOOP));

		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource(self::REFUSED);
	}

	/**
	 * What a copy left behind by a killed run is worth to a passer-by. The
	 * database is asked first: the response code says the probe refused, and
	 * this says it refused before it had done anything.
	 */
	public function aCallerThatShowsNoSecretChangesNothing(AcceptanceTester $I)
	{
		$this->hide($I);
		$I->amOnPage($this->probeUrl(self::UNINSTALL));
		$this->show($I);

		$I->seeInDatabase('e107_plugin', array(
			'plugin_path'        => 'forum',
			'plugin_installflag' => 1,
		));
		$I->seeResponseCodeIs(403);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * @param AcceptanceTester $I
	 */
	public function aCallerThatShowsTheWrongSecretChangesNothing(AcceptanceTester $I)
	{
		$this->hide($I);
		$I->amOnPage($this->probeUrl(\Helper\ProbeGuard::PARAMETER.'=not-even-close&'.self::UNINSTALL));
		$this->show($I);

		$I->seeInDatabase('e107_plugin', array(
			'plugin_path'        => 'forum',
			'plugin_installflag' => 1,
		));
		$I->seeResponseCodeIs(403);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The case that covers every probe this Cest does not name. A fixture that
	 * boots e107 in the app root and reserves no room for the guard never
	 * reaches the docroot at all.
	 */
	public function theHelperRefusesAFixtureThatReservedNoRoomForTheGuard(AcceptanceTester $I)
	{
		$refused = '';

		try
		{
			$I->writeAppFile(self::UNGUARDED_FILE,
				"<?php\nrequire_once(__DIR__.'/class2.php');\necho 'PROBE_OK';\n");
		}
		catch(\RuntimeException $e)
		{
			$refused = $e->getMessage();
		}

		$I->assertStringContainsString(\Helper\ProbeGuard::MARKER, $refused,
			'writeAppFile() must refuse a fixture that boots e107 with no guard');

		$I->amOnPage('/'.self::UNGUARDED_FILE);
		$I->dontSeeInSource('PROBE_OK');
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		return '/'.\Helper\ForumFixture::PROBE_FILE.'?'.$query;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function show(AcceptanceTester $I)
	{
		$I->haveHttpHeader(\Helper\ProbeGuard::HEADER, \Helper\ProbeGuard::secret());
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function hide(AcceptanceTester $I)
	{
		$I->deleteHeader(\Helper\ProbeGuard::HEADER);
	}
}
