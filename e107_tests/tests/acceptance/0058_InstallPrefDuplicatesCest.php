<?php

/**
 * What a fresh install stores for the preferences default_install.xml declares
 * twice, and what the site publishes as a result.
 *
 * The resolution happens after parsing: xmlClass keeps every occurrence of a
 * duplicated key and e107ImportPrefs() assigns them in document order, so the
 * last one wins. Asserting the file's text would therefore prove nothing about
 * what a site is founded on. Everything here is read back out of a site that
 * has just been installed, through the application's own preference handler.
 *
 * DESTRUCTIVE. Every test drops every table and installs again, because a
 * preference is site-wide mutable state and any Cest before this one could have
 * written one. Dropping the tables and installing again is what makes "on a
 * fresh install" mean it. The site handed on to whatever runs next is therefore
 * a fresh install with the sitename and theme 0000a_UnattendedInstallCest uses,
 * and nothing this Cest wrote survives; a later Cest wanting the fixtures of
 * 0024 to 0032 has to build them itself.
 *
 * @see xmlClassTest::testE107ImportPrefsResolvesDefaultInstallValues for the
 *      same values taken straight off the importer.
 */
class InstallPrefDuplicatesCest
{
	const PROBE_FILE = 'e107_tests_install_prefs_probe.php';

	const SITENAME = \Helper\Acceptance::INSTALL_SITENAME;

	/** A URL nothing else on the page could be carrying. */
	const CONTROL_URL = 'https://example.com/e107-xurl-control';

	/** Written through a field the preferences form really renders. */
	const SAVE_MARKER = 'p13-save-marker';

