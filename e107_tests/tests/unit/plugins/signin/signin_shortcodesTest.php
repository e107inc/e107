<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The signin menu asks the pm plugin for the unread-messages link. It has to
 * cope with pm being listed as installed while its shortcode batch is not
 * loaded, because those two facts come from different places and can disagree.
 */
class signin_shortcodesTest extends \Test\Unit
{
	/** @var plugin_signin_signin_shortcodes */
	private $sc;

	/** @var mixed the plug_installed pref as found, restored in _after() */
	private $savedInstalled = null;

	/** @var mixed the allowEmailLogin pref as found, restored in _after() */
	private $savedEmailLogin = null;

	protected function _before()
	{
		require_once(e_PLUGIN.'signin/signin_shortcodes.php');

		try
		{
			$this->sc = $this->make('plugin_signin_signin_shortcodes');
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$this->sc->__construct();

		$this->savedInstalled = e107::getConfig()->get('plug_installed');
		$this->savedEmailLogin = e107::getConfig()->get('allowEmailLogin');
	}

	protected function _after()
	{
		e107::getConfig()->set('plug_installed', $this->savedInstalled);
		e107::getConfig()->set('allowEmailLogin', $this->savedEmailLogin);
	}

	/**
	 * plug_installed is a core preference; the class holding sc_pm_nav() lives
	 * in the plugin's e_shortcode.php, which is read only when pm appears in
	 * the e_shortcode addon list. Naming pm in the first without the second is
	 * the state left by a failed install, and by an uninstall earlier in the
	 * same request, and it used to end the page with a call to a method on
	 * null.
	 *
	 * Where the batch is available the answer has to be the batch's own, which
	 * is what stops this passing against a shortcode that refuses every time.
	 */
	public function testPmNavAnswersWhetherOrNotTheBatchIsLoaded()
	{
		e107::getConfig()->setPref('plug_installed/pm', '2.0');

		self::assertTrue(e107::isInstalled('pm'),
			'precondition: pm has to look installed, or the guard above is never reached');

		$batch = e107::getScBatch('pm', true);
		$expected = is_object($batch) ? $batch->sc_pm_nav() : null;

		self::assertSame($expected, $this->sc->sc_signin_pm_nav());
	}

	/**
	 * The early return, kept honest so the fix above cannot be mistaken for it.
	 */
	public function testPmNavSaysNothingWhenPmIsNotInstalled()
	{
		$installed = e107::getConfig()->get('plug_installed');
		unset($installed['pm']);
		e107::getConfig()->set('plug_installed', $installed);

		self::assertNull($this->sc->sc_signin_pm_nav());
	}

	/**
	 * One value of the username field names it twice, in the placeholder and in
	 * the sr-only label, so an empty one leaves a screen reader nothing to read.
	 */
	public function testTheUsernameFieldIsNamedForEveryEmailLoginSetting()
	{
		$names = array(
			0 => LAN_LOGINMENU_1,
			1 => LAN_LOGINMENU_49,
			2 => LAN_LOGINMENU_50,
		);

		foreach($names as $pref => $name)
		{
			e107::getConfig()->set('allowEmailLogin', $pref);

			$this->assertUsernameFieldIsNamed($name, 'allowEmailLogin='.$pref);
		}
	}

	/**
	 * The pref is absent between the files landing and the database update that
	 * seeds it, which is the shape of an upgrade in place.
	 */
	public function testTheUsernameFieldIsNamedWhileTheSettingIsAbsent()
	{
		e107::getConfig()->remove('allowEmailLogin');

		$this->assertUsernameFieldIsNamed(LAN_LOGINMENU_1, 'allowEmailLogin absent');
	}

	/** Builds the batch against the pref as it stands, then reads the rendered field. */
	private function assertUsernameFieldIsNamed($name, $case)
	{
		$sc = $this->make('plugin_signin_signin_shortcodes');
		$sc->__construct();
		$markup = $sc->sc_signin_input_username();

		self::assertStringContainsString("placeholder='".$name."'", $markup, $case);
		self::assertStringContainsString(">".$name."</label>", $markup, $case);
	}
}
