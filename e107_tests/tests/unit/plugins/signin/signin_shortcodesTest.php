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

	/** @var array the prefs these tests write, as found, restored in _after() */
	private $saved = array();

	protected function _before()
	{
		require_once(e_PLUGIN.'signin/signin_shortcodes.php');

		$this->sc = $this->buildBatch();

		foreach(array('plug_installed', 'allowEmailLogin', 'user_reg_veri', 'auth_method') as $pref)
		{
			$this->saved[$pref] = e107::getConfig()->get($pref);
		}
	}

	protected function _after()
	{
		foreach($this->saved as $pref => $value)
		{
			e107::getConfig()->set($pref, $value);
		}
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
	 * Every string the batch renders has to come from the plugin's own language
	 * file, because signin installs without login_menu and a name borrowed from
	 * another plugin is undefined the moment that plugin is not on disk.
	 */
	public function testTheRenderedStringsComeFromTheSigninLanguageFile()
	{
		$path = e_PLUGIN.'signin/languages/English/English_front.php';

		self::assertFileExists($path);

		$strings = include($path);

		self::assertIsArray($strings, $path.' has to return its strings as an array');

		$used = array(
			'LAN_SIGNIN_USERNAME',
			'LAN_SIGNIN_EMAIL',
			'LAN_SIGNIN_USEREMAIL',
			'LAN_SIGNIN_SIGNIN',
			'LAN_SIGNIN_SIGNUP',
			'LAN_SIGNIN_FPW',
			'LAN_SIGNIN_RESEND',
			'LAN_SIGNIN_PROFILE',
			'LAN_SIGNIN_ADMIN',
			'LAN_SIGNIN_MAINTENANCE',
		);

		foreach($used as $name)
		{
			self::assertArrayHasKey($name, $strings);
			self::assertSame($strings[$name], defset($name), $name.' has to be defined from this file');
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
		self::assertStringContainsString(LAN_SIGNIN_FPW, $html);

		$this->assertWrappedNameResolves('signin', 'SIGNIN_RESEND_HREF', LAN_SIGNIN_RESEND);
	}

	/** The button asked for a shortcode the batch never defined, so its href came out empty (issue #6139). */
	public function testTheResendButtonLinksToTheResendForm()
	{
		$html = $this->renderSigninForm(array());

		self::assertStringContainsString(
			'<a href="'.e_SIGNUP.'?resend" class="btn btn-default btn-secondary btn-sm  btn-block">'.LAN_SIGNIN_RESEND.'</a>',
			$html);
	}

	/** Each of these is a site where signup.php refuses the resend form, and a wrapper whose shortcode says nothing is markup no page renders. */
	public function testTheResendButtonIsAbsentWhereTheResendFormRefuses()
	{
		$refusals = array(
			'registration closed'       => array('regMode' => 0),
			'login only'                => array('regMode' => 2),
			'no verification'           => array('user_reg_veri' => '0'),
			'verification never set'    => array('user_reg_veri' => null),
			'admin approval'            => array('user_reg_veri' => '2'),
			'another authentication'    => array('auth_method' => 'oauth'),
		);

		foreach($refusals as $case => $settings)
		{
			$html = $this->renderSigninForm($settings);

			self::assertStringNotContainsString('?resend', $html, $case);
			self::assertStringNotContainsString(LAN_SIGNIN_RESEND, $html, $case);
		}
	}

	/** The signup link is the other caller of the gate the resend button shares, and registration has three settings rather than two. */
	public function testTheSignupLinkIsGatedWithTheResendButton()
	{
		self::assertStringContainsString('<li class="nav-item"><a class="nav-link" href="'.e_SIGNUP.'">'.LAN_SIGNIN_SIGNUP.'</a></li>',
			$this->renderSigninForm(array()));

		self::assertStringNotContainsString(e_SIGNUP, $this->renderSigninForm(array('regMode' => 2)));
	}

	/**
	 * The menu a member sees is the other half of the same template, and its two
	 * remaining names sit in a wrapper each, which is markup no page renders
	 * until the shortcode inside it answers.
	 */
	public function testTheSignedInMenuResolvesEveryLanguageToken()
	{
		$html = e107::getParser()->parseTemplate(e107::getTemplate('signin', 'signin', 'signout'), true, $this->sc);

		self::assertStringContainsString(LAN_SIGNIN_PROFILE, $html);

		$this->assertWrappedNameResolves('signin', 'SIGNIN_SIGNUP_HREF', LAN_SIGNIN_SIGNUP);
		$this->assertWrappedNameResolves('signout', 'SIGNIN_ADMIN_HREF', LAN_SIGNIN_ADMIN);
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

	/** Reads a name out of the wrapper markup that holds it, which no page renders until its shortcode answers. */
	private function assertWrappedNameResolves($key, $code, $name)
	{
		$wrapper = e107::getRegistry('templates/wrapper/signin');

		self::assertArrayHasKey($code, varset($wrapper[$key], array()), $key.'/'.$code.' has to be wrapped');
		self::assertStringContainsString($name,
			e107::getParser()->parseTemplate($wrapper[$key][$code], true, $this->sc), $code);
	}

	/** Renders the signed-out form against the settings the resend button needs, less whatever the case takes away. */
	private function renderSigninForm(array $refusal)
	{
		$settings = $refusal + array('user_reg_veri' => '1', 'auth_method' => 'e107', 'regMode' => 1);

		foreach(array('user_reg_veri', 'auth_method') as $pref)
		{
			if($settings[$pref] === null)
			{
				e107::getConfig()->remove($pref);
			}
			else
			{
				e107::getConfig()->set($pref, $settings[$pref]);
			}
		}

		$sc = $this->buildBatch();

		$regMode = new ReflectionProperty('plugin_signin_signin_shortcodes', 'regMode');
		$regMode->setAccessible(true);
		$regMode->setValue($sc, $settings['regMode']);

		$sc->wrapper('signin/signin');

		return e107::getParser()->parseTemplate(e107::getTemplate('signin', 'signin', 'signin'), true, $sc);
	}

	/** Builds the batch, which reads every pref it answers from exactly once, here. */
	private function buildBatch()
	{
		try
		{
			$sc = $this->make('plugin_signin_signin_shortcodes');
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		return $sc;
	}

	/** Builds the batch against the pref as it stands, then reads the rendered field. */
	private function assertUsernameFieldIsNamed($name, $case)
	{
		$markup = $this->buildBatch()->sc_signin_input_username();

		self::assertStringContainsString("placeholder='".$name."'", $markup, $case);
		self::assertStringContainsString(">".$name."</label>", $markup, $case);
	}
}
