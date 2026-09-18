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
 * The one store every dismissible admin notice reads. A record that outlives
 * the condition it was made under, or that vanishes with the cache, is a
 * notice the administrator either never sees again or is nagged with after
 * saying no, so both edges are pinned here.
 */
class NoticeSuppressionTest extends \Test\Unit
{
	const USER = 7;

	/** @var mixed */
	private $saved;

	/** @var NoticeSuppression */
	private $suppression;

	protected function _before()
	{
		require_once(e_HANDLER.'Admin/NoticeSuppression.php');

		$this->saved = \e107::getConfig()->get(NoticeSuppression::PREF);
		$this->suppression = new NoticeSuppression(\e107::getConfig(), \e107::getLog(), self::USER);
		$this->forget();
	}

	protected function _after()
	{
		$config = \e107::getConfig();

		if($this->saved === null)
		{
			$config->remove(NoticeSuppression::PREF);
		}
		else
		{
			$config->set(NoticeSuppression::PREF, $this->saved);
		}

		$config->save(false, true, false);
	}

	private function forget()
	{
		\e107::getConfig()->remove(NoticeSuppression::PREF)->save(false, true, false);
	}

	public function testASuppressionHoldsForItsFingerprintAlone()
	{
		$this->suppression->suppress('unit-test', 'abc');

		self::assertTrue($this->suppression->isSuppressed('unit-test', 'abc'));
		self::assertFalse($this->suppression->isSuppressed('unit-test', 'xyz'), 'a changed fingerprint ends it');
		self::assertFalse($this->suppression->isSuppressed('unit-test'), 'a fingerprint is not optional once recorded');
		self::assertFalse($this->suppression->isSuppressed('another', 'abc'), 'one record, one notice');
	}

	public function testAnUnconditionalSuppressionHoldsUntilReleased()
	{
		$this->suppression->suppress('unit-test');

		self::assertTrue($this->suppression->isSuppressed('unit-test'));
		self::assertFalse($this->suppression->isSuppressed('unit-test', 'abc'), 'a notice that now carries a fingerprint is a new notice');

		$this->suppression->release('unit-test');
		self::assertFalse($this->suppression->isSuppressed('unit-test'));
	}

	public function testALapsedSuppressionIsGoneAndIsPrunedOnTheNextWrite()
	{
		$this->suppression->suppress('lapsed', '', time() - 1);
		$this->suppression->suppress('holding', '', time() + 3600);

		self::assertFalse($this->suppression->isSuppressed('lapsed'));
		self::assertTrue($this->suppression->isSuppressed('holding'));

		$this->suppression->suppress('another');
		$records = \e107::getConfig()->get(NoticeSuppression::PREF);

		self::assertArrayNotHasKey('lapsed', $records, 'a lapsed record is not kept');
		self::assertArrayHasKey('holding', $records);
		self::assertArrayHasKey('another', $records);
	}

	public function testReleaseForgetsOneNoticeOnly()
	{
		$this->suppression->suppress('first', 'a');
		$this->suppression->suppress('second', 'b');

		$this->suppression->release('first');

		self::assertFalse($this->suppression->isSuppressed('first', 'a'));
		self::assertTrue($this->suppression->isSuppressed('second', 'b'));

		$this->suppression->release('never-recorded');
		self::assertTrue($this->suppression->isSuppressed('second', 'b'), 'releasing an unknown id changes nothing');
	}

	public function testTheRecordSaysWhoAndWhen()
	{
		$before = time();
		$this->suppression->suppress('unit-test', 'abc', 0);
		$record = \e107::getConfig()->get(NoticeSuppression::PREF);

		self::assertSame('abc', $record['unit-test']['while']);
		self::assertSame(0, $record['unit-test']['until']);
		self::assertSame(self::USER, $record['unit-test']['by']);
		self::assertGreaterThanOrEqual($before, $record['unit-test']['at']);
	}

	public function testTheRecordIsSavedNotMerelySet()
	{
		$this->suppression->suppress('unit-test', 'abc');

		$reloaded = \e107::getConfig('core', true, true)->get(NoticeSuppression::PREF);

		self::assertSame('abc', $reloaded['unit-test']['while'], 'the record must survive a reload from the database');
		self::assertTrue($this->suppression->isSuppressed('unit-test', 'abc'));
	}

	public function testGarbageInThePreferenceReadsAsNothing()
	{
		$config = \e107::getConfig();
		$config->set(NoticeSuppression::PREF, 'not an array')->save(false, true, false);
		self::assertFalse($this->suppression->isSuppressed('unit-test'));

		$config->set(NoticeSuppression::PREF, array('unit-test' => 'not a record', 'other' => array()))->save(false, true, false);
		self::assertFalse($this->suppression->isSuppressed('unit-test'));
		self::assertTrue($this->suppression->isSuppressed('other'), 'a record with nothing in it is an unconditional one');

		$this->suppression->suppress('unit-test', 'abc');
		self::assertTrue($this->suppression->isSuppressed('unit-test', 'abc'), 'writing over garbage works');
	}
}
