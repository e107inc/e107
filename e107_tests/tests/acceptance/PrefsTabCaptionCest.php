<?php

/**
 * Each Preferences panel is headed with the name its side-menu link gives it.
 */
class PrefsTabCaptionCest
{
	const PREFERENCES = '/e107_admin/prefs.php';

	const PANEL_LINKS = "a[href^='#core-prefs-']";

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function everyPanelIsHeadedWithTheNameOfItsMenuLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::PREFERENCES);

		$panels = $I->grabMultiple(self::PANEL_LINKS, 'href');
		$names = $I->grabMultiple(self::PANEL_LINKS);
		$mismatched = array();

		foreach(array_combine($panels, $names) as $panel => $name)
		{
			$name = $this->normalise($name);
			$captions = $I->grabMultiple('fieldset' . $panel . ' h4.caption');
			$heading = $captions ? $this->normalise($captions[0]) : '';

			if(substr($heading, -strlen($name)) !== $name)
			{
				$mismatched[$panel] = array('menu' => $name, 'heading' => $heading);
			}
		}

		$I->assertNotEmpty($panels, 'The Preferences side menu offered no panel links');
		$I->assertSame(array(), $mismatched, 'These Preferences panels are headed with a name other than their menu link');
	}

	private function normalise($text)
	{
		return trim(preg_replace('/\s+/u', ' ', $text));
	}
}
