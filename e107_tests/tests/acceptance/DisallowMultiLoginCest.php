<?php

/**
 * With disallowMultiLogin on, a login ends the account's other live sessions and notes it in the rolling log; an expired row is no session and earns no note.
 */
class DisallowMultiLoginCest
{
	const PASSWORD = 'multilogin-pass';

	/** Rolling log title of a login that ended the account's other sessions. */
	const DROPPED = 'LAN_ROLL_LOG_07';

	/** @var string[] */
	private $prefsWere = array();

	public function _before(AcceptanceTester $I)
	{
		foreach(array('disallowMultiLogin' => 1, 'roll_log_active' => 1) as $name => $value)
		{
			$this->prefsWere[$name] = $I->haveSitePref($name, $value);
		}
	}

	public function _after(AcceptanceTester $I)
	{
		foreach($this->prefsWere as $name => $was)
		{
			$I->haveSitePref($name, $was === '' ? null : $was);
		}
	}

	public function aLiveSessionOfTheAccountIsEndedAndNoted(AcceptanceTester $I)
	{
		$userId = $I->haveMember('multiloginlive', self::PASSWORD);
		$other = $this->haveSessionOf($I, $userId, time() + 600);
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('multiloginlive', self::PASSWORD);

		$I->dontSeeInDatabase('e107_session', array('session_id' => $other));
		$I->assertSame(1, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)));
	}

	public function anExpiredSessionOfTheAccountIsNotNotedAsEnded(AcceptanceTester $I)
	{
		$userId = $I->haveMember('multiloginexpired', self::PASSWORD);
		$this->haveSessionOf($I, $userId, time() - 60);
		$lastLog = $this->lastLogId($I);

		$I->loginAsMember('multiloginexpired', self::PASSWORD);

		$I->assertSame(0, $I->grabNumRecords('e107_dblog', array('dblog_id >' => $lastLog, 'dblog_title' => self::DROPPED)),
			'an expired row is left to the collector, not reported as a session the login ended');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @param int $expires
	 * @return string the row's session_id
	 */
	private function haveSessionOf(AcceptanceTester $I, $userId, $expires)
	{
		$id = 'sha256$'.hash('sha256', 'multilogin-elsewhere-'.$userId);

		$I->haveInDatabase('e107_session', array(
			'session_id'      => $id,
			'session_expires' => $expires,
			'session_user'    => $userId,
			'session_data'    => '',
		));

		return $id;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int
	 */
	private function lastLogId(AcceptanceTester $I)
	{
		$ids = $I->grabColumnFromDatabase('e107_dblog', 'dblog_id');

		return $ids ? (int) max($ids) : 0;
	}
}
