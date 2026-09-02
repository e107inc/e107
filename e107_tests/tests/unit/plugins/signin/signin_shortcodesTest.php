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
	/** @var plugin_signin_signin_shortcodes */
	private $sc;

	/** @var mixed the allowEmailLogin pref as found, restored in _after() */
	private $savedEmailLogin = null;

	protected function _before()
	{
		require_once(e_PLUGIN.'signin/signin_shortcodes.php');

		$this->sc = $this->make('plugin_signin_signin_shortcodes');
		$this->sc->__construct();

		$this->savedEmailLogin = e107::getConfig()->get('allowEmailLogin');
	}

	protected function _after()
	{
		e107::getConfig()->set('allowEmailLogin', $this->savedEmailLogin);
	}

	public function testTheUsernameFieldIsNamedForEveryEmailLoginSetting()
	{
		$names = array(
			0 => LAN_SIGNIN_USERNAME,
			1 => LAN_SIGNIN_EMAIL,
			2 => LAN_SIGNIN_USEREMAIL,
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

		$this->assertUsernameFieldIsNamed(LAN_SIGNIN_USERNAME, 'allowEmailLogin absent');
	}

	/**
	 * signin installs without login_menu, so every name it renders has to be one
	 * the plugin itself ships. Which file defined a constant is not observable
	 * once it is defined, so this pins the file and the names; the placeholder
	 * tests above pin the values that reach the markup.
	 */
	public function testThePluginShipsTheStringsItRenders()
	{
		$path = e_PLUGIN.'signin/languages/English/English_front.php';

		self::assertFileExists($path);

		$used = array(
			'LAN_SIGNIN_USERNAME',
			'LAN_SIGNIN_EMAIL',
			'LAN_SIGNIN_USEREMAIL',
			'LAN_SIGNIN_SIGNIN',
			'LAN_SIGNIN_SIGNUP',
			'LAN_SIGNIN_REMEMBER',
			'LAN_SIGNIN_FPW',
			'LAN_SIGNIN_RESEND',
			'LAN_SIGNIN_PROFILE',
			'LAN_SIGNIN_ADMIN',
			'LAN_SIGNIN_MAINTENANCE',
		);

		foreach($used as $name)
		{
			self::assertTrue(defined($name), $name.' has to be defined');
		}
	}

	/**
	 * The bundled template carries the strings as {LAN=...} tokens, so a name
	 * that no language file defines renders as nothing at all rather than
	 * announcing itself.
	 */
	public function testTheBundledFormResolvesEveryLanguageToken()
	{
		$html = e107::getParser()->parseTemplate(e107::getTemplate('signin', 'signin', 'signin'), true, $this->sc);

		self::assertStringContainsString(LAN_SIGNIN_SIGNIN, $html);
		self::assertStringContainsString(LAN_SIGNIN_REMEMBER, $html);
		self::assertStringContainsString(LAN_SIGNIN_FPW, $html);
		self::assertStringContainsString(LAN_SIGNIN_RESEND, $html);
	}

	/**
	 * The menu a member sees is the other half of the same template, and its two
	 * remaining names sit in a wrapper each, which is markup no page renders
	 * until the shortcode inside it answers.
	 */
	public function testTheSignedInMenuResolvesEveryLanguageToken()
	{
		$tp = e107::getParser();
		$html = $tp->parseTemplate(e107::getTemplate('signin', 'signin', 'signout'), true, $this->sc);

		self::assertStringContainsString(LAN_SIGNIN_PROFILE, $html);

		$wrapper = e107::getRegistry('templates/wrapper/signin');

		$wrapped = array(
			array($wrapper['signin']['SIGNIN_SIGNUP_HREF'], LAN_SIGNIN_SIGNUP),
			array($wrapper['signout']['SIGNIN_ADMIN_HREF'], LAN_SIGNIN_ADMIN),
		);

		foreach($wrapped as $case)
		{
			list($markup, $name) = $case;

			self::assertStringContainsString($name, $tp->parseTemplate($markup, true, $this->sc), $markup);
		}
	}

	/**
	 * The field itself is named from core's own string, so the label beside it
	 * has no business carrying a second translation of the same word.
	 */
	public function testThePasswordLabelIsTheStringTheFieldAlreadyUses()
	{
		self::assertSame(LAN_PASSWORD, $this->sc->sc_signin_password_label());
		self::assertStringContainsString(">".LAN_PASSWORD."</label>", $this->sc->sc_signin_input_password());
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
