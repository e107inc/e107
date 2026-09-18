<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Flood;

/**
 * The per-source ration behind the password reset form. Site-wide flood
 * protection would let one caller close that form for everybody, so the two
 * properties pinned here are that one source's attempt does not bar another's,
 * and that the site's own setting still turns the whole thing off.
 */
class SourceGateTest extends \Test\Unit
{
	const KIND = 'unitGate';
	const SOURCE = '203.0.113.5';
	const OTHER = '203.0.113.6';

	protected function _before()
	{
		require_once(e_HANDLER.'Flood/SourceGate.php');

		$this->forget();
	}

	protected function _after()
	{
		$this->forget();
	}

	private function forget()
	{
		\e107::getDb()->createQueryBuilder()->delete(SourceGate::TABLE)
			->where('tmp_ip', self::KIND)->execute();
	}

	/**
	 * @param int $timeout
	 * @param bool $enabled
	 * @return SourceGate
	 */
	private function gate($timeout = 60, $enabled = true)
	{
		return new SourceGate(\e107::getDb(), $enabled, $timeout);
	}

	public function testASourceIsOpenUntilItHasTried()
	{
		$gate = $this->gate();

		self::assertFalse($gate->isClosedTo(self::KIND, self::SOURCE));

		$gate->record(self::KIND, self::SOURCE);

		self::assertTrue($gate->isClosedTo(self::KIND, self::SOURCE));
	}

	public function testOneSourceDoesNotCloseTheGateForAnother()
	{
		$gate = $this->gate();
		$gate->record(self::KIND, self::SOURCE);

		self::assertTrue($gate->isClosedTo(self::KIND, self::SOURCE));
		self::assertFalse($gate->isClosedTo(self::KIND, self::OTHER), 'one caller must not ration everybody else');
		self::assertFalse($gate->isClosedTo('unitGateOther', self::SOURCE), 'a ration is per kind');
	}

	public function testTheGateOpensAgainOnceTheTimeoutHasPassed()
	{
		$gate = $this->gate(1);
		$gate->record(self::KIND, self::SOURCE);

		self::assertTrue($gate->isClosedTo(self::KIND, self::SOURCE));

		\e107::getDb()->createQueryBuilder()->update(SourceGate::TABLE)
			->set('tmp_time', time() - 1)
			->where('tmp_ip', self::KIND)->where('tmp_info', self::SOURCE)->execute();

		self::assertFalse($gate->isClosedTo(self::KIND, self::SOURCE), 'a lapsed attempt no longer bars the source');
	}

	public function testRecordingAgainReplacesTheEarlierAttempt()
	{
		$gate = $this->gate();
		$gate->record(self::KIND, self::SOURCE);
		$gate->record(self::KIND, self::SOURCE);

		$rows = \e107::getDb()->createQueryBuilder()->from(SourceGate::TABLE)
			->where('tmp_ip', self::KIND)->where('tmp_info', self::SOURCE)->count();

		self::assertSame(1, (int) $rows, 'a source keeps one row however often it tries');
	}

	public function testTheSiteSettingTurnsTheWholeThingOff()
	{
		$off = $this->gate(60, false);
		$off->record(self::KIND, self::SOURCE);

		self::assertFalse($off->isClosedTo(self::KIND, self::SOURCE));

		$rows = \e107::getDb()->createQueryBuilder()->from(SourceGate::TABLE)
			->where('tmp_ip', self::KIND)->count();

		self::assertSame(0, (int) $rows, 'a site with flood protection off records nothing');
	}

	public function testForgetOpensTheGate()
	{
		$gate = $this->gate();
		$gate->record(self::KIND, self::SOURCE);
		$gate->forget(self::KIND, self::SOURCE);

		self::assertFalse($gate->isClosedTo(self::KIND, self::SOURCE));
	}
}
