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
		$I->haveMember(self::MEMBER, self::MEMBER_PASS);
		$I->loginAsMember(self::MEMBER, self::MEMBER_PASS);
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
