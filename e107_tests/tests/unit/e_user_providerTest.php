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

	/** @var e_user_provider */
	protected $e_user_provider;

	protected function _before()
	{
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
		$this->assertCount(46, $result,
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

		$result = e_user_provider::getSupplementalFieldsOf("Odnoklassniki");
		$this->assertTrue(array_key_exists('key', $result['keys']));
		$this->assertTrue(array_key_exists('secret', $result['keys']));
		$this->assertIsNotArray($result['keys']['key']);
		$this->assertIsNotArray($result['keys']['secret']);

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

		$result = e_user_provider::getSupplementalFieldsOf("Vkontakte");
		$this->assertTrue(array_key_exists('photo_size', $result));
	}
}
