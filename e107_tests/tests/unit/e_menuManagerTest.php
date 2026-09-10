<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	class e_menuManagerTest extends \Codeception\Test\Unit
	{
		use \Test\BootedCli;

		public function testChecklayoutPreviewsFeatureboxWithoutThePluginLanguageFile()
		{
			list($out, $exitCode) = $this->previewInBareBootstrap('{FEATUREBOX}');

			$this->assertSame(false, strpos($out, 'Undefined constant'),
				"checklayout() must not print a constant only the featurebox plugin defines (#5956).\n" . $out);
			$this->assertSame(0, $exitCode, "Previewing a {FEATUREBOX} layout area must not fatal.\n" . $out);
			$this->assertTrue(strpos($out, '[Feature Box]') !== false,
				"The preview should fall back to the plugin's English name.\n" . $out);
		}

		/**
		 * Runs one theme-layout shortcode through {@see e_menuManager::checklayout()}
		 * in a child process whose bootstrap defines no global plugin language
		 * constants, the state of a site that has not installed the plugin.
		 *
		 * @param string $fragment
		 * @return array output and exit code
		 */
		private function previewInBareBootstrap($fragment)
		{
			$code  = "require_once('" . addslashes(APP_PATH . '/e107_handlers/menumanager_class.php') . "'); ";
			$code .= "\$reflection = new ReflectionClass('e_menuManager'); ";
			$code .= "\$reflection->newInstanceWithoutConstructor()->checklayout('" . $fragment . "');";

			list($output, $exitCode) = $this->runInBootedCli($code, '', array('cli' => true, 'no_lan' => true));

			return array(implode("\n", $output), $exitCode);
		}
	}
