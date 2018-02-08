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
}
