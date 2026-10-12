<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\User;

class SignInTokenTest extends \Test\Unit
{
	const DIGEST = '0cc175b9c0f1b6a831c399e269772661';

	const HANDLE = '00112233445566778899aabbccddeeff';

	public function testATokenFromBeforeRecordsWereKeptHasNoHandle()
	{
		$token = SignInToken::parse('7.'.self::DIGEST);

		self::assertSame(7, $token->accountId());
		self::assertSame(self::DIGEST, $token->passwordDigest());
		self::assertSame('', $token->handle());
		self::assertSame('7.'.self::DIGEST, (string) $token);
	}

	public function testATokenWithAHandleReadsBackAsItWasWritten()
	{
		$written = (string) (new SignInToken(7, self::DIGEST))->withHandle(self::HANDLE);

		self::assertSame('7.'.self::DIGEST.'.'.self::HANDLE, $written);
		self::assertSame(self::HANDLE, SignInToken::parse($written)->handle());
	}

	public function testSplittingOnEveryDotStillGivesTheAccountAndDigestFirst()
	{
		list($accountId, $digest) = explode('.', (string) (new SignInToken(7, self::DIGEST, self::HANDLE)));

		self::assertSame('7', $accountId);
		self::assertSame(self::DIGEST, $digest);
	}

	public function testAGuestHasNoAccount()
	{
		$token = SignInToken::parse('');

		self::assertSame(0, $token->accountId());
		self::assertSame('', $token->handle());
	}
}
