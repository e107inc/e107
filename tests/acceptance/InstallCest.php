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
    public function tryToTest(AcceptanceTester $I)
    {
    	$I->amOnPage('/');
	$I->see("Home");
    }
}
