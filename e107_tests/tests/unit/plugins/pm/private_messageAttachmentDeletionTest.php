<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

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
class private_messageAttachmentDeletionTest extends \Test\Unit
{
	/** @var private_message_attachment_double */
	private $pm;

	/** @var string */
	private $root;

	/** @var string */
	private $legacy;

	/** @var string stored name, as pm_attachments carries it */
	private $name;

	/** @var array */
	private $pmIds = array();

	/** @var array */
	private $genIds = array();

	/** @var array */
	private $files = array();

	/** @var array */
	private $dirs = array();

	protected function _before()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');
		require_once(__DIR__ . '/private_message_attachment_double.php');

		e107::includeLan(e_PLUGIN . 'pm/languages/' . e_LANGUAGE . '.php');

		$this->root = e_TEMP . 'pm_attachment_deletion_' . uniqid() . '/';
		$this->legacy = e_TEMP . 'pm_attachment_legacy_' . uniqid() . '/';

		$this->pm = new private_message_attachment_double();
		$this->pm->root = $this->root;
		$this->pm->legacy = $this->legacy;

		$this->name = $this->storedName((int) USERID);
	}

	protected function _after()
	{
		$db = e107::getDb();

		foreach($this->pmIds as $id)
		{
			$db->createQueryBuilder()->delete('private_msg')->where('pm_id', (int) $id)->execute();
		}

		foreach($this->genIds as $id)
		{
			$db->createQueryBuilder()->delete('generic')->where('gen_id', (int) $id)->execute();
		}

		foreach($this->files as $file)
		{
			if(is_file($file))
			{
				unlink($file);
			}
		}

		foreach(array_reverse($this->dirs) as $dir)
		{
			if(is_dir($dir))
			{
				rmdir($dir);
			}
		}

		foreach(array($this->root, $this->legacy) as $dir)
		{
			if(is_dir($dir))
			{
				rmdir($dir);
			}
		}
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

	/**
	 * @param int $owner
	 * @return string the name upload_handler builds: time_userid_random_original
	 */
	private function storedName($owner)
	{
		return time() . '_' . $owner . '_pmtest_report.pdf';
	}

	/**
	 * @return string where the sending member's uploads go
	 */
	private function mediaDir()
	{
		return $this->root . (((int) USERID > 0) ? sprintf('user_%06d/', (int) USERID) : 'anon/');
	}

	/**
	 * @param string $dir
	 * @param string $name
	 * @return string the file written
	 */
	private function storeAttachment($dir, $name)
	{
		mkdir($dir, 0755, true);

		$path = $dir . $name;

		file_put_contents($path, 'attachment');

		$this->files[] = $path;
		$this->dirs[] = $dir;

		self::assertFileExists($path, 'The fixture attachment was not written');

		return $path;
	}

	/**
	 * @param string $attachments chr(0)-separated list, as add() writes it
	 * @return int
	 */
	private function seedMessage($attachments)
	{
		$id = e107::getDb()->createQueryBuilder()->insert('private_msg')->insertGetId(array(
			'pm_from'        => (int) USERID,
			'pm_to'          => (string) ((int) USERID + 1),
			'pm_sent'        => time(),
			'pm_read'        => 0,
			'pm_subject'     => 'Attachment deletion fixture',
			'pm_text'        => 'Attachment deletion fixture',
			'pm_sent_del'    => 0,
			'pm_read_del'    => 0,
			'pm_attachments' => $attachments,
			'pm_option'      => '',
			'pm_size'        => 11
		));

		self::assertNotEmpty($id, 'Could not seed a private message.');

		$this->pmIds[] = $id;

		return (int) $id;
	}

	/**
	 * @param string $attachments chr(0)-separated list, as add() serialises it
	 */
	private function seedQueuedBulkSend($attachments)
	{
		$pmInfo = array(
			'pm_from'        => (int) USERID,
			'pm_attachments' => $attachments,
			'to_array'       => array(array('user_id' => (int) USERID + 2, 'user_name' => 'Queued'))
		);

		$id = e107::getDb()->createQueryBuilder()->insert('generic')->insertGetId(array(
			'gen_type'      => 'pm_bulk',
			'gen_datestamp' => time(),
			'gen_user_id'   => (int) USERID,
			'gen_ip'        => '',
			'gen_intdata'   => 1,
			'gen_chardata'  => e107::serialize($pmInfo, TRUE)
		));

		self::assertNotEmpty($id, 'Could not queue a bulk send.');

		$this->genIds[] = $id;
	}

	/**
	 * @param int $pmid
	 * @return array|bool
	 */
	private function fetchMessage($pmid)
	{
		return e107::getDb()->createQueryBuilder()->select('pm_id')->from('private_msg')
			->where('pm_id', (int) $pmid)->fetchRow();
	}
}
