<?php


class InstallCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function installWelcomePageContainsExpectedContent(AcceptanceTester $I)
    {
    	$I->amOnPage('/install.php');
		$I->see("e107 Installation :: Step 1");
		$I->see("Language Selection");
    }

	public function installStep1ToStep2(AcceptanceTester $I)
    {
        $I->amOnPage('/install.php');
        $I->wantTo("Verify Proceed to Step 1 of the Installation");
		$I->selectOption("language", 'English');
		$I->click('start');
		$I->see("MySQL Server Details", 'h3');
    }
}
