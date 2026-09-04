<?php

/**
 * A news submission is stored through the bbcode save pass, so {@see bb_img::toDB()} is what decides which [img] parameters survive it.
 */
class SubmitNewsBbcodeSaveCest
{
	const TITLE = 'e107 tests bbcode save pass';

	const MEMBER = 'subnewsbbcode';

	const MEMBER_PASS = 'x107x107';

	const PAYLOAD = '[img height=1" onload="alert(document.domain)]{e_THEME}bootstrap3/images/logo.png[/img]';

	public function _before(AcceptanceTester $I)
	{
		$this->emptyQueue($I);
		$this->haveMember($I);
		$this->loginAsMember($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$this->emptyQueue($I);
	}

	public function submittedNewsRunsTheBbcodeSavePass(AcceptanceTester $I)
	{
		$I->wantTo('drop an [img] parameter the bbcode whitelist rejects, on submission');

		$I->amOnPage('/submitnews.php');
		$I->seeElement('#dataform');
		$I->dontSeeElement('input[name=submitnews_name]');

		$I->submitForm('#dataform', array(
			'submitnews_title'       => self::TITLE,
			'submitnews_item'        => self::PAYLOAD,
			'submitnews_keywords'    => '',
			'submitnews_summary'     => '',
			'submitnews_description' => '',
			'cat_id'                 => 1,
		), 'submitnews_submit');

		$I->seeInDatabase('e107_submitnews', array('submitnews_title' => self::TITLE));

		$stored = $I->grabFromDatabase('e107_submitnews', 'submitnews_item',
			array('submitnews_title' => self::TITLE));

		$I->assertStringContainsString('[img', $stored,
			'the submission has to reach the queue for this to be testing anything');
		$I->assertStringNotContainsString('onload', $stored,
			'the bbcode save pass has to drop a parameter that is not on the [img] whitelist');
	}

	/**
	 * A member, because subnews_class ships as e_UC_MEMBER and post_html does not.
	 *
	 * @param AcceptanceTester $I
	 * @return int user id
	 */
	private function haveMember(AcceptanceTester $I)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name' => self::MEMBER, 'user_loginname' => self::MEMBER, 'user_login' => self::MEMBER,
			'user_password' => md5(self::MEMBER_PASS),
			'user_email' => self::MEMBER.'@example.com',
			'user_join' => time(), 'user_ban' => 0,
			'user_lastvisit' => time() - 86400, 'user_currentvisit' => time() - 86400,
			'user_class' => '253',
			'user_admin' => 0, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
	}

	/**
	 * The stored password is a plain md5, which UserHandler reads as PASSWORD_E107_MD5 whatever the site is set to.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function loginAsMember(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', self::MEMBER);
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');
	}

	/**
	 * Remove this test's own rows, and no others; floodprotect reads the newest row in the table whoever wrote it.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function emptyQueue(AcceptanceTester $I)
	{
		$statement = $I->getDbModule()->_getDbh()
			->prepare('DELETE FROM e107_submitnews WHERE submitnews_title = ?');
		$statement->execute(array(self::TITLE));
	}
}
