<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

/**
 * Depth-first order of an adjacency list, the shape a parent-id column gives a table.
 *
 * A node's sort key is the order and the id of every node on the path from its root down to
 * itself, each zero-padded to ten digits (a negative value keeps its sign and sorts first), so
 * sorting the keys as strings walks the tree depth first with siblings in order-column order
 * and a child's key extends its parent's. The depth counts the nodes on that path. A node whose
 * parent is 0, negative or not in the list is a root, and a parent chain that returns to
 * itself, a node naming itself included, is cut where it closes, so every list has an order.
 */
final class TreeOrder
{
	/**
	 * @param array $nodes id => array('parent' => int, 'order' => int)
	 * @return array id => array('sort' => string, 'depth' => int), in the input's order
	 */
	public static function positions(array $nodes)
	{
		$positions = array();
		$ordered = array();

		foreach($nodes as $id => $node)
		{
			$ordered[$id] = self::position($id, $nodes, $positions, array());
		}

		return $ordered;
	}

	/**
	 * @param int $id
	 * @param array $nodes
	 * @param array $positions
	 * @param array $path ids on the descent that reached $id, as keys
	 * @return array array('sort' => string, 'depth' => int)
	 */
	private static function position($id, array $nodes, array &$positions, array $path)
	{
		if(isset($positions[$id]))
		{
			return $positions[$id];
		}

		$parent = (int) $nodes[$id]['parent'];
		$own = sprintf('%010d%010d', $nodes[$id]['order'], $id);
		$path[$id] = true;

		if($parent <= 0 || !isset($nodes[$parent]) || isset($path[$parent]))
		{
			return $positions[$id] = array('sort' => $own, 'depth' => 1);
		}

		$above = self::position($parent, $nodes, $positions, $path);

		return $positions[$id] = array('sort' => $above['sort'].$own, 'depth' => $above['depth'] + 1);
	}
}
