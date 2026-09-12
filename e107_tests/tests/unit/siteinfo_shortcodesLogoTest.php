<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The dimension probe in {@see siteinfo_shortcodes::sc_logo()} is gated on BOOTSTRAP being
 * undefined, and the shared unit process boots a Bootstrap sitetheme, so every test here takes a
 * booted process of its own with no theme. That process carries $_E107['debug'], because a site
 * configured that way is where e107 reports every diagnostic and installs no handler to catch it,
 * so it is the render whose output the probe actually reached.
 */
class siteinfo_shortcodesLogoTest extends \Test\Unit
{
	/** The URL prefix the child answers with, so the rendered src is predictable. */
	const THEME_URL = '/e107_tests_6148_theme/';

	/** @var string a legacy theme tree whose logo is readable but is not an image */
	private $theme;

	protected function _before()
	{
		$this->theme = e_TEMP.'e107_tests_6148_theme/';

		if(!is_dir($this->theme.'images'))
		{
			mkdir($this->theme.'images', 0755, true);
		}

		file_put_contents($this->theme.'images/e_logo.png', '');
	}

	protected function _after()
	{
		@unlink($this->theme.'images/e_logo.png');
		@rmdir($this->theme.'images');
		@rmdir($this->theme);
	}

	/** A zero-byte logo is an ordinary site state and must leave the render's output clean. */
	public function testAnUnreadableLogoRendersWithoutADiagnostic()
	{
		$printed = $this->renderLogoWithoutATheme();

		self::assertSame(0, preg_match('/getimagesize/', $printed),
			"the shortcode printed the failed probe into the render's output:\n".$printed);
	}

	/** Unknown dimensions are omitted, which is what the shortcode's own !empty() guards do. */
	public function testAnUnreadableLogoRendersWithoutDimensions()
	{
		$matches = array();
		$printed = $this->renderLogoWithoutATheme();

		self::assertSame(1, preg_match('/@@(.*)@@/s', $printed, $matches),
			"the probe printed no markup:\n".$printed);

		$markup = $matches[1];

		self::assertSame(1, preg_match('/<img[^>]+'.preg_quote(self::THEME_URL, '/').'images\/e_logo\.png/', $markup),
			'the logo is not the one the legacy theme ships: '.$markup);
		self::assertSame(0, preg_match('/width|height|[&?]a?w=|[&?]a?h=/', $markup),
			'a dimension was rendered from an unreadable file: '.$markup);
	}

	/**
	 * Renders {LOGO} where BOOTSTRAP is undefined and the site carries no logo of its own, so the theme's file is the one probed.
	 *
	 * @return string everything the child printed, the markup fenced in '@@'
	 */
	private function renderLogoWithoutATheme()
	{
		$php = "e107::getConfig()->set('sitelogo', ''); "
			."define('THEME', '".addslashes($this->theme)."'); "
			."define('THEME_ABS', '".self::THEME_URL."'); "
			."require_once(e_PLUGIN.'siteinfo/e_shortcode.php'); "
			."\$sc = new siteinfo_shortcodes(); "
			."echo '@@'.\$sc->sc_logo().'@@';"
			."echo 'BOOTSTRAP='.(defined('BOOTSTRAP') ? '1' : '0');";

		list($output, $status) = $this->runInBootedCli($php, '', array('cli' => true, 'no_theme' => true, 'debug' => true));

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the probe exited ".$status.":\n".$printed);
		self::assertSame(1, preg_match('/BOOTSTRAP=0/', $printed),
			"BOOTSTRAP was defined, so the probe never ran and this test measured nothing:\n".$printed);

		return $printed;
	}
}
