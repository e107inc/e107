<?php

/**
 * The identity-confirmation value on the admin password-change form, compared
 * against a value PHP is willing to read as a number.
 *
 * updateadmin.php gates its whole handler on the posted `ac` field matching
 * md5(ADMINPWCHANGE), where ADMINPWCHANGE is the signed-in administrator's own
 * user_pwchange timestamp. Compared with `==`, two strings of the form `0e`
 * followed by only digits are both read as the float 0.0 and come out equal, so
 * on the administrators whose md5(user_pwchange) happens to take that form the
 * gate accepts any value of that shape.
 *
 * It is a narrow window - about one administrator in 340 million, and only
 * while that account keeps that timestamp - which is why the assertions here
 * seed the timestamp rather than wait for one. 240610708 is the published md5
 * preimage of 0e462097431906509019562988736854; the account is put on it in
 * _before() and put back on the value the sample dump ships in _after().
 *
 * Nothing here writes a password. Every case posts two passwords that do not
 * match, so a submission that gets past the gate stops at the handler's own
 * "Error - please re-submit." and a submission that does not renders nothing at
 * all. That marker is the whole signal: the refusal path leaves the block
 * unentered and the page has no else branch for it.
 *
 * @see e107_admin/updateadmin.php  the gate
 * @see e107_handlers/user_model.php  e_user_model::checkAdminPwchangeToken()
 * @see class2.php  define('ADMINPWCHANGE', $user->getAdminPwchange())
 */
class MagicHashComparisonCest
{
	const ROUTE = '/e107_admin/updateadmin.php';

	/** md5('240610708') is '0e462097431906509019562988736854'. */
	const SEEDED_PWCHANGE = 240610708;

	/** The user_pwchange e107_v2.3.0.sample.sql ships for user_id 1. */
	const DUMP_PWCHANGE = 1590351985;

	/**
	 * A magic hash that is not the one this account's form renders. Equal to it
	 * under `==` and equal to it in not one byte.
	 */
	const OTHER_MAGIC_VALUE = '0e830400451993494058024219903391';

	/**
	 * A well-formed value that is neither the right one nor a magic hash. The
	 * control that says the refusal below is the gate working rather than the
	 * gate refusing everything.
	 */
	const WRONG_VALUE = 'deadbeefdeadbeefdeadbeefdeadbeef';

	/**
	 * Rendered by updateadmin.php only once the gate has passed, from the
	 * branch that handles two passwords that do not match.
	 *
	 * @see e107_languages/English/admin/lan_updateadmin.php  UDALAN_1
	 */
	const PAST_THE_GATE_MARKER = 'Error - please re-submit.';

	public function _before(AcceptanceTester $I)
	{
		$I->updateInDatabase('e107_user', array('user_pwchange' => self::SEEDED_PWCHANGE),
			array('user_id' => 1));

		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->updateInDatabase('e107_user', array('user_pwchange' => self::DUMP_PWCHANGE),
			array('user_id' => 1));
	}

	/**
	 * The gate must not accept a value that is only numerically equal to the
	 * one this administrator was served.
	 */
	public function theAdminPasswordFormRefusesAnotherMagicHash(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an admin password change confirmed with a different magic hash');

		$I->amOnPage(self::ROUTE);
		$served = $this->grabConfirmValue($I);
		$token = $this->grabToken($I);

		$I->assertTrue((bool) preg_match('/^0e\d{30}$/', $served),
			'The seeded user_pwchange renders '.$served.', which is not a magic hash, so this case '
			.'no longer exercises type juggling.');
		$I->assertNotSame($served, self::OTHER_MAGIC_VALUE);
		$I->assertTrue($served == self::OTHER_MAGIC_VALUE,
			'The two values are no longer loosely equal, so this case proves nothing.');

		$I->sendPostRequest(self::ROUTE, $this->payload(self::OTHER_MAGIC_VALUE, $token));

		$I->assertStringNotContainsString(self::PAST_THE_GATE_MARKER, $I->grabPageSource(),
			'updateadmin.php entered its password-change handler for ac = '.self::OTHER_MAGIC_VALUE
			.', which is not the value it rendered ('.$served.'). The two are equal only to PHP\'s '
			.'numeric string comparison.');
	}

	/**
	 * Positive control: the form's own value still gets through.
	 *
	 * This is the assertion the item turns on. Every administrator posts this
	 * field on every password change, so a gate that stopped accepting it would
	 * take the page out of service and the refusal above would mean nothing.
	 */
	public function theAdminPasswordFormStillAcceptsItsOwnConfirmValue(AcceptanceTester $I)
	{
		$I->wantTo('Keep the admin password form working when it carries its own confirm value');

		$I->amOnPage(self::ROUTE);

		$I->sendPostRequest(self::ROUTE,
			$this->payload($this->grabConfirmValue($I), $this->grabToken($I)));

		$I->assertStringContainsString(self::PAST_THE_GATE_MARKER, $I->grabPageSource(),
			'updateadmin.php no longer enters its handler for the ac value its own form renders, so '
			.'the refusal asserted above is a dead page rather than a confirmation check.');
	}

	/**
	 * Backwards-compatibility control: a well-formed wrong value was refused
	 * before and is refused now.
	 */
	public function theAdminPasswordFormRefusesAnOrdinaryWrongValue(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an admin password change confirmed with an ordinary wrong value');

		$I->amOnPage(self::ROUTE);

		$I->sendPostRequest(self::ROUTE, $this->payload(self::WRONG_VALUE, $this->grabToken($I)));

		$I->assertStringNotContainsString(self::PAST_THE_GATE_MARKER, $I->grabPageSource(),
			'updateadmin.php entered its password-change handler for ac = '.self::WRONG_VALUE.'.');
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * Two passwords that do not match, so a submission that gets past the gate
	 * stops at the handler's own error rather than writing anything.
	 *
	 * @param string $confirmValue value for the ac field
	 * @param string $token        CSRF token the page rendered
	 * @return array
	 */
	private function payload($confirmValue, $token)
	{
		return array(
			'update_settings' => 'no-value',
			'ac'              => $confirmValue,
			'a_password'      => 'p75-First-Pass',
			'a_password2'     => 'p75-Second-Pass',
			'e-token'         => $token,
		);
	}

	/**
	 * @return string the ac value updateadmin.php rendered for this account
	 */
	private function grabConfirmValue(AcceptanceTester $I)
	{
		$matches = array();

		if(!preg_match('/name=[\'"]ac[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('updateadmin.php rendered no ac field to post back.');
		}

		return $matches[1];
	}

	/**
	 * @return string the CSRF token on the page currently loaded
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$matches = array();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The current page rendered no e-token to post back.');
		}

		return $matches[1];
	}
}
