<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security;

use e107\Security\Jwe\Compact;

/**
 * The public verbs, and the promise the CAPTCHA rests on.
 *
 * A CAPTCHA answer is sealed into a token and handed to the visitor who has
 * to solve it. If any part of the payload can be read back out of the token,
 * the whole feature is decorative. Every other assertion here is ordinary
 * token hygiene; {@see SealedTokenTest::testTheSealedValuesAreNowhereInTheToken()}
 * is the one that matters.
 */
class SealedTokenTest extends \Test\Unit
{

	/**
	 * @var SealedToken
	 */
	protected $token;

	protected function _before()
	{
		require_once(e_HANDLER . 'Security/SealedToken.php');
		require_once(__DIR__ . '/sealed_token_doubles.php');

		SealedToken::resetKeyRing();

		$this->token = new SealedToken('unit-test');
	}

	protected function _after()
	{
		SealedToken::resetKeyRing();
	}

	public function testSealAndOpenRoundTripTheClaims()
	{
		$claims = array(
			'dest'   => 'https://example.com/some/page?a=1&b=2',
			'number' => 42,
			'nested' => array('one' => 'two'),
		);

		$sealed = $this->token->seal($claims, 600);

		$this->assertIsString($sealed);

		$opened = $this->token->open($sealed);

		$this->assertIsArray($opened);
		$this->assertSame($claims['dest'], $opened['dest']);
		$this->assertSame($claims['number'], $opened['number']);
		$this->assertSame($claims['nested'], $opened['nested']);
	}

	public function testTheRegisteredClaimsComeBackWithThePayload()
	{
		$opened = $this->token->open($this->token->seal(array('a' => 'b'), 600));

		foreach(array('iss', 'iat', 'nbf', 'exp', 'jti') as $claim)
		{
			$this->assertArrayHasKey($claim, $opened, $claim . ' is not in the opened payload');
		}

		$this->assertSame(32, strlen($opened['jti']), 'jti is what the single-use marker will be keyed on');
		$this->assertSame($opened['iat'] + 600, $opened['exp']);
	}

	/**
	 * A caller that supplies its own exp would otherwise be choosing its own
	 * expiry, which is the one claim it must not get to choose.
	 */
	public function testAClaimNameThisClassOwnsIsRefused()
	{
		foreach(array('iss', 'iat', 'nbf', 'exp', 'jti') as $claim)
		{
			$this->assertFalse($this->token->seal(array($claim => 'mine'), 600), $claim . ' was accepted from the caller');
		}
	}

	public function testTheTokenIsRfc7516CompactSerialisation()
	{
		$parts = explode('.', $this->token->seal(array('a' => 'b'), 600));

		$this->assertCount(5, $parts);
		$this->assertSame(Compact::HEADER, $parts[0]);
		$this->assertSame('', $parts[1], 'alg=dir wraps no key, so the second part is empty');
		$this->assertSame('{"alg":"dir","enc":"A256CBC-HS512"}', Compact::base64UrlDecode($parts[0]));
	}

	/**
	 * THE SECURITY PROPERTY. The predecessor signed rather than encrypted, so
	 * every CAPTCHA solution e107 issued was base64 in the page source.
	 *
	 * The assertion is on the value that was sealed, and on that value in
	 * every encoding a token could plausibly carry it in. Searching for a
	 * marker like '<script>' would pass against an implementation that
	 * merely escaped it.
	 */
	public function testTheSealedValuesAreNowhereInTheToken()
	{
		$solution = 'A7QK92';
		$secret = 'the quick brown fox jumps over the lazy dog';

		$sealed = $this->token->seal(array('solution' => $solution, 'note' => $secret), 600);

		$parts = explode('.', $sealed);
		$raw = '';

		foreach($parts as $part)
		{
			$raw .= $part === '' ? '' : Compact::base64UrlDecode($part);
		}

		foreach(array($solution, $secret) as $value)
		{
			$this->assertStringNotContainsString($value, $sealed, 'a sealed value appears in the token');
			$this->assertStringNotContainsString($value, $raw, 'a sealed value appears in the decoded token');
			$this->assertStringNotContainsString(base64_encode($value), $sealed, 'a sealed value appears base64 encoded in the token');
			$this->assertStringNotContainsString(bin2hex($value), $sealed, 'a sealed value appears hex encoded in the token');
			$this->assertStringNotContainsString(rawurlencode($value), $sealed, 'a sealed value appears url encoded in the token');
		}

		$this->assertStringNotContainsString('solution', $raw, 'even the claim names are readable');
	}

	/**
	 * Domain separation. Every form gets its own purpose precisely so that a
	 * CAPTCHA solved on the contact page cannot be replayed at registration.
	 */
	public function testATokenDoesNotOpenUnderAnotherPurpose()
	{
		$sealed = $this->token->seal(array('a' => 'b'), 600);

		$other = new SealedToken('unit-test-other');

		$this->assertFalse($other->open($sealed));
		$this->assertIsArray($this->token->open($sealed), 'the control did not open either');
	}

	public function testAnExpiredTokenIsRefused()
	{
		$this->assertFalse($this->token->open($this->token->seal(array('a' => 'b'), -100)));
	}

