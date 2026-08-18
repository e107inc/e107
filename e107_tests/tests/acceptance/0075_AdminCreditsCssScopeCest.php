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

	/**
	 * Selector list of every rule in the credits stylesheet, as served.
	 *
	 * The admin header merges each inline registration into one <style>
	 * element, joined on a blank line, so the credits sheet is the merged part
	 * naming {@see self::WRAPPER_CLASS}.
	 *
	 * @param AcceptanceTester $I
	 * @return string[]
	 */
	private function grabCreditsStylesheetSelectors(AcceptanceTester $I)
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

		$I->assertCount(1, $stylesheets, 'Expected one inline stylesheet naming '.self::WRAPPER_CLASS.'.');

		$selectors = array();

		foreach (explode('}', $stylesheets[0]) as $rule)
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
