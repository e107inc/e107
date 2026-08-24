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
 * login_menu.php requires the plugin's own template AFTER the theme's, from a
 * bare block rather than an else, so every variable the core template means a
 * theme to be able to set is assigned behind if(!isset(...)).
 * $LM_STATITEM_SEPARATOR was the one exception, and its only consumer,
 * sc_lm_plugin_stats(), is the shortcode revived by #6044.
 */
class login_menu_separatorTest extends \Codeception\Test\Unit
{
	public function testAThemeCanSetTheStatisticsItemSeparator()
	{
		e107::plugLan('login_menu', null);

		$LM_STATITEM_SEPARATOR = ', ';

		include(e_PLUGIN . 'login_menu/login_menu_template.php');

		$this->assertSame(', ', $LM_STATITEM_SEPARATOR);
	}
}
