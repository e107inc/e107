<?php
namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\Web;
use Codeception\Module as CodeceptionModule;

/**
 * Shared admin-login helper for the e107 Codeception suites.
 *
 * Adds loginAsAdmin() and grabFreshAdminToken() to any actor that enables the
 * module, so acceptance (PhpBrowser) and webdriver (WebDriver) tests can
 * authenticate without repeating the form fields or the success marker. Enable
 * a browser module implementing {@see Web} (PhpBrowser or WebDriver) in the
 * same suite.
 *
 * {@see ADMIN_USER} / {@see ADMIN_PASS} are the canonical test credentials. Both
 * fixtures resolve to them: the acceptance install creates this account and the
 * sample dump (used by the unit and webdriver suites) ships it. Reference the
 * constants instead of repeating the literals so the canary password lives in
 * exactly one place.
 */
class AdminLogin extends CodeceptionModule
{
	const ADMIN_USER = 'admin';
	const ADMIN_PASS = 'x107';
	const LOGIN_PATH = '/e107_admin/admin.php';
	// The dashboard greets "<DisplayName>'s Control Panel"; match the suffix so
	// the marker is independent of the logged-in account's display name.
	const CONTROL_PANEL_MARKER = "'s Control Panel";
	const TOKEN_FIELD_PATTERN = '/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/';

	/**
	 * Log into the admin area and assert the control-panel marker is shown.
	 *
	 * @param string|null $user Defaults to {@see ADMIN_USER}.
	 * @param string|null $pass Defaults to {@see ADMIN_PASS}.
	 * @return void
	 */
	public function loginAsAdmin($user = null, $pass = null)
	{
		$browser = $this->resolveBrowserModule();

		$browser->amOnPage(self::LOGIN_PATH);
		$browser->fillField('authname', $user === null ? self::ADMIN_USER : $user);
		$browser->fillField('authpass', $pass === null ? self::ADMIN_PASS : $pass);
		$browser->click('authsubmit');

		if (method_exists($browser, 'waitForText'))
		{
			$browser->waitForText(self::CONTROL_PANEL_MARKER, 10);
		}
		else
		{
			$browser->see(self::CONTROL_PANEL_MARKER);
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
		$browser = $this->resolveBrowserModule();

		$browser->amOnPage($adminPagePath);
		$source = $browser->grabPageSource();

		$matches = array();
		if (!preg_match(self::TOKEN_FIELD_PATTERN, $source, $matches))
		{
			throw new \RuntimeException(
				"Could not locate an e-token on '{$adminPagePath}'. "
				. "Ensure the session is authenticated and the page renders an admin form."
			);
		}

		return $matches[1];
	}

	/**
	 * @return \Codeception\Module|Web
	 * @throws ModuleException When no supported browser module is enabled.
	 */
	private function resolveBrowserModule()
	{
		foreach (['PhpBrowser', 'WebDriver'] as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new ModuleException(
			__CLASS__,
			'Enable a browser module (PhpBrowser or WebDriver) in this suite.'
		);
	}
}
