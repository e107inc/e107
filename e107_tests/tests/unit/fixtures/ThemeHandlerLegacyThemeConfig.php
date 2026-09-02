<?php

/**
 * Stands in for a pre-v2.1.4 theme's configuration class, which is named
 * theme_<folder> rather than theme_config and whose process() returns whether
 * anything changed rather than whether a write succeeded.
 *
 * {@see themeHandler::setThemeConfig()} takes its legacy branch for any class
 * not named theme_config.
 */
class ThemeHandlerLegacyThemeConfig
{
	public function process()
	{
		return false;
	}
}
