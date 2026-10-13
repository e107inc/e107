<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 */

class news_classTest extends \Codeception\Test\Unit
{
	/** @var news */
	protected $news;

	protected function _before()
	{
		try
		{
			$this->news = $this->make('news');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Regression for issue #5649: render_newsgrid() must tolerate an array
	 * caption that has no key matching e_LANGUAGE. Before the fix the array
	 * was passed straight to defined(), which raises TypeError on PHP 8+:
	 *   "defined(): Argument #1 ($constant_name) must be of type string,
	 *    array given".
	 */
	public function testRenderNewsgridDoesNotTypeErrorOnArrayCaptionMissingLanguageKey()
	{
		$parm = array(
			'caption' => array(
				// Intentionally omit e_LANGUAGE so the first array→string
				// resolution does not fire and the array reaches defined().
				'NotARealLanguage' => 'LAN_NEWSLATEST_MENU_TITLE',
			),
			'count' => 0,
		);

		// Defeat any cached result so we exercise the code path under test.
		$cacheKey = 'nq_news_grid_menu_'.md5(serialize($parm));
		e107::getCache()->clear($cacheKey);

		try
		{
			$this->news->render_newsgrid($parm);
		}
		catch (\TypeError $e)
		{
			if (strpos($e->getMessage(), 'defined()') !== false)
			{
				$this->fail('render_newsgrid() leaked an array into defined(): '.$e->getMessage());
			}
			throw $e;
		}

		self::assertTrue(true, 'render_newsgrid() returned without leaking an array into defined()');
	}

	public function testLoadJoinRefusesAnOrderByThatIsNotAColumnList()
	{
		$tree = new e_news_tree();
		$tree->loadJoin(0, true, array(
			'db_order' => 'n.news_datestamp DESC,(SELECT SLEEP(5))',
			'db_limit' => '0,10 PROCEDURE ANALYSE(1,1)',
		));

		$query = (string) $tree->getParam('db_query');

		$this->assertStringNotContainsString('SLEEP', $query);
		$this->assertStringNotContainsString('PROCEDURE', $query);
		$this->assertStringContainsString('ORDER BY `n`.`news_datestamp` DESC LIMIT 0,10', $query);
	}

	public function testLoadJoinKeepsAnOrdinaryOrderAndLimit()
	{
		$tree = new e_news_tree();
		$tree->loadJoin(0, true, array('db_order' => 'n.news_title ASC, n.news_id desc', 'db_limit' => '5, 20'));

		$this->assertStringContainsString('ORDER BY `n`.`news_title` ASC, `n`.`news_id` DESC LIMIT 5,20', (string) $tree->getParam('db_query'));
	}
}
