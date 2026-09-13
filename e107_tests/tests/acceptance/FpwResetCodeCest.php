<?php

/**
 * Regression tests for GHSA-gm6q-rqm6-p9m4: fpw.php reset-code redemption.
 *
 * The expiry check deleted the expired row and then fell through and reset the
 * password anyway, which combined with the 300-second grace on the lazy prune in
 * class2.php made a nominally 10-minute code usable for 900 seconds. The
 * redemption lookup is also a case-insensitive SQL LIKE, so a case variant of a
 * live code reached the comparison; that comparison now answers exactly as a
 * miss does, which is what stops an attacker recovering the code's casing one
 * letter at a time.
 *
 * The expired row is planted 60 seconds in the past on purpose: class2.php only
 * prunes rows older than time() - 300, so the row is still there when fpw.php
 * looks at it, which is the state the fall-through was reachable in.
 */
class FpwResetCodeCest
{
	const LIVE_CODE = 'AbCdEfGhIjKl';
	const EXPIRED_CODE = 'MnPqRsTuVwXy';
	const REPLAY_CODE = 'ZaBcDeFgHiJk';

	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function anExpiredCodeDoesNotResetThePassword(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an expired password reset code');

		$userId = $this->haveProbeUser($I, 'fpwexpired');
		$before = $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));

		$I->haveInDatabase('e107_tmp', array(
			'tmp_ip'   => 'pwreset',
			'tmp_time' => time() - 60,
			'tmp_info' => $userId . '#fpwexpired#' . self::EXPIRED_CODE,
		));

		$I->amOnPage('/fpw.php?' . self::EXPIRED_CODE);

		$I->see('This is not a valid link to reset your password.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $userId,
			'user_password' => $before,
		));
	}

	/**
	 * The control: without it, a completely broken fpw.php would pass the test
	 * above.
	 */
	public function aLiveCodeStillResetsThePassword(AcceptanceTester $I)
	{
		$I->wantTo('Still accept a password reset code that has not expired');

		$userId = $this->haveProbeUser($I, 'fpwlive');
		$before = $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));

		$I->haveInDatabase('e107_tmp', array(
			'tmp_ip'   => 'pwreset',
			'tmp_time' => time() + 600,
			'tmp_info' => $userId . '#fpwlive#' . self::LIVE_CODE,
		));

		$I->amOnPage('/fpw.php?' . self::LIVE_CODE);

		$I->dontSeeInDatabase('e107_user', array(
			'user_id'       => $userId,
			'user_password' => $before,
		));
	}

	/**
	 * The code is spent when it is redeemed. It used to survive, so anyone
	 * holding the emailed link, a corporate mail scanner or a shared mailbox
	 * included, could mint a fresh password for the account over and over until
	 * the row expired.
	 */
	public function aRedeemedCodeCannotBeUsedTwice(AcceptanceTester $I)
	{
		$I->wantTo('Spend a password reset code when it is redeemed');

		$userId = $this->haveProbeUser($I, 'fpwreplay');

		$I->haveInDatabase('e107_tmp', array(
			'tmp_ip'   => 'pwreset',
			'tmp_time' => time() + 600,
			'tmp_info' => $userId . '#fpwreplay#' . self::REPLAY_CODE,
		));

		$I->amOnPage('/fpw.php?' . self::REPLAY_CODE);

		$afterFirst = $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));

		$I->amOnPage('/fpw.php?' . self::REPLAY_CODE);

		$I->see('This is not a valid link to reset your password.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $userId,
			'user_password' => $afterFirst,
		));
		$I->dontSeeInDatabase('e107_tmp', array(
			'tmp_info' => $userId . '#fpwreplay#' . self::REPLAY_CODE,
		));
	}

	/**
	 * The lookup folds case, so this row is found; the comparison must still
	 * refuse it, and refuse it the same way a miss is refused.
	 */
	public function aCaseVariantOfALiveCodeIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a case variant of a live password reset code');

		$userId = $this->haveProbeUser($I, 'fpwcase');
		$before = $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));

		$I->haveInDatabase('e107_tmp', array(
			'tmp_ip'   => 'pwreset',
			'tmp_time' => time() + 600,
			'tmp_info' => $userId . '#fpwcase#' . self::LIVE_CODE,
		));

		$I->amOnPage('/fpw.php?' . strtolower(self::LIVE_CODE));

		$I->see('This is not a valid link to reset your password.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $userId,
			'user_password' => $before,
		));
	}

	/**
	 * A throwaway account, so a reset never touches the credentials the rest of
	 * the suite logs in with.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @return int the new user id
	 */
	private function haveProbeUser(AcceptanceTester $I, $name)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_login'     => $name,
			'user_password'  => '$2y$10$abcdefghijklmnopqrstuvOo3Z0lLQVMdLpFXCkdbYlKAiVDlmb2',
			'user_email'     => $name . '@example.com',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));
	}
}
