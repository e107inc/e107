<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_menu::renderMenu() includes a plugin's menu file named by the {PLUGIN} and {MENU} shortcodes.
 */
class menuPathTest extends \Codeception\Test\Unit
{
	const PROBE = 'menuPathProbe_menu.php';

	protected function _before()
	{
		$this->getModule('\Helper\Unit')->writeAppFile(self::PROBE, "<?php\n\$GLOBALS['menuPathProbeIncluded'] = true;\n");
		$GLOBALS['menuPathProbeIncluded'] = false;
	}

	protected function _after()
	{
		unset($GLOBALS['menuPathProbeIncluded']);
	}

	/**
	 * @return array
	 */
	public function climbingShortcodes()
	{
		return array(
			'plugin'           => array('{PLUGIN=../menuPathProbe_menu}'),
			'menu'             => array('{MENU=../menuPathProbe}'),
			'menu, colon form' => array('{MENU: path=../menuPathProbe}'),
			'menu, deeper'     => array('{MENU=forum/../../menuPathProbe}'),
		);
	}

	/**
	 * @dataProvider climbingShortcodes
	 * @param string $shortcode
	 */
	public function testAShortcodeCannotIncludeAFileOutsideThePluginsFolder($shortcode)
	{
		$this->assertFileExists(e_PLUGIN.'../'.self::PROBE);

		e107::getParser()->parseTemplate($shortcode, true);

		$this->assertFalse($GLOBALS['menuPathProbeIncluded']);
	}

	public function testAPluginMenuStillRenders()
	{
		$this->assertStringContainsString('e107', e107::getParser()->parseTemplate('{PLUGIN=siteinfo/powered_by_menu}', true));
		$this->assertStringContainsString('e107', e107::getParser()->parseTemplate('{MENU=siteinfo/powered_by}', true));
	}
}
