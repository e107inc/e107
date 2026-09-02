<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {ADMIN_LOGO} writes the logo's own pixel dimensions into an inline style, and
 * getimagesize() answers false for anything it cannot read as an image. The
 * last of the shortcode's three branches takes e107_images/adminlogo.png
 * without testing for it, so a missing file is an ordinary site state.
 */
class admin_shortcodesLogoTest extends \Codeception\Test\Unit
{
	/** @var admin_shortcodes */
	private $sc;

	/** @var string the shipped logo the third branch reads */
	private $logo;

	/** @var string where the shipped logo is parked while it has to be absent */
	private $parked;

	protected function _before()
	{
		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');

		require_once(e_CORE.'shortcodes/batch/admin_shortcodes.php');

		$this->sc = $this->make('admin_shortcodes');

		$this->logo = e_IMAGE.'adminlogo.png';
		$this->parked = e_IMAGE.'e107_tests_5999_adminlogo.png';
	}

	protected function _after()
	{
		$this->restoreLogo();
	}

	/**
	 * The markup a third-party admin theme's stylesheet already selects on.
	 */
	public function testTheDimensionsOfAReadableLogoAreRendered()
	{
		$dimensions = getimagesize($this->logo);

		$expected = "<img class='logo admin_logo' src='".e_IMAGE_ABS."adminlogo.png' style='width: "
			.$dimensions[0].'px; height: '.$dimensions[1]."px' alt='".ADLAN_153."' />\n";

		self::assertSame($expected, $this->sc->sc_admin_logo());
	}

	public function testAMissingLogoRendersWithoutDimensions()
	{
		$expected = "<img class='logo admin_logo' src='".e_IMAGE_ABS."adminlogo.png' alt='".ADLAN_153."' />\n";

		self::assertTrue(rename($this->logo, $this->parked));

		try
		{
			self::assertSame($expected, $this->sc->sc_admin_logo());
		}
		finally
		{
			$this->restoreLogo();
		}
	}

	/**
	 * Idempotent, so the failure path and {@see admin_shortcodesLogoTest::_after()} can both call it.
	 */
	private function restoreLogo()
	{
		if(is_file($this->parked))
		{
			rename($this->parked, $this->logo);
		}
	}
}
