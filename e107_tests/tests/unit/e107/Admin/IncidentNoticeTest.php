<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * What the administrator is shown and what stops showing it. Every dismissible
 * notice reads through this, so the three ways a record can be no news are
 * pinned once here rather than per subsystem.
 */
class IncidentNoticeTest extends \Test\Unit
{
	const ID = 'unit-notice';

	/** @var NoticeSuppression */
	private $suppression;

	protected function _before()
	{
		require_once(e_HANDLER.'Admin/NoticeSuppression.php');
		require_once(e_HANDLER.'Admin/IncidentNotice.php');

		$this->suppression = new NoticeSuppression(\e107::getConfig(), \e107::getLog(), 1);
		$this->suppression->release(self::ID);
	}

	protected function _after()
	{
		$this->suppression->release(self::ID);
	}

	/**
	 * @param string $fingerprint
	 * @param array|null $incident
	 * @param int $supersededAt
	 * @return IncidentNotice
	 */
	private function notice($fingerprint, $incident = null, $supersededAt = 0, $onDismiss = null)
	{
		return new IncidentNotice($this->suppression, self::ID, $fingerprint, $incident, $supersededAt, $onDismiss);
	}

	/**
	 * @param int $last
	 * @return array
	 */
	private function incident($last)
	{
		return array('first' => $last - 600, 'last' => $last, 'count' => 3, 'ip' => '203.0.113.9');
	}

	public function testNothingToReportWithoutARecord()
	{
		self::assertNull($this->notice('f')->toReport());
		self::assertSame(self::ID, $this->notice('f')->id());
	}

	public function testARecordIsReportedUntilSomethingSettlesIt()
	{
		$now = time();

		self::assertNotNull($this->notice('f', $this->incident($now))->toReport(),
			'nothing has settled it, so every record is news');
		self::assertNotNull($this->notice('f', $this->incident($now), $now - 60)->toReport());
		self::assertNull($this->notice('f', $this->incident($now - 60), $now)->toReport(),
			'the record is older than the thing that settles it');
	}

	public function testADismissalHoldsForItsFingerprintAndEndsWithIt()
	{
		$incident = $this->incident(time());

		$this->notice('one', $incident)->dismiss();

		self::assertNull($this->notice('one', $incident)->toReport(), 'dismissed');
		self::assertNull($this->notice('one', $this->incident(time() + 5))->toReport(),
			'a fresh record under the same fingerprint stays dismissed');
		self::assertNotNull($this->notice('two', $incident)->toReport(),
			'a changed fingerprint asks again');
	}

	public function testTheRecordItReportsIsTheOneItWasGiven()
	{
		$incident = $this->incident(time());

		self::assertSame($incident, $this->notice('f', $incident)->toReport());
	}

	public function testGarbageInsteadOfARecordIsNoRecord()
	{
		self::assertNull($this->notice('f', 'not a record')->toReport());
	}

	/**
	 * The admin-header bell keeps its own copy in the session, so a dismissal
	 * that only wrote the preference would leave the notice on screen until the
	 * dashboard next ran its checks.
	 */
	public function testDismissingTellsWhateverHoldsItsOwnCopy()
	{
		$told = array();
		$notice = $this->notice('f', $this->incident(time()), 0, function($id) use (&$told) { $told[] = $id; });

		$notice->dismiss();

		self::assertSame(array(self::ID), $told);
		self::assertNull($notice->toReport());
	}
}
