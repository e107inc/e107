<?php

/**
 * Regression tests for the usersettings confirmation payload.
 *
 * The confirmation stage used to rebuild the pending change out of `updated_data`,
 * a base64 blob the browser posted back, vouched for by `updated_key`. The vouching
 * never worked: the key was crypt() salted with e_TOKEN, which is not a secret from
 * the member submitting the form, and `hasReadonlyField()` was handed the blob as a
 * string, so its foreach iterated nothing and it returned false for everything. A
 * member who knew only their own password could name any column of the user table,
 * user_admin and user_perms included, and become an administrator.
 *
 * The pending change is now held in the session and the posted blob is not read at
 * all, so these tests forge it and assert the forgery has no effect while the change
 * the member actually asked for still lands.
 */
class UsersettingsConfirmationPayloadCest
{
	const MEMBER = 'payloadmember';
	const MEMBER_PASS = 'Member1Pass!';
	// LAN_USET_43, the message a write that could not be applied leaves behind.
	const WRITE_REFUSED = 'Error updating user data';
	// LAN_USET_41, the message a settings write that did land leaves behind.
	const SAVED_MARKER = 'Settings updated and saved into database.';
	const FORGED = '{
    "user_admin": "1",
    "user_perms": "0",
    "user_xup": "Facebook_attacker-chosen"
}';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
	}

	/**
	 * The escalation, end to end, exactly as an ordinary member could drive it.
	 *
	 * Two forgeries are posted because the vouching changed shape mid-fix: the
	 * confirmation's own key validated any payload while the key was crypt()-derived,
	 * and an HMAC over the forged bytes validated it afterwards. Sending both means
	 * this reds whichever of the two the code under test would have accepted.
	 */
	public function aForgedConfirmationPayloadCannotWriteAdminColumns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmation payload the member rewrote');

		$userId = $this->haveMember($I, 'forge');
		$this->loginAsMember($I, 'forge');

		// As the unfixed code vouched for it before the HMAC went in: one constant per
		// session, so the form's own key validated anything.
		$this->startEmailChange($I, 'forge-new@example.test');
		$replayed = $this->confirmationFields($I);
		$replayed['currentpassword'] = self::MEMBER_PASS;
		$replayed['updated_data'] = base64_encode(self::FORGED);
		$replayed['updated_key'] = $this->grabValidationKey($I);
		$I->sendPostRequest('/usersettings.php', $replayed);
		$this->seeStillAnOrdinaryMember($I, $userId, 'a replayed validation key');

		// And as it vouched for it afterwards: a keyed hash the member can compute,
		// because e_TOKEN is rendered into the form they are looking at. This starts a
		// fresh confirmation, so it meets a live pending change rather than the one the
		// request above has already spent.
		$token = $this->startEmailChange($I, 'forge-newer@example.test');
		$forged = $this->confirmationFields($I);
		$forged['currentpassword'] = self::MEMBER_PASS;
		$forged['updated_data'] = base64_encode(self::FORGED);
		$forged['updated_key'] = hash_hmac('sha256', self::FORGED, $token);
		$I->sendPostRequest('/usersettings.php', $forged);
		$this->seeStillAnOrdinaryMember($I, $userId, 'a recomputed validation key');
	}

	/**
	 * The control that stops "refuse everything" passing the test above: the change
	 * the member actually asked for still lands, and it lands even though the posted
	 * blob says something else entirely.
	 */
	public function theChangeTheMemberAskedForStillLands(AcceptanceTester $I)
	{
		$I->wantTo('Apply the change the member asked for and ignore what the form carried back');

		$userId = $this->haveMember($I, 'ignored');
		$this->loginAsMember($I, 'ignored');

		$token = $this->startEmailChange($I, 'ignored-new@example.test');
		$fields = $this->confirmationFields($I);
		$fields['currentpassword'] = self::MEMBER_PASS;
		$fields['updated_data'] = base64_encode(self::FORGED);
		$fields['updated_key'] = hash_hmac('sha256', self::FORGED, $token);

		$I->sendPostRequest('/usersettings.php', $fields);

		$I->assertSame('ignored-new@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)),
			'the email change the member asked for was saved');
		$this->seeStillAnOrdinaryMember($I, $userId, 'the forged payload alongside it');
	}

	/**
	 * Only the most recent confirmation is held, so confirming an older one must be
	 * refused rather than quietly spending whatever is in the slot now. Without that,
	 * anyone holding the session could plant a change and have the account holder's
	 * own re-authentication apply it.
	 */
	public function anOlderConfirmationCannotSpendANewerChange(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmation that is not the change now waiting');

		$userId = $this->haveMember($I, 'displaced');
		$this->loginAsMember($I, 'displaced');

		$this->startEmailChange($I, 'displaced-first@example.test');
		$first = $this->confirmationFields($I);
		$first['currentpassword'] = self::MEMBER_PASS;

		// A second confirmation displaces the first, as a second tab would.
		$this->startEmailChange($I, 'displaced-second@example.test');
		$second = $this->confirmationFields($I);
		$second['currentpassword'] = self::MEMBER_PASS;

		$I->sendPostRequest('/usersettings.php', $first);

		$I->assertSame('displaced@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)),
			'the displaced confirmation applied neither its own change nor the newer one');
		$I->assertStringNotContainsString(self::SAVED_MARKER, $I->grabResponseBody(),
			'and did not report a successful update');

		// The change that is actually waiting still completes.
		$I->sendPostRequest('/usersettings.php', $second);

		$I->assertSame('displaced-second@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)));
	}


	/**
	 * A confirmation the server is no longer holding anything for saves nothing and
	 * says so, rather than reporting success.
	 */
	public function aConfirmationWithNothingHeldIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmation the server has nothing pending for');

		$userId = $this->haveMember($I, 'nothingheld');
		$this->loginAsMember($I, 'nothingheld');

		$this->startEmailChange($I, 'nothingheld-new@example.test');
		$fields = $this->confirmationFields($I);
		$fields['currentpassword'] = self::MEMBER_PASS;

		// Spend it once, legitimately.
		$I->sendPostRequest('/usersettings.php', $fields);
		$I->assertSame('nothingheld-new@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)));

		// Replaying it has nothing left to apply.
		$I->updateInDatabase('e107_user', array('user_email' => 'nothingheld@example.test'), array('user_id' => $userId));
		$I->sendPostRequest('/usersettings.php', $fields);

		$I->assertSame('nothingheld@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)),
			'the replay applied nothing');
		$I->assertStringNotContainsString(self::SAVED_MARKER, $I->grabResponseBody(),
			'and did not report a successful update');
	}

	/**
	 * The cheapest attack of the set: post the confirmation without ever asking for
	 * one. It needs no rendered form, so it was the only route that did not turn on
	 * the site's password encoding, and it is the one route the suite never walked.
	 */
	public function aConfirmationNobodyAskedForWritesNothing(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmation the member invented rather than asked for');

		$userId = $this->haveMember($I, 'coldpost');
		$this->loginAsMember($I, 'coldpost');

		$token = $this->grabToken($I);

		$I->sendPostRequest('/usersettings.php', array(
			'SaveValidatedInfo' => '1',
			'updated_data'      => base64_encode(self::FORGED),
			'updated_key'       => crypt(self::FORGED, $token),
			'currentpassword'   => self::MEMBER_PASS,
			'e-token'           => $token,
		));

		$this->seeStillAnOrdinaryMember($I, $userId, 'a confirmation nobody asked for');
		$I->assertStringNotContainsString(self::SAVED_MARKER, $I->grabResponseBody(),
			'and no success was reported');
	}


	/**
	 * A confirmed change is spent the moment it is confirmed: the session slot is
	 * cleared before the write, so a write the database refuses cannot be retried.
	 * Answering it with a page carrying no message leaves the member believing it
	 * saved.
	 */
	public function aConfirmedWriteThatFailsIsReported(AcceptanceTester $I)
	{
		$I->wantTo('Report a confirmed change the database refused');

		$userId = $this->haveMember($I, 'failedwrite');
		$this->loginAsMember($I, 'failedwrite');

		$this->startEmailChange($I, 'failedwrite-new@example.test', array('username' => 'takenname'));
		$fields = $this->confirmationFields($I);
		$fields['currentpassword'] = self::MEMBER_PASS;

		// Somebody claims the display name between the prompt and the confirmation, so
		// the write fails on the unique index rather than on anything the member did.
		$this->haveMember($I, 'nameholder', 'takenname');

		$I->sendPostRequest('/usersettings.php', $fields);

		$I->assertStringContainsString(self::WRITE_REFUSED, $I->grabResponseBody(),
			'the member is told the change could not be written');
		$I->assertSame('failedwrite@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)),
			'and nothing of it landed');
	}


	/**
	 * The change is held for one row and the write took its target from the query
	 * string, so an administrator confirming their own stashed change at somebody
	 * else's id put it on that record instead.
	 */
	public function aConfirmationCannotBeRedirectedAtAnotherRow(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmation aimed at a row other than the one it was stashed for');

		$otherId = $this->haveMember($I, 'bystander');

		$I->loginAsAdmin();

		$this->startEmailChange($I, 'administrator-new@example.test');
		$fields = $this->confirmationFields($I);
		$fields['currentpassword'] = self::MEMBER_PASS;

		$I->sendPostRequest('/usersettings.php?' . $otherId, $fields);

		$I->assertSame('bystander@example.test',
			$I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $otherId)),
			'the change stashed for the administrator did not land on another member');
	}


	/**
	 * The three columns the first stage's allow list keeps out of a member's reach:
	 * two that make an administrator, and the external-login binding tracked as its
	 * own advisory, so a re-cut that reopens either one reds here.
	 *
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @param string $how
	 */
	private function seeStillAnOrdinaryMember(AcceptanceTester $I, $userId, $how)
	{
		$I->assertSame('0', (string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $userId)),
			'user_admin was not written through ' . $how);
		$I->assertSame('', (string) $I->grabFromDatabase('e107_user', 'user_perms', array('user_id' => $userId)),
			'user_perms was not written through ' . $how);
		$I->assertSame('', (string) $I->grabFromDatabase('e107_user', 'user_xup', array('user_id' => $userId)),
			'user_xup was not written through ' . $how);
	}

	/**
	 * Post an email change, which is the cheapest way for an ordinary member to reach
	 * the confirmation stage on a default install.
	 *
	 * @param AcceptanceTester $I
	 * @param string $email
	 * @param array $extra further settings fields to change in the same submission
	 * @return string the e-token, which is also e_TOKEN
	 */
	private function startEmailChange(AcceptanceTester $I, $email, array $extra = array())
	{
		$token = $this->grabToken($I);

		$I->sendPostRequest('/usersettings.php', array_merge(array(
			'email'          => $email,
			'hideemail'      => '1',
			'password1'      => '',
			'password2'      => '',
			'e-token'        => $token,
			'updatesettings' => 'Update Settings',
		), $extra));

		$I->assertStringContainsString('currentpassword', $I->grabResponseBody(),
			'the confirmation stage was reached');

		return $token;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array
	 */
	private function confirmationFields(AcceptanceTester $I)
	{
		$body = $I->grabResponseBody();

		$field = strpos($body, "name='currentpassword'");
		$I->assertNotFalse($field, 'the confirmation form is rendered');
		$open = strrpos((string) substr($body, 0, $field), "<form method='post'");
		$I->assertNotFalse($open, 'the current-password field sits inside a form');
		$close = strpos($body, '</form>', $open);
		$body = (string) substr($body, $open, $close - $open);

		$matches = array();
		preg_match_all("#<input type='hidden' name='([^']+)' value='([^']*)' />#", $body, $matches, PREG_SET_ORDER);

		$fields = array();
		foreach ($matches as $match)
		{
			$fields[$match[1]] = $match[2];
		}

		$I->assertArrayHasKey('e-token', $fields, 'the confirmation form carries an e-token');
		$fields['SaveValidatedInfo'] = '1';

		return $fields;
	}

	/**
	 * The validation key the confirmation form itself offered, where it still offers one.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabValidationKey(AcceptanceTester $I)
	{
		$matches = array();
		preg_match("#<input type='hidden' name='updated_key' value='([^']*)' />#", $I->grabResponseBody(), $matches);

		return isset($matches[1]) ? $matches[1] : '';
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$I->amOnPage('/usersettings.php');

		$matches = array();
		$found = preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $I->grabPageSource(), $matches);
		$I->assertNotEmpty($found, 'usersettings.php renders an e-token');

		return $matches[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string $displayName
	 * @return int
	 */
	private function haveMember(AcceptanceTester $I, $name, $displayName = self::MEMBER)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name'      => $displayName,
			'user_loginname' => $name,
			'user_login'     => $name,
			'user_password'  => password_hash(self::MEMBER_PASS, PASSWORD_DEFAULT),
			'user_email'     => $name . '@example.test',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_admin'     => 0,
			'user_class'     => '',
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
			'user_xup'       => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 */
	private function loginAsMember(AcceptanceTester $I, $name)
	{
		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', $name);
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');
		$I->amOnPage('/usersettings.php');
		$I->seeInSource($name);
	}
}
