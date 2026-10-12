<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\User;

class AccountSessionsTest extends \Test\Unit
{
	const ACCOUNT = 7101;
	const OTHER = 7102;
	const LIFETIME = 3600;
	const IP = '203.0.113.5';
	const AGENT = 'Mozilla/5.0 (X11; Linux x86_64) Firefox/131.0';

	protected function _before()
	{
		\Test\CoreTable::create(AccountSessions::TABLE);
	}

	protected function _after()
	{
		\e107::getDb()->execute('DROP TEMPORARY TABLE IF EXISTS `'.MPREFIX.AccountSessions::TABLE.'`');
		\Test\CoreTable::drop(AccountSessions::TABLE);
	}

	/**
	 * @param string $ip
	 * @param string $agent
	 * @return AccountSessions
	 */
	private function sessions($ip = self::IP, $agent = self::AGENT)
	{
		return new AccountSessions(\e107::getDb(), self::LIFETIME, $ip, $agent);
	}

	/**
	 * @param string $handle
	 * @return int
	 */
	private function lastSeen($handle)
	{
		return (int) \e107::getDb()->createQueryBuilder()->select('user_session_seen')->from(AccountSessions::TABLE)
			->where('user_session_handle', $handle)->fetchOne();
	}

	/**
	 * @param string $handle
	 * @param int $seen
	 */
	private function seenAt($handle, $seen)
	{
		\e107::getDb()->createQueryBuilder()->update(AccountSessions::TABLE)
			->set('user_session_seen', $seen)->where('user_session_handle', $handle)->execute();
	}

	/**
	 * Shadows the table with one a statement about sessions fails against, as on a site whose database update has not created it yet.
	 */
	private function breakTheTable()
	{
		\e107::getDb()->execute('CREATE TEMPORARY TABLE `'.MPREFIX.AccountSessions::TABLE.'` (unrelated INT NOT NULL)');
	}

	/**
	 * @param AccountSession[] $sessions
	 * @return string[]
	 */
	private static function handles(array $sessions)
	{
		return array_map(function (AccountSession $session) { return $session->handle(); }, $sessions);
	}

	public function testABegunSessionIsOneOfTheAccountsSessions()
	{
		$before = time();
		$handle = $this->sessions()->begin(self::ACCOUNT);

		self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $handle);

