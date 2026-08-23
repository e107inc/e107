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
	public function testEveryStatisticsRowIsAListGroupItem()
	{
		e107::plugLan('login_menu', null);

		$sc_style = array();

		include(e_PLUGIN . 'login_menu/login_menu_template.php');

		$wrapper = $sc_style['LM_STATS']['pre'];

		$this->assertSame(
			substr_count($wrapper, '<li'),
			substr_count($wrapper, 'list-group-item'),
			'Every <li> the LM_STATS wrapper opens must carry list-group-item: '
			. $wrapper
		);
	}
}
