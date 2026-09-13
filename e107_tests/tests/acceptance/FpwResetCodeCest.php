<?php

/**
 * Regression tests for GHSA-gm6q-rqm6-p9m4, password reset half.
 *
 * fpw.php used to delete an expired reset row, log a debug line, and then fall
 * through and reset the password anyway. Combined with the lazy purge in
 * class2.php, which only removes rows older than time()-300, the real window
 * was 900 seconds rather than the intended 600, so a code stayed redeemable
 * long after it was supposed to have died.
 *
 * The redemption lookup is also a case-insensitive SQL LIKE. A code that only
 * matched after case folding used to redirect while an unknown code rendered
 * an error page, and that difference told an attacker when a guess was right
 * apart from its capitalisation. Both answers are now identical.
 */
class FpwResetCodeCest
{
	/** @var string unique per run, so no stale row can satisfy an assertion */
	private $runId;

	/** @var int */
	private $userId;

	/** @var string */
	private $loginName;

	/** @var string user_password before any reset is attempted */
	private $originalHash;

	public function _before(AcceptanceTester $I)
	{
		$this->runId     = substr(md5(uniqid('', true)), 0, 8);
		$this->loginName = 'ghsagm6q'.$this->runId;

		$this->originalHash = '$2y$10$'.str_pad($this->runId, 53, 'x');

		$this->userId = $I->haveInDatabase('e107_user', array(
			'user_name'      => 'GHSA gm6q '.$this->runId,
			'user_loginname' => $this->loginName,
			'user_password'  => $this->originalHash,
			'user_email'     => $this->loginName.'@example.invalid',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
			'user_perms'     => '',
			'user_realm'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
		));
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function anExpiredResetCodeDoesNotChangeThePassword(AcceptanceTester $I)
	{
		$I->wantTo('refuse an expired password reset code (GHSA-gm6q-rqm6-p9m4)');

		$code = $this->seedResetCode($I, time() - 60);

		$I->amOnPage('/fpw.php?'.$code);

		$I->see('This is not a valid link to reset your password.');
		$I->dontSee('Your password has been changed successfully.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $this->userId,
			'user_password' => $this->originalHash,
		));
		$I->dontSeeInDatabase('e107_tmp', array('tmp_info' => $this->tmpInfo($code)));
	}

	public function anUnexpiredResetCodeStillChangesThePassword(AcceptanceTester $I)
	{
		$I->wantTo('still honour a password reset code inside its window');

		$code = $this->seedResetCode($I, time() + 600);

		$I->amOnPage('/fpw.php?'.$code);

		$I->see('Your password has been changed successfully.');
		$I->dontSeeInDatabase('e107_user', array(
			'user_id'       => $this->userId,
			'user_password' => $this->originalHash,
		));
	}

	/**
	 * The code is spent when it is redeemed. It used to survive, because the
	 * only DELETE on the success path prunes rows whose tmp_time has passed and
	 * the redeemed row is by definition not one of those. Anyone holding the
	 * emailed link, a corporate mail scanner or a shared mailbox included, could
	 * mint a fresh password for the account over and over until it expired.
	 */
	public function aRedeemedResetCodeCannotBeUsedTwice(AcceptanceTester $I)
	{
		$I->wantTo('spend a password reset code when it is redeemed');

		$code = $this->seedResetCode($I, time() + 600);

		$I->amOnPage('/fpw.php?'.$code);
		$I->see('Your password has been changed successfully.');

		$afterFirst = $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $this->userId));

		$I->amOnPage('/fpw.php?'.$code);

		$I->see('This is not a valid link to reset your password.');
		$I->dontSee('Your password has been changed successfully.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $this->userId,
			'user_password' => $afterFirst,
		));
		$I->dontSeeInDatabase('e107_tmp', array('tmp_info' => $this->tmpInfo($code)));
	}

	/**
	 * The lookup folds case, so a wrong-case guess reaches the row. It must be
	 * answered exactly like an unknown code, and it must not reset anything.
	 */
	public function aCaseFoldedCodeIsRefusedLikeAnUnknownOne(AcceptanceTester $I)
	{
		$I->wantTo('answer a case-folded reset code exactly like an unknown one');

		$code = $this->seedResetCode($I, time() + 600);
		$folded = strtoupper($code) === $code ? strtolower($code) : strtoupper($code);

		$I->amOnPage('/fpw.php?'.$folded);
		$foldedResponse = $I->grabPageSource();

		$I->see('This is not a valid link to reset your password.');
		$I->seeInDatabase('e107_user', array(
			'user_id'       => $this->userId,
			'user_password' => $this->originalHash,
		));

		$I->amOnPage('/fpw.php?'.$this->unknownCode());
		$unknownResponse = $I->grabPageSource();

		$I->assertSame(
			$this->normalise($unknownResponse),
			$this->normalise($foldedResponse),
			'a case-folded code and an unknown code must produce the same page');
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Write a password reset request straight into the tmp table, the way
	 * fpw.php's own request branch does.
	 *
	 * @param AcceptanceTester $I
	 * @param int $expires value stored in tmp_time
	 * @return string the 12-character reset code
	 */
	private function seedResetCode(AcceptanceTester $I, $expires)
	{
		$code = $this->resetCode();

		$I->haveInDatabase('e107_tmp', array(
			'tmp_ip'   => 'pwreset',
			'tmp_time' => $expires,
			'tmp_info' => $this->tmpInfo($code),
		));

		return $code;
	}

	/**
	 * @param string $code
	 * @return string tmp_info in fpw.php's "<uid>#<loginname>#<code>" shape
	 */
	private function tmpInfo($code)
	{
		return $this->userId.'#'.$this->loginName.'#'.$code;
	}

	/**
	 * A code of the shape generateRandomString('############') produces: twelve
	 * characters of mixed-case alphabet, which fpw.php's own [\W_] filter must
	 * leave untouched.
	 *
	 * @return string
	 */
	private function resetCode()
	{
		$alpha = 'AaBbCcDdEeFfGgHhIiJjKkLMmNnPpQqRrSsTtUuVvWwXxYyZz';
		$code  = '';

		for($i = 0; $i < 12; $i++)
		{
			$code .= $alpha[mt_rand(0, strlen($alpha) - 1)];
		}

		return $code;
	}

	/**
	 * @return string a code of the right shape that was never issued
	 */
	private function unknownCode()
	{
		return 'ZzYyXxWwVvUu';
	}

	/**
	 * Strip the parts of a rendered page that legitimately differ between two
	 * requests, so the remaining comparison is about the answer itself.
	 *
	 * @param string $html
	 * @return string
	 */
	private function normalise($html)
	{
		$html = preg_replace('/value=[\'"][0-9a-f]{32}[\'"]/', 'value="TOKEN"', $html);
		$html = preg_replace('/content=[\'"][0-9a-f]{32}[\'"]/', 'content="TOKEN"', $html);
		$html = preg_replace('/\d{2}:\d{2}(:\d{2})?/', 'TIME', $html);

		// footer_default.php closes every page with md5() of a browser-cache key
		// that folds in time() whenever e_NOCACHE is set, which is always, so the
		// marker changes every second. Two requests either side of a second
		// boundary would otherwise fail this comparison about one time in three.
		$html = preg_replace('/<!-- [0-9a-f]{32} -->/', '<!-- CACHEKEY -->', $html);

		return trim($html);
	}
}
