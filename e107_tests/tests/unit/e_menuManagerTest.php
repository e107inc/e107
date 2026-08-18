<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	class e_menuManagerTest extends \Test\Unit
	{
		public function testChecklayoutPreviewsFeatureboxWithoutThePluginLanguageFile()
		{
			list($out, $exitCode) = $this->previewInBareBootstrap('{FEATUREBOX}');

			$this->assertStringNotContainsString('Undefined constant', $out,
				"checklayout() must not print a constant only the featurebox plugin defines (#5956).\n" . $out);
			$this->assertSame(0, $exitCode, "Previewing a {FEATUREBOX} layout area must not fatal.\n" . $out);
			$this->assertStringContainsString('[Feature Box]', $out,
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
			$root = realpath(e_HANDLER . '..');
			$this->assertNotFalse($root, 'Could not locate the e107 root.');

			$code = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
			$code .= "\$_E107 = array('cli' => true, 'no_lan' => true); ";
			$code .= "require_once('" . addslashes($root . '/class2.php') . "'); ";
			$code .= "require_once('" . addslashes($root . '/e107_handlers/menumanager_class.php') . "'); ";
			$code .= "\$reflection = new ReflectionClass('e_menuManager'); ";
			$code .= "\$reflection->newInstanceWithoutConstructor()->checklayout('" . $fragment . "');";

			$output = array();
			$exitCode = 0;
			exec(sprintf('php -r %s 2>&1', escapeshellarg($code)), $output, $exitCode);

			return array(implode("\n", $output), $exitCode);
		}
	}
