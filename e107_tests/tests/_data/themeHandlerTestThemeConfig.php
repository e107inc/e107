<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Stands in for a theme's own theme_config class in themeHandlerTest; lives
 * outside tests/unit because a test file's classes are declared for the whole
 * run and three shipped themes own this name.
 */

class theme_config
{
	public function config()
	{
		return themeHandlerTest::themeConfigFields();
	}
}
