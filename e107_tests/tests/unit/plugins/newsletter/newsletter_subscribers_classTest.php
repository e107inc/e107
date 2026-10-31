<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 * @see https://github.com/e107inc/e107/issues/6873
 */
class newsletter_subscribers_classTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		require_once(e_PLUGIN.'newsletter/handlers/newsletter_subscribers_class.php');
	}

	public function testAStoredListReadsAsItsIdsInOrder()
	{
		$subscribers = new newsletter_subscribers(chr(1).'12'.chr(1).'3'.chr(1).'15');

		self::assertSame(array(12, 3, 15), $subscribers->ids());
	}

	public function testEntriesThatAreNoUserIdAndRepeatsAreDropped()
	{
		$subscribers = new newsletter_subscribers('7'.chr(1).chr(1).'0'.chr(1).'junk'.chr(1).' 9 '.chr(1).'7'.chr(1).'-4'.chr(1));

		self::assertSame(array(7, 9), $subscribers->ids());
	}

	public function testAnEmptyColumnHoldsNobody()
	{
		$subscribers = new newsletter_subscribers('');

		self::assertSame(array(), $subscribers->ids());
		self::assertSame('', $subscribers->toStoredString());
	}

	public function testAddingAppendsOnceAndOnlyAPositiveId()
	{
		$subscribers = new newsletter_subscribers(chr(1).'3');

		$subscribers->add(12);
		$subscribers->add('12');
		$subscribers->add(3);
		$subscribers->add(0);

		self::assertSame(array(3, 12), $subscribers->ids());
	}

	public function testRemovingAnIdLeavesTheIdsItIsAPrefixOf()
	{
		$subscribers = new newsletter_subscribers(chr(1).'3'.chr(1).'1'.chr(1).'12'.chr(1).'15');

		$subscribers->remove(1);

		self::assertSame(array(3, 12, 15), $subscribers->ids());
		self::assertFalse($subscribers->has(1));
		self::assertTrue($subscribers->has('12'));
	}

	public function testRemovingAnIdNotInTheListChangesNothing()
	{
		$subscribers = new newsletter_subscribers(chr(1).'3'.chr(1).'12');

		$subscribers->remove(4);

		self::assertSame(array(3, 12), $subscribers->ids());
	}

	public function testTheStoredStringPutsTheSeparatorBeforeEachId()
	{
		$subscribers = new newsletter_subscribers('3'.chr(1).'12'.chr(1).'12');

		self::assertSame(chr(1).'3'.chr(1).'12', $subscribers->toStoredString());
	}

	public function testRemovingTheLastSubscriberStoresAnEmptyColumn()
	{
		$subscribers = new newsletter_subscribers(chr(1).'5');

		$subscribers->remove(5);

		self::assertSame('', $subscribers->toStoredString());
	}
}
