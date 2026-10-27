<?php

/** The login page on the shipped Bootstrap 5 theme, where .form-group has no margin of its own (#6800). */
class LoginFormSpacingCest
{
        public function eachLoginFieldGetsItsOwnSpacing(AcceptanceTester $I)
        {
                $I->amOnPage('/login.php');

                $I->seeElement('#login-template .form-group.my-2 input[name="username"]');
                $I->seeElement('#login-template .form-group.my-2 input[name="userpass"]');
        }

        public function theLoginButtonFillsTheWidthOfTheFields(AcceptanceTester $I)
        {
                $I->amOnPage('/login.php');

                $I->seeElement('#login-template .form-group.my-2 input[name="userlogin"].w-100');
        }
}
