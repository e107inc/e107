<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Floor coverage for the vendored, downgraded firebase/php-jwt.
 *
 * The downgrade rewrites syntax, not APIs, so vendored source can parse under
 * the PHP 5.6 floor and still fatal the moment it runs. php-floor-lint only
 * parses, and until this file nothing executed php-jwt at all, so php-jwt 7.x
 * typing its key-length validators against OpenSSLAsymmetricKey (a class PHP
 * only gained in 8.0) while openssl_pkey_get_private() hands them a resource
 * below it went straight through CI. These tests sign and verify on whichever
 * interpreter is running, so the low cells of the matrix are where a
 * post-floor type declaration shows up.
 */
class JWTTest extends \Test\Unit
{
	protected function _before()
	{
		e107::getInstance();

		if (!class_exists('Firebase\JWT\JWT'))
		{
			$this->markTestSkipped('firebase/php-jwt is not autoloadable');
		}
		if (!function_exists('openssl_pkey_new'))
		{
			$this->markTestSkipped('ext-openssl is unavailable');
		}
	}

	public function testRs256SignsAndVerifiesOnThisInterpreter()
	{
		$key = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);
		$this->assertNotEmpty($key, 'could not generate an RSA key pair');

		$this->assertTrue(openssl_pkey_export($key, $privateKey));
		$details = openssl_pkey_get_details($key);

		$token = \Firebase\JWT\JWT::encode(['sub' => 'e107'], $privateKey, 'RS256');
		$decoded = \Firebase\JWT\JWT::decode(
			$token,
			new \Firebase\JWT\Key($details['key'], 'RS256')
		);

		$this->assertSame('e107', $decoded->sub);
	}

	public function testAnRsaKeyBelowTheMinimumIsRefused()
	{
		$key = openssl_pkey_new([
			'private_key_bits' => 1024,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);
		if (empty($key))
		{
			$this->markTestSkipped('this OpenSSL build refuses a 1024-bit key outright');
		}
		$this->assertTrue(openssl_pkey_export($key, $privateKey));

		$this->expectException('DomainException');
		$this->expectExceptionMessage('Provided key is too short');
		\Firebase\JWT\JWT::encode(['sub' => 'e107'], $privateKey, 'RS256');
	}
}
