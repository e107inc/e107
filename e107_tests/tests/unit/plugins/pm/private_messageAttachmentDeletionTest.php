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
 * What deleting a message does to the files it carried. Attachments are stored
 * in the media tree, and the delete kept unlinking the directory beside the
 * plugin that releases before it wrote to, so every attachment outlived its
 * message.
 *
 * Which file is only half of it: one upload is named by every recipient's row,
 * by the sender's outbox copy, and by the recipients a bulk send has left in
 * the queue for the cron task, so a message going does not mean the file may.
 */
class private_messageAttachmentDeletionTest extends private_message_attachment_fixture
{
	/** @var private_message_attachment_double */
	private $pm;

	protected function _before()
	{
		parent::_before();

		$this->pm = $this->pointAtTestTrees(new private_message_attachment_double());
	}

	/**
	 * The defect. The attachment is in the media tree, the last message naming
	 * it goes, and the file has to go with it.
	 */
	public function testDeletingTheLastMessageRemovesTheAttachment()
	{
		$file = $this->storeAttachment($this->mediaDir(), $this->name);
		$pmid = $this->seedMessage($this->name);

		$this->pm->del($pmid, TRUE);

		self::assertFileDoesNotExist($file);
		self::assertEmpty($this->fetchMessage($pmid), 'The message row itself must still be deleted');
	}

	/**
	 * A file stored before the media tree arrived is still the message's file,
	 * and is still the delete's to remove.
	 */
	public function testDeletingAMessageRemovesAnAttachmentLeftInTheOldDirectory()
	{
		$file = $this->storeAttachment($this->legacy, $this->name);
		$pmid = $this->seedMessage($this->name);

		$this->pm->del($pmid, TRUE);

		self::assertFileDoesNotExist($file);
	}

	/**
	 * One upload, a row per recipient. The member deleting theirs must not take
	 * the file away from everybody who has not.
	 */
	public function testAnAttachmentAnotherMessageStillNamesIsKept()
	{
		$file = $this->storeAttachment($this->mediaDir(), $this->name);
		$mine = $this->seedMessage($this->name);
		$theirs = $this->seedMessage($this->name);

		$this->pm->del($mine, TRUE);

		self::assertFileExists($file, 'The recipient who has not deleted theirs still has the attachment');
		self::assertNotEmpty($this->fetchMessage($theirs), 'Only the message that was deleted goes');
	}

	/**
	 * A send over pm_max_send leaves the rest of the recipients serialised in
	 * the generic table for the cron task, so their rows do not exist yet. The
	 * file is owed to them all the same.
	 */
	public function testAnAttachmentAQueuedBulkSendStillOwesIsKept()
	{
		$file = $this->storeAttachment($this->mediaDir(), $this->name);
		$pmid = $this->seedMessage($this->name);
		$this->seedQueuedBulkSend($this->name);

		$this->pm->del($pmid, TRUE);

		self::assertFileExists($file, 'The recipients cron has yet to be given are still owed the attachment');
	}

	/**
	 * The order the two reads happen in. A bulk run that finishes between them
	 * has inserted the rows it owed and dropped its queue row, so a delete that
	 * read the messages first and the queue second would find neither and take
	 * a file every one of those recipients now holds.
	 */
	public function testAnAttachmentIsKeptWhenABulkRunFinishesDuringTheDelete()
	{
		$file = $this->storeAttachment($this->mediaDir(), $this->name);
		$outbox = $this->seedMessage($this->name);
		$genId = $this->seedQueuedBulkSend($this->name);

		$pm = $this->pointAtTestTrees(new private_message_queue_race_double());
		$pm->onQueueRead = function () use ($genId)
		{
			$this->finishQueuedBulkSend($genId);
		};

		$pm->del($outbox, TRUE);

		self::assertFileExists($file, 'The recipients the run has just reached hold the attachment');
	}

	/**
	 * The stored name says who uploaded it. A name that does not answer, or
	 * answers with somebody other than the sender, is a name nothing is
	 * unlinked for.
	 */
	public function testAnAttachmentNotBelongingToTheSenderIsLeftAlone()
	{
		$other = $this->storedName((int) USERID + 1);
		$file = $this->storeAttachment($this->root . sprintf('user_%06d/', (int) USERID + 1), $other);
		$pmid = $this->seedMessage($other);

		$this->pm->del($pmid, TRUE);

		self::assertFileExists($file);
	}
}
