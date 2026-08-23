<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Covers the two rules issue #5994 turns on: which string addresses a featurebox
 * category ({@see plugin_featurebox_category::address()}), and what a sef is
 * narrowed to before it is stored ({@see plugin_featurebox_category::toSef()}).
 */
class featureboxCategorySefTest extends \Test\Unit
{
	/**
	 * The filter Menu Manager puts every modifier it writes through, copied from
	 * {@see featurebox_menu::shortcode()}. A sef that does not survive it cannot be
	 * placed from Menu Manager at all.
	 *
	 * @param string $modifier
	 * @return string
	 */
	private function throughMenuManager($modifier)
	{
		return preg_replace('/[^\w-]/', '', $modifier);
	}

	public function testTheSefIsTheAddress()
	{
		self::assertSame('what-you-get', plugin_featurebox_category::address(array(
			'fb_category_sef'      => 'what-you-get',
			'fb_category_template' => 'theme_features',
		)));
	}

	/**
	 * db_verify adds the column without filling it, so every row is addressed by
	 * its template until featurebox_setup::upgrade_post() runs. That window is
	 * where an untouched site would otherwise lose every {FEATUREBOX|x}.
	 */
	public function testTheTemplateIsTheAddressWhileTheSefIsStillEmpty()
	{
		self::assertSame('bootstrap3_carousel', plugin_featurebox_category::address(array(
			'fb_category_sef'      => '',
			'fb_category_template' => 'bootstrap3_carousel',
		)));

		self::assertSame('bootstrap3_carousel', plugin_featurebox_category::address(array(
			'fb_category_template' => 'bootstrap3_carousel',
		)));
	}

	public function testARowThatAddressesNothingYieldsAnEmptyAddress()
	{
		self::assertSame('', plugin_featurebox_category::address(array()));
		self::assertSame('', plugin_featurebox_category::address(array('fb_category_sef' => '  ')));
	}

	/**
	 * The reason featurebox's sef is narrower than news_category's: eHelper::secureSef()
	 * keeps accented letters, and Menu Manager's ASCII \w would then eat them, so a
	 * Slovak 'kategória' would be placed as 'kategria' and resolve to nothing.
	 */
	public function testAnAccentedSefIsTransliteratedRatherThanMangled()
	{
		$sef = plugin_featurebox_category::toSef('kategória');

		self::assertSame('kategoria', $sef);
		self::assertSame($sef, $this->throughMenuManager($sef),
			'a stored sef has to survive the Menu Manager modifier filter unchanged');

		self::assertSame('kategria', $this->throughMenuManager(eHelper::secureSef('kategória')),
			'control: this is what storing the secureSef() value would have cost her');
	}

	public function testEveryAccentedAlphabetCoreCarriesSurvivesAsAscii()
	{
		foreach(array('žltý' => 'zlty', 'ľúbime' => 'lubime', 'größe' => 'grosse', 'Ünal' => 'Unal') as $typed => $expected)
		{
			$sef = plugin_featurebox_category::toSef($typed);

			self::assertSame($expected, $sef);
			self::assertSame($sef, $this->throughMenuManager($sef));
		}
	}

	/**
	 * The upgrade copies fb_category_template into fb_category_sef verbatim, so
	 * re-saving a migrated category must not rewrite the address underneath every
	 * layout that already names it.
	 */
	public function testAMigratedTemplateNameSurvivesSanitisingUnchanged()
	{
		foreach(array('bootstrap3_carousel', 'bootstrap_tabs', 'unassigned', 'default', 'theme-features') as $template)
		{
			self::assertSame($template, plugin_featurebox_category::toSef($template));
		}
	}

	public function testSeparatorsAndMarkupCollapseIntoOneDash()
	{
		self::assertSame('what-you-get', plugin_featurebox_category::toSef('what you get'));
		self::assertSame('what-you-get', plugin_featurebox_category::toSef('  what / you & get!  '));
		self::assertSame('bold', plugin_featurebox_category::toSef('<b>bold</b>'));
	}

	public function testASefThatNarrowsToNothingComesBackEmpty()
	{
		self::assertSame('', plugin_featurebox_category::toSef('!!!'));
		self::assertSame('', plugin_featurebox_category::toSef(''));
	}
}
