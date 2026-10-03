<?php

/**
 * Shortcode batch whose constructor stores an undeclared property first and calls the parent constructor only when asked to, as third-party theme_shortcodes classes do.
 */
class ShortcodeConstructorOrderFixture extends e_shortcode
{
	/** @param bool $callParent */
	public function __construct($callParent)
	{
		$this->themeoptions = array('layout' => 'wide');

		if($callParent)
		{
			parent::__construct();
		}
	}
}

/**
 * Shortcode batch with the empty constructor of the bundled Voux theme, which never calls the parent one.
 */
class ShortcodeEmptyConstructorFixture extends e_shortcode
{
	public function __construct()
	{
	}
}
