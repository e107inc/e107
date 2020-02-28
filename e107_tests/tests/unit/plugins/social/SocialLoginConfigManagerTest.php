<?php

use Codeception\Stub;

/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
class SocialLoginConfigManagerTest extends \Codeception\Test\Unit
{
	/**
	 * @var e_core_pref
	 */
	private $pref;
	/**
	 * @var social_login_config
	 */
	private $manager;

	/**
	 * @throws Exception
	 */
	public function _before()
	{
		include_once(e_PLUGIN . "social/includes/social_login_config.php");

		$this->pref = $this->make('e_pref');
		$this->pref->set(social_login_config::SOCIAL_LOGIN_PREF, [
			'Twitter-OAuth1' => [
				'enabled' => true,
				'keys' => [
					'id' => 'ID',
					'secret' => 'SECRET',
				],
			],
			'StackExchange-OpenID' => [
				'enabled' => false,
			],
			'GitHub-OAuth2' => [
				'enabled' => true,
				'keys' => [
					'id' => 'ID',
					'secret' => 'SECRET',
				],
				'scope' => 'identity',
			],
			'OpenID' => [
				'enabled' => true,
			],
		]);
		$this->manager = new social_login_config($this->pref);
	}

	public function testFlagSettingOff()
	{
		$this->pref->set(social_login_config::SOCIAL_LOGIN_FLAGS, 0x0);
		$this->manager = new social_login_config($this->pref);
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));
	}

	public function testFlagSettingGlobalOffPreventsOthersOn()
	{
		$this->manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, 0);
		$this->manager->setFlag(social_login_config::ENABLE_BIT_TEST_PAGE, 1);
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));
	}

	public function testFlagSettingGlobalOnAllowsOtherToggles()
	{
		$this->manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, 1);
		$this->manager->setFlag(social_login_config::ENABLE_BIT_TEST_PAGE, 0);
		$this->assertTrue($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));

		$this->manager->setFlag(social_login_config::ENABLE_BIT_TEST_PAGE, 1);
		$this->assertTrue($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertTrue($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));
	}

	/**
	 * Don't break existing client code that checks if social_login_active is 0 or not!
	 * If the global bit is 0, all the other bits should be 0, too.
	 */
	public function testFlagGlobalOffTurnsAllOff()
	{
		$this->pref->set(social_login_config::SOCIAL_LOGIN_FLAGS, ~0);
		$this->manager = new social_login_config($this->pref);
		$this->assertTrue($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertTrue($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));

		$this->manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, 0);
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL));
		$this->assertFalse($this->manager->isFlagActive(social_login_config::ENABLE_BIT_TEST_PAGE));
	}

	public function testIsProviderEnabled()
	{
		$this->assertTrue($this->manager->isProviderEnabled('Twitter'));
		$this->assertFalse($this->manager->isProviderEnabled('StackExchangeOpenID'));
		$this->assertTrue($this->manager->isProviderEnabled('GitHub'));
		$this->assertTrue($this->manager->isProviderEnabled('OpenID'));
	}

	public function testForgetProvider()
	{
		$this->manager->forgetProvider('OpenID');
		$result = $this->manager->getConfiguredProviders();
		$this->assertCount(3, $result);

		$this->manager->forgetProvider('StackExchangeOpenID');
		$result = $this->manager->getConfiguredProviders();
		$this->assertCount(2, $result);

		$this->manager->forgetProvider('FakeProvider');
		$result = $this->manager->getConfiguredProviders();
		$this->assertCount(2, $result);
	}

	public function testSetProviderConfig()
	{
		$this->manager->setProviderConfig('MyEnabledProvider', ['enabled' => true]);
		$result = $this->manager->getConfiguredProviders();
		$this->assertContains('MyEnabledProvider', $result);
		$this->assertTrue($this->manager->isProviderEnabled('MyEnabledProvider'));

		$this->manager->setProviderConfig('MyDisabledProvider', ['garbage' => 'nonsense']);
		$result = $this->manager->getConfiguredProviders();
		$this->assertContains('MyDisabledProvider', $result);
		$this->assertFalse($this->manager->isProviderEnabled('MyDisabledProvider'));
	}

	public function testSetProviderConfigForgetsProviderIfEmpty()
	{
		$this->manager->setProviderConfig('EmptyProvider', [
			'enabled' => null,
			'keys' => [
				'id' => '',
				'secret' => 0,
			],
			'scope' => false,
		]);
		$result = $this->manager->getConfiguredProviders();
		$this->assertNotContains('EmptyProvider', $result);
	}

	public function testSetProviderConfigDiscardsEmptyOptions()
	{
		$this->manager->setProviderConfig('MiscProvider', [
			'enabled' => true,
			'openid_identifier' => '',
			'keys' => [
				'id' => null,
				'secret' => 0,
			],
			'scope' => false,
		]);
		$result = $this->manager->getProviderConfig('MiscProvider');
		$this->assertEquals(['enabled' => true], $result);
	}

	public function testSetProviderConfigOverwritesNonArray()
	{
		$this->pref->set(social_login_config::SOCIAL_LOGIN_PREF, 'bad string!');
		$manager = new social_login_config($this->pref);
		$expected = ['enabled' => true];

		$manager->setProviderConfig('FirstProvider', $expected);
		$result = $manager->getProviderConfig('FirstProvider');

		$this->assertEquals($expected, $result);
	}

	public function testGetProviderConfig()
	{
		$result = $this->manager->getProviderConfig('Twitter');
		$this->assertTrue($result['enabled']);
		$this->assertArrayHasKey('keys', $result);
		$this->assertArrayNotHasKey('scope', $result);

		$result = $this->manager->getProviderConfig('Twitter', 'keys/id');
		$this->assertEquals('ID', $result);

		$result = $this->manager->getProviderConfig('Twitter', '/keys/secret');
		$this->assertEquals('SECRET', $result);

		$result = $this->manager->getProviderConfig('Twitter', '/fake');
		$this->assertNull($result);

		$result = $this->manager->getProviderConfig('StackExchangeOpenID');
		$this->assertFalse($result['enabled']);
		$this->assertArrayNotHasKey('keys', $result);
		$this->assertArrayNotHasKey('scope', $result);

		$result = $this->manager->getProviderConfig('GitHub');
		$this->assertEquals('identity', $result['scope']);
	}

	public function testGetConfiguredProviders()
	{
		$result = $this->manager->getConfiguredProviders();

		$this->assertCount(4, $result);
		$this->assertContains('Twitter', $result);
		$this->assertContains('StackExchangeOpenID', $result);
		$this->assertContains('GitHub', $result);
		$this->assertContains('OpenID', $result);
	}

	public function testNormalizeProviderNameFixesCapitalization()
	{
		$output = $this->manager->normalizeProviderName("Github");
		$this->assertEquals("GitHub-OAuth2", $output);
	}

	public function testNormalizeProviderNamePassesThroughUnknownName()
	{
		$output = $this->manager->normalizeProviderName("iPhone");
		$this->assertEquals("iPhone", $output);
	}

	public function testNormalizeProviderNameRemovesTypeFromName()
	{
		$output = $this->manager->normalizeProviderName("StackExchangeOpenID");
		$this->assertEquals("StackExchange-OpenID", $output);

		$output = $this->manager->normalizeProviderName("aolOPENid");
		$this->assertEquals("AOL-OpenID", $output);
	}

	public function testNormalizeProviderNameFindsCorrectType()
	{
		$output = $this->manager->normalizeProviderName("StackExchange");
		$this->assertEquals("StackExchange-OAuth2", $output);

		$output = $this->manager->normalizeProviderName("Telegram");
		$this->assertEquals("Telegram", $output);
	}

	public function testNormalizeProviderNameGeneric()
	{
		$output = $this->manager->normalizeProviderName("openid");
		$this->assertEquals("OpenID", $output);
	}

	public function testNormalizeProviderNameFakeGeneric()
	{
		$output = $this->manager->normalizeProviderName("OAuth2");
		$this->assertEquals("OAuth2", $output);
	}

	public function testDenormalizeProviderName()
	{
		$output = $this->manager->denormalizeProviderName("OpenID");
		$this->assertEquals("OpenID", $output);

		$output = $this->manager->denormalizeProviderName("StackExchange-OAuth1");
		$this->assertEquals("StackExchangeOAuth1", $output);

		$output = $this->manager->denormalizeProviderName("StackExchange-OAuth2");
		$this->assertEquals("StackExchange", $output);

		$output = $this->manager->denormalizeProviderName("StackExchange-OpenID");
		$this->assertEquals("StackExchangeOpenID", $output);
	}
}