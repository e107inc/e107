<?php

/**
 * An Esperanto pack built from the English files, missing phrases and whole files, as an old pack is on a newer core.
 */
class StaleLanguagePackCest
{
	const PROBE_FILE = 'e107_tests_stale_language_pack.php';

	const PACK = 'e107_languages/Esperanto/';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$this->writePack($I, 'Esperanto.php', $this->without(file_get_contents(APP_PATH.'/e107_languages/English/English.php'), array('CORE_LAN4', 'CORE_LAN5')));
		$this->writePack($I, 'lan_contact.php', $this->without($this->translated(file_get_contents(APP_PATH.'/e107_languages/English/lan_contact.php')), array('LAN_CONTACT_08')));
		$this->writePack($I, 'admin/lan_admin.php', $this->without(file_get_contents(APP_PATH.'/e107_languages/English/admin/lan_admin.php'), array('LAN_NAVIGATION')));

		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->haveSitePrefUntilAfter('sitelanguage', 'Esperanto');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
	}

	public function eachMissingPhraseComesOutInEnglish(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('noLanguageSubs', 0);

		$phrases = $I->grabProbeJson();

		$I->assertSame('Eo Contact Details', $phrases['LAN_CONTACT_01'], 'a phrase the pack has stays its own');
		$I->assertSame('Send', $phrases['LAN_CONTACT_08']);
		$I->assertSame('Please delete install.php from your server', $phrases['CORE_LAN4']);
		$I->assertSame('Navigation', $phrases['LAN_NAVIGATION']);
		$I->assertSame('Sign up', $phrases['signin']);

		$pages = array('/contact.php', '/signup.php', '/e107_admin/admin.php', '/e107_admin/prefs.php', '/e107_admin/users.php',
			'/e107_admin/newspost.php', '/e107_admin/cpage.php', '/e107_admin/language.php');

		foreach($pages as $page)
		{
			$this->seePageStanding($I, $page);
		}

		$I->amOnPage('/e107_admin/prefs.php?[debug=basic]');
		$I->seeInSource('phrase(s) missing from');

		$I->amOnPage('/contact.php');
		$I->seeInSource('value="Send"');
	}

	public function withNoLanguageSubsAMissingPhraseShowsItsName(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('noLanguageSubs', 1);

		$phrases = $I->grabProbeJson();

		$I->assertSame('Eo Contact Details', $phrases['LAN_CONTACT_01']);
		$I->assertSame('LAN_CONTACT_08', $phrases['LAN_CONTACT_08']);
		$I->assertSame('CORE_LAN4', $phrases['CORE_LAN4']);
		$I->assertSame('LAN_SIGNIN_SIGNUP', $phrases['signin'], 'the labels #6846 found blank read their names');

		$this->seePageStanding($I, '/contact.php');
		$I->seeInSource('value="LAN_CONTACT_08"');
		$this->seePageStanding($I, '/e107_admin/admin.php');
	}

	public function theSitesOwnPhraseWinsOverTheFill(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('noLanguageSubs', 0);
		$this->writePack($I, 'Esperanto_custom.php', "<?php\ndefine('CORE_LAN5', 'Propra');\n");

		$phrases = $I->grabProbeJson();

		$I->assertSame('Propra', $phrases['CORE_LAN5'], 'the custom file loads after the main one, so the fill has to wait for it');
		$I->assertSame('Please delete install.php from your server', $phrases['CORE_LAN4']);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string           $page
	 * @return void
	 */
	private function seePageStanding(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);
		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('Undefined constant');
		$I->dontSeeInSource('Fatal error');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string           $file under the pack's folder
	 * @param string           $source
	 * @return void
	 */
	private function writePack(AcceptanceTester $I, $file, $source)
	{
		$I->writeAppFile(self::PACK.$file, $source);
	}

	/**
	 * @param string   $source an English language file
	 * @param string[] $names
	 * @return string the file without the lines defining $names
	 */
	private function without($source, array $names)
	{
		foreach($names as $name)
		{
			$source = preg_replace('/^.*[\'"]'.preg_quote($name, '/').'[\'"]\s*(=>|,).*\n/m', '', $source, -1, $count);

			if($count !== 1)
			{
				throw new \RuntimeException('expected one line defining '.$name);
			}
		}

		return $source;
	}

	/**
	 * @param string $source an English language file
	 * @return string the same with each value marked as the pack's own
	 */
	private function translated($source)
	{
		return preg_replace('/((=>|,)\s*")([^"])/', '$1Eo $3', $source);
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

header('Content-Type: text/plain');

e107::coreLan('contact');
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
e107::plugLan('signin', '', true);

$phrases = array();

foreach(array('LAN_CONTACT_01', 'LAN_CONTACT_08', 'CORE_LAN4', 'CORE_LAN5', 'LAN_NAVIGATION') as $name)
{
	$phrases[$name] = defined($name) ? constant($name) : null;
}

$phrases['signin'] = e107::getParser()->parseTemplate('{LAN=LAN_SIGNIN_SIGNUP}');

echo "PROBE_OK\n".json_encode($phrases);
PHP;
	}
}
