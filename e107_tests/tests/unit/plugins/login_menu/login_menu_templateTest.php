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
 * Regression coverage for issue #6048: the LM_STATS wrapper closed its
 * captioned <li> and opened a bare one for the figures, so inside
 * <ul class="list-group"> the statistics rendered without the border and the
 * padding that every other entry in the logged-in menu carries.
 */
class login_menu_templateTest extends \Test\Unit
{
	/** @var mixed */
	protected $savedMenuData;

	/** @var mixed */
	protected $savedStatsTemplate;

	protected function _before()
	{
		$this->savedMenuData = e107::getRegistry('login_menu_data');
		$this->savedStatsTemplate = isset($GLOBALS['LOGIN_MENU_STATS'])
			? $GLOBALS['LOGIN_MENU_STATS'] : null;
	}

	protected function _after()
	{
		e107::setRegistry('login_menu_data', null);

		if ($this->savedMenuData !== null)
		{
			e107::setRegistry('login_menu_data', $this->savedMenuData);
		}

		if ($this->savedStatsTemplate === null)
		{
			unset($GLOBALS['LOGIN_MENU_STATS']);
		}
		else
		{
			$GLOBALS['LOGIN_MENU_STATS'] = $this->savedStatsTemplate;
		}
	}

	public function testEveryStatisticsRowIsAListGroupItem()
	{
		e107::plugLan('login_menu', null);

		$sc_style = array();

		include(e_PLUGIN . 'login_menu/login_menu_template.php');

		$wrapper = $sc_style['LM_STATS']['pre'];

		preg_match_all('/<li\b[^>]*>/', $wrapper, $opened);

		$this->assertNotEmpty($opened[0], $wrapper);

		foreach ($opened[0] as $element)
		{
			$this->assertSame(
				1,
				preg_match('/class="[^"]*\blist-group-item\b/', $element),
				$element . ' in ' . $wrapper
			);
		}
	}

	/**
	 * With the three core counters off and only a plugin statistic ticked,
	 * every inner shortcode returns '' and the template's own whitespace is
	 * all that is left. The wrapper gate treats that as content, so styling
	 * the second element would have drawn an empty bordered row.
	 */
	public function testTheStatisticsBlockIsDroppedWhenEveryCounterIsEmpty()
	{
		e107::plugLan('login_menu', null);
		require_once(e_PLUGIN . 'login_menu/login_menu_shortcodes.php');

		$sc_style = array();

		include(e_PLUGIN . 'login_menu/login_menu_template.php');

		$GLOBALS['LOGIN_MENU_STATS'] = $LOGIN_MENU_STATS;
		e107::setRegistry('login_menu_data', array('enable_stats' => true));

		$sc = new login_menu_shortcodes();

		$this->assertSame('', $sc->sc_lm_stats());
	}
}
