<?php

namespace Test;

/**
 * User classes added for the length of one test; keep this in PHP 5.6 syntax.
 */
trait UserClasses
{
	/**
	 * Adds user classes for one test and re-reads the shared class tree so they count; call what it returns in a finally block.
	 *
	 * @param array[] $classes userclass_classes rows, each with its userclass_id
	 * @return callable deletes the classes and puts back the class tree they were added to
	 */
	protected function withUserClasses(array $classes)
	{
		$userClass = \e107::getUserClass();
		$tree = $userClass->class_tree;
		$ids = array_map('intval', array_column($classes, 'userclass_id'));

		$restore = static function () use ($userClass, $tree, $ids)
		{
			\e107::getDb()->delete('userclass_classes', 'userclass_id IN (' . implode(',', $ids) . ')');
			$userClass->clearCache();
			$userClass->class_tree = $tree;
		};

		$added = false;

		try
		{
			foreach($classes as $class)
			{
				self::assertNotFalse(\e107::getDb()->insert('userclass_classes', $class));
			}

			$userClass->clearCache();
			$userClass->readTree(true);
			$added = true;
		}
		finally
		{
			if(!$added)
			{
				$restore();
			}
		}

		return $restore;
	}
}
