<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once(__DIR__ . '/private_message_attachment_fixture.php');

/**
 * What the queue looks like while the cron task is sending out of it. A send
 * over pm_max_send leaves its recipients serialised in the generic table, and
 * that row is the only thing saying their attachment is still owed to them
 * until their messages are in the table.
 */
class pm_e_cronTest extends private_message_attachment_fixture
{
	protected function _before()
	{
		parent::_before();

		require_once(e_PLUGIN . 'pm/e_cron.php');
		require_once(__DIR__ . '/pm_cron_double.php');

		self::assertSame(0, $this->queuedSendCount(),
			'These tests drive the real run, which takes the first queued send it finds, so the queue has to be theirs');
	}

	/**
	 * The defect. Taking the row off the queue before the messages it stands
	 * for are inserted leaves the recipients holding their attachment by
	 * nothing at all, and a delete landing in that window takes the file.
	 */
	public function testADeleteDuringABulkRunKeepsTheAttachmentTheQueueStillOwes()
	{
		$file = $this->storeAttachment($this->mediaDir(), $this->name);
		$outbox = $this->seedMessage($this->name);
		$genId = $this->seedQueuedBulkSend($this->name);

		$cron = new pm_cron_double();
		$cron->pm = $this->pointAtTestTrees(new private_message_add_double());
		$cron->pm->onAdd = function ($pm) use ($outbox)
		{
			return $pm->del($outbox, TRUE);
		};

		$cron->processPM();

		self::assertSame(1, $cron->pm->adds, 'The run must send through the messenger this test gave it');
		self::assertFileExists($file, 'The recipients the run has yet to reach are still owed the attachment');
		self::assertEmpty($this->fetchQueuedSend($genId), 'The queued row goes once the messages it stood for are in');
	}

	/**
	 * A run that dies part way through keeps the send it claimed, so the next
	 * run does not deliver the same chunk a second time and the attachment
	 * stays held meanwhile.
	 */
	public function testARunThatDiesPartWayThroughDoesNotSendTheSameChunkAgain()
	{
		$genId = $this->seedQueuedBulkSend($this->name);

		$cron = new pm_cron_double();
		$cron->pm = $this->pointAtTestTrees(new private_message_add_double());
		$cron->pm->onAdd = function ()
		{
			throw new pm_cron_run_died('The run died part way through the send');
		};

		try
		{
			$cron->processPM();

			self::fail('The fixture send was meant to die part way through');
		}
		catch(pm_cron_run_died $died)
		{
		}

		$claimed = $this->fetchQueuedSend($genId);

		self::assertNotEmpty($claimed, 'A run that died must not have taken the send with it');
		self::assertNotSame('pm_bulk', $claimed['gen_type'], 'The send a run is part way through is off the queue for the length of the run');

		$next = new pm_cron_double();
		$next->pm = $this->pointAtTestTrees(new private_message_add_double());

		$next->processPM();

		self::assertSame(0, $next->pm->adds, 'The next run must not send a chunk another run has claimed');
	}

	/**
	 * The race the claim is there for: two runs read the same row as queued
	 * before either of them takes it, and only one may send it.
	 */
	public function testOnlyOneRunCanClaimTheSameQueuedSend()
	{
		$genId = $this->seedQueuedBulkSend($this->name);

		$first = new pm_cron_double();
		$second = new pm_cron_double();

		self::assertTrue($first->claim($genId), 'The first run takes the send');
		self::assertFalse($second->claim($genId), 'The run that arrives second finds it taken');
	}

	/**
	 * A gen_id that is not a queued send is not a send to take. The claim reads
	 * the row's type as well as its id, so it cannot rewrite a row the plugin
	 * keeps in the same table for something else.
	 */
	public function testARowThatIsNotAQueuedSendIsLeftAsItStands()
	{
		$genId = $this->seedGenericRow('pm_limit');

		$cron = new pm_cron_double();

		self::assertFalse($cron->claim($genId), 'A row of another type is not a send to claim');

		$row = $this->fetchQueuedSend($genId);

		self::assertSame('pm_limit', $row['gen_type'], 'The claim must leave a row it did not take alone');
	}
}
