<?php
namespace Helper;

use Codeception\Lib\Interfaces\Web;
use Test\Password;
use Test\Poll;

/**
 * Shared sign-in helper for the e107 Codeception suites.
 *
 * Adds loginAsAdmin(), amSignedInAs() and grabFreshAdminToken() to any actor
 * that enables the module, so acceptance (PhpBrowser) and webdriver (WebDriver)
 * tests can authenticate without repeating the form fields or the success
 * marker. Enable a browser module implementing {@see Web} (PhpBrowser or
 * WebDriver) in the same suite.
 *
 * {@see ADMIN_USER} / {@see ADMIN_PASS} are the canonical test credentials. Both
 * fixtures resolve to them: the acceptance install creates this account and the
 * sample dump (used by the unit and webdriver suites) ships it. Reference the
 * constants instead of repeating the literals so the canary password lives in
 * exactly one place.
 */
class AdminLogin extends AppFixture
{
	const ADMIN_USER = 'admin';
	const ADMIN_PASS = 'x107';
	const LOGIN_PATH = '/e107_admin/admin.php';
	// The dashboard greets "<DisplayName>'s Control Panel"; match the suffix so
	// the marker is independent of the logged-in account's display name.
	const CONTROL_PANEL_MARKER = "'s Control Panel";
	const TOKEN_FIELD_PATTERN = '/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/';
	const SIGN_IN_PROBE = 'e107_tests_sign_in_probe.php';
	const SIGNED_IN_ADMIN = 'SIGNED_IN_ADMIN';

	/**
	 * Log into the admin area and assert the control-panel marker is shown, first storing a cheap hash of a matching password ({@see AdminLogin::cheapenStoredHash()}); in a browser it declares e107.org ({@see AdminLogin::expectAdminPagesToAskE107Org()}).
	 *
	 * @param string|null $user Defaults to {@see ADMIN_USER}.
	 * @param string|null $pass Defaults to {@see ADMIN_PASS}.
	 * @return void
	 */
	public function loginAsAdmin($user = null, $pass = null)
	{
		$user = $user === null ? self::ADMIN_USER : $user;
		$pass = $pass === null ? self::ADMIN_PASS : $pass;
		$browser = $this->browser();
		$this->cheapenStoredHash($user, $pass);

		$browser->amOnPage(self::LOGIN_PATH);
		$browser->fillField('authname', $user);
		$browser->fillField('authpass', $pass);
		$browser->click('authsubmit');
		$this->expectAdminPagesToAskE107Org();

		if (method_exists($browser, 'waitForText'))
		{
			$this->waitForTextInSource($browser, self::CONTROL_PANEL_MARKER, 10);
		}
		else
		{
			$browser->see(self::CONTROL_PANEL_MARKER);
		}
	}

	/**
	 * Be signed in as $user without the form: a guarded probe sets the session through {@see \UserHandler::makeUserCookie()}, and nothing else a sign-in checks or does runs; for an administrator in a browser it declares e107.org ({@see AdminLogin::expectAdminPagesToAskE107Org()}).
	 *
	 * @param string|null $user login name; defaults to {@see ADMIN_USER}
	 * @return void
	 * @throws \RuntimeException when there is no such account
	 */
	public function amSignedInAs($user = null)
	{
		$this->app()->writeAppFile(self::SIGN_IN_PROBE, self::signInProbeSource());
		$answer = $this->getModule('\Helper\Probe')->_probe(self::SIGN_IN_PROBE, 'user='.rawurlencode($user === null ? self::ADMIN_USER : $user));

		if (strpos($answer, self::SIGNED_IN_ADMIN) !== false)
		{
			$this->expectAdminPagesToAskE107Org();
		}
	}

	/**
	 * In a browser an administrator's pages ask e107.org about updates, so signing one in there declares it.
	 *
	 * @return void
	 */
	private function expectAdminPagesToAskE107Org()
	{
		if (method_exists($this->browser(), 'waitForText'))
		{
			$this->getModule('\Helper\Outbound')->expectOutboundRequest('e107.org');
		}
	}

	/**
	 * Swaps the account's stored hash for a {@see Password::hash()} of the same password, once it is proven to match.
	 *
	 * @param string $user
	 * @param string $pass
	 * @return void
	 */
	private function cheapenStoredHash($user, $pass)
	{
		if (!$this->hasModule('\Helper\SiteDb'))
		{
			return;
		}

		try
		{
			$dbh = $this->getModule('\Helper\SiteDb')->_getDbh();
			$select = $dbh->prepare('SELECT `user_id`, `user_password` FROM `'.E107Base::E107_MYSQL_PREFIX.'user` WHERE `user_loginname` = ?');
			$select->execute(array($user));
			$row = $select->fetch(\PDO::FETCH_ASSOC);
		}
		catch (\PDOException $e)
		{
			return;
		}

		if (empty($row) || !Password::isCostly($row['user_password']) || !password_verify($pass, $row['user_password']))
		{
			return;
		}

		$update = $dbh->prepare('UPDATE `'.E107Base::E107_MYSQL_PREFIX.'user` SET `user_password` = ? WHERE `user_id` = ?');
		$update->execute(array(Password::hash($pass), $row['user_id']));
	}

	/**
	 * Waits for text to turn up in the page source, which unlike waitForText()
	 * holds no element handle across the form submit.
	 *
	 * @param mixed  $browser browser module, already resolved
	 * @param string $marker  text to wait for
	 * @param int    $timeout seconds
	 * @return void
	 */
	private function waitForTextInSource($browser, $marker, $timeout)
	{
		$arrived = Poll::until(function () use ($browser, $marker)
		{
			try
			{
				return strpos((string) $browser->grabPageSource(), $marker) !== false;
			}
			catch (\Exception $e)
			{
				return false;
			}
		}, $timeout);

		if (!$arrived)
		{
			$browser->see($marker);
		}
	}

	/**
	 * Grab the current `e-token` CSRF value from an authenticated admin page.
	 *
	 * @param string $adminPagePath Defaults to {@see LOGIN_PATH}.
	 * @return string
	 * @throws \RuntimeException When the page renders no `e-token` field, which
	 *         usually means the session is unauthenticated.
	 */
	public function grabFreshAdminToken($adminPagePath = self::LOGIN_PATH)
	{
		$this->browser()->amOnPage($adminPagePath);

		return $this->grabToken();
	}

	/**
	 * The `e-token` the page the browser is on renders in a form.
	 *
	 * @return string
	 * @throws \RuntimeException When the page renders no `e-token` field.
	 */
	public function grabToken()
	{
		$matches = array();
		if (!preg_match(self::TOKEN_FIELD_PATTERN, $this->browser()->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The current page rendered no e-token to post back.');
		}

		return $matches[1];
	}

	/**
	 * @return string
	 */
	private static function signInProbeSource()
	{
		return <<<'PHP'
<?php
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$user = e107::getDb()->createQueryBuilder()->select('user_id', 'user_password', 'user_admin')->from('user')
	->where('user_loginname', isset($_GET['user']) ? (string) $_GET['user'] : '')->fetchRow();

if(!$user)
{
	echo "NO_SUCH_USER\n";
	exit;
}

e107::getUserSession()->makeUserCookie($user);
echo "PROBE_OK\n";
echo $user['user_admin'] ? "SIGNED_IN_ADMIN\n" : '';
PHP;
	}
}
