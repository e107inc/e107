<?php

/**
 * A password still stored as md5, as on a site upgraded in place from e107 v1, is replaced with the preferred hash at the member's next login.
 */
class LegacyPasswordUpgradeCest
{
	const MEMBER = 'legacymd5member';

	const MEMBER_PASS = 'x107legacy';

	public function anMd5PasswordIsRehashedAtLogin(AcceptanceTester $I)
	{
		$I->wantTo('rehash a password stored as md5 when its member logs in');

		$userId = $I->haveMember(self::MEMBER, self::MEMBER_PASS, array('user_password' => md5(self::MEMBER_PASS)));

		$I->loginAsMember(self::MEMBER, self::MEMBER_PASS);

		$stored = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $userId));

		$I->assertNotSame(md5(self::MEMBER_PASS), $stored,
			'The login accepted the md5 hash and left it in place, so the account stays on the weakest encoding.');
		$I->assertTrue(password_verify(self::MEMBER_PASS, $stored),
			'The hash that replaced the md5 one no longer verifies the member\'s password.');
	}
}
