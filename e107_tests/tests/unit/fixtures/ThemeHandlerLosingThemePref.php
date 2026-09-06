<?php

/**
 * {@see e_theme_pref} whose every write loses the compare-and-swap that
 * {@see e_pref::persist()} runs, so a save reports failure without reaching
 * the database.
 *
 * e_HANDLER.'pref_class.php' must already be loaded when this file is included.
 */
class ThemeHandlerLosingThemePref extends e_theme_pref
{
	protected function persist()
	{
		return array('status' => 'conflict');
	}
}
