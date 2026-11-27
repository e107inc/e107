<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The admin skin repair at the top of e107_admin/auth.php, loaded in booted children, each of them a main admin, with the admin header held back.
 */
class authAdminSkinRepairTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\CorePrefs;

	const PAST_AUTH = '@@e107help-past-auth@@';

	const SHOWN_ERRORS = '@@e107help-shown-errors@@';

	/** @var callable */
	private $restorePrefs;

	protected function _before()
	{
		$this->restorePrefs = $this->keepStoredCorePrefs();
	}

	protected function _after()
	{
		call_user_func($this->restorePrefs);
	}

	public function missingPreferenceProvider()
	{
		return array(
			'admincss'   => array('admincss', 'css/modern-light.css'),
			'admintheme' => array('admintheme', 'bootstrap3'),
		);
	}

	/**
	 * @dataProvider missingPreferenceProvider
	 * @param string $key
	 * @param string $repaired
	 */
	public function testAMissingSkinPreferenceIsStoredSoTheNextPageLoads($key, $repaired)
	{
		$this->removeStored($key);

		$first = $this->loadAuth();
		$this->assertStringNotContainsString(self::PAST_AUTH, $first, "the first page should repair the preference and redirect:\n".$first);
		$this->assertSame($repaired, self::storedPref('SitePrefs', $key), "the repair should store the preference it found missing:\n".$first);

		$second = $this->loadAuth();
		$this->assertStringContainsString(self::PAST_AUTH, $second, "the page after the repair should load, not redirect again:\n".$second);
	}

	/**
	 * @dataProvider missingPreferenceProvider
	 * @param string $key
	 */
	public function testARepairThatCannotBeSavedRendersInsteadOfRedirecting($key)
	{
		$this->removeStored($key);

		$page = $this->loadAuth(
			"\$errno = new ReflectionProperty('e_front_model', '_db_errno'); "
			."\$errno->setAccessible(true); "
			."\$errno->setValue(e107::getConfig(), 1); ",
			"echo '".self::SHOWN_ERRORS."', implode('|', (array) e107::getMessage()->get(E_MESSAGE_ERROR, 'default', true, false)), '".self::SHOWN_ERRORS."'; "
		);

		$this->assertStringContainsString(self::PAST_AUTH, $page, "a redirect after a failed save sends the admin straight back to the same failure:\n".$page);
		$this->assertNull(self::storedPref('SitePrefs', $key), "the save was meant to fail:\n".$page);
		$this->assertStringContainsString(self::SHOWN_ERRORS.self::SHOWN_ERRORS, $page, "this runs before the login check, so a failed save must put nothing on the screen:\n".$page);
	}

	/**
	 * Removes $key from the stored core preferences in a child of its own, so this process's copy is left as it was.
	 *
	 * @param string $key
	 */
	private function removeStored($key)
	{
		$this->outputOfBootedCli("e107::getConfig()->remove('".$key."')->save(false, true, false); ");
		$this->assertNull(self::storedPref('SitePrefs', $key), 'the preference was meant to be missing before the admin area loads');
	}

	/**
	 * @param string $seed statements run before auth.php is loaded
	 * @param string $after statements run once auth.php has let the page through
	 * @return string what the child wrote
	 */
	private function loadAuth($seed = '', $after = '')
	{
		return $this->outputOfBootedCli(
			$seed."require_once(e_ADMIN.'auth.php'); echo '".self::PAST_AUTH."'; ".$after,
			self::buffered(),
			array('e_NOHEADER' => true)
		);
	}
}
