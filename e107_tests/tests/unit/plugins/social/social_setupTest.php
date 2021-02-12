<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class social_setupTest extends \Codeception\Test\Unit
{
	public function _before()
	{
		include_once(e_PLUGIN . "social/includes/social_login_config.php");
		include_once(e_PLUGIN . "social/social_setup.php");
	}

	public function testUpgradeProviderNameNormalization()
	{
		e107::getConfig()->set(social_login_config::SOCIAL_LOGIN_PREF, SOCIAL_LOGIN_LEGACY_DATA);
		$social_setup = new social_setup();
		$this->assertTrue($social_setup->upgrade_required());
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/AOL"));
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/AOL-OpenID"));
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/Github"));
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/GitHub-OAuth2"));
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/Live"));
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/WindowsLive"));

		$social_setup->upgrade_pre();
		$this->assertFalse($social_setup->upgrade_required());
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/AOL"));
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/AOL-OpenID"));
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/Github"));
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/GitHub-OAuth2"));
		$this->assertIsNotArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/Live"));
		$this->assertIsArray(e107::getConfig()->getPref(social_login_config::SOCIAL_LOGIN_PREF . "/WindowsLive-OAuth2"));
	}

	public function testUpgradeFixRenamedProvidersXup()
	{
		$renamedProviders = social_setup::RENAMED_PROVIDERS;
		foreach ($renamedProviders as $oldProviderName => $newProviderName)
		{
			$db = e107::getDb();
			$db->insert('user', [
				'user_loginname' => $oldProviderName . '012345',
				'user_name' => $oldProviderName . '012345',
				'user_password' => '559b3b2f2d54b647ae7a5beb5c8c36c3',
				'user_email' => '',
				'user_xup' => $oldProviderName . '_ThisSegmentDoesNotMatter',
			]);
			$insertId = $db->lastInsertId();

			$social_setup = new social_setup();
			$this->assertTrue($social_setup->upgrade_required());
			$social_setup->upgrade_pre();

			$result = $db->retrieve('user', '*', 'user_id=' . $insertId);
			$this->assertEquals($newProviderName . '_ThisSegmentDoesNotMatter', $result['user_xup']);
			$this->assertFalse($social_setup->upgrade_required());
		}
	}

	/**
	 * @see https://github.com/e107inc/e107/pull/4099#issuecomment-590579521
	 */
	public function testUpgradeFixSteamXupBug()
	{
		$db = e107::getDb();
		$db->insert('user', [
			'user_loginname' => 'SteambB8047',
			'user_name' => 'SteambB8047',
			'user_password' => '$2y$10$.u22u/U392cUhvJm2DJ57.wsKtxKKj3WsZ.x6LsXoUVHVuprZGgUu',
			'user_email' => '',
			'user_xup' => 'Steam_https://steamcommunity.com/openid/id/76561198006790310',
		]);
		$insertId = $db->lastInsertId();

		$social_setup = new social_setup();
		$this->assertTrue($social_setup->upgrade_required());
		$social_setup->upgrade_pre();

		$result = $db->retrieve('user', '*', 'user_id=' . $insertId);
		$this->assertEquals('Steam_76561198006790310', $result['user_xup']);
		$this->assertFalse($social_setup->upgrade_required());
	}
}
const SOCIAL_LOGIN_LEGACY_DATA =
array(
	'FakeProviderNeverExisted' =>
		array(
			'enabled' => '1',
		),
	'AOL' =>
		array(
			'enabled' => '1',
		),
	'Facebook' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'Foursquare' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Github' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'Google' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'scope' => 'c',
			'enabled' => '1',
		),
	'LinkedIn' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Live' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'OpenID' =>
		array(
			'enabled' => '1',
		),
	'Steam' =>
		array(
			'keys' =>
				array(
					'key' => 'a',
				),
			'enabled' => '1',
		),
	'Twitter' =>
		array(
			'keys' =>
				array(
					'key' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
	'Yahoo' =>
		array(
			'keys' =>
				array(
					'id' => 'a',
					'secret' => 'b',
				),
			'enabled' => '1',
		),
);