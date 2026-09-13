<?php

/**
 * Regression test for issue #6002: the member list parses e_QUERY positionally
 * as from.records.order, and both URL configurations reach it with fewer than
 * three components. The missing record count arrived as 0, which the query
 * builder emits as LIMIT 0, so the page answered "No registered members yet."
 * instead of the members at that offset.
 */
class UserListPagingCest
{
	const SEEDED = 25;
	const PER_PAGE = 20;
	const NO_MEMBERS = 'No registered members yet.';

	/** @var string[] seeded member names, lowest user_id first */
	private $seeded = array();

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	public function anOffsetOnlyQueryListsTheMembersAtThatOffset(AcceptanceTester $I)
	{
		$I->wantTo('list members from a URL that carries only an offset');

		$this->seedMembers($I, self::SEEDED);

		$I->amOnPage('/user.php?' . self::PER_PAGE);
		$I->dontSee(self::NO_MEMBERS);
		$I->see($this->seeded[0]);
		$I->dontSee($this->seeded[self::SEEDED - 1]);
	}

	public function theSefListDefaultsListMembers(AcceptanceTester $I)
	{
		$I->wantTo('list members from the query the SEF list rule builds for /user/list');

		$I->amOnPage('/user.php?0.0.0');
		$I->dontSee(self::NO_MEMBERS);
		$I->see(\Helper\AdminLogin::ADMIN_USER);
	}

	public function anExplicitRecordCountIsHonoured(AcceptanceTester $I)
	{
		$I->wantTo('keep an explicit record count out of the default');

		$this->seedMembers($I, 3);

		$I->amOnPage('/user.php?0.2.DESC');
		$I->see($this->seeded[2]);
		$I->dontSee($this->seeded[0]);
	}

	public function theListLinksToTheNextPage(AcceptanceTester $I)
	{
		$I->wantTo('reach the second page from the navigation bar');

		$this->seedMembers($I, self::SEEDED);

		$I->amOnPage('/user.php');
		$I->see($this->seeded[self::SEEDED - 1]);
		$I->seeInSource('user.php?20.20.DESC');
	}

	public function aPostedRecordCountOfZeroDoesNotEmptyTheList(AcceptanceTester $I)
	{
		$I->wantTo('survive a record count of zero submitted from the list form');

		$this->seedMembers($I, self::SEEDED);

		$I->amOnPage('/user.php');
		$I->submitForm("form[action*='user.php']", array('records' => '0', 'order' => 'DESC'));
		$I->dontSee(self::NO_MEMBERS);
		$I->see($this->seeded[self::SEEDED - 1]);
	}

	/**
	 * @param int $count
	 * @return void
	 */
	private function seedMembers(AcceptanceTester $I, $count)
	{
		$this->seeded = array();

		for($i = 1; $i <= $count; $i++)
		{
			$name = 'PagingMember' . str_pad($i, 2, '0', STR_PAD_LEFT);
			$I->haveInDatabase('e107_user', array(
				'user_name'      => $name,
				'user_loginname' => strtolower($name),
				'user_email'     => strtolower($name) . '@example.test',
				'user_password'  => '',
				'user_signature' => '',
				'user_prefs'     => '',
				'user_class'     => '',
				'user_perms'     => '',
				'user_realm'     => '',
				'user_ban'       => 0,
				'user_join'      => time(),
			));
			$this->seeded[] = $name;
		}
	}
}
