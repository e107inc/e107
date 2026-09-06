<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers the parameter handling of {@see featurebox_menu::shortcode()}, which
 * featurebox_menu.php uses to turn one menu placement into a {FEATUREBOX} call.
 */
class featurebox_menuTest extends \Test\Unit
{
	public function _before()
	{
		require_once(e_PLUGIN . 'featurebox/e_menu.php');
	}

	public function testUnconfiguredPlacementRendersThePreferenceCategoryAlone()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode(array(), 'bootstrap3_carousel')
		);
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode('', 'bootstrap3_carousel')
		);
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode(null, 'bootstrap3_carousel')
		);
	}

	public function testCategoryArrivesFromMenuManagerArray()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap_tabs}',
			featurebox_menu::shortcode(array('category' => 'bootstrap_tabs'), 'bootstrap3_carousel')
		);
	}

	public function testCategoryArrivesFromMenuShortcodeQueryString()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap_tabs}',
			featurebox_menu::shortcode('category=bootstrap_tabs', 'bootstrap3_carousel')
		);
		$this->assertSame(
			'{FEATUREBOX|bootstrap_tabs=cols=2}',
			featurebox_menu::shortcode('category=bootstrap_tabs&cols=2', 'bootstrap3_carousel')
		);
	}

	public function testPathIsNotForwardedToTheShortcode()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap_tabs}',
			featurebox_menu::shortcode('path=featurebox/featurebox&category=bootstrap_tabs', 'bootstrap3_carousel')
		);
	}

	public function testFlagParametersSurviveOnlyWhenSet()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel=notablestyle=1}',
			featurebox_menu::shortcode(array('notablestyle' => 1), 'bootstrap3_carousel')
		);
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode(array('notablestyle' => 0), 'bootstrap3_carousel')
		);
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode('notablestyle=0&no_fill_empty=0', 'bootstrap3_carousel')
		);
	}

	public function testTablestylePassesThrough()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap_tabs=tablestyle=fbmenu}',
			featurebox_menu::shortcode(array('category' => 'bootstrap_tabs', 'tablestyle' => 'fbmenu'), 'bootstrap3_carousel')
		);
	}

	public function testHostileCategoryCannotCloseTheShortcode()
	{
		$built = featurebox_menu::shortcode(array('category' => 'bootstrap_tabs}{SITENAME'), 'bootstrap3_carousel');

		$this->assertSame('{FEATUREBOX|bootstrap_tabsSITENAME}', $built);
	}

	public function testNonScalarCategoryFallsBackWithoutANotice()
	{
		$this->assertSame(
			'{FEATUREBOX|bootstrap3_carousel}',
			featurebox_menu::shortcode('category[]=bootstrap_tabs', 'bootstrap3_carousel')
		);
	}

	public function testHostileParameterValueCannotCloseTheShortcode()
	{
		$built = featurebox_menu::shortcode(array('tablestyle' => 'x}{SITENAME'), 'bootstrap3_carousel');

		$this->assertSame('{FEATUREBOX|bootstrap3_carousel=tablestyle=x%7D%7BSITENAME}', $built);
	}

	/**
	 * Parsed by {@see e_parse_shortcode::doCode()} itself rather than by a
	 * restatement of it, so the split is asserted where it actually happens.
	 */
	public function testTheBuiltStringSplitsIntoModAndParmAsIntended()
	{
		$sc = new featurebox_menu_test_shortcodes();

		$this->assertSame(
			'mod=bootstrap3_carousel|parm=',
			e107::getParser()->parseTemplate(
				featurebox_menu::shortcode(array(), 'bootstrap3_carousel'),
				true,
				$sc
			)
		);

		$this->assertSame(
			'mod=bootstrap_tabs|parm=cols=2&notablestyle=1',
			e107::getParser()->parseTemplate(
				featurebox_menu::shortcode('category=bootstrap_tabs&cols=2&notablestyle=1', 'bootstrap3_carousel'),
				true,
				$sc
			)
		);
	}

	public function testAHostileCategoryReachesTheShortcodeAsTextRatherThanMarkup()
	{
		$this->assertSame(
			'mod=bootstrap_tabsSITENAME|parm=',
			e107::getParser()->parseTemplate(
				featurebox_menu::shortcode(array('category' => 'bootstrap_tabs}{SITENAME'), 'bootstrap3_carousel'),
				true,
				new featurebox_menu_test_shortcodes()
			)
		);
	}
}

class featurebox_menu_test_shortcodes
{
	public function sc_featurebox($parm = null, $mod = '')
	{
		$parm = is_array($parm) ? http_build_query($parm, '', '&') : (string) $parm;

		return 'mod=' . $mod . '|parm=' . $parm;
	}
}
