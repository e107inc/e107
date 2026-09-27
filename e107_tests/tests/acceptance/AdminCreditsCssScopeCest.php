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
	const STYLESHEET_VARIABLE = '$css';
	const REGISTRAR = 'css';
	const INLINE_REGISTRATION = 'inline';

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
	 * The credits stylesheet, as {@see self::PAGE} declares it and as that page serves it.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function grabCreditsStylesheet(AcceptanceTester $I)
	{
		$stylesheet = $this->declaredCreditsStylesheet($I);

		$I->assertSame(array(trim($stylesheet)), $this->servedCreditsStylesheets($I),
			'Expected the admin page to serve the stylesheet declared in '.self::PAGE.' whole, once, and as the '
			.'only inline CSS naming '.self::WRAPPER_CLASS.'. Another file registering the credits sheet, a blank '
			.'line cutting this one in two, or rules appended to it elsewhere all reach the admin page unscanned.');

		return $stylesheet;
	}

	/**
	 * Every inline stylesheet the served page carries that names {@see self::WRAPPER_CLASS}, trimmed.
	 *
	 * The admin header merges the inline registrations into one <style> element joined on an empty line, so a
	 * registration that ends in a newline of its own leaves that newline on the front of the part after it.
	 *
	 * @param AcceptanceTester $I
	 * @return string[]
	 */
	private function servedCreditsStylesheets(AcceptanceTester $I)
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
					$stylesheets[] = trim($part);
				}
			}
		}

		return $stylesheets;
	}

	/**
	 * The credits stylesheet as its own page declares it, read from the source the site serves.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function declaredCreditsStylesheet(AcceptanceTester $I)
	{
		$tokens = array();

		foreach (token_get_all(file_get_contents(APP_PATH.self::PAGE)) as $token)
		{
			if (!is_array($token))
			{
				$tokens[] = array(null, $token);
			}
			elseif ($token[0] !== T_WHITESPACE && $token[0] !== T_COMMENT && $token[0] !== T_DOC_COMMENT)
			{
				$tokens[] = $token;
			}
		}

		$declarations = array();
		$registrations = 0;
		$otherUses = array();

		foreach ($tokens as $position => $token)
		{
			if ($this->mayRegisterInlineCss($tokens, $position))
			{
				$registrations++;
			}

			if ($token[0] !== T_VARIABLE || $token[1] !== self::STYLESHEET_VARIABLE)
			{
				continue;
			}

			$before = $this->tokenText($tokens, $position - 1);
			$after = $this->tokenText($tokens, $position + 1);
			$literal = isset($tokens[$position + 2]) ? $tokens[$position + 2] : array(null, '');
			$end = $this->tokenText($tokens, $position + 3);

			if ($after === '=' && $literal[0] === T_CONSTANT_ENCAPSED_STRING && $end === ';')
			{
				$declarations[] = $literal[1];
			}
			elseif ($before !== ',' || $after !== ')')
			{
				$otherUses[] = $before.' '.$token[1].' '.$after;
			}
		}

		$I->assertCount(1, $declarations,
			'Expected '.self::PAGE.' to declare '.self::STYLESHEET_VARIABLE.' as one plain string literal that '
			.'ends the statement, which is the sheet this test scans.');

		$I->assertSame(array(), $otherUses,
			'Expected every other use of '.self::STYLESHEET_VARIABLE.' to be a bare argument. A sheet composed '
			.'anywhere else carries rules to the admin page that this test never scans.');

		$I->assertSame(1, $registrations,
			'Expected '.self::PAGE.' to make one ::'.self::REGISTRAR.'() call that registers inline CSS, which is '
			.'the one stylesheet it declares. A second inline registration reaches the admin page unscanned, and '
			.'a call this test cannot read the type of is counted as one rather than waved through.');

		$I->assertSame(0, preg_match('/\\\\|\$[A-Za-z_\x80-\xff{]/', $declarations[0]),
			'Expected the declared stylesheet to need no decoding: a backslash escape or an interpolation puts '
			.'something other than these characters on the page.');

		$I->assertStringContainsString(self::WRAPPER_CLASS, $declarations[0],
			'Expected the declared stylesheet to name the credits wrapper.');

		return (string) substr($declarations[0], 1, -1);
	}

	/**
	 * Whether the token at $position opens a static {@see e107::css()} call that this test cannot read as registering something other than inline CSS.
	 *
	 * @param array $tokens
	 * @param int $position
	 * @return bool
	 */
	private function mayRegisterInlineCss($tokens, $position)
	{
		if ($tokens[$position][0] !== T_STRING || $tokens[$position][1] !== self::REGISTRAR
			|| $this->tokenText($tokens, $position - 1) !== '::'
			|| $this->tokenText($tokens, $position + 1) !== '(')
		{
			return false;
		}

		$type = isset($tokens[$position + 2]) ? $tokens[$position + 2] : array(null, '');

		if ($type[0] !== T_CONSTANT_ENCAPSED_STRING || $this->tokenText($tokens, $position + 3) !== ','
			|| strpos($type[1], '\\') !== false)
		{
			return true;
		}

		return (string) substr($type[1], 1, -1) === self::INLINE_REGISTRATION;
	}

	/**
	 * The source text of the token at $position, or the empty string where the walk has run off either end.
	 *
	 * @param array $tokens
	 * @param int $position
	 * @return string
	 */
	private function tokenText($tokens, $position)
	{
		return isset($tokens[$position]) ? $tokens[$position][1] : '';
	}

	/**
	 * Selector list of every rule in the credits stylesheet.
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
