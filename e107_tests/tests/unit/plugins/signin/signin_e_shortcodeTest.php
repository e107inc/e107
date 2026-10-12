<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The guest half of the signin template is one string holding the dropdown and
 * the login form together, so a site that admits no guest to its login page can
 * only be honoured where {SIGNIN} picks the half to parse (issue #6501).
 */
class signin_e_shortcodeTest extends \Test\Unit
{
	/** @var signin_shortcodes */
	private $sc;

	/** @var array the prefs these tests write, as found, restored in _after() */
	private $saved = array();

	/** @var e_user the session user these tests stand in for, restored in _after() */
	private $user;

	protected function _before()
	{
		require_once(e_PLUGIN.'signin/e_shortcode.php');

		$this->sc = new signin_shortcodes();

		foreach(array('user_reg', 'social_login_active', 'plug_installed') as $pref)
		{
			$this->saved[$pref] = e107::getConfig()->get($pref);
		}

		$this->user = e107::getRegistry('core/e107/current_user');

		$this->visit(0);
	}

	protected function _after()
	{
		e107::setRegistry('core/e107/current_user', $this->user);

		foreach($this->saved as $pref => $value)
		{
			e107::getConfig()->set($pref, $value);
		}
	}

	/**
	 * User registration/login set to Disabled is the site saying it has no public
	 * login, which login.php has always honoured and this dropdown never did.
	 */
	public function testNothingIsOfferedToAGuestWhereRegistrationIsDisabled()
	{
		e107::getConfig()->set('user_reg', 0);

		self::assertSame(SITEURL.'login.php', e_LOGIN,
			'precondition: a custom login page is a site whose login this setting does not speak for');

		self::assertNull($this->sc->sc_signin());
	}

	/** Both of the settings that leave a site with a login page still get the form. */
	public function testTheFormStandsWhereTheSiteStillTakesLogins()
	{
		foreach(array(1, 2) as $userReg)
		{
			e107::getConfig()->set('user_reg', $userReg);

			self::assertStringContainsString('name="userlogin"', $this->sc->sc_signin(), 'user_reg='.$userReg);
		}
	}

	/**
	 * A site whose only way in is a social provider keeps its entry point, on the
	 * same pair of settings login.php reads at its own top.
	 */
	public function testTheFormStandsWhereOnlySocialLoginIsLeft()
	{
		require_once(e_PLUGIN.'social/includes/social_login_config.php');

		e107::getConfig()->set('user_reg', 0);
		e107::getConfig()->setPref('plug_installed/social', '1.0');
		e107::getConfig()->set('social_login_active', 1 << social_login_config::ENABLE_BIT_GLOBAL);

		self::assertTrue(e107::getUserProvider()->isSocialLoginEnabled(),
			'precondition: the provider has to report social login on, or the guard is never reached');

		self::assertStringContainsString('name="userlogin"', $this->sc->sc_signin());
	}

	/**
	 * Disabled closes the door to guests only. A member on such a site keeps the
	 * menu, because the credentials they logged in with are still accepted.
	 */
	public function testTheMemberMenuStandsWhereRegistrationIsDisabled()
	{
		e107::getConfig()->set('user_reg', 0);

		$this->visit(1);

		$html = $this->sc->sc_signin();

		self::assertStringContainsString(LAN_SIGNIN_PROFILE, $html);
	}

	/** Seats the request at the given user id, which is all these shortcodes ask of it. */
	private function visit($userId)
	{
		e107::setRegistry('core/e107/current_user', $this->make('e_user', array('getId' => $userId)));
	}
}
