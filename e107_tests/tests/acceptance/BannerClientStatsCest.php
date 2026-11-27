<?php

/**
 * A banner client signs in on banner.php and sees their statistics there.
 */
class BannerClientStatsCest
{
	const LOGIN = 'statsclient';
	const PASSWORD = 'statspass';

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('banner');
		$I->writeAppFile('e107_plugins/banner/banner_template.php', <<<'PHP'
<?php
$BANNER_LOGIN_TABLE = "<form method='post' action='".e_SELF."'>{BANNER_LOGIN_TABLE_LOGIN}{BANNER_LOGIN_TABLE_PASSW}{BANNER_LOGIN_TABLE_SUBMIT}</form>";
$BANNER_TABLE_START = '<table>';
$BANNER_TABLE = '<tr><td>{BANNER_TABLE_ACTIVE}</td><td>{BANNER_TABLE_STARTDATE} {BANNER_TABLE_ENDDATE}</td></tr>';
$BANNER_TABLE_END = '</table>';
PHP
		);

		$I->haveInDatabase('e107_banner', \Test\Rows::banner(array(
			'banner_clientname'     => 'Stats client',
			'banner_clientlogin'    => self::LOGIN,
			'banner_clientpassword' => self::PASSWORD,
			'banner_campaign'       => 'stats',
		)));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall('banner');
		$I->dropPluginProbe();
	}

	public function aWrongClientLoginIsToldSo(AcceptanceTester $I)
	{
		$this->signIn($I, 'not the password');

		$I->see('unable to find those details');
		$I->dontSee('PAGE_NAME');
	}

	private function signIn(AcceptanceTester $I, $password)
	{
		$I->resetAllCookies();
		$I->amOnPage('/e107_plugins/banner/banner.php');

		$I->fillField('clientlogin', self::LOGIN);
		$I->fillField('clientpassword', $password);
		$I->click('clientsubmit');
	}
}
