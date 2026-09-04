<?php

/**
 * Regression tests for GHSA-9qgj-v67f-r22q: usersettings.php saved a new account
 * password without ever asking for the current one, while the same page already
 * refused an email change until the current password was given.
 *
 * A member changing their own password now goes through the confirmation stage
 * that email and login-name changes already used, so a stolen session no longer
 * converts into a credential the thief knows.
 *
 * The stage-2 controls matter as much as the refusals: the confirmation form is
 * where the fix does its work, and the hashes it applies are now carried in the
 * session rather than through the form, so a change that silently dropped the
 * new password would look exactly like a change that was refused.
 */
class UsersettingsPasswordReauthCest
{
	const MEMBER = 'reauthmember';
	const MEMBER_PASS = 'Member1Pass!';
	const NEW_PASS = 'BrandNewPass1!';
	const ADMIN_SET_PASS = 'AdminSetPass1!';
	// LAN_USET_41, the success message, and LAN_USER_01, the display name's field label.
	const SAVED_MARKER = 'Settings updated and saved into database.';
	const DISPLAY_NAME_LABEL = 'Display name';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
	}

	/**
	 * The finding itself.
	 */
	public function aPasswordChangeWithoutTheCurrentPasswordIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a self-service password change that carries no current password');

		$userId = $this->haveMember($I, 'norefuse');
		$this->loginAsMember($I, 'norefuse');
		$before = $this->grabHash($I, $userId);

		$this->postSettings($I, 'norefuse', array(
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));

		$I->assertSame($before, $this->grabHash($I, $userId), 'the stored password is untouched');
		$I->assertStringContainsString('currentpassword', $I->grabResponseBody(),
			'the confirmation form is rendered instead');
	}

	/**
	 * The unfixed code hashed the new password and skipped the email
	 * re-authentication in the same breath, so sending both in one request was
	 * cheaper than sending the email change on its own.
	 */
	public function aPasswordChangeDoesNotWaiveTheEmailConfirmation(AcceptanceTester $I)
	{
		$I->wantTo('Keep the email confirmation when a password change rides along');

		$userId = $this->haveMember($I, 'combined');
		$this->loginAsMember($I, 'combined');
		$before = $this->grabHash($I, $userId);

		$this->postSettings($I, 'combined', array(
			'email'     => 'attacker@evil.test',
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));

		$I->assertSame('combined@example.test', $this->grabEmail($I, $userId), 'the email is untouched');
		$I->assertSame($before, $this->grabHash($I, $userId), 'the password is untouched');
		$I->assertStringContainsString('currentpassword', $I->grabResponseBody(),
			'the confirmation form is rendered instead');
	}

	/**
	 * The waiver survived in one shape: a validation error riding along dropped the
	 * confirmation without dropping the email change, so the recovery address moved
	 * on a submission the member was answered with an error.
	 */
	public function aValidationErrorDoesNotWaiveTheEmailConfirmation(AcceptanceTester $I)
	{
		$I->wantTo('Keep the email confirmation when a validation error rides along with the password');

		$userId = $this->haveMember($I, 'errored');
		$this->loginAsMember($I, 'errored');
		$before = $this->grabHash($I, $userId);

		$this->postSettings($I, 'errored', array(
			'username'  => 'a',
			'email'     => 'attacker@evil.test',
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));

		$body = $I->grabResponseBody();
		$I->assertSame('errored@example.test', $this->grabEmail($I, $userId), 'the email is untouched');
		$I->assertSame($before, $this->grabHash($I, $userId), 'the password is untouched');
		$I->assertStringContainsString(self::DISPLAY_NAME_LABEL, $body,
			'the member is told which field is wrong');
		$I->assertStringNotContainsString(self::SAVED_MARKER, $body,
			'and is not told the settings were saved');
	}


	/**
	 * Refusing everything would pass both tests above. This is the same change,
	 * carried through the confirmation stage, and it has to land.
	 */
	public function aConfirmedPasswordChangeIsSaved(AcceptanceTester $I)
	{
		$I->wantTo('Save a password change once the current password is confirmed');

		$userId = $this->haveMember($I, 'confirmed');
		$this->loginAsMember($I, 'confirmed');

		$this->postSettings($I, 'confirmed', array(
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));
		$this->confirmWith($I, self::MEMBER_PASS);

		$I->assertTrue(password_verify(self::NEW_PASS, $this->grabHash($I, $userId)),
			'the new password is the stored credential');

		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', 'confirmed');
		$I->fillField('userpass', self::NEW_PASS);
		$I->click('userlogin');
		$I->amOnPage('/usersettings.php');
		$I->seeInSource('confirmed');
	}

	/**
	 * The email address is the account-recovery address, so a confirmation that
	 * saved the password but dropped the email change would be a quiet
	 * half-completion.
	 */
	public function aConfirmedEmailAndPasswordChangeAreBothSaved(AcceptanceTester $I)
	{
		$I->wantTo('Save an email and a password change together once confirmed');

		$userId = $this->haveMember($I, 'both');
		$this->loginAsMember($I, 'both');

		$this->postSettings($I, 'both', array(
			'email'     => 'both-new@example.test',
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));
		$this->confirmWith($I, self::MEMBER_PASS);

		$I->assertSame('both-new@example.test', $this->grabEmail($I, $userId));
		$I->assertTrue(password_verify(self::NEW_PASS, $this->grabHash($I, $userId)));
	}

	/**
	 * Getting the current password wrong saves nothing, and the member can type it
	 * again rather than starting the change over.
	 */
	public function aWrongCurrentPasswordSavesNothing(AcceptanceTester $I)
	{
		$I->wantTo('Save nothing when the confirmation carries the wrong password');

		$userId = $this->haveMember($I, 'wrongpass');
		$this->loginAsMember($I, 'wrongpass');
		$before = $this->grabHash($I, $userId);

		$this->postSettings($I, 'wrongpass', array(
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));
		list($action, $fields) = $this->confirmationForm($I);
		$fields['currentpassword'] = 'NotTheCurrentPassword!';
		$I->sendPostRequest($action, $fields);

		$I->assertSame($before, $this->grabHash($I, $userId), 'nothing was saved');

		$fields['currentpassword'] = self::MEMBER_PASS;
		$I->sendPostRequest($action, $fields);

		$I->assertTrue(password_verify(self::NEW_PASS, $this->grabHash($I, $userId)),
			'and the second attempt completes the change');
	}


	/**
	 * The confirmation page has nowhere to show a validation error, so a submission
	 * that has one is answered with the errors and saves nothing.
	 */
	public function anInvalidFieldIsReportedInsteadOfTheConfirmation(AcceptanceTester $I)
	{
		$I->wantTo('Report a validation error rather than ask to confirm a change that cannot be saved');

		$userId = $this->haveMember($I, 'baddata');
		$this->loginAsMember($I, 'baddata');
		$before = $this->grabHash($I, $userId);

		$this->postSettings($I, 'baddata', array(
			'username'  => 'x',
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));

		$body = $I->grabResponseBody();
		$I->assertStringNotContainsString('currentpassword', $body,
			'the confirmation form is not offered');
		$I->assertStringContainsString(self::DISPLAY_NAME_LABEL, $body,
			'the member is told which field is wrong');
		$I->assertStringNotContainsString(self::SAVED_MARKER, $body,
			'and is not told the settings were saved');
		$I->assertSame($before, $this->grabHash($I, $userId), 'and the password is untouched');
	}


	/**
	 * A password change the member walked away from must not be applied to some
	 * later confirmation. Both submissions here carry a second field as well, so
	 * both confirmations round-trip a payload of the same shape; nothing the
	 * confirmation form carries can be relied on to tell them apart.
	 */
	public function anAbandonedPasswordChangeIsNotAppliedToALaterConfirmation(AcceptanceTester $I)
	{
		$I->wantTo('Drop a password change the member abandoned at the confirmation');

		$userId = $this->haveMember($I, 'abandoned');
		$this->loginAsMember($I, 'abandoned');

		$this->postSettings($I, 'abandoned', array(
			'signature' => 'walked away from this one',
			'password1' => self::NEW_PASS,
			'password2' => self::NEW_PASS,
		));
		list($abandonedAction, $abandonedFields) = $this->confirmationForm($I);

		$this->postSettings($I, 'abandoned', array('email' => 'abandoned-new@example.test'));
		$this->confirmWith($I, self::MEMBER_PASS);

		$I->assertSame('abandoned-new@example.test', $this->grabEmail($I, $userId));
		$I->assertFalse(password_verify(self::NEW_PASS, $this->grabHash($I, $userId)),
			'the abandoned password did not ride in on the email confirmation');
		$I->assertTrue(password_verify(self::MEMBER_PASS, $this->grabHash($I, $userId)),
			'and the account still has the password it started with');

		// Now go back to the confirmation that was walked away from and submit it. The
		// change it was rendered for is gone, so it has to say so rather than report a
		// success that rehashes the password the account already has.
		$abandonedFields['currentpassword'] = self::MEMBER_PASS;
		$I->sendPostRequest($abandonedAction, $abandonedFields);

		$I->assertStringNotContainsString(self::SAVED_MARKER, $I->grabResponseBody(),
			'the stale confirmation is not reported as a successful update');
		$I->assertTrue(password_verify(self::MEMBER_PASS, $this->grabHash($I, $userId)),
			'and it still has the password it started with');
	}

	/**
	 * BC control. An email change on its own took the confirmation route before
	 * this fix and still does, and stage 2 must still re-derive the password
	 * hashes from the password it was given.
	 */
	public function aConfirmedEmailChangeStillCompletes(AcceptanceTester $I)
	{
		$I->wantTo('Still complete an email-only change through the confirmation form');

		$userId = $this->haveMember($I, 'emailonly');
		$this->loginAsMember($I, 'emailonly');

		$this->postSettings($I, 'emailonly', array('email' => 'emailonly-new@example.test'));
		$this->confirmWith($I, self::MEMBER_PASS);

		$I->assertSame('emailonly-new@example.test', $this->grabEmail($I, $userId));

		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', 'emailonly');
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');
		$I->amOnPage('/usersettings.php');
		$I->seeInSource('emailonly');
	}

	/**
	 * BC control. An administrator resetting somebody else's password does not
	 * know it, so the confirmation must not be asked of them.
	 */
	public function anAdministratorCanStillSetAnotherUsersPassword(AcceptanceTester $I)
	{
		$I->wantTo('Let an administrator set another account password without knowing it');

		$userId = $this->haveMember($I, 'adminset');

		$I->loginAsAdmin();
		$I->amOnPage('/usersettings.php?' . $userId);
		$I->sendPostRequest('/usersettings.php?' . $userId, array(
			'email'          => 'adminset@example.test',
			'hideemail'      => '1',
			'password1'      => self::ADMIN_SET_PASS,
			'password2'      => self::ADMIN_SET_PASS,
			'e-token'        => $this->grabToken($I, '/usersettings.php?' . $userId),
			'updatesettings' => 'Update Settings',
		));

		$I->assertTrue(password_verify(self::ADMIN_SET_PASS, $this->grabHash($I, $userId)),
			'the administrator-supplied password is the stored credential');
	}

	/**
	 * The exemption above is for an administrator who cannot know the password they
	 * are setting. Their own account is not that case, and the guard tested the query
	 * string rather than the record being written, so the main administrator editing
	 * itself at its own id took the exemption.
	 */
	public function anAdministratorSettingTheirOwnPasswordIsStillAsked(AcceptanceTester $I)
	{
		$I->wantTo('Ask an administrator to confirm a password change to their own account');

		$I->loginAsAdmin();

		$adminId = $I->grabFromDatabase('e107_user', 'user_id', array('user_loginname' => 'admin'));
		$before  = $this->grabHash($I, $adminId);

		$I->sendPostRequest('/usersettings.php?' . $adminId, array(
			'email'          => $this->grabEmail($I, $adminId),
			'hideemail'      => '1',
			'password1'      => self::ADMIN_SET_PASS,
			'password2'      => self::ADMIN_SET_PASS,
			'e-token'        => $this->grabToken($I, '/usersettings.php?' . $adminId),
			'updatesettings' => 'Update Settings',
		));

		$I->assertSame($before, $this->grabHash($I, $adminId), 'the stored password is untouched');
		$I->assertStringContainsString('currentpassword', $I->grabResponseBody(),
			'the confirmation form is rendered instead');
	}


	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @return int
	 */
	private function haveMember(AcceptanceTester $I, $name)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name'      => self::MEMBER,
			'user_loginname' => $name,
			'user_login'     => $name,
			'user_password'  => password_hash(self::MEMBER_PASS, PASSWORD_DEFAULT),
			'user_email'     => $name . '@example.test',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
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

	/**
	 * Post an ordinary settings update, carrying only the fields under test.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param array $fields
	 */
	private function postSettings(AcceptanceTester $I, $name, array $fields)
	{
		$post = array(
			'email'          => $name . '@example.test',
			'hideemail'      => '1',
			'password1'      => '',
			'password2'      => '',
			'e-token'        => $this->grabToken($I, '/usersettings.php'),
			'updatesettings' => 'Update Settings',
		);

		$I->sendPostRequest('/usersettings.php', array_merge($post, $fields));
	}

	/**
	 * Read back the confirmation form the last response rendered: its own action, and
	 * the fields a browser would submit to it.
	 *
	 * @param AcceptanceTester $I
	 * @return array the action URL, then the fields
	 */
	private function confirmationForm(AcceptanceTester $I)
	{
		$form = $this->confirmationFormMarkup($I);

		$action = array();
		$I->assertNotEmpty(preg_match("#<form method='post' action='([^']*)'>#", $form, $action),
			'the confirmation form declares an action');

		$matches = array();
		preg_match_all("#<input type='hidden' name='([^']+)' value='([^']*)' />#", $form, $matches, PREG_SET_ORDER);

		$fields = array();
		foreach ($matches as $match)
		{
			$fields[$match[1]] = $match[2];
		}

		$I->assertArrayHasKey('e-token', $fields, 'the confirmation form carries an e-token');
		$fields['SaveValidatedInfo'] = '1';

		return array($action[1], $fields);
	}

	/**
	 * The whole <form> the confirmation was rendered into, so the caller submits exactly
	 * the fields it offered rather than a list of names fixed at the time of writing.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function confirmationFormMarkup(AcceptanceTester $I)
	{
		$body = $I->grabResponseBody();

		$field = strpos($body, "name='currentpassword'");
		$I->assertNotFalse($field, 'the confirmation form is rendered');

		$open = strrpos((string) substr($body, 0, $field), "<form method='post'");
		$I->assertNotFalse($open, 'the current-password field sits inside a form');

		$close = strpos($body, '</form>', $open);
		$I->assertNotFalse($close, 'that form is closed');

		return (string) substr($body, $open, $close - $open);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $password
	 */
	private function confirmWith(AcceptanceTester $I, $password)
	{
		list($action, $fields) = $this->confirmationForm($I);
		$fields['currentpassword'] = $password;

		$I->sendPostRequest($action, $fields);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $path
	 * @return string
	 */
	private function grabToken(AcceptanceTester $I, $path)
	{
		$I->amOnPage($path);

		$matches = array();
		$found = preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $I->grabPageSource(), $matches);
		$I->assertNotEmpty($found, $path . ' renders an e-token');

		return $matches[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @return string
	 */
	private function grabHash(AcceptanceTester $I, $userId)
	{
		return $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @return string
	 */
	private function grabEmail(AcceptanceTester $I, $userId)
	{
		return $I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId));
	}
}
