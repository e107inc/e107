<?php

/**
 * Where a front-end sign-in sends the visitor afterwards, and which addresses it will not send them to.
 */
class LoginReturnAddressCest
{
	const MEMBER = 'e107_tests_return_member';
	const PASSWORD = 'Return-Password-1';

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::MEMBER, self::PASSWORD);
	}

	public function aTokenHiddenBehindAnEncodedAmpersandIsNotReturnedTo(AcceptanceTester $I)
	{
		$I->assertSame('/news.php', $this->landingAfterSigningInAt($I, '/news.php?extend.1&amp;e-token=forged'));
	}

	public function aPlainlySpelledTokenIsNotReturnedTo(AcceptanceTester $I)
	{
		$I->assertSame('/news.php', $this->landingAfterSigningInAt($I, '/news.php?extend.1&e-token=forged'));
	}

	public function aNameSpelledTheWayPhpRewritesItIsNotReturnedTo(AcceptanceTester $I)
	{
		$I->assertSame('/news.php', $this->landingAfterSigningInAt($I, '/news.php?extend.1&ajax.used=1'));
	}

	public function aTokenSplitByAMenuMarkerIsNotReturnedTo(AcceptanceTester $I)
	{
		$I->assertSame('/news.php', $this->landingAfterSigningInAt($I, '/news.php?[z][x]e-to[x]ken=forged'));
	}

	public function aTokenInThePathIsNotReturnedTo(AcceptanceTester $I)
	{
		$I->assertSame('/', $this->landingAfterSigningInAt($I, '/news.php/extend.1&e-token=forged'));
	}

	/**
	 * @return string the path and query below the site's address that the sign-in redirects to, after a login menu on $address is submitted
	 */
	private function landingAfterSigningInAt(AcceptanceTester $I, $address)
	{
		$landing = $I->grabLandingAfterRawPost($address, array(
			'username'  => self::MEMBER,
			'userpass'  => self::PASSWORD,
			'userlogin' => 'Sign In',
		), array(
			'Sec-Fetch-Site' => 'same-origin',
			'Sec-Fetch-Mode' => 'navigate',
			'Sec-Fetch-Dest' => 'document',
			'Sec-Fetch-User' => '?1',
		));
		$I->assertNotSame('', $landing, 'the sign-in redirects');

		return $landing;
	}
}
