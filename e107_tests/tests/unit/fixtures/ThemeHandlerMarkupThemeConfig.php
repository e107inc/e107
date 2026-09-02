<?php

/**
 * Stands in for a theme whose configuration holds markup rows rather than field
 * declarations: numeric keys, and help text on the first row only. That is the
 * shape the shipped _blank theme has.
 *
 * {@see themeHandler::renderThemeConfig()} takes its is_numeric() branch for a
 * row like this.
 */
class ThemeHandlerMarkupThemeConfig
{
	public function config()
	{
		return array(
			array(
				'caption' => 'Sample configuration field',
				'html'    => "<input type='text' name='markup_example' />",
				'help'    => 'Example help text for this input field',
			),
			array(
				'caption' => 'Sample configuration field 2',
				'html'    => "<input type='text' name='markup_example2' />",
			),
		);
	}
}
