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
	const LOGO = 'e107_images/adminlogo.png';

	/** @var admin_shortcodes */
	private $sc;

	protected function _before()
	{
		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');

		require_once(e_CORE.'shortcodes/batch/admin_shortcodes.php');

		$this->sc = $this->make('admin_shortcodes');
	}

	/**
	 * The markup a third-party admin theme's stylesheet already selects on.
	 */
	public function testTheDimensionsOfAReadableLogoAreRendered()
	{
		$dimensions = getimagesize(APP_PATH.'/'.self::LOGO);

		$expected = "<img class='logo admin_logo' src='".e_IMAGE_ABS."adminlogo.png' style='width: "
			.$dimensions[0].'px; height: '.$dimensions[1]."px' alt='".ADLAN_153."' />\n";

		self::assertSame($expected, $this->sc->sc_admin_logo());
	}

	/**
	 * The logo is tracked by git, so it is parked with the registry, and put back here as well: the deployer can be a remote one.
	 */
	public function testAMissingLogoRendersWithoutDimensions()
	{
		\Helper\AppFileRegistry::park(self::LOGO);
		$expected = "<img class='logo admin_logo' src='".e_IMAGE_ABS."adminlogo.png' alt='".ADLAN_153."' />\n";

		$app = $this->getModule('\Helper\Unit');
		$shipped = file_get_contents(APP_PATH.'/'.self::LOGO);
		$app->deleteAppFile(self::LOGO);

		try
		{
			self::assertSame($expected, $this->sc->sc_admin_logo());
		}
		finally
		{
			$app->writeAppFile(self::LOGO, $shipped);
		}
	}
}
