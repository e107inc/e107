<?php

/**
 * P6 item 1. unsubscribe.php verifies an unsubscribe request in one branch and
 * acts on it in another.
 *
 * The identifying tuple travels in a base64 query parameter: plugin, user id,
 * userclass, email and join date. user_mailout::unsubscribe('check', ...)
 * matches all four of those against the user row; unsubscribe('process', ...)
 * matches none of them and goes straight to
 * e107::getSystemUser($id)->removeClass($userclass).
 *
 * The GET branch at unsubscribe.php:84 calls 'check'. The POST branch at
 * unsubscribe.php:66-68 does not, so an anonymous request that presents a
 * fabricated tuple and a "remove" field strips any userclass from any user id.
 * Nothing in the tuple has to be right except the two integers.
 *
 * Every request here is cookieless. e_core_session::hasAmbientAuthority() asks
 * whether the request carried any cookie at all, and a request that carried
 * none is deliberately exempt from the CSRF check, so a cookieless POST is what
 * an attacker sends and it is also the only shape in which the endpoint's own
 * verification is the thing being measured.
 */
class NewsletterUnsubscribeCest
{
	const RESET_FILE = 'e107_tests_p6_unsubscribe_reset.php';

	/**
	 * NEWSLETTER, one of the classes the installer ships. Using a class the
	 * application already knows keeps the fixture to a single user row.
	 */
	const CLASS_ID = 3;

	/** @var int */
	private $victimId;

	/** @var string */
	private $victimEmail;

	/** @var int */
	private $victimJoin = 1600000000;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::RESET_FILE, $this->resetSource());
		$I->amOnPage('/'.self::RESET_FILE);
		$I->seeInSource('RESET_DONE');

		$suffix = uniqid('', false);

		$this->victimEmail = 'p6-unsub-'.$suffix.'@example.com';

		$this->victimId = $I->haveInDatabase('e107_user', array(
			'user_name'      => 'p6unsub'.$suffix,
			'user_loginname' => 'p6unsub'.$suffix,
			'user_email'     => $this->victimEmail,
			'user_password'  => '',
			'user_join'      => $this->victimJoin,
			'user_class'     => (string) self::CLASS_ID,
			'user_signature' => '',
			'user_prefs'     => '',
			'user_perms'     => '',
			'user_realm'     => '',
		));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::RESET_FILE);
	}

	/**
	 * The payload an attacker can build without knowing anything about the
	 * victim beyond a user id and a userclass id.
	 *
	 * @param array $overrides
	 * @return string value for the id query parameter
	 */
	private function payload(array $overrides = array())
	{
		$data = array_merge(array(
			'plugin'    => 'user',
			'id'        => $this->victimId,
			'userclass' => self::CLASS_ID,
			'email'     => $this->victimEmail,
			'date'      => $this->victimJoin,
		), $overrides);

		return base64_encode(http_build_query($data, '', '&'));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $overrides
	 * @return void
	 */
	private function postRemoval(AcceptanceTester $I, array $overrides = array())
	{
		$I->resetAllCookies();
		$I->sendPostRequest('/unsubscribe.php?id='.urlencode($this->payload($overrides)),
			array('remove' => 'Remove'));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $why
	 * @return void
	 */
	private function seeClassIntact(AcceptanceTester $I, $why)
	{
		$this->seeTheEndpointRan($I);
		$I->seeInDatabase('e107_user', array(
			'user_id'    => $this->victimId,
			'user_class' => (string) self::CLASS_ID,
		));
		$I->dontSee('has been removed from');
		codecept_debug($why);
	}

	/**
	 * A crash is not a refusal.
	 *
	 * On PHP 8 this endpoint dies before it reaches the branch under test:
	 * user_mailout declares its $mailerName as LAN_MAILOUT_68, which lives in
	 * the admin language file and is defined nowhere on the front end, so
	 * e107::getAddon() throws on an undefined constant. On PHP 5.6 and 7 the
	 * same line is a notice that yields the string, the endpoint runs, and the
	 * missing verification is live, which is the pair of versions
	 * release/v2.3.x supports.
	 *
	 * Without this assertion every refusal test in this file would pass on PHP 8
	 * for the wrong reason and go on passing after a fix that never ran.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeTheEndpointRan(AcceptanceTester $I)
	{
		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');
	}

	public function aForgedEmailDoesNotStripTheUserclass(AcceptanceTester $I)
	{
		$I->wantTo('refuse an unsubscribe whose email does not belong to the user id');

		$this->postRemoval($I, array('email' => 'somebody-else@example.com'));

		$this->seeClassIntact($I, 'email did not match the user row');
	}

	public function aForgedJoinDateDoesNotStripTheUserclass(AcceptanceTester $I)
	{
		$I->wantTo('refuse an unsubscribe whose join date does not belong to the user id');

		$this->postRemoval($I, array('date' => 1));

		$this->seeClassIntact($I, 'join date did not match the user row');
	}

	/**
	 * The whole tuple invented. This is the request an attacker actually sends:
	 * they know a user id exists and they know which class they want gone.
	 */
	public function aWhollyFabricatedTupleDoesNotStripTheUserclass(AcceptanceTester $I)
	{
		$I->wantTo('refuse an unsubscribe that presents no genuine identifier at all');

		$this->postRemoval($I, array(
			'email' => 'attacker@example.com',
			'date'  => 0,
		));

		$this->seeClassIntact($I, 'nothing in the tuple identified the user');
	}

	/**
	 * The GET branch is the half that does verify, so it is the control for the
	 * refusal above: the same forged tuple has always been turned away here.
	 * If this ever stops holding, the POST tests above are measuring nothing.
	 */
	public function theConfirmationPageRefusesAForgedTuple(AcceptanceTester $I)
	{
		$I->wantTo('keep the confirmation page refusing a forged tuple');

		$I->resetAllCookies();
		$I->amOnPage('/unsubscribe.php?id='
			.urlencode($this->payload(array('email' => 'somebody-else@example.com'))));

		$this->seeTheEndpointRan($I);
		$I->see('Invalid URL');
		$I->dontSee('Please click the button below to remove');
	}

	public function theConfirmationPageStillOffersAGenuineUnsubscribe(AcceptanceTester $I)
	{
		$I->wantTo('keep offering the confirmation form for a genuine unsubscribe link');

		$I->resetAllCookies();
		$I->amOnPage('/unsubscribe.php?id='.urlencode($this->payload()));

		$this->seeTheEndpointRan($I);
		$I->see('Please click the button below to remove');
		$I->seeInSource($this->victimEmail);
	}

	/**
	 * Positive control. A genuine link must still unsubscribe, or a refusal that
	 * simply breaks the feature would satisfy every test above.
	 */
	public function aGenuineUnsubscribeStillStripsTheUserclass(AcceptanceTester $I)
	{
		$I->wantTo('keep a genuine unsubscribe working');

		$this->postRemoval($I);

		$this->seeTheEndpointRan($I);
		$I->see('has been removed from');
		$I->dontSeeInDatabase('e107_user', array(
			'user_id'    => $this->victimId,
			'user_class' => (string) self::CLASS_ID,
		));
	}

	/**
	 * @return string
	 */
	private function resetSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0033_NewsletterUnsubscribeCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');
// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
echo 'RESET_DONE';
PHP;
	}
}
