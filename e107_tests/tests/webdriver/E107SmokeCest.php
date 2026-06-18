<?php

/**
 * Smoke test for the WebDriver harness itself.
 *
 * This is the canary that proves three things at once:
 *   1. the e107 install under test actually boots and serves pages;
 *   2. a real browser, running in the dedicated `selenium` container, can reach
 *      the app over HTTP at its cross-container host and is driven correctly by
 *      Codeception (the WebDriver endpoint, capabilities, and session all work);
 *   3. the host-header / pref-cache wiring that lets the browser's non-localhost
 *      host through (trusted_hosts in the dump + E107Cache invalidation) is in
 *      place, since a regression there 503s every page with "Site Configuration
 *      Issue".
 *
 * It deliberately asserts nothing feature-specific: it is the harness's own
 * health check, not a test of any particular e107 page. Real feature suites
 * build on top of this once it is green.
 */
class E107SmokeCest
{
    /**
     * The public front page renders the real e107 front-end theme through a
     * real browser (not an error page, not a host-mismatch 503).
     *
     * @param \WebDriverTester $I
     * @return void
     */
    public function frontPageRenders($I)
    {
        $I->wantTo('load the e107 front page through a real browser');

        $I->amOnPage('/');
        $I->waitForElement('body', 10);

        // The host-header killswitch renders a bare "Site Configuration Issue"
        // page; a working themed page never does. Asserting its absence keeps
        // this robust against theme/content changes while still catching the
        // cross-container host regression this harness exists to support.
        $I->dontSee('Site Configuration Issue');
        $I->seeElement('.navbar');
    }

    /**
     * An administrator can log into the admin control panel, exercising a real
     * form submit + post-login redirect through the browser.
     *
     * @param \WebDriverTester $I
     * @return void
     */
    public function adminLoginWorks($I)
    {
        $I->wantTo('log into the e107 admin area through a real browser');

        // Shared helper: fills the admin login form and asserts the control
        // panel marker, failing fast if credentials or the flow regress.
        $I->loginAsAdmin();
    }
}
