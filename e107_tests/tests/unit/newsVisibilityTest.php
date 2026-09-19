<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A news item set to "all but <class>" stores the negative class id, which the
 * news models matched with REGEXP e_CLASS_REGEXP and so hid from everybody.
 * The models hand their query to e_model with the predicate's values bound
 * through db_params. See issue #6282.
 */
class newsVisibilityTest extends \Test\Unit
{
	const PREFIX = 'uc_probe_';

	/** @var int a userclass the runtime user is not a member of */
	private $absentClass = 42;

	/** @var int[] news_id by probe name */
	private $ids = array();

	protected function _before()
	{
		require_once(e_HANDLER.'news_class.php');

		$this->assertNotContains((string) $this->absentClass, explode(',', USERCLASS_LIST), 'This test needs a userclass the runtime user is outside of.');

		$this->removeProbeRows();
		$this->ids['public'] = $this->addItem('public', 0);
		$this->ids['restricted'] = $this->addItem('restricted', $this->absentClass);
		$this->ids['inverted'] = $this->addItem('inverted', -$this->absentClass);
	}

	protected function _after()
	{
		$this->removeProbeRows();
	}

	public function testAnItemHiddenFromAnotherClassLoads()
	{
		$this->assertSame($this->ids['public'], $this->loadedId('public'));
		$this->assertSame($this->ids['inverted'], $this->loadedId('inverted'), 'An item hidden from one class is shown to everybody else.');
		$this->assertSame(0, $this->loadedId('restricted'), 'An item limited to a class the user is outside of stays hidden.');
	}

	public function testTheActiveTreeKeepsAnItemHiddenFromAnotherClass()
	{
		$tree = new e_news_tree();
		$tree->loadJoinActive(0, true, array('db_limit' => '0,50'));
		$titles = array_column($tree->toArray(), 'news_title');

		$this->assertContains(self::PREFIX.'public', $titles);
		$this->assertContains(self::PREFIX.'inverted', $titles);
		$this->assertNotContains(self::PREFIX.'restricted', $titles);
	}

	/**
	 * @param string $name
	 * @return int the news_id the item model loaded, 0 when it loaded nothing
	 */
	private function loadedId($name)
	{
		$item = new e_news_item();
		$item->load($this->ids[$name], true);

		return (int) $item->get('news_id');
	}

	/**
	 * @param string $name
	 * @param int $class
	 * @return int news_id
	 */
	private function addItem($name, $class)
	{
		return (int) e107::getDb()->insert('news', array(
			'news_title'       => self::PREFIX.$name,
			'news_sef'         => self::PREFIX.$name,
			'news_body'        => 'probe',
			'news_class'       => (string) $class,
			'news_category'    => 1,
			'news_author'      => 1,
			'news_datestamp'   => time() - 60,
			'news_start'       => 0,
			'news_end'         => 0,
			'news_render_type' => '0',
		));
	}

	private function removeProbeRows()
	{
		e107::getDb()->delete('news', "news_title LIKE '".self::PREFIX."%'");
	}
}
