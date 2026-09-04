<?php

/**
 * What Admin > Password writes when an administrator changes their own
 * password.
 *
 * Three things about that one screen. It has to write at all: the field-type
 * table it hands to validatorClass::addFieldTypes() is read off the user
 * handler from outside the class, and a private property there fatals the
 * request before the update runs. It has to refuse a confirmation that is a
 * different password: "1e3" and "1000" are two spellings PHP reads as the same
 * number, so a loose comparison accepts them and stores the first box, locking
 * the administrator out of an account they believe carries the second. And
 * where email login is on, the second hash it stores has to be a hash of the
 * password that was typed, not of whatever is left after the plaintext has been
 * scrubbed out of $_POST.
 *
 * The submission is assembled from the fields the screen itself renders, so a
 * form that stops rendering the token the session check asks for, or the marker
 * its own handler asks for, fails here rather than passing on fields the test
 * supplied for it.
 *
 * Asserted through the application's own CheckPassword() in a fresh process,
 * because a hash carries a salt and cannot be compared by value.
 *
 * @see e107_admin/updateadmin.php the screen under test
 */
class AdminPasswordChangeCest
{
	const PROBE_FILE = 'e107_tests_admin_password_probe.php';

	const PAGE = '/e107_admin/updateadmin.php';

	/** What this test types into both boxes when it means to change the password. */
	const NEW_PASS = 'x107-changed';

	/** Two spellings of one number, which is what a loose confirmation accepts. */
	const MISTYPED = '1e3';
	const MISTYPED_CONFIRMATION = '1000';

	/** Known only to this run, so the probe answers this Cest and nothing else. */
	private $probeKey;

	public function _before(AcceptanceTester $I)
	{
		$this->probeKey = md5(uniqid('', true));

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage($this->probeUrl('setup'));
		$I->seeInSource('PROBE_OK');

		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('teardown'));
		$I->seeInSource('PROBE_OK');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function theScreenStoresThePasswordThatWasTyped(AcceptanceTester $I)
	{
		$I->wantTo('change my own administrator password from Admin > Password');

		$this->submitPasswordChange($I, self::NEW_PASS, self::NEW_PASS);

		$I->dontSeeInSource('Cannot access private property');

		$stored = $this->grabStoredPasswordCheck($I, self::NEW_PASS);

		$I->assertTrue($stored['password'],
			'Admin > Password must store the password typed into it.');

		$I->assertTrue($stored['email_password'],
			'With email login on, the email password must be a hash of the typed password and not of the empty string.');
	}

