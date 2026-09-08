<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

/**
 * DB-less tests for {@see TreeOrder}: the depth-first key and depth of every node of an
 * adjacency list, including the lists a parent-id column can hold that no tree should.
 */
class TreeOrderTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER."Database/TreeOrder.php");
	}

	/**
	 * @return array id => array('parent' => int, 'order' => int)
	 */
	private function forest()
	{
		return array(
			2  => array('parent' => 1, 'order' => 2),
			11 => array('parent' => 10, 'order' => 1),
			1  => array('parent' => 0, 'order' => 1),
			4  => array('parent' => 3, 'order' => 1),
			10 => array('parent' => 0, 'order' => 1),
			3  => array('parent' => 1, 'order' => 1),
		);
	}

	/**
	 * @param array $positions
	 * @return int[] ids in key order
	 */
	private function walk(array $positions)
	{
		uasort($positions, function ($a, $b)
		{
			return strcmp($a['sort'], $b['sort']);
		});

		return array_keys($positions);
	}

	public function testKeysWalkTheForestDepthFirstWithSiblingsInOrder()
	{
		$positions = TreeOrder::positions($this->forest());

		$this->assertSame(array(1, 3, 4, 2, 10, 11), $this->walk($positions));
		$this->assertSame(array(2 => 2, 11 => 2, 1 => 1, 4 => 3, 10 => 1, 3 => 2), array_map(function ($position)
		{
			return $position['depth'];
		}, $positions));
	}

	public function testAChildKeyExtendsItsParentKey()
	{
		$positions = TreeOrder::positions($this->forest());

		$this->assertStringStartsWith($positions[1]['sort'], $positions[3]['sort']);
		$this->assertStringStartsWith($positions[3]['sort'], $positions[4]['sort']);
		$this->assertStringStartsWith($positions[10]['sort'], $positions[11]['sort']);
		$this->assertSame('00000000010000000001', $positions[1]['sort']);
	}

	/**
	 * Roots 1 and 10 share an order, so a key that appended the bare id would put 10's
	 * subtree between 1 and 1's children.
	 */
	public function testIdsOfDifferentLengthsDoNotInterleaveSubtrees()
	{
		$positions = TreeOrder::positions($this->forest());

		$this->assertSame(array(1, 3, 4, 2), array_slice($this->walk($positions), 0, 4));
	}

	public function testAMissingOrNegativeParentMakesARoot()
	{
		$positions = TreeOrder::positions(array(
			5 => array('parent' => 99, 'order' => 1),
			6 => array('parent' => -1, 'order' => 1),
			7 => array('parent' => 5, 'order' => 1),
		));

		$this->assertSame(1, $positions[5]['depth']);
		$this->assertSame(1, $positions[6]['depth']);
		$this->assertSame(2, $positions[7]['depth']);
	}

	public function testACycleIsCutWhereItCloses()
	{
		$positions = TreeOrder::positions(array(
			1 => array('parent' => 2, 'order' => 1),
			2 => array('parent' => 3, 'order' => 1),
			3 => array('parent' => 1, 'order' => 1),
			4 => array('parent' => 3, 'order' => 1),
		));

		$this->assertCount(4, $positions);
		$this->assertSame(array(3, 2, 1, 4), $this->walk($positions));
		$this->assertSame(array(1 => 3, 2 => 2, 3 => 1, 4 => 2), array_map(function ($position)
		{
			return $position['depth'];
		}, $positions));
	}

	public function testANodeNamingItselfAsParentIsARoot()
	{
		$positions = TreeOrder::positions(array(1 => array('parent' => 1, 'order' => 1)));

		$this->assertSame(array(1 => array('sort' => '00000000010000000001', 'depth' => 1)), $positions);
	}

	public function testAnEmptyListHasNoPositions()
	{
		$this->assertSame(array(), TreeOrder::positions(array()));
	}
}
