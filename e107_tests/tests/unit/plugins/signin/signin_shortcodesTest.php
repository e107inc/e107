<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The username field is named from one value, which the sr-only label and the
 * placeholder both read, so an empty one leaves the field unnamed on screen and
 * to a screen reader at once.
 */
class signin_shortcodesTest extends \Codeception\Test\Unit
{
	/** @var mixed the allowEmailLogin pref as found, restored in _after() */
	private $savedEmailLogin = null;

	protected function _before()
	{
		require_once(e_PLUGIN.'signin/signin_shortcodes.php');

		$this->savedEmailLogin = e107::getConfig()->get('allowEmailLogin');
	}

	protected function _after()
	{
		e107::getConfig()->set('allowEmailLogin', $this->savedEmailLogin);
	}

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
