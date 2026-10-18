<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_user_providerTest extends \Codeception\Test\Unit
{
	use \Test\CorePrefs;

	/** @var e_user_provider */
	protected $e_user_provider;

	protected function _before()
	{
		e_user_providerTestAdapter::$constructed = 0;
		e_user_providerTestToken::$unserialised = 0;

		try
		{
			include_once(e_HANDLER . 'user_handler.php');
			$this->e_user_provider = $this->make('e_user_provider');
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e_user_provider object: {$e}");
		}
	}

	public function testGetSupportedProviders()
	{
		$result = e_user_provider::getSupportedProviders();

		$this->assertIsArray($result);
		$this->assertContains("Facebook", $result);
		$this->assertContains("Twitter", $result);
		$this->assertCount(53, $result,
			"The number of Hybridauth providers has changed! If this is intentional, note the change " .
			"in Hybridauth providers in the release changelog and update the count in this test."
		);
	}

	public function testGetProviderType()
	{
		$result = e_user_provider::getTypeOf("NotARealProvider");
		$this->assertFalse($result);

		$result = e_user_provider::getTypeOf("Steam");
		$this->assertEquals("OpenID", $result);

		$result = e_user_provider::getTypeOf("StackExchangeOpenID");
		$this->assertEquals("OpenID", $result);

		$result = e_user_provider::getTypeOf("Twitter");
		$this->assertEquals("OAuth1", $result);

		$result = e_user_provider::getTypeOf("WordPress");
		$this->assertEquals("OAuth2", $result);
	}

	public function testGetStandardFieldsOf()
	{
		$result = e_user_provider::getStandardFieldsOf("Facebook");
		$this->assertTrue(array_key_exists('id', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));
		$this->assertTrue(array_key_exists('scope', $result));

		$result = e_user_provider::getStandardFieldsOf("Steam");
		$this->assertTrue(array_key_exists('openid_identifier', $result));

		$result = e_user_provider::getStandardFieldsOf("Telegram");
		$this->assertEmpty($result);

		$result = e_user_provider::getStandardFieldsOf("Twitter");
		$this->assertTrue(array_key_exists('key', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));
	}

	public function testGetSupplementalFieldsOf()
	{
		$result = e_user_provider::getSupplementalFieldsOf("Facebook");
		$this->assertTrue(array_key_exists('photo_size', $result));

		$result = e_user_provider::getSupplementalFieldsOf("Foursquare");
		$this->assertTrue(array_key_exists('api_version', $result));
		$this->assertTrue(array_key_exists('photo_size', $result));

		$result = e_user_provider::getSupplementalFieldsOf("Google");
		$this->assertTrue(array_key_exists('photo_size', $result));

	/*	$result = e_user_provider::getSupplementalFieldsOf("Odnoklassniki");
		$this->assertTrue(array_key_exists('key', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));
		$this->assertIsNotArray($result['keys']['key']);
		$this->assertIsNotArray($result['keys']['secret']);*/

		$result = e_user_provider::getSupplementalFieldsOf("StackExchange");
		$this->assertTrue(array_key_exists('api_key', $result));
		$this->assertTrue(array_key_exists('site', $result));

		$result = e_user_provider::getSupplementalFieldsOf("Steam");
		$this->assertFalse(array_key_exists('id', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));

		$result = e_user_provider::getSupplementalFieldsOf("Telegram");
		$this->assertTrue(array_key_exists('id', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));

		$result = e_user_provider::getSupplementalFieldsOf("Twitter");
		$this->assertTrue(array_key_exists('authorize', $result));
		$this->assertTrue(array_key_exists('photo_size', $result));
		$this->assertIsNotArray($result['photo_size']);

	/*	$result = e_user_provider::getSupplementalFieldsOf("Vkontakte");
		$this->assertTrue(array_key_exists('photo_size', $result));*/
	}

	public function testIsSocialLoginEnabledBuildsNoHybridauth()
	{
		$this::assertTrue(e107::isInstalled('social'), 'the fixture must have the social plugin installed');
		e107::setRegistry('core/e107/user/provider', null);

		$provider = new e_user_provider();
		$provider->isSocialLoginEnabled();

		$this::assertNull(e107::getRegistry('core/e107/user/provider'), 'the Hybridauth provider directory was scanned');
		$hybridauth = new \e107\Reflection\ReflectionProperty('e_user_provider', 'hybridauth');
		$this::assertNull($hybridauth->getValue($provider));
	}

	public function testGetConfigBuildsTheDefaultConfigOnFirstUse()
	{
		$provider = new e_user_provider();

		$config = $provider->getConfig();

		$this::assertSame(array('callback', 'providers', 'debug_mode', 'debug_file'), array_keys($config));
		$this::assertSame($provider->generateCallbackUrl(), $config['callback']);
	}

	public function testSetBackUrlKeepsTheProviderList()
	{
		$provider = new e_user_provider();

		$provider->setBackUrl('/news.php');

		$config = $provider->getConfig();
		$this::assertSame($provider->generateCallbackUrl('/news.php'), $config['callback']);
		$this::assertArrayHasKey('providers', $config);
	}

	public function testNewSuppressExceptions()
	{
		$this->assertInstanceOf(
			e_user_provider::class,
			new e_user_provider("Facebook", ["providers" => ["Facebook", ["enabled" => true]]])
		);
	}

	public function testNewNoSuppressConfigurationException()
	{
		$this->expectException(\Hybridauth\Exception\InvalidArgumentException::class);
		new e_user_provider(
			"Facebook",
			["providers" => ["Facebook" => ["enabled" => true]]],
			false
		);
	}

	public function testNewNoSuppressUnknownProviderException()
	{
		$this->expectException(\Hybridauth\Exception\InvalidArgumentException::class);
		new e_user_provider("NotARealProvider", array(), false);
	}

	public function testNewNoSuppressDisabledException()
	{
		$this->expectException(\Hybridauth\Exception\UnexpectedValueException::class);
		new e_user_provider(
			"Facebook",
			["providers" => ["Facebook" => ["enabled" => false]]],
			false
		);
	}

	public function testAStoredAdapterOptionChoosesNoClass()
	{
		$restore = $this->withStoredFacebook(array('adapter' => 'e_user_providerTestAdapter'));
		try
		{
			new e_user_provider('Facebook');
		}
		finally
		{
			$restore();
		}

		$this::assertSame(0, e_user_providerTestAdapter::$constructed);
	}

	public function testAFacebookLoginLeavesForFacebookWhateverAdapterIsStored()
	{
		$address = $this->facebookSignInAddress(array('adapter' => 'e_user_providerTestAdapter'));

		parse_str((string) parse_url($address, PHP_URL_QUERY), $query);
		$this::assertSame('www.facebook.com', parse_url($address, PHP_URL_HOST));
		$this::assertSame('123456789012345', isset($query['client_id']) ? $query['client_id'] : null);
		$this::assertSame(0, e_user_providerTestAdapter::$constructed);
	}

	/**
	 * @param array $options saved for Facebook as {@see e_user_providerTest::withStoredFacebook()} saves them
	 * @return string the address a visitor signing in with Facebook is sent to
	 */
	private function facebookSignInAddress(array $options)
	{
		$restore = $this->withStoredFacebook($options);
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$address = '';
		\Hybridauth\HttpClient\Util::setRedirectHandler(function ($url) use (&$address)
		{
			$address = $url;
			throw new \LogicException('The visitor would leave here.');
		});
		try
		{
			$provider = new e_user_provider('Facebook');
			$provider->login(false);
		}
		catch (\LogicException $leaving)
		{
		}
		finally
		{
			\Hybridauth\HttpClient\Util::setRedirectHandler(null);
			if ($method === null)
			{
				unset($_SERVER['REQUEST_METHOD']);
			}
			else
			{
				$_SERVER['REQUEST_METHOD'] = $method;
			}
			$restore();
		}

		return $address;
	}

	public function testAStoredLogFileLeavesTheLogWhereE107KeepsIt()
	{
		$elsewhere = tempnam(sys_get_temp_dir(), 'hybridauth');
		$restore = $this->withStoredFacebook(array('debug_file' => $elsewhere));
		try
		{
			$logger = $this->facebookAdapterProperty('logger');
		}
		finally
		{
			$restore();
			unlink($elsewhere);
		}

		$file = new \e107\Reflection\ReflectionProperty('Hybridauth\Logger\Logger', 'file');
		$this::assertSame(e_LOG . 'hybridAuth.log', $file->getValue($logger));
	}

	public function testAStoredLogLevelLeavesTheLevelE107Sets()
	{
		$restore = $this->withStoredFacebook(array('debug_mode' => 'none'));
		try
		{
			$logger = $this->facebookAdapterProperty('logger');
		}
		finally
		{
			$restore();
		}

		$level = new \e107\Reflection\ReflectionProperty('Hybridauth\Logger\Logger', 'level');
		$this::assertSame('error', $level->getValue($logger));
	}

	public function testStoredCurlOptionsLeaveTheRequestsAsHybridauthMakesThem()
	{
		$restore = $this->withStoredFacebook(array('curl_options' => array(CURLOPT_USERAGENT => 'stored-agent')));
		try
		{
			$client = $this->facebookAdapterProperty('httpClient');
		}
		finally
		{
			$restore();
		}

		$options = new \e107\Reflection\ReflectionProperty('Hybridauth\HttpClient\Curl', 'curlOptions');
		$this::assertSame($options->getValue(new \Hybridauth\HttpClient\Curl()), $options->getValue($client));
	}

	public function testAStoredTokenIsNotUnserialised()
	{
		$restore = $this->withStoredFacebook(array('tokens' => array(
			'access_token' => array('lateObject' => serialize(new e_user_providerTestToken())),
		)));
		try
		{
			new e_user_provider('Facebook');
		}
		finally
		{
			$restore();
		}

		$this::assertSame(0, e_user_providerTestToken::$unserialised);
	}

	public function testATokenFromTheProvidersServerIsNotUnserialised()
	{
		$restore = $this->withStoredProvider('Keycloak', array(
			'enabled' => 1,
			'url'     => 'https://keycloak.example.invalid',
			'realm'   => 'site',
			'keys'    => array('id' => 'site', 'secret' => 'client-secret'),
		));
		try
		{
			$exchange = new \e107\Reflection\ReflectionMethod('Hybridauth\Adapter\OAuth2', 'validateAccessTokenExchange');
			$answer = $exchange->invoke($this->builtAdapter('Keycloak'), http_build_query(array(
				'access_token' => array('lateObject' => serialize(new e_user_providerTestToken())),
			)));
		}
		finally
		{
			$restore();
		}

		$this::assertSame(0, e_user_providerTestToken::$unserialised);
		$this::assertTrue($answer->exists('access_token'));
	}

	public function testAStoredTokenConnectsNoVisitor()
	{
		$restore = $this->withStoredFacebook(array('tokens' => array('access_token' => 'stored-access-token')));
		try
		{
			$provider = new e_user_provider('Facebook');
		}
		finally
		{
			$restore();
		}

		$this::assertNull($provider->adapter);
	}

	public function testTelegramIsOfferedNoTokensField()
	{
		$this::assertArrayNotHasKey('tokens', e_user_provider::getFieldsOf('Telegram'));
	}

	public function testStoredAddressesLeaveTheSignInAsE107MakesIt()
	{
		parse_str((string) parse_url($this->facebookSignInAddress(array()), PHP_URL_QUERY), $expected);
		$address = $this->facebookSignInAddress(array(
			'endpoints'                => array('authorize_url' => 'https://identity.example.invalid/authorize'),
			'callback'                 => 'https://callback.example.invalid/',
			'authorize_url_parameters' => array('redirect_uri' => 'https://parameters.example.invalid/'),
		));

		parse_str((string) parse_url($address, PHP_URL_QUERY), $query);
		$this::assertSame('www.facebook.com', parse_url($address, PHP_URL_HOST));
		unset($expected['state'], $query['state']);
		$this::assertSame($expected, $query);
	}

	public function testTelegramIsOfferedNoCallbackField()
	{
		$this::assertArrayNotHasKey('callback', e_user_provider::getFieldsOf('Telegram'));
	}

	/**
	 * @param string $property one of {@see \Hybridauth\Adapter\AbstractAdapter}'s
	 * @return mixed what it holds in the Facebook adapter e107 builds from the stored settings
	 */
	private function facebookAdapterProperty($property)
	{
		$held = new \e107\Reflection\ReflectionProperty('Hybridauth\Adapter\AbstractAdapter', $property);

		return $held->getValue($this->builtAdapter('Facebook'));
	}

	/**
	 * @param string $providerName
	 * @return \Hybridauth\Adapter\AdapterInterface the adapter e107 builds for it from the stored settings
	 */
	private function builtAdapter($providerName)
	{
		$hybridauth = new \e107\Reflection\ReflectionProperty('e_user_provider', 'hybridauth');

		return $hybridauth->getValue(new e_user_provider($providerName))->getAdapter($providerName);
	}

	/**
	 * @param array $options saved for an enabled Facebook beside its app id and secret, as the social plugin saves them
	 * @return callable as {@see e_user_providerTest::withStoredProvider()} returns it
	 */
	private function withStoredFacebook(array $options)
	{
		return $this->withStoredProvider('Facebook', array_merge(
			array('enabled' => 1, 'keys' => array('id' => '123456789012345', 'secret' => 'app-secret')),
			$options
		));
	}

	/**
	 * @param string $providerName
	 * @param array $settings saved for it as the social plugin saves them
	 * @return callable puts the social login preferences back and forgets what the provider's adapter kept in the session
	 */
	private function withStoredProvider($providerName, array $settings)
	{
		$restorePrefs = $this->withCorePrefs(array('social_login' => array(), 'social_login_active' => 1));

		include_once(e_PLUGIN . 'social/includes/social_login_config.php');
		$manager = new social_login_config(e107::getConfig());
		$manager->setProviderConfig($providerName, $settings);

		return function () use ($providerName, $restorePrefs)
		{
			$storage = new \Hybridauth\Storage\Session();
			$storage->deleteMatch($providerName . '.');
			$restorePrefs();
		};
	}
}

/**
 * Counts its constructions, standing in for any class a stored option could name.
 */
class e_user_providerTestAdapter
{
	public static $constructed = 0;

	public function __construct()
	{
		self::$constructed++;
	}

	public function isConnected()
	{
		return false;
	}

	public function authenticate()
	{
	}

	public function getUserProfile()
	{
		return new \Hybridauth\User\Profile();
	}
}

/**
 * Counts how often it is unserialised, standing in for any object a stored token could carry.
 */
class e_user_providerTestToken
{
	public static $unserialised = 0;

	public function __wakeup()
	{
		self::$unserialised++;
	}

	public function __toString()
	{
		return 'token';
	}
}