	public function twoSpellingsOfOneNumberAreNotAConfirmation(AcceptanceTester $I)
	{
		$I->wantTo('be refused rather than handed a password I never typed');

		$this->submitPasswordChange($I, self::MISTYPED, self::MISTYPED_CONFIRMATION);

		$mistyped = $this->grabStoredPasswordCheck($I, self::MISTYPED);

		$I->assertFalse($mistyped['password'],
			'"'.self::MISTYPED.'" and "'.self::MISTYPED_CONFIRMATION.'" are two different passwords, so neither may be stored.');

		$unchanged = $this->grabStoredPasswordCheck($I, \Helper\AdminLogin::ADMIN_PASS);

		$I->assertTrue($unchanged['password'],
			'A refused change must leave the password the account already had.');
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * Submit the form with every hidden field the screen rendered into it.
	 *
	 * @param AcceptanceTester $I
	 * @param string $password
	 * @param string $confirmation
	 * @return void
	 */
	private function submitPasswordChange(AcceptanceTester $I, $password, $confirmation)
	{
		$I->amOnPage(self::PAGE);
		$source = $I->grabPageSource();

		$I->sendPostRequest(self::PAGE, array(
			'update_settings' => 'no-value',
			'ac'              => $this->grabHiddenField($source, 'ac'),
			'e-token'         => $this->grabHiddenField($source, 'e-token'),
			'a_password'      => $password,
			'a_password2'     => $confirmation,
		));
	}

	/**
	 * @param string $source rendered page
	 * @param string $name hidden field name
	 * @return string
	 * @throws RuntimeException when the screen renders no such field, which is a defect in the screen
	 */
	private function grabHiddenField($source, $name)
	{
		$matches = array();
		$pattern = '/name=[\'"]'.preg_quote($name, '/').'[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/';

		if (!preg_match($pattern, $source, $matches))
		{
			throw new RuntimeException('Admin > Password rendered no "'.$name.'" field, so the request it builds cannot be the one the browser sends.');
		}

		return $matches[1];
	}

	/**
	 * Ask the application, in a fresh process, whether the stored hashes are
	 * hashes of $password.
	 *
	 * @param AcceptanceTester $I
	 * @param string $password
	 * @return array password and email_password, each true when the stored hash matches
	 * @throws RuntimeException when the probe answers with anything else
	 */
	private function grabStoredPasswordCheck(AcceptanceTester $I, $password)
	{
		$I->amOnPage($this->probeUrl('read').'&pass='.urlencode($password));

		$matches = array();
		if (!preg_match('/PROBE_OK (.+)/', $I->grabPageSource(), $matches))
		{
			throw new RuntimeException('The stored-password probe published nothing.');
		}

		return json_decode(trim($matches[1]), true);
	}

	/**
	 * @param string $act
	 * @return string
	 */
	private function probeUrl($act)
	{
		return '/'.self::PROBE_FILE.'?key='.$this->probeKey.'&act='.$act;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return str_replace('%PROBE_KEY%', $this->probeKey, <<<'PHP'
<?php
// Fixture for 0084_AdminPasswordChangeCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(!isset($_GET['key']) || !hash_equals('%PROBE_KEY%', $_GET['key']))
{
	echo "not this run\n";
	return;
}

$adminId = 1;
$backupKey = 'e107_tests_admin_password_backup';
$columns = array('user_password' => 'string', 'user_prefs' => 'string', 'user_pwchange' => 'int');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$config = e107::getConfig('core');
$sql = e107::getDb();
$row = $sql->retrieve('user', 'user_loginname, user_email, user_password, user_prefs, user_pwchange', 'user_id = '.$adminId);

switch($act)
{
	case 'setup':
		if($config->get($backupKey) === null)
		{
			$stash = array('allowEmailLogin' => $config->get('allowEmailLogin'));

			foreach($columns as $name => $type)
			{
				$stash[$name] = $row[$name];
			}

			$config->set($backupKey, $stash);
		}

		$config->set('allowEmailLogin', 2);
		$config->save(false, true, false);
		echo "PROBE_OK setup\n";
		break;

	case 'teardown':
		$stash = $config->get($backupKey);

		if(is_array($stash))
		{
			$data = array();

			foreach($columns as $name => $type)
			{
				$data[$name] = $stash[$name];
			}

			$sql->update('user', array(
				'data'         => $data,
				'_FIELD_TYPES' => $columns,
				'WHERE'        => 'user_id = '.$adminId,
			));

			if($stash['allowEmailLogin'] === null)
			{
				$config->remove('allowEmailLogin');
			}
			else
			{
				$config->set('allowEmailLogin', $stash['allowEmailLogin']);
			}

			$config->remove($backupKey);
			$config->save(false, true, false);
		}

		echo "PROBE_OK teardown\n";
		break;

	case 'read':
		$userMethods = e107::getUserSession();
		$typed = isset($_GET['pass']) ? $_GET['pass'] : '';
		$prefs = e107::getArrayStorage()->unserialize($row['user_prefs']);
		$emailHash = isset($prefs['email_password']) ? $prefs['email_password'] : '';

		echo "PROBE_OK ".json_encode(array(
			'password'       => $userMethods->CheckPassword($typed, $row['user_loginname'], $row['user_password']) === PASSWORD_VALID,
			'email_password' => $emailHash !== '' && $userMethods->CheckPassword($typed, $row['user_email'], $emailHash) === PASSWORD_VALID,
		))."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP
		);
	}
}
