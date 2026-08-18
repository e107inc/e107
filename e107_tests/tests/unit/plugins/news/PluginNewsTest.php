<?php

use e107\Reflection\ReflectionMethod;
use e107\Reflection\ReflectionProperty;

class PluginNewsTest extends \Test\Unit
{

	/**
	 * @see https://github.com/e107inc/e107/issues/4982
	 */
	public function testCategoryListDefaultSef()
	{
		include_once "e107_core/url/news/url.php";
		$eUrlConfig = new core_news_url();
		$output     = $eUrlConfig->create(
			["list", "category"],
			["category_id" => 579773, "name" => "regression"],
			["full" => false, "amp" => "&amp;", "equal" => "=", "encode" => true, "lan" => null]);
		$this->assertEquals("news.php?list.579773.0", $output);
	}

	/**
	 * @see https://github.com/e107inc/e107/issues/5955
	 */
	public function testNewsGridMenuOffersTheFeaturedParmTheRendererReads()
	{
		$fields = $this->newsGridMenuFields();

		$this->assertArrayHasKey(
			"featured",
			$fields,
			"e_news_tree::render() reads \$parms['featured'], and menu_class::updateParms() "
			. "stores each posted value under its field name verbatim, so the News Grid menu "
			. "field has to be named 'featured' for the setting to reach the renderer."
		);
		$this->assertArrayNotHasKey(
			"feature",
			$fields,
			"Nothing reads \$parms['feature']; a field of that name is saved and never used."
		);

		$this->assertArrayHasKey("count", $fields);
		$this->assertArrayHasKey("titleLimit", $fields);
		$this->assertArrayHasKey("summaryLimit", $fields);
	}

	/**
	 * @see https://github.com/e107inc/e107/issues/5955
	 */
	public function testNewsGridMenuFeaturedCountSurvivesToTheRenderedGrid()
	{
		$fields = $this->newsGridMenuFields();
		$posted = array("count" => 3, "layout" => "col-md-4");

		$plain = $this->renderNewsGrid(array_intersect_key($posted, $fields));
		$featured = $this->renderNewsGrid(
			array_intersect_key(array_merge($posted, array("featured" => 1)), $fields)
		);

		$this->assertStringNotContainsString("row featured", $plain);
		$this->assertStringContainsString(
			"row featured",
			$featured,
			"menu_class::updateParms() keeps only the keys config('news_grid') declares, so a "
			. "featured count typed into the Menu Manager reaches e_news_tree::render() only "
			. "while the field carries the name the renderer reads."
		);
	}

	public function testNewsFrontCategoryUrl()
	{
		$payload = $this->simulateShowNewsItem();
		$this->testNewsFrontCategoryUrlSef(
			$payload,
			"core",
			"/news.php?list.{$payload['category_id']}.0"
		);
	}

	public function testNewsFrontCategoryUrlCoreSef()
	{
		$payload = $this->simulateShowNewsItem();
		$this->testNewsFrontCategoryUrlSef(
			$payload,
			"core/sef",
			"/news/category/{$payload['category_id']}/{$payload['category_sef']}"
		);
	}

	public function testNewsFrontCategoryUrlCoreSefFull()
	{
		$payload = $this->simulateShowNewsItem();
		$this->testNewsFrontCategoryUrlSef(
			$payload,
			"core/sef_full",
			"/news/category/{$payload['category_sef']}"
		);
	}

	public function testNewsFrontCategoryUrlCoreSefNoid()
	{
		$payload = $this->simulateShowNewsItem();
		$this->testNewsFrontCategoryUrlSef(
			$payload,
			"core/sef_noid",
			"/news/category/{$payload['category_sef']}.html"
		);
	}

	/**
	 * @param $payload  array
	 * @param $sefType  string
	 * @param $expected string
	 * @return void
	 */
	private function testNewsFrontCategoryUrlSef($payload, $sefType, $expected)
	{
		$urlConfig         = $oldUrlConfig = e107::getConfig()->get('url_config', array());
		$urlConfig["news"] = $sefType;
		e107::getConfig()->set('url_config', $urlConfig);
		$router = new eRouter();
		$router->loadConfig(true);
		$oldRouter = e107::getUrl()->front()->getRouter();
		try
		{
			e107::getUrl()->front()->setRouter($router);
			$output = e107::getUrl()->create('news/list/category', $payload);
			$this->assertEquals($expected, $output);
		}
		finally
		{
			e107::getUrl()->front()->setRouter($oldRouter);
			e107::getConfig()->set('url_config', $oldUrlConfig);
		}
	}

	/**
	 * @return array
	 */
	private function newsGridMenuFields()
	{
		require_once e_PLUGIN . "news/e_menu.php";
		$menu = new news_menu();

		return $menu->config("news_grid");
	}

	/**
	 * @param $parms array
	 * @return string
	 */
	private function renderNewsGrid($parms)
	{
		e107::getCache()->clear("nq_news_grid_menu_" . md5(serialize($parms)));

		return $this->make("news")->render_newsgrid($parms);
	}

	/**
	 * @return array
	 */
	private function simulateShowNewsItem()
	{
		$rowCount = e107::getDb()->select("news", "news_id");
		$this->assertGreaterThanOrEqual(
			1,
			$rowCount,
			"This integration test requires at least one news item in the database"
		);
		$rows = e107::getDb()->rows();
		include_once e_PLUGIN . "news/news.php";
		$news = new news_front();

		$property = new ReflectionProperty($news, "subAction");
		$property->setValue($news, current($rows)["news_id"]);

		$method = new ReflectionMethod($news, "renderViewTemplate");
		try
		{
			$method->invoke($news);
		}
		catch(ReflectionException $e)
		{
			$this->fail("ReflectionException: " . $e->getMessage());
		}

		$property = new ReflectionProperty($news, "currentRow");

		return $property->getValue($news);
	}
}