	public function testATokenIsRefusedTheSecondItsLifetimeIsUp()
	{
		$past = new SealedTokenAtTime('unit-test');
		$past->offset = -600;

		$this->assertNotFalse($past->seal(array('a' => 'b'), 600), 'the fixture did not seal');
		$this->assertFalse($this->token->open($past->seal(array('a' => 'b'), 600)), 'exp equal to now is still expired');

		$past->offset = -590;

		$this->assertIsArray($this->token->open($past->seal(array('a' => 'b'), 600)), 'ten seconds of life left is still alive');
	}

	public function testANotYetValidTokenIsRefused()
	{
		$future = new SealedTokenAtTime('unit-test');
		$future->offset = 3600;

		$this->assertFalse($this->token->open($future->seal(array('a' => 'b'), 600)));
	}

	public function testATokenSealedByAnotherSiteIsRefused()
	{
		$sealed = $this->token->seal(array('a' => 'b'), 600);
		$claims = $this->token->open($sealed);

		$this->assertSame(defined('SITEURL') ? SITEURL : '', $claims['iss']);

		$elsewhere = new SealedTokenElsewhere('unit-test');

		$this->assertFalse($elsewhere->open($sealed), 'a token issued by another site was accepted');
		$this->assertFalse($this->token->open($elsewhere->seal(array('a' => 'b'), 600)), 'this site accepted another site issuer');
	}

	/**
	 * One bit, in each part in turn, including the header and the empty
	 * encrypted-key slot that alg=dir leaves behind.
	 */
	public function testATamperedTokenIsRefusedAndDoesNotThrow()
	{
		$sealed = $this->token->seal(array('a' => 'b'), 600);

		foreach(array(0, 2, 3, 4) as $index)
		{
			$parts = explode('.', $sealed);
			$raw = Compact::base64UrlDecode($parts[$index]);
			$raw[0] = chr(ord($raw[0]) ^ 1);
			$parts[$index] = Compact::base64UrlEncode($raw);

			$this->assertFalse($this->token->open(implode('.', $parts)), 'part ' . $index . ' was tampered with and accepted');
		}

		$parts = explode('.', $sealed);
		$parts[1] = Compact::base64UrlEncode('key');

		$this->assertFalse($this->token->open(implode('.', $parts)), 'a wrapped key was accepted under alg=dir');
	}

	public function testOpenAnswersFalseForAnythingAtAll()
	{
		$rubbish = array(
			'null'        => null,
			'integer'     => 12345,
			'zero'        => 0,
			'boolean'     => true,
			'empty'       => '',
			'array'       => array('a'),
			'float'       => 1.5,
			'dots'        => '....',
			'four parts'  => 'a.b.c.d',
			'six parts'   => 'a.b.c.d.e.f',
			'nul bytes'   => "\0\0\0\0\0",
			'old jwt'     => 'eyJhbGciOiJIUzI1NiJ9.eyJhIjoiYiJ9.c2lnbmF0dXJl',
			'ten megabyte'=> str_repeat('a', 10 * 1024 * 1024),
		);

		foreach($rubbish as $name => $input)
		{
			$this->assertFalse($this->token->open($input), $name . ' was not refused');
		}
	}

	/**
	 * None of the three call sites in core catches, so a host that cannot
	 * acquire a key has to degrade rather than white-page.
	 */
	public function testSealAndOpenAnswerFalseWhenTheKeyCannotBeAcquired()
	{
		$sealed = $this->token->seal(array('a' => 'b'), 600);

		$keyless = new KeylessSealedToken('unit-test');

		$this->assertFalse($keyless->seal(array('a' => 'b'), 600));
		$this->assertFalse($keyless->open($sealed));
	}

	public function testTheSecretIsProvisionedOnceAndIsUsable()
	{
		$secret = SealedToken::provision();

		$this->assertSame(SealedToken::SECRET_LENGTH, strlen($secret));
		$this->assertTrue(ctype_xdigit($secret));
		$this->assertSame($secret, SealedToken::provision(), 'provisioning twice minted twice');
		$this->assertSame($secret, \e107::getConfig()->get(SealedToken::PREF_SECRET));
	}

	/**
	 * The instance holds no state that survives it, so two instances of the
	 * same purpose are interchangeable and {@see \e107::getSealedToken()} is
	 * free to hand the same one to everybody.
	 */
	public function testTwoInstancesOfOnePurposeAgree()
	{
		$other = new SealedToken('unit-test');

		$this->assertIsArray($other->open($this->token->seal(array('a' => 'b'), 600)));
	}

	public function testAnEmptyPurposeIsTheDefaultPurpose()
	{
		$blank = new SealedToken('');
		$named = new SealedToken(SealedToken::DEFAULT_PURPOSE);

		$this->assertSame(SealedToken::DEFAULT_PURPOSE, $blank->getPurpose());
		$this->assertIsArray($named->open($blank->seal(array('a' => 'b'), 600)));
	}

	public function testTheAccessorMemoisesOneInstancePerPurpose()
	{
		$this->assertSame(\e107::getSealedToken('one'), \e107::getSealedToken('one'));
		$this->assertNotSame(\e107::getSealedToken('one'), \e107::getSealedToken('two'));
		$this->assertSame('one', \e107::getSealedToken('one')->getPurpose());
		$this->assertFalse(\e107::getSealedToken('two')->open(\e107::getSealedToken('one')->seal(array('a' => 'b'), 600)));
	}
}
