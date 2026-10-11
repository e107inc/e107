<?php

/**
 * The debug block {@see news_front::debug()} prints to an administrator while
 * debugging is on lists the request's parameters, which a link can carry.
 */
class NewsDebugBlockCest
{
	const PROBE = '<u>NewsDebugProbe</u>';

	public function aQueryParameterIsShownAsText(AcceptanceTester $I)
	{
		$I->wantTo('see a query parameter in the news debug block as text');

		$this->openDebugBlock($I, self::PROBE);

		$I->seeInSource(htmlspecialchars(self::PROBE, ENT_QUOTES, 'UTF-8'));
		$I->dontSeeInSource(self::PROBE);
	}

	/**
	 * e_QUERY arrives encoded, and the block does not encode it a second time.
	 */
	public function anAlreadyEncodedValueIsNotEncodedAgain(AcceptanceTester $I)
	{
		$this->openDebugBlock($I, 'One&Two');

		$I->seeInSource('<b>e_QUERY:</b></td><td>probe=One&amp;Two</td>');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $probe the value of the news page's probe parameter
	 */
	private function openDebugBlock(AcceptanceTester $I, $probe)
	{
		$I->loginAsAdmin();
		$I->setCookie('e107_debug_level', 'level=1');

		$I->amOnPage('/news.php?probe=' . rawurlencode($probe));

		$I->see('News Debug Info');
	}
}
