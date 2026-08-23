<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class eRouterTest extends \Test\Unit
{

	/** @var string[] override config files this test wrote, newest last */
	private $written = array();

	protected function _after()
	{
		foreach(array_reverse($this->written) as $file)
		{
			unlink($file);
			$dir = dirname($file);
			if(count(scandir($dir)) === 2)
			{
				rmdir($dir);
			}
		}
		$this->written = array();
	}

	/**
	 * news is a core URL module and also a bundled plugin folder, so the pass that
	 * drops overrides belonging to uninstalled plugins used to drop this one too.
	 */
	public function testAdminReadModulesKeepsAnOverrideForACoreModuleThatIsAlsoAPluginFolder()
	{
		$this->writeOverrideConfig('news', 'sef');

		$modules = eRouter::adminReadModules();

		self::assertContains('news', $modules['core']);
		self::assertContains('news', $modules['override']);

		$locations = eRouter::adminBuildLocations($modules);
		self::assertContains('override/sef', $locations['news']);
		self::assertNotContains('core/sef', $locations['news']);
	}

	public function testAdminReadModulesStillDropsAnOverrideForAnUninstalledPlugin()
	{
		$plugin = $this->anUninstalledPluginFolder();
		$this->writeOverrideConfig($plugin, 'sef');

		$modules = eRouter::adminReadModules();

		self::assertNotContains($plugin, $modules['plugin']);
		self::assertNotContains($plugin, $modules['override']);
	}

	public function testAdminReadModulesReadsOnlyTheRequestedType()
	{
		$modules = eRouter::adminReadModules('core');

		self::assertNotEmpty($modules['core']);
		self::assertSame(array(), $modules['plugin']);
		self::assertSame(array(), $modules['override']);
	}

	/**
	 * @param string $module
	 * @param string $profile
	 * @return void
	 */
	private function writeOverrideConfig($module, $profile)
	{
		$dir = e_CORE . 'override/url/' . $module;
		if(!is_dir($dir))
		{
			mkdir($dir, 0775, true);
		}

		$file = $dir . '/' . $profile . '_url.php';
		$class = eDispatcher::getConfigClassName($module, 'override/' . $profile);
		file_put_contents($file, "<?php\nclass {$class} extends eUrlConfig\n{\n\tpublic function config()\n\t{\n\t\treturn array();\n\t}\n}\n");
		$this->written[] = $file;
	}

	/**
	 * @return string a bundled plugin folder that needs installing and is not installed
	 */
	private function anUninstalledPluginFolder()
	{
		foreach(e107::getFile()->get_dirs(e_PLUGIN) as $plugin)
		{
			if(is_readable(e_PLUGIN . $plugin . '/plugin.xml') && !e107::isInstalled($plugin))
			{
				return $plugin;
			}
		}

		self::markTestSkipped('every bundled plugin is installed');
	}

	/**
	 * {@see eUrlRule::setData()} filters a rule definition through
	 * get_class_vars(), so a key the class has no property for is dropped
	 * without a word. Three core rules declared 'defaultVars' for years and the
	 * defaults never applied. Rule definitions only: the module-level config
	 * block reaches a rule through a different path, and #6028 covers that.
	 */
	public function testEveryCoreUrlRuleDeclaresOnlyKeysTheRuleObjectReads()
	{
		$known = array();
		$reflection = new ReflectionClass('eUrlRule');

		foreach($reflection->getProperties() as $property)
		{
			$known[] = $property->getName();
		}

		$inspected = 0;
		$unread = array();

		foreach(glob(e_CORE . 'url/*/*.php') as $file)
		{
			$module = basename(dirname($file));
			$class = 'core_' . $module . '_' . basename($file, '.php');

			require_once $file;
			self::assertTrue(class_exists($class), $file . ' declares ' . $class);

			$config = new $class();
			$definition = $config->config();

			foreach(varset($definition['rules'], array()) as $pattern => $rule)
			{
				$inspected++;

				if(!is_array($rule))
				{
					continue;
				}

				foreach(array_keys($rule) as $key)
				{
					if(is_int($key) || in_array($key, $known, true))
					{
						continue;
					}

					$unread[] = $module . '/' . basename($file) . " rule '" . $pattern . "' declares '" . $key . "'";
				}
			}
		}

		self::assertGreaterThan(10, $inspected, 'no core URL rules were inspected');
		self::assertSame(array(), $unread);
	}

}
