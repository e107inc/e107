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
 * The ration behind the activation email resend: per source and kind, lapsing, one row per source, and silent when switched off.
 */
class SourceGateTest extends \Codeception\Test\Unit
{
	const KIND = 'unitGate';
	const SOURCE = '203.0.113.5';
	const OTHER = '203.0.113.6';

	const OF_KIND = "tmp_ip = 'unitGate'";
	const OF_SOURCE = "tmp_ip = 'unitGate' AND tmp_info = '203.0.113.5'";

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
		\e107::getDb()->delete(SourceGate::TABLE, self::OF_KIND);
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

		\e107::getDb()->update(SourceGate::TABLE, array(
			'data'  => array('tmp_time' => time() - 1),
			'WHERE' => self::OF_SOURCE,
		));

		self::assertFalse($gate->isClosedTo(self::KIND, self::SOURCE), 'a lapsed attempt no longer bars the source');
	}

	public function testRecordingAgainReplacesTheEarlierAttempt()
	{
		$gate = $this->gate();
		$gate->record(self::KIND, self::SOURCE);
		$gate->record(self::KIND, self::SOURCE);

		self::assertSame(1, (int) \e107::getDb()->count(SourceGate::TABLE, '(*)', self::OF_SOURCE),
			'a source keeps one row however often it tries');
	}

	public function testASwitchedOffGateRecordsNothing()
	{
		$off = $this->gate(60, false);
		$off->record(self::KIND, self::SOURCE);

		self::assertFalse($off->isClosedTo(self::KIND, self::SOURCE));
		self::assertSame(0, (int) \e107::getDb()->count(SourceGate::TABLE, '(*)', self::OF_KIND),
			'a gate switched off records nothing');
	}

	public function testForgetOpensTheGate()
	{
		$gate = $this->gate();
		$gate->record(self::KIND, self::SOURCE);
		$gate->forget(self::KIND, self::SOURCE);

		self::assertFalse($gate->isClosedTo(self::KIND, self::SOURCE));
	}

	public function testForgetMatchesASourceThatNeedsQuoting()
	{
		$source = "o'brien\\";
		$gate = $this->gate();
		$gate->record(self::KIND, $source);

		self::assertTrue($gate->isClosedTo(self::KIND, $source));

		$gate->forget(self::KIND, $source);

		self::assertFalse($gate->isClosedTo(self::KIND, $source), 'forget() must quote the source it matches, not only the one it was built with');
	}
}
