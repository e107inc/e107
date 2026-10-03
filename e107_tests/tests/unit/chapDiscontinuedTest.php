<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * CHAP login is discontinued (#6653): a site that had password_CHAP on checks the password as typed and renders no CHAP-only markup.
 */
class chapDiscontinuedTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** RFC 5737 TEST-NET-2 as eIPHandler stores it, so a failure recorded here cannot ban the CLI. */
	const TEST_IP = '0000:0000:0000:0000:0000:ffff:c633:6403';

	const TEST_USER = 'chapformer';
	const TEST_PASS = 'correct horse battery staple';
	const CHALLENGE = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

	const CHAP_WITH_FALLBACK = 1;
	const CHAP_ONLY = 2;

	/** @var userlogin */
	private $lg;

	/** @var int */
	private $userId;

	/** @var mixed */
	private $chapWas;

	protected function _before()
	{
		$this->clearState();

		$this->userId = e107::getDb()->insert('user', array(
			'user_name'      => self::TEST_USER,
			'user_loginname' => self::TEST_USER,
			'user_email'     => self::TEST_USER . '@example.com',
			'user_password'  => md5(self::TEST_PASS),
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
		));
		$this->assertNotEmpty($this->userId);

		$this->chapWas = e107::getConfig()->get('password_CHAP');
		e107::getSession()->set('challenge', self::CHALLENGE);

		$this->lg = $this->make('userlogin');
		$this->lg->__construct();

		$property = new ReflectionProperty('userlogin', 'userIP');
		$property->setAccessible(true);
		$property->setValue($this->lg, self::TEST_IP);
	}

	protected function _after()
	{
		e107::setRegistry('core/e107/user/' . (int) $this->userId, null);
		e107::getSession()->clear('challenge');

		if($this->chapWas === null)
		{
			e107::getConfig()->remove('password_CHAP')->save(false, true, false);
		}
		else
		{
			e107::getConfig()->set('password_CHAP', $this->chapWas)->save(false, true, false);
		}

		$this->clearState();
	}

	private function clearState()
	{
		$sql = e107::getDb();
		$sql->delete('generic', "gen_ip='" . self::TEST_IP . "'");
		$sql->delete('banlist', "banlist_ip='" . self::TEST_IP . "'");
		$sql->delete('user', "user_loginname='" . self::TEST_USER . "'");
	}

	private function failedLoginCount()
	{
		return (int) e107::getDb()->count('generic', '(*)',
			"WHERE gen_ip='" . self::TEST_IP . "' AND gen_type='failed_login'");
	}

	public function testThePasswordAsTypedLogsInWhereChapOnlyWasChosen()
	{
		e107::getConfig()->set('password_CHAP', self::CHAP_ONLY);

		$this->assertTrue($this->lg->login(self::TEST_USER, self::TEST_PASS, 0, '', true));
	}

	public function testAChapResponseLeftInTheFormDoesNotDisplaceThePasswordAsTyped()
	{
		e107::getConfig()->set('password_CHAP', self::CHAP_WITH_FALLBACK);

		$this->assertTrue($this->lg->login(self::TEST_USER, self::TEST_PASS, 0, str_repeat('b', 32), true));
	}

	public function testAPasswordHashedByAStalePageIsAskedForAgainWithoutCountingAsAFailure()
	{
		e107::getConfig()->set('password_CHAP', self::CHAP_WITH_FALLBACK);
		$hashedByTheOldScript = md5(md5(md5(self::TEST_PASS) . self::TEST_USER) . self::CHALLENGE);

		$this->assertFalse($this->lg->login(self::TEST_USER, '', 0, $hashedByTheOldScript, true));
		$this->assertSame(0, $this->failedLoginCount());
	}

	/** A theme's own copy of an old login template may still hide its form behind the setting. */
	public function testTheDatabaseUpdateResetsTheSettingToPlaintext()
	{
		require_once(e_ADMIN . 'update_routines.php');
		e107::getConfig()->set('password_CHAP', self::CHAP_ONLY)->save(false, true, false);

		update_20x_to_latest('do');

		$this->assertSame(0, (int) e107::getPref('password_CHAP'));
	}

	public function testTheSigninFormHasNoChapHook()
	{
		require_once(e_PLUGIN . 'signin/signin_shortcodes.php');
		$sc = $this->make('plugin_signin_signin_shortcodes');
		$sc->__construct();

		$form = $sc->sc_signin_form('start');
		$this->assertStringContainsString('<form method="post"', $form);
		$this->assertStringNotContainsString('hashLoginPassword', $form);
	}

	/** In a child, because including a template here would make a later include_once of it a no-op. */
	public function testTheLoginPageTemplateShowsTheFormWhereChapOnlyWasChosen()
	{
		list($output) = $this->runInBootedCli("e107::getConfig()->set('password_CHAP', " . self::CHAP_ONLY . "); "
			. "include(e_CORE . 'templates/login_template.php'); echo \$LOGIN_TEMPLATE['page']['body'];");
		$body = implode("\n", $output);

		$this->assertStringContainsString('{LAN=LOGIN_4}', $body);
		$this->assertStringNotContainsString('display:none', $body);
	}

	public function testTheLoginMenuTemplateShowsTheFormWhereChapOnlyWasChosen()
	{
		list($output) = $this->runInBootedCli("e107::plugLan('login_menu', null); "
			. "\$pref = e107::getPref(); \$pref['password_CHAP'] = " . self::CHAP_ONLY . "; "
			. "include(e_PLUGIN . 'login_menu/login_menu_template.php'); echo \$LOGIN_MENU_FORM;");
		$form = implode("\n", $output);

		$this->assertStringContainsString('{LM_USERNAME_INPUT}', $form);
		$this->assertStringNotContainsString('display:none', $form);
	}
}
