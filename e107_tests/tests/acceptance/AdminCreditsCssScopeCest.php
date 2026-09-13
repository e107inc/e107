<?php

/**
 * Pins every rule of the Admin > Credits stylesheet to the credits wrapper.
 *
 * @see https://github.com/e107inc/e107/issues/5981
 */
class AdminCreditsCssScopeCest
{
	const PAGE = '/e107_admin/credits.php';
	const WRAPPER_CLASS = '.credits-content';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);
		$I->seeResponseCodeIs(200);
	}

	public function theCreditsPageStillRendersItsContent(AcceptanceTester $I)
	{
		$I->wantTo('see the credits content and its wrapper rules on the admin credits page');

		$I->seeInSource('class="well credits-content"');
		$I->see('Developers');
		$I->see('Released under the terms of the GNU GPL License');

		$selectors = $this->grabCreditsStylesheetSelectors($I);

		$I->assertContains('.copyright', $selectors);
		$I->assertContains('.wrapper-middle', $selectors);
	}

	public function theCreditsStylesheetNamesNoElement(AcceptanceTester $I)
	{
		$I->wantTo('keep the credits stylesheet off every element outside the credits wrapper');

		$selectors = $this->grabCreditsStylesheetSelectors($I);

		$elementSelectors = array();

		foreach ($selectors as $selector)
		{
			if (strpos($selector, '.') !== 0)
			{
				$elementSelectors[] = $selector;
			}
		}

		$I->assertSame(array(), $elementSelectors, 'Credits rules reach the whole admin page unless they name a class.');

		$I->assertContains('.credits-content p', $selectors);
		$I->assertContains('.credits-content a', $selectors);
		$I->assertContains('.credits-content a:hover', $selectors);
	}

	public function theCreditsStylesheetCarriesNoBlankLine(AcceptanceTester $I)
	{
		$I->wantTo('keep the credits stylesheet in one piece where the admin header merges it');

		$I->assertSame(0, preg_match('/\n[^\S\n]*\n/', $this->grabCreditsStylesheet($I)),
			'The admin header splits one inline registration from the next on an empty line, so a line of '
			.'nothing but whitespace in the credits CSS is one whitespace pass away from cutting the sheet '
			.'in two.');
	}

	/**
	 * The credits stylesheet, as served.
	 *
	 * The admin header merges each inline registration into one <style>
	 * element, joined on an empty line, so the credits sheet is the part
	 * naming {@see self::WRAPPER_CLASS} and it may carry no line of its own
	 * that a whitespace pass would empty.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabCreditsStylesheet(AcceptanceTester $I)
	{
		$styles = array();
		preg_match_all('#<style[^>]*>(.*?)</style>#s', $I->grabPageSource(), $styles);

		$stylesheets = array();

		foreach ($styles[1] as $style)
		{
			foreach (explode("\n\n", $style) as $part)
			{
				if (strpos($part, self::WRAPPER_CLASS) !== false)
				{
					$stylesheets[] = $part;
				}
			}
		}

		$I->assertCount(1, $stylesheets,
			'Expected one inline stylesheet naming '.self::WRAPPER_CLASS.'. A blank line inside the credits '
			.'CSS reads as a second registration here, because a blank line is what the admin header joins '
			.'the registrations on.');

		return $stylesheets[0];
	}

	/**
	 * Selector list of every rule in the credits stylesheet, as served.
	 *
	 * @param AcceptanceTester $I
	 * @return string[]
	 */
	private function grabCreditsStylesheetSelectors(AcceptanceTester $I)
	{
		$selectors = array();

		foreach (explode('}', $this->grabCreditsStylesheet($I)) as $rule)
		{
			$parts = explode('{', $rule);

			if (count($parts) < 2)
			{
				continue;
			}

			$selector = trim(preg_replace('/\s+/', ' ', $parts[0]));

			if ($selector !== '')
			{
				$selectors[] = $selector;
			}
		}

		return $selectors;
	}
}
