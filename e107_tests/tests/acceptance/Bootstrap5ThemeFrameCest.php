<?php

/** The page frame of the shipped Bootstrap 5 theme: a navbar that stays in the page flow, and air above the footer (#6800). */
class Bootstrap5ThemeFrameCest
{
        public function theNavbarIsStickyInsteadOfFixed(AcceptanceTester $I)
        {
                $I->amOnPage('/news.php');

                $I->seeElement('nav.navbar.sticky-top');
                $I->dontSeeElement('nav.navbar.fixed-top');
        }

        public function theFooterHasSpaceAboveIt(AcceptanceTester $I)
        {
                $I->amOnPage('/news.php');

                $I->seeElement('footer.footer.mt-4');
        }
}
