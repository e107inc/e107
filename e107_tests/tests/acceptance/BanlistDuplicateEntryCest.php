<?php

/**
 * Admin > Banlist refuses a second entry of one address and type, naming the entry in the way.
 */
class BanlistDuplicateEntryCest
{
	const CREATE = '/e107_admin/banlist.php?mode=main&action=create';
	const WHITELIST_CREATE = '/e107_admin/banlist.php?mode=white&action=create';
	const FORM = '#core-banlist-form';
	const ADDRESS = '198.18.71.71';
	const STORED = '0000:0000:0000:0000:0000:ffff:c612:4747';
	const HOST = '*.Banlist-Dup.example';
	const EMAIL = 'Banlist-Case@Example.com';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/** @var bool whether a test put back the plain key of a site that has not run the update */
	private $plainKey = false;

	public function _after(AcceptanceTester $I)
	{
		$dbh = $I->getDbModule()->_getDbh();
		$dbh->prepare('DELETE FROM e107_banlist WHERE banlist_ip IN (?, ?, ?)')->execute(array(self::STORED, self::HOST, self::EMAIL));

		if($this->plainKey)
		{
			$dbh->exec('ALTER TABLE e107_banlist DROP INDEX banlist_ip, ADD UNIQUE KEY banlist_ip (banlist_ip, banlist_bantype)');
			$this->plainKey = false;
		}
	}

	public function aSecondManualEntryForABannedAddressIsRefused(AcceptanceTester $I)
	{
		$id = $this->haveBan($I, -1, 'first');

		$I->amOnPage(self::CREATE);
		$I->submitForm(self::FORM, array('banlist_ip' => self::ADDRESS, 'banlist_reason' => 'second'), 'etrigger_submit');

		$I->see(self::ADDRESS.' is already listed with the type Manual (ID '.$id.')');
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => self::STORED));
	}

	public function aWhitelistEntryBesideABanIsStillTaken(AcceptanceTester $I)
	{
		$this->haveBan($I, -1, 'banned first');

		$I->amOnPage(self::WHITELIST_CREATE);
		$I->submitForm(self::FORM, array('banlist_ip' => self::ADDRESS, 'banlist_reason' => 'whitelisted after'), 'etrigger_submit');

		$I->dontSee('is already listed');
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => self::STORED, 'banlist_bantype' => 100));
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => self::STORED, 'banlist_bantype' => -1));
	}

	public function anEntrySavedWithItsOwnAddressIsNotRefused(AcceptanceTester $I)
	{
		$id = $this->haveBan($I, -1, 'before');

		$I->amOnPage('/e107_admin/banlist.php?mode=main&action=edit&id='.$id);
		$I->submitForm(self::FORM, array('banlist_ip' => self::ADDRESS, 'banlist_reason' => 'after'), 'etrigger_submit');

		$I->dontSee('is already listed');
		$I->seeInDatabase('e107_banlist', array('banlist_id' => $id, 'banlist_reason' => 'after'));
	}

	public function anEditGivingAnEntryTheTypeAnotherHoldsIsRefused(AcceptanceTester $I)
	{
		$manual = $this->haveBan($I, -1, 'manual');
		$flood = $this->haveBan($I, -2, 'flood');

		$I->amOnPage('/e107_admin/banlist.php?mode=main&action=edit&id='.$flood);
		$I->submitForm(self::FORM, array('banlist_ip' => self::ADDRESS, 'banlist_bantype' => -1, 'banlist_reason' => 'flood'), 'etrigger_submit');

		$I->see(self::ADDRESS.' is already listed with the type Manual (ID '.$manual.')');
		$I->seeInDatabase('e107_banlist', array('banlist_id' => $flood, 'banlist_bantype' => -2));
	}

	public function eitherCopyOfAnEntryASiteNotYetUpdatedHoldsTwiceCanBeEdited(AcceptanceTester $I)
	{
		$I->getDbModule()->_getDbh()->exec('ALTER TABLE e107_banlist DROP INDEX banlist_ip, ADD KEY banlist_ip (banlist_ip)');
		$this->plainKey = true;
		$first = $this->haveBan($I, -2, 'copy one', self::HOST);
		$second = $this->haveBan($I, -2, 'copy two', strtolower(self::HOST));

		foreach(array($first, $second) as $id)
		{
			$I->amOnPage('/e107_admin/banlist.php?mode=main&action=edit&id='.$id);
			$I->submitForm(self::FORM, array('banlist_ip' => self::HOST, 'banlist_bantype' => -2, 'banlist_reason' => 'edited '.$id), 'etrigger_submit');

			$I->dontSee('is already listed');
			$I->seeInDatabase('e107_banlist', array('banlist_id' => $id, 'banlist_reason' => 'edited '.$id));
		}
	}

	public function anEditThatOnlyChangesTheLetterCaseIsNotRefused(AcceptanceTester $I)
	{
		$id = $this->haveBan($I, -1, 'before', self::EMAIL);

		$I->amOnPage('/e107_admin/banlist.php?mode=main&action=edit&id='.$id);
		$I->submitForm(self::FORM, array('banlist_ip' => strtolower(self::EMAIL), 'banlist_reason' => 'after'), 'etrigger_submit');

		$I->dontSee('is already listed');
		$I->seeInDatabase('e107_banlist', array('banlist_id' => $id, 'banlist_reason' => 'after'));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $type
	 * @param string $reason
	 * @param string $ip as stored
	 * @return int banlist_id
	 */
	private function haveBan(AcceptanceTester $I, $type, $reason, $ip = self::STORED)
	{
		return (int) $I->haveInDatabase('e107_banlist', array(
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 1,
			'banlist_reason'     => $reason,
			'banlist_notes'      => '',
		));
	}
}
