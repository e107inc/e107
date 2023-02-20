<?php


class PluginNewsTest extends \Codeception\Test\Unit
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

		$reflection = new ReflectionClass($news);
		$property   = $reflection->getProperty("subAction");
		$property->setAccessible(true);
		$property->setValue($news, current($rows)["news_id"]);

		$method = $reflection->getMethod("renderViewTemplate");
		$method->setAccessible(true);
		try
		{
			$method->invoke($news);
		}
		catch(ReflectionException $e)
		{
			$this->fail("ReflectionException: " . $e->getMessage());
		}

		$property = $reflection->getProperty("currentRow");
		$property->setAccessible(true);

		return $property->getValue($news);
	}
}