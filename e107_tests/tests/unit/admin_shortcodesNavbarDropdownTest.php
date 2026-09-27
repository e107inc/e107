<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The admin navbar hangs {ADMIN_UPDATE} and {ADMIN_NOTIFICATIONS} off a cloud
 * icon and a bell. Both arrive shut, and the bootstrap3 admin theme paints an
 * open navbar dropdown as a full-height sheet below 768px, so neither may ship
 * the open class or claim to be expanded.
 */
class admin_shortcodesNavbarDropdownTest extends \Test\Unit
{
	/** @var admin_shortcodes */
	private $sc;

	/** @var array registry entries this test overwrites, restored in _after() */
	private $savedRegistry = array();

	/** @var mixed the check_updates pref as found, restored in _after() */
	private $savedCheckUpdates = null;

	protected function _before()
	{
		// LAN_NEWVERSION lives in the admin LAN file. Loaded rather than
		// defined by hand, which would poison every constant below it.
		e107::coreLan('admin', true);

		require_once(e_CORE.'shortcodes/batch/admin_shortcodes.php');

		$this->sc = $this->make('admin_shortcodes');

		$this->savedRegistry['core/e107/singleton/ecache'] = e107::getCache();
		$this->savedCheckUpdates = e107::getConfig()->get('check_updates');

		e107::getConfig()->set('check_updates', 1);
		e107::setRegistry('core/e107/singleton/ecache', $this->cacheReturning(array(
			'version' => '2.4.0',
			'url'     => 'https://e107.org/download',
			'infourl' => 'https://e107.org/release-notes',
		)));
	}

	protected function _after()
	{
		foreach($this->savedRegistry as $key => $value)
		{
			e107::setRegistry($key, $value);
		}

		e107::getConfig()->set('check_updates', $this->savedCheckUpdates);
		eHelper::clearSystemNotification('sc_admin_update');
		eHelper::clearSystemNotification('navbar-dropdown-probe');
	}

	/**
	 * A cache whose Update_core entry is the given payload, so the shortcode
	 * never reaches the network. A 'status' key would make it return null.
	 *
	 * @param array $status
	 * @return ecache
	 */
	private function cacheReturning(array $status)
	{
		$cached = e107::serialize($status, 'json');

		return $this->make('ecache', array(
			'retrieve' => function($CacheTag, $MaximumAge = false, $ForcedCheck = false, $syscache = false) use ($cached)
			{
				return ($CacheTag === 'Update_core') ? $cached : false;
			},
		));
	}

	/**
	 * @param string $actual rendered navbar markup
	 * @param string $control a needle proving it rendered at all
	 * @param string $dropdown the dropdown's name, for the failure messages
	 */
	private function assertDropdownArrivesShut($actual, $control, $dropdown)
	{
		$this->assertStringContainsString($control, $actual,
			'The '.$dropdown.' did not render at all, so the assertions below prove nothing.');

		$this->assertStringNotContainsString('dropdown open', $actual,
			'The '.$dropdown.' arrived with its dropdown open, which the bootstrap3 admin theme paints as a full-height sheet over the whole admin page below 768px.');
		$this->assertStringNotContainsString('aria-expanded="true"', $actual,
			'The '.$dropdown.' told assistive technology its dropdown was expanded before anybody opened it.');
	}

	public function testTheCoreUpdateNoticeRendersClosed()
	{
		$this->assertDropdownArrivesShut((string) $this->sc->sc_admin_update(),
			'core-update-available', 'core-update notice');
	}

	public function testTheNotificationsBellRendersClosed()
	{
		eHelper::addSystemNotification('navbar-dropdown-probe', 'A probe notification.');

		$this->assertDropdownArrivesShut((string) $this->sc->sc_admin_notifications(),
			'A probe notification.', 'notifications bell');
	}

	/**
	 * A feed row with no download url leaves nothing to offer, so the notice
	 * renders nothing and takes any earlier notice off the bell with it.
	 */
	public function testACoreUpdateWithNoDownloadUrlRendersNothingAndClearsTheBell()
	{
		eHelper::addSystemNotification('sc_admin_update', 'A notice from an earlier request.');

		e107::setRegistry('core/e107/singleton/ecache', $this->cacheReturning(array(
			'version' => '2.4.0',
			'infourl' => 'https://e107.org/release-notes',
		)));

		$this->assertNull($this->sc->sc_admin_update());
		$this->assertArrayNotHasKey('sc_admin_update', eHelper::getSystemNotification(),
			'A core-update notice outlived the update it announced.');
	}

	/**
	 * Positive control. The notice still has to carry its toggle, both links
	 * and the bell copy, so a fix that rendered less of it cannot pass.
	 */
	public function testTheCoreUpdateNoticeStillCarriesItsToggleLinksAndBellCopy()
	{
		$actual = (string) $this->sc->sc_admin_update();

		$this->assertStringContainsString('data-toggle="dropdown"', $actual);
		$this->assertStringContainsString('aria-expanded="false"', $actual);
		$this->assertStringContainsString('fa-cloud-download', $actual);
		$this->assertStringContainsString('href="https://e107.org/download"', $actual);
		$this->assertStringContainsString('href="https://e107.org/release-notes"', $actual);
		$this->assertStringContainsString('2.4.0', $actual);

		$notifications = eHelper::getSystemNotification();
		$this->assertArrayHasKey('sc_admin_update', $notifications,
			'The notice rendered but never reached the bell, which is the copy that survives the request.');
		$this->assertStringContainsString('2.4.0', $notifications['sc_admin_update']['message']);
	}
}
