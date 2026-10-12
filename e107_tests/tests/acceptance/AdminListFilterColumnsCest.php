<?php

/**
 * What an admin list's search and its ajax filter hand a delegated administrator, read from the user list and the FAQs list.
 */
class AdminListFilterColumnsCest
{
	const FILTER = '/e107_admin/users.php?mode=main&action=filter&ajax_used=1';

	const LIST_PAGE = '/e107_admin/users.php?mode=main&action=list';

	/** The account whose secrets the searches must keep to themselves. */
	const VICTIM = 'p7rpfiltervictim';

	/** Written into the victim's password hash, and nowhere else. */
	const HASH_MARK = 'HASHMARKp7rp';

	/** Manages users and bans; nothing in it opens the administrator permissions. */
	const DELEGATED = 'p7rpfilteradmin';

	/** Administers the FAQs plugin and nothing else. */
	const FAQS_ADMIN = 'p7rpfaqsadmin';

	const FAQS_FILTER = '/e107_plugins/faqs/admin_config.php?mode=main&action=filter&ajax_used=1';

	const FAQS_LIST = '/e107_plugins/faqs/admin_config.php?mode=main&action=list';

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::VICTIM, self::VICTIM, array(
			'user_password' => '$2y$10$' . self::HASH_MARK . str_repeat('A', 41),
			'user_admin'    => 1,
			'user_perms'    => '0',
			'user_class'    => '',
		));

		$I->haveMember(self::DELEGATED, self::DELEGATED, array(
			'user_admin' => 1,
			'user_perms' => '4',
			'user_class' => '',
		));

		$I->loginAsAdmin(self::DELEGATED, self::DELEGATED);
	}

	public function theFilterDoesNotHandBackThePasswordHashesForAPatternTerm(AcceptanceTester $I)
	{
		$answer = $this->filter($I, '.');

		$I->assertStringNotContainsString(self::HASH_MARK, $answer, 'The filter handed back the victim\'s password hash.');
		$I->assertStringNotContainsString($this->storedHashOf($I, 'admin'), $answer, 'The filter handed back the main administrator\'s password hash.');
	}

	public function theFilterDoesNotHandBackAPasswordHashThatHoldsTheTerm(AcceptanceTester $I)
	{
		$answer = $this->filter($I, self::HASH_MARK);

		$I->assertStringNotContainsString(self::HASH_MARK.'AAAA', $answer, 'The filter handed back the password hash holding the term.');
	}

	public function theFilterStillSuggestsTheColumnsTheListDraws(AcceptanceTester $I)
	{
		$answer = $this->filter($I, self::VICTIM);

		$I->assertStringContainsString(self::VICTIM . '@example.com', $answer, 'The filter no longer suggests the e-mail column.');
		$I->assertStringNotContainsString(self::HASH_MARK, $answer, 'The filter handed back the victim\'s password hash.');
	}

	public function theFilterPrintsTheTypedTermAsText(AcceptanceTester $I)
	{
		$answer = $this->filter($I, '<b>p7rpterm</b>');

		$I->assertStringNotContainsString('<b>p7rpterm</b>', $answer, 'The filter printed the typed term as markup.');
		$I->assertStringContainsString('&lt;b&gt;p7rpterm', $answer);
	}

	public function theListSearchDoesNotMatchThePasswordColumn(AcceptanceTester $I)
	{
		$I->amOnPage(self::LIST_PAGE . '&searchquery=' . self::HASH_MARK);

		$I->dontSeeInSource(self::VICTIM);
	}

	public function theListSearchStillFindsAUserByName(AcceptanceTester $I)
	{
		$I->amOnPage(self::LIST_PAGE . '&searchquery=' . self::VICTIM);

		$I->seeInSource(self::VICTIM);
	}

	public function theSearchInTheFieldOptionDoesNotProbeThePasswordColumn(AcceptanceTester $I)
	{
		$this->dontSeeTheListAnswerAProbe($I, 'filter_options=searchfield__user_password&searchquery=');
	}

	public function theValueFilterDoesNotProbeThePasswordColumn(AcceptanceTester $I)
	{
		$this->dontSeeTheListAnswerAProbe($I, 'searchquery=&filter_options=user_password__');
	}

	public function theBooleanFilterDoesNotProbeThePasswordColumn(AcceptanceTester $I)
	{
		$this->dontSeeTheListAnswerAProbe($I, 'searchquery=&filter_options=bool__user_password__');
	}

	/**
	 * The questions the FAQs plugin installs are the main administrator's, and its list joins each author's row.
	 */
	public function aFaqsAdministratorDoesNotReadTheAuthorsPasswordHash(AcceptanceTester $I)
	{
		$I->havePluginInstalled('faqs');

		try
		{
			$plugin = (int) $I->grabFromDatabase('e107_plugin', 'plugin_id', array('plugin_path' => 'faqs'));
			$I->haveMember(self::FAQS_ADMIN, self::FAQS_ADMIN, array(
				'user_admin' => 1,
				'user_perms' => 'P' . $plugin,
				'user_class' => '',
			));
			$I->resetAllCookies();
			$I->loginAsAdmin(self::FAQS_ADMIN, self::FAQS_ADMIN);

			$I->sendPostRequest(self::FAQS_FILTER, array(
				'searchquery' => '.',
				'e-token'     => $I->grabFreshAdminToken(self::FAQS_LIST),
			));

			$I->assertStringNotContainsString($this->storedHashOf($I, 'admin'), $I->grabPageSource(),
				'The FAQs filter handed back the main administrator\'s password hash.');
		}
		finally
		{
			$I->dropPluginInstall('faqs');
		}
	}

	/**
	 * Which rows a list answers with says what the matched column holds, one guess at a time.
	 *
	 * @param AcceptanceTester $I
	 * @param string $query the probe, completed with a value the hash holds and one it does not
	 * @return void
	 */
	private function dontSeeTheListAnswerAProbe(AcceptanceTester $I, $query)
	{
		$I->amOnPage(self::LIST_PAGE . '&' . $query . self::HASH_MARK);
		$hit = strpos($I->grabPageSource(), self::VICTIM) !== false;

		$I->amOnPage(self::LIST_PAGE . '&' . $query . 'p7rpNOTINAHASH');
		$miss = strpos($I->grabPageSource(), self::VICTIM) !== false;

		$I->assertSame($miss, $hit, 'The list answered differently for a probe its password hash holds.');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $term
	 * @return string what the filter answered
	 */
	private function filter(AcceptanceTester $I, $term)
	{
		$I->sendPostRequest(self::FILTER, array(
			'searchquery' => $term,
			'e-token'     => $I->grabFreshAdminToken(self::LIST_PAGE),
		));

		return $I->grabPageSource();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $loginName
	 * @return string
	 */
	private function storedHashOf(AcceptanceTester $I, $loginName)
	{
		$hash = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_loginname' => $loginName));

		$I->assertNotEmpty($hash, 'No stored password for '.$loginName);

		return $hash;
	}
}
