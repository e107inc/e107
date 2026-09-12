<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A featurebox item set to "all but <class>" stores the negative class id,
 * which the category tree's own SQL_CALC_FOUND_ROWS query matched with an
 * IN () of the visitor's classes, so the item vanished for everybody. The tree now hands the predicate's values to the
 * model through db_params. See issue #6282.
 */
class featureboxVisibilityTest extends \Test\Unit
{
	const PREFIX = 'uc_probe_';

	/** @var int a userclass the runtime user is not a member of */
	private $absentClass = 42;

	/** @var int the category the probe items hang under */
	private $categoryId;

	protected function _before()
	{
		require_once(e_PLUGIN.'featurebox/includes/tree.php');

		$this->assertNotContains((string) $this->absentClass, explode(',', USERCLASS_LIST), 'This test needs a userclass the runtime user is outside of.');

		$this->removeProbeRows();
		$this->categoryId = (int) e107::getDb()->insert('featurebox_category', array(
			'fb_category_title'    => self::PREFIX.'category',
			'fb_category_icon'     => '',
			'fb_category_template' => 'default',
			'fb_category_random'   => 0,
			'fb_category_class'    => 0,
			'fb_category_limit'    => 0,
			'fb_category_parms'    => '',
		));
		$this->addItem('public', 0);
		$this->addItem('restricted', $this->absentClass);
		$this->addItem('inverted', -$this->absentClass);
	}

	protected function _after()
	{
		$this->removeProbeRows();
	}

	public function testTheCategoryTreeKeepsAnItemHiddenFromAnotherClass()
	{
		$tree = new plugin_featurebox_tree();
		$tree->load($this->categoryId, true);
		$titles = array_column($tree->toArray(), 'fb_title');

		$this->assertContains(self::PREFIX.'public', $titles);
		$this->assertContains(self::PREFIX.'inverted', $titles, 'An item hidden from one class is shown to everybody else.');
		$this->assertNotContains(self::PREFIX.'restricted', $titles, 'An item limited to a class the user is outside of stays hidden.');
	}

	/**
	 * @param string $name
	 * @param int $class
	 * @return int fb_id
	 */
	private function addItem($name, $class)
	{
		return (int) e107::getDb()->insert('featurebox', array(
			'fb_title'      => self::PREFIX.$name,
			'fb_text'       => 'probe',
			'fb_mode'       => 0,
			'fb_class'      => $class,
			'fb_rendertype' => 0,
			'fb_template'   => 'default',
			'fb_order'      => 1,
			'fb_image'      => '',
			'fb_imageurl'   => '',
			'fb_category'   => $this->categoryId,
		));
	}

	private function removeProbeRows()
	{
		$sql = e107::getDb();
		$sql->delete('featurebox', "fb_title LIKE '".self::PREFIX."%'");
		$sql->delete('featurebox_category', "fb_category_title LIKE '".self::PREFIX."%'");
	}
}
