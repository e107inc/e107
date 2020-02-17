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
		$this->assertCount(42, $result,
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
}