		$sessions = $this->sessions()->sessionsOf(self::ACCOUNT);
		self::assertCount(1, $sessions);
		self::assertSame($handle, $sessions[0]->handle());
		self::assertSame(self::ACCOUNT, $sessions[0]->accountId());
		self::assertGreaterThanOrEqual($before, $sessions[0]->startedAt());
		self::assertSame($sessions[0]->startedAt(), $sessions[0]->lastSeenAt());
		self::assertSame(self::IP, $sessions[0]->ip());
		self::assertSame(self::AGENT, $sessions[0]->userAgent());
		self::assertSame(array(), $this->sessions()->sessionsOf(self::OTHER));
	}

	public function testAnAdoptedSessionHasNoKnownStart()
	{
		$handle = $this->sessions()->adopt(self::ACCOUNT);

		$sessions = $this->sessions()->sessionsOf(self::ACCOUNT);
		self::assertSame(array($handle), self::handles($sessions));
		self::assertSame(0, $sessions[0]->startedAt());
		self::assertGreaterThanOrEqual(time() - 1, $sessions[0]->lastSeenAt());
	}

	public function testEachSignInIsItsOwnSessionTheMostRecentlySeenFirst()
	{
		$first = $this->sessions()->begin(self::ACCOUNT);
		$second = $this->sessions()->begin(self::ACCOUNT);
		$this->seenAt($first, time() - 60);

		self::assertNotSame($first, $second);
		self::assertSame(array($second, $first), self::handles($this->sessions()->sessionsOf(self::ACCOUNT)));
	}

	public function testAnAgentLongerThanTheColumnIsCut()
	{
		$this->sessions(self::IP, str_repeat('a', 300))->begin(self::ACCOUNT);

		self::assertSame(255, strlen($this->sessions()->sessionsOf(self::ACCOUNT)[0]->userAgent()));
	}

	public function testALiveSessionIsConfirmedAndAnEndedOneIsNot()
	{
		$handle = $this->sessions()->begin(self::ACCOUNT);

		self::assertTrue($this->sessions()->confirm(self::ACCOUNT, $handle));
		self::assertTrue($this->sessions()->end(self::ACCOUNT, $handle));
		self::assertFalse($this->sessions()->confirm(self::ACCOUNT, $handle));
	}

	public function testAHandleIsConfirmedOnlyForItsOwnAccount()
	{
		$handle = $this->sessions()->begin(self::ACCOUNT);

		self::assertFalse($this->sessions()->confirm(self::OTHER, $handle));
	}

	public function testConfirmingNotesTheSessionWasSeenOncePerInterval()
	{
		$handle = $this->sessions()->begin(self::ACCOUNT);
		$recently = time() - AccountSessions::SEEN_INTERVAL + 30;
		$this->seenAt($handle, $recently);

		$this->sessions('203.0.113.9')->confirm(self::ACCOUNT, $handle);
		self::assertSame($recently, $this->lastSeen($handle));

		$this->seenAt($handle, time() - AccountSessions::SEEN_INTERVAL - 1);
		$this->sessions('203.0.113.9')->confirm(self::ACCOUNT, $handle);
		self::assertGreaterThanOrEqual(time() - 1, $this->lastSeen($handle));
		self::assertSame('203.0.113.9', $this->sessions()->sessionsOf(self::ACCOUNT)[0]->ip());
	}

	public function testASessionIsConfirmedWhenItsRecordCannotBeLookedUp()
	{
		$this->breakTheTable();

		self::assertTrue($this->sessions()->confirm(self::ACCOUNT, str_repeat('a', 32)));
	}

	public function testNoHandleIsGivenWhenTheRecordCannotBeStored()
	{
		$this->breakTheTable();

		self::assertSame('', $this->sessions()->begin(self::ACCOUNT));
		self::assertSame('', $this->sessions()->adopt(self::ACCOUNT));
	}

	public function testEndingNeedsTheHandlesOwnAccount()
	{
		$handle = $this->sessions()->begin(self::ACCOUNT);

		self::assertFalse($this->sessions()->end(self::OTHER, $handle));
		self::assertTrue($this->sessions()->confirm(self::ACCOUNT, $handle));
	}

	public function testEndingTheOthersKeepsTheNamedSessionAndOtherAccountsAndSaysWhatItEnded()
	{
		$kept = $this->sessions()->begin(self::ACCOUNT);
		$ended = array($this->sessions('203.0.113.7')->begin(self::ACCOUNT), $this->sessions('203.0.113.8')->begin(self::ACCOUNT));
		$theirs = $this->sessions()->begin(self::OTHER);

		$reported = $this->sessions()->endOthers(self::ACCOUNT, $kept);
		$handles = self::handles($reported);
		$addresses = array_map(function (AccountSession $session) { return $session->ip(); }, $reported);
		sort($ended);
		sort($handles);
		sort($addresses);

		self::assertSame($ended, $handles);
		self::assertSame(array('203.0.113.7', '203.0.113.8'), $addresses);
		self::assertSame(array($kept), self::handles($this->sessions()->sessionsOf(self::ACCOUNT)));
		self::assertSame(array($theirs), self::handles($this->sessions()->sessionsOf(self::OTHER)));
	}

	public function testEndingTheOthersOfNoSessionEndsThemAll()
	{
		$this->sessions()->begin(self::ACCOUNT);
		$this->sessions()->begin(self::ACCOUNT);

		self::assertCount(2, $this->sessions()->endOthers(self::ACCOUNT, ''));
		self::assertSame(array(), $this->sessions()->sessionsOf(self::ACCOUNT));
		self::assertSame(array(), $this->sessions()->endOthers(self::ACCOUNT, ''));
	}

	public function testASessionIdleForLongerThanASessionLivesIsNoLongerListedAndIsCollected()
	{
		$live = $this->sessions()->begin(self::ACCOUNT);
		$idle = $this->sessions()->begin(self::ACCOUNT);
		$this->seenAt($live, time() - self::LIFETIME);
		$this->seenAt($idle, time() - self::LIFETIME - AccountSessions::SEEN_INTERVAL - 1);

		self::assertSame(array($live), self::handles($this->sessions()->sessionsOf(self::ACCOUNT)));
		self::assertGreaterThanOrEqual(1, $this->sessions()->collect());
		self::assertFalse($this->sessions()->confirm(self::ACCOUNT, $idle));
		self::assertTrue($this->sessions()->confirm(self::ACCOUNT, $live));
	}
}