	/** The string default_install.xml used to resolve sitecontacts to. */
	const POISON = 'sitecontactinfo';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->haveFreshInstall();
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The seven keys default_install.xml has historically declared twice.
	 *
	 * sitename and sitetag are here to record that the installer overwrites both
	 * after the import (install.php sets them from the wizard's answers), which
	 * is why their duplicates never reached a live site.
	 */
	public function freshInstallStoresOnePreferenceValueEach(AcceptanceTester $I)
	{
		$I->wantTo('read back the preferences a fresh install was founded on');

		$prefs = $this->grabPrefs($I);

		$expected = array(
			// A userclass the contact form resolves to the main admin, and one
			// the preferences dropdown can preselect.
			'sitecontacts'     => '250',
			'html_abuse'       => '1',
			'filter_script'    => '1',
			'pageCookieExpire' => '84600',
			// Empty, so no icon is published for a destination the site has not
			// been given.
			'xurl'             => array(),
			// install.php overwrites both of these after the import.
			'sitename'         => self::SITENAME,
			'sitetag'          => 'e107 Website System',
		);

		$got = array();

		foreach($expected as $key => $unused)
		{
			$got[$key] = isset($prefs[$key]) ? $prefs[$key] : null;
		}

		$I->assertSame($expected, $got);
	}

	/**
	 * The consequence a reader of the advisory cares about. bootstrap5 puts
	 * {XURL_ICONS} in its sitewide footer and the social plugin is installed by
	 * default, so a seeded placeholder is published on every page of every new
	 * site as an anchor with an aria-label naming a destination it does not have.
	 */
	public function freshInstallPublishesNoSocialLinkThatGoesNowhere(AcceptanceTester $I)
	{
		$I->wantTo('see that a fresh site publishes no dead social links');

		$I->amOnPage('/');

		$dead = array();

		foreach($this->socialAnchors($I) as $anchor)
		{
			if(strpos($anchor, 'href="#"') !== false)
			{
				$dead[] = $anchor;
			}
		}

		$I->assertSame(array(), $dead,
			'A fresh site must publish no social icon pointing at "#".');
	}

	/**
	 * A theme's install.xml is imported in 'replace' mode after the core one, so
	 * it can put back anything the core file no longer seeds. voux is the
	 * bundled theme that used to, and it is offered by the installer's own theme
	 * list, so its demo content is a route to the same dead anchors.
	 */
	public function noBundledThemeSeedsADeadSocialLink(AcceptanceTester $I)
	{
		$I->wantTo('see that installing a theme with demo content publishes no dead social links');

		$I->haveFreshInstall(array('theme' => 'voux'));

		$prefs = $this->grabPrefs($I);

		$I->assertSame('voux', $prefs['sitetheme'],
			'The installer must have accepted the theme whose demo content is the subject.');

		$xurl = $prefs['xurl'];

		$I->assertNotContains('#', is_array($xurl) ? $xurl : array($xurl),
			'A theme must not seed a social URL that goes nowhere.');

		$I->amOnPage('/');
		$I->assertStringNotContainsString('href="#"', implode("\n", $this->socialAnchors($I)));
	}

	/**
	 * The positive control for the two assertions above, which would otherwise
	 * pass just as readily on a page that renders no social icons at all, or on
	 * a theme that had stopped calling the shortcode.
	 */
	public function aConfiguredSocialUrlIsStillPublished(AcceptanceTester $I)
	{
		$I->wantTo('see that a configured social URL still reaches the footer');

		$this->probe($I, 'xurl&url='.urlencode(self::CONTROL_URL));

		$I->amOnPage('/');

		$anchors = $this->socialAnchors($I);

		$I->assertNotEmpty($anchors, 'The theme published no social icons at all.');
		$I->assertStringContainsString('href="'.self::CONTROL_URL.'"', implode("\n", $anchors));
	}

	/**
	 * What storing a string where a userclass belongs costs a site owner.
	 *
	 * On PHP 8 "sitecontactinfo" == 0 is false, so uc_dropdown() marks no option
	 * selected and the browser shows the first one, which is nobody. prefs.php
	 * saves every posted field whether or not the admin touched it, so the first
	 * save of the preferences form takes the contact form's recipient away and
	 * the form stops offering anybody to write to.
	 *
	 * The marker is what makes the sitecontacts assertion mean anything: a POST
	 * the application refused would leave sitecontacts at 250 as surely as a
	 * POST it accepted.
	 */
	public function savingPreferencesUntouchedKeepsTheContactRecipient(AcceptanceTester $I)
	{
		$I->wantTo('save the preferences form untouched and still have a contact recipient');

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/prefs.php');

		$I->assertSame('250', $this->selectedOption($I, 'sitecontacts'),
			'The preferences form must preselect the stored contact userclass.');

		$I->submitForm('#core-prefs form', array('sitedescription' => self::SAVE_MARKER), 'updateprefs');

		$prefs = $this->grabPrefs($I);

		$I->assertSame(self::SAVE_MARKER, $prefs['sitedescription'],
			'The preferences form must have been saved at all.');

		$I->assertSame('250', $prefs['sitecontacts'],
			'Saving the form untouched must not change who the contact form reaches.');
	}

	/**
	 * Every site installed before the fix is still carrying the string, and the
	 * XML change is install-time only. update_core_prefs() fills in keys a site
	 * is missing, which this one is not, so repairing it needs its own step.
	 */
	public function anAlreadyPoisonedContactRecipientIsRepaired(AcceptanceTester $I)
	{
		$I->wantTo('see the core update repair a contact recipient stored as a string');

		$this->probe($I, 'sitecontacts&value='.self::POISON);

		$prefs = $this->grabPrefs($I);
		$I->assertSame(self::POISON, $prefs['sitecontacts'],
			'The probe must have left the site in the state the old installer left it in.');

		// update_routines.php is main-admin only and says so at its own top, so
		// the run that asks it to repair a preference has to be a main admin.
		$I->loginAsAdmin();

		$this->probe($I, 'update');

		$prefs = $this->grabPrefs($I);
		$I->assertSame('250', $prefs['sitecontacts'],
			'The core preferences update must repair a contact recipient nothing can read back.');
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * The value of the option a named select marks selected.
	 *
	 * @param AcceptanceTester $I
	 * @param string $field
	 * @return string|null null when the select preselects nothing
	 */
	private function selectedOption(AcceptanceTester $I, $field)
	{
		$pattern = "/name='".preg_quote($field, '/')."'.*?<\\/select>/s";

		if(!preg_match($pattern, $I->grabPageSource(), $select))
		{
			throw new RuntimeException('The page carries no "'.$field.'" select.');
		}

		if(!preg_match("/<option[^>]*\\bvalue='([^']*)'[^>]*\\bselected=/", $select[0], $option))
		{
			return null;
		}

		return $option[1];
	}

	/**
	 * Every anchor on the last response that the social plugin's xurl template
	 * produced.
	 *
	 * @param AcceptanceTester $I
	 * @return string[] opening tags
	 */
	private function socialAnchors(AcceptanceTester $I)
	{
		preg_match_all('/<a\b[^>]*\bsocial-icon\b[^>]*>/i', $I->grabPageSource(), $matches);

		return $matches[0];
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array preference name => stored value
	 */
	private function grabPrefs(AcceptanceTester $I)
	{
		$body = $this->probe($I, 'prefs');

		if(!preg_match('/PROBE_OK (\{.*\})/', $body, $matches))
		{
			throw new RuntimeException('The probe published no preferences: '.trim($body));
		}

		return json_decode($matches[1], true);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act='.$query);

		$body = $I->grabPageSource();

		if(strpos($body, 'PROBE_OK') === false)
		{
			throw new RuntimeException('Preference probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0033_InstallPrefDuplicatesCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : 'prefs';

switch($act)
{
	case 'xurl':
		$url = isset($_GET['url']) ? $_GET['url'] : '';
		e107::getConfig('core')
			->set('xurl', $url === '' ? array() : array('facebook' => $url))
			->save(false, true, false);
		echo "PROBE_OK xurl\n";
		break;

	case 'sitecontacts':
		e107::getConfig('core')
			->set('sitecontacts', isset($_GET['value']) ? $_GET['value'] : '')
			->save(false, true, false);
		echo "PROBE_OK sitecontacts\n";
		break;

	case 'update':
		require_once(e_ADMIN.'update_routines.php');
		update_core_prefs('do');
		echo "PROBE_OK update\n";
		break;

	default:
		$keys = array('sitecontacts', 'html_abuse', 'filter_script', 'pageCookieExpire',
			'xurl', 'sitename', 'sitetag', 'sitedescription', 'sitetheme');
		$out = array();

		foreach($keys as $key)
		{
			$out[$key] = e107::getConfig('core')->get($key);
		}

		echo "PROBE_OK ".json_encode($out)."\n";
}
PHP;
	}
}
