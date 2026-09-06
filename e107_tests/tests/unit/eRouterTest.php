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
	 * user.php reads its query string positionally as from.records.order, so a
	 * member-list URL that carries only the offset used to leave the record
	 * count at zero and the listing came back as LIMIT 0. It also hands the
	 * paging bar one URL carrying a token, so the token has to come back intact.
	 */
	public function testLegacyMemberListUrlCarriesEveryPagingComponent()
	{
		require_once e_CORE . 'url/user/url.php';
		$config = new core_user_url();

		self::assertSame('user.php?40.20.DESC', $config->create(array('profile', 'list'), array('page' => 40)));
		self::assertSame('user.php?40.5.ASC', $config->create(array('profile', 'list'), array('page' => 40, 'records' => 5, 'order' => 'ASC')));

		self::assertSame('user.php?--FROM--.20.DESC', $config->create(array('profile', 'list'), array('page' => '--FROM--')));
		self::assertSame('user.php', $config->create(array('profile', 'list'), array()));
	}

	/**
	 * A top replier whose account has gone carries a null id and a null name.
	 */
	public function testAProfileUrlForNobodyFallsBackToTheMemberList()
	{
		require_once e_CORE . 'url/user/url.php';
		$config = new core_user_url();

		$deleted = array('user_id' => null, 'user_name' => null, 'user_forums' => 9, 'percentage' => 9);

		self::assertSame('user.php', $config->create(array('profile', 'view'), $deleted));
		self::assertSame('user.php', $config->create(array('profile', 'edit'), $deleted));
		self::assertSame('user.php?id.7', $config->create(array('profile', 'view'), array('user_id' => 7, 'user_name' => 'replier')));
	}

	/**
	 * The SEF rule reaches user.php through the same positional query, built by
	 * {@see e_parse::simpleParse()} from the request parameters the rule allows.
	 */
	public function testSefMemberListRuleBuildsEveryPagingComponent()
	{
		require_once e_CORE . 'url/user/rewrite_url.php';
		$config = new core_user_rewrite_url();
		$rules = $config->config();
		$template = $rules['rules']['list']['legacyQuery'];
		$tp = e107::getParser();

		self::assertSame('40.5.ASC', $tp->simpleParse($template, new e_vars(array('page' => 40, 'records' => 5, 'order' => 'ASC')), '0'));
		self::assertSame('0.0.0', $tp->simpleParse($template, new e_vars(array()), '0'));
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
