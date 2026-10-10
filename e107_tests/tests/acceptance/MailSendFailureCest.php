<?php

/**
 * A page that sends an email says so when the email could not be sent.
 *
 * e107Email::sendEmail() answers true when the mailer accepted the message and
 * the mailer's error message when it did not; it never answers false. Each page
 * here tested that answer for truthiness, so a message that never left was
 * announced as sent and the failure branch beneath it never ran.
 *
 * For the length of the Cest the mailer sends through /bin/false with logging
 * off, so every send fails the same way wherever the suite runs and nothing
 * leaves the container. After each test the probe puts back the preferences
 * the site had and removes the ones it did not.
 */
class MailSendFailureCest
{
	const PROBE_FILE = 'e107_tests_mail_send_failure_probe.php';

	const PASSWORD = 'mail-send-failure-password';

	/** Login name of the account the quick-add test creates, which the probe removes. */
	const CREATED = 'mailfailcreated';

	const ROUTE_USERS = '/e107_admin/users.php?mode=main&action=list';

	const ROUTE_ADD = '/e107_admin/users.php?mode=main&action=add';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=setup');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=teardown');
	}

	public function aContactMessageThatCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when my contact message could not be sent');

		$I->amOnPage('/contact.php');
		$post = array(
			'author_name'    => 'Mail Send Failure',
			'email_send'     => 'mail-send-failure-sender@example.invalid',
			'subject'        => 'Mail send failure',
			'body'           => 'This body is comfortably longer than the fifteen characters the form insists on.',
			'send-contactus' => 'Send',
			'e-token'        => $I->grabToken(),
		);
		$I->sendPostRequest('/contact.php', array_merge($post, $this->captcha($I)));

		$I->seeInSource("<div class='alert alert-danger'>There was a problem sending your message.</div>");
		$I->dontSee('Your message was sent.');
	}

	public function anAccountRemovalWhoseConfirmationCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the email confirming my account removal could not be sent');

		$id = $I->haveMember('mailfailmember', self::PASSWORD);
		$I->loginAsMember('mailfailmember', self::PASSWORD);
		$session = $I->grabFromDatabase('e107_user', 'user_sess', array('user_id' => $id));

		$I->sendPostRequest('/usersettings.php', array('delete_account' => 1, 'e-token' => $I->grabToken()));

		$I->see('the confirmation email could not be sent');
		$I->dontSee('A confirmation email has been sent');
		$I->assertSame($session, $I->grabFromDatabase('e107_user', 'user_sess', array('user_id' => $id)),
			'a removal key no email carried must not be stored');
	}

	public function aSignupTestEmailThatCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the signup test email could not be sent');

		$I->loginAsAdmin();
		$I->amOnPage('/signup.php');

		if(!preg_match("#href='([^']*signup\.php\?test[^']*)'#", $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the signup page published no test email link');
		}

		$I->amOnPage(html_entity_decode($matches[1]));

		$I->see('the registration mail was not sent');
		$I->dontSee('Please check your inbox.');
	}

	public function aPreferencesTestEmailThatCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the test email from Preferences could not be sent');

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/prefs.php');
		$I->sendPostRequest('/e107_admin/prefs.php', array(
			'testemail'   => 1,
			'testaddress' => 'mail-send-failure-test@example.invalid',
			'e-token'     => $I->grabToken(),
		));

		$I->see('The email could not be sent.');
		$I->dontSee('The email has been successfully sent');
	}

	public function aTestBounceThatCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the test bounce email could not be sent');

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/mailout.php?mode=prefs&action=prefs');
		$I->sendPostRequest('/e107_admin/mailout.php?mode=prefs&action=prefs', array(
			'send_bounce_test' => 1,
			'e-token'          => $I->grabToken(),
		));

		$I->see('Failed Bounce email sent to');
		$I->dontSee('Test Bounce sent to');
	}

	public function anApprovalWhoseNotificationCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the email telling a member their account was approved could not be sent');

		$id = $I->haveMember('mailfailapproved', self::PASSWORD, array('user_ban' => 2, 'user_sess' => 'mailfailapproved'));

		$I->loginAsAdmin();
		$this->postUserRowTrigger($I, 'verify', $id);

		$I->see('Failed to send email to:');
		$I->dontSee('Email sent to:');
	}

	public function anActivationResendThatCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when an activation email I re-sent could not be sent');

		$id = $I->haveMember('mailfailresent', self::PASSWORD, array('user_ban' => 2, 'user_sess' => 'mailfailresent'));

		$I->loginAsAdmin();
		$this->postUserRowTrigger($I, 'resend', $id);

		$I->see('Failed to Re-send activation email to');
		$I->dontSee('Activation Email Re-sent to');
	}

	public function aQuickAddWhoseEmailCouldNotBeSentSaysSo(AcceptanceTester $I)
	{
		$I->wantTo('be told when the email to an account I added could not be sent');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_ADD);

		if(!preg_match('/name=[\'"]ac[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the quick add form rendered no ac field');
		}

		$I->sendPostRequest(self::ROUTE_ADD, array(
			'etrigger_submit' => 'Add user',
			'username'        => self::CREATED,
			'loginname'       => self::CREATED,
			'email'           => self::CREATED.'@example.invalid',
			'realname'        => '',
			'password'        => 'Mail-Send-Failure-1',
			'sendconfemail'   => 1,
			'ac'              => $matches[1],
			'e-token'         => $I->grabToken(),
		));

		$I->seeInDatabase('e107_user', array('user_loginname' => self::CREATED));
		$I->see('Error sending email');
		$I->dontSee('Email sent successfully');
	}

	/**
	 * Post a single-row control the way the user list posts one.
	 *
	 * @param AcceptanceTester $I
	 * @param string $trigger operation name
	 * @param int $id row to apply it to
	 * @return void
	 */
	private function postUserRowTrigger(AcceptanceTester $I, $trigger, $id)
	{
		$I->amOnPage(self::ROUTE_USERS);
		$I->sendPostRequest(self::ROUTE_USERS, array(
			'etrigger_'.$trigger => $id,
			'e-token'            => $I->grabToken(),
		));
	}

	/**
	 * A CAPTCHA answer the site will accept, minted inside the site, which keeps the answer in the visitor's session.
	 *
	 * @param AcceptanceTester $I
	 * @return array rand_num and code_verify
	 */
	private function captcha(AcceptanceTester $I)
	{
		$out = $I->probe('act=captcha');

		preg_match('/RAND=(\S+)/', $out, $rand);
		preg_match('/CODE=(\S+)/', $out, $code);

		return array('rand_num' => $rand[1], 'code_verify' => $code[1]);
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$created = self::CREATED;

		return <<<PHP
<?php
// Fixture for MailSendFailureCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$config = e107::getConfig('core');
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$prefs = array(
	'mailer'             => 'sendmail',
	'sendmail'           => '/bin/false',
	'mail_log_options'   => '0,0',
	'mail_bounce_email'  => 'mail-send-failure-bounce@example.invalid',
	'sitecontacts'       => (string) e_UC_MAINADMIN,
	'contact_visibility' => (string) e_UC_PUBLIC,
	'use_coppa'          => '0',
	'user_reg_veri'      => '2',
);

switch(\$act)
{
	case 'setup':
		\$backup = array();
		foreach(\$prefs as \$key => \$value)
		{
			if(\$config->isData(\$key))
			{
				\$backup[\$key] = \$config->get(\$key);
			}
			\$config->set(\$key, \$value);
		}
		\$config->set('e107_tests_mail_send_failure_backup', \$backup);
		\$config->save(false, true, false);
		echo "PROBE_OK\\n";
		break;

	case 'teardown':
		\$backup = (array) \$config->get('e107_tests_mail_send_failure_backup', array());
		foreach(array_keys(\$prefs) as \$key)
		{
			if(array_key_exists(\$key, \$backup))
			{
				\$config->set(\$key, \$backup[\$key]);
			}
			else
			{
				\$config->remove(\$key);
			}
		}
		\$config->remove('e107_tests_mail_send_failure_backup');
		\$config->save(false, true, false);
		e107::getDb()->delete('user', "user_loginname = '$created'");
		echo "PROBE_OK\\n";
		break;

	case 'captcha':
		\$img = e107::getSecureImg();
		\$rand = \$img->createCode();
		echo "PROBE_OK\\n";
		echo "RAND=".\$rand."\\n";
		echo "CODE=".\$img->getSecret()."\\n";
		break;

	default:
		echo "unknown action\\n";
}
PHP;
	}
}
