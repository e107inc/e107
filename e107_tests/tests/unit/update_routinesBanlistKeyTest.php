<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The 2.x update's step that lets the ban list hold one entry per address and type.
 *
 * @see https://github.com/e107inc/e107/issues/6771
 */
class update_routinesBanlistKeyTest extends \Codeception\Test\Unit
{
	use \Test\BanlistRows;

	const REASON = 'update_routinesBanlistKeyTest';
	const ADDRESS = '0000:0000:0000:0000:0000:ffff:c000:0263';

	/** @var callable puts the banlist_ip key back as this test found it */
	private $restoreKey;

	protected function _before()
	{
		require_once(e_ADMIN . 'update_routines.php');

		$this->restoreKey = \Test\BanlistAddressKey::set(false);
		update_20x_to_latest('do');
		$this->assertTrue(update_20x_to_latest('check'), 'The fixture must have nothing else pending, or the checks below prove nothing.');
		\Test\BanlistAddressKey::set(false);
	}

	protected function _after()
	{
		e107::getDb()->delete('banlist', "banlist_reason = '".self::REASON."'");
		call_user_func($this->restoreKey);
		e107::getIPHandler()->regenerateFiles();
	}

	private function runTheUpdate()
	{
		$this->assertFalse(update_20x_to_latest('check'), 'update_needed() reports by returning false');

		update_20x_to_latest('do');

		$this->assertTrue(update_20x_to_latest('check'), 'Once it has run, the update must not stay pending.');
		$this->assertTrue(\Test\BanlistAddressKey::isUnique());
	}

	public function testTheUpdateIsDueUntilTheKeyHoldsOneEntryPerAddressAndType()
	{
		$this->runTheUpdate();
	}

	public function testTheUpdateAddsTheKeyWhereThereIsNoneLeft()
	{
		\Test\BanlistAddressKey::set(null);
		$first = $this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);

		$this->runTheUpdate();

		$this->assertSame(array($first), $this->rowsThisTestWrote());
	}

	public function testCopiesOfOneFloodBanKeepTheFirst()
	{
		$first = $this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);

		$this->runTheUpdate();

		$this->assertSame(array($first), $this->rowsThisTestWrote());
	}

	public function testALiveBanOutlastsAnEarlierCopyThatHasExpired()
	{
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_MANUAL, time() - 60);
		$live = $this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_MANUAL, time() + 3600);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_MANUAL);

		$this->runTheUpdate();

		$this->assertSame(array($live), $this->rowsThisTestWrote(), 'The row enforced today is the one that stays, so the update lifts no ban.');
	}

	public function testCopiesThatHaveAllExpiredKeepTheFirst()
	{
		$first = $this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_LOGINS, time() - 120);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_LOGINS, time() - 60);

		$this->runTheUpdate();

		$this->assertSame(array($first), $this->rowsThisTestWrote());
	}

	public function testEntriesOfDifferentTypesForOneAddressAreAllKept()
	{
		$ids = array(
			$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD),
			$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_WHITELIST),
			$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_TEMPORARY),
		);

		$this->runTheUpdate();

		$this->assertSame($ids, $this->rowsThisTestWrote(), 'A ban beside a whitelist entry, or beside a row an old import parked, is a design the key keeps.');
	}

	public function testSpellingsTheKeyComparesAsOneAreOneEntry()
	{
		$first = $this->haveRow('Banlist-Key-Test@Example.com', eIPHandler::BAN_TYPE_MANUAL);
		$this->haveRow('banlist-key-test@example.com', eIPHandler::BAN_TYPE_MANUAL);

		$this->runTheUpdate();

		$this->assertSame(array($first), $this->rowsThisTestWrote(), 'Grouping must follow the column\'s comparison, or the key cannot be added over what it left.');
	}

	public function testAfterTheUpdateASecondEntryOfOneTypeIsRefused()
	{
		$this->runTheUpdate();

		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);

		$this->assertFalse($this->insertRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD));
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_LOGINS);
	}

	public function testRunningTheUpdateAgainChangesNothing()
	{
		$first = $this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);
		$this->haveRow(self::ADDRESS, eIPHandler::BAN_TYPE_FLOOD);
		$this->runTheUpdate();

		update_20x_to_latest('do');

		$this->assertSame(array($first), $this->rowsThisTestWrote());
		$this->assertTrue(\Test\BanlistAddressKey::isUnique());
	}
}
