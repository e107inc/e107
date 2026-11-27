<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class applicationRouterVarTemplatesTest extends \Codeception\Test\Unit
{
	use \Test\CorePrefs;

	public function testTheNewsSefCategoryRuleTakesTheModulesTestItTemplate()
	{
		$rules = $this->rulesThroughUrlConfig('news', 'core/sef');

		self::assertStringContainsString('(?P<id>[\d]+)', $rules['Category/<id:{testIt}>/<name:{sefsecure}>']->pattern);
	}

	public function testTheGalleryRewriteRuleTakesThePluginsGalleryCatTemplate()
	{
		$rules = $this->rulesThroughUrlConfig('gallery', 'plugin/rewrite');

		self::assertStringContainsString('(?P<cat>[\w\pL\s\-+.,]+)', $rules['<cat:{galleryCat}>']->pattern);
	}

	public function testAModuleTemplateNamedLikeACoreTemplateLeavesTheCoreOneInForce()
	{
		$rules = $this->rulesFromModuleConfig(
			array('varTemplates' => array('number' => '[a-z]+')),
			array('<id:{number}>' => array('demo/view'))
		);

		self::assertStringContainsString('(?P<id>[\d]+)', $rules['<id:{number}>']->pattern);
	}

	public function testARuleDeclaringItsOwnTemplatesReplacesTheModulesWhole()
	{
		$rules = $this->rulesFromModuleConfig(
			array('varTemplates' => array('code' => '[a-z]+', 'other' => '[0-9]+')),
			array('<id:{code}>/<x:{other}>' => array('demo/view', 'varTemplates' => array('code' => '[A-Z]{2}')))
		);
		$pattern = $rules['<id:{code}>/<x:{other}>']->pattern;

		self::assertStringContainsString('(?P<id>[A-Z]{2})', $pattern);
		self::assertStringContainsString('(?P<x>[^\/]+)', $pattern);
	}

	/**
	 * @param string $module
	 * @param string $location url_config location, e.g. 'core/sef'
	 * @return eUrlRule[] the module's rules as a router built from the URL config cache compiles them
	 */
	private function rulesThroughUrlConfig($module, $location)
	{
		$restore = $this->withUrlConfig(array($module => $location));

		try
		{
			$router = new eRouter();

			return $router->getRules($module);
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * @param array $config the config block of a URL module no shipped profile declares
	 * @param array $rules its rules, each an array
	 * @return eUrlRule[]
	 */
	private function rulesFromModuleConfig(array $config, array $rules)
	{
		$router = new eRouter();
		$globalConfig = new \e107\Reflection\ReflectionProperty('eRouter', '_globalConfig');
		$globalConfig->setValue($router, array('demo' => $config));
		$router->setRuleSets(array('demo' => $rules));

		return $router->getRules('demo');
	}
}
