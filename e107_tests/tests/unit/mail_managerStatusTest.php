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
 * The mailout admin lists a page of mails and of a mail's recipients, with the number there are in all for its page links.
 */
class mail_managerStatusTest extends \Test\Unit
{
	const APP = 'statustest';

	/** @var e107MailManager */
	private $mailer;

	/** @var int[] */
	private $mailIds = array();

	protected function _before()
	{
		require_once(e_HANDLER . 'mail_manager_class.php');

		$this->mailer = new e107MailManager();
	}

	protected function _after()
	{
		$db = e107::getDb();
		$db->createQueryBuilder()->delete('mail_content')->where('mail_create_app', self::APP)->execute();

		foreach($this->mailIds as $id)
		{
			$db->createQueryBuilder()->delete('mail_recipients')->where('mail_detail_id', $id)->execute();
		}
	}

	public function testAPageOfMailsComesWithTheNumberThereAreInAll()
	{
		foreach(array('a', 'b', 'c') as $subject)
		{
			$this->seedMail($subject);
		}

		$filter = array("`mail_create_app` = '" . self::APP . "'");

		self::assertSame(2, $this->mailer->selectEmailStatus(0, 2, 'mail_source_id, mail_subject', $filter, 'mail_source_id', 'desc'));
		self::assertSame(3, $this->mailer->getEmailCount());

		$newest = $this->mailer->getNextEmailStatus();
		self::assertSame('c', $newest['mail_subject']);
		self::assertNotFalse($this->mailer->getNextEmailStatus());
		self::assertFalse($this->mailer->getNextEmailStatus());

		self::assertSame(1, $this->mailer->selectEmailStatus(2, 2, '*', $filter, 'not a column!', 'asc'), 'an unknown order column falls back to the mail id');
		self::assertSame(3, $this->mailer->getEmailCount());
		self::assertSame('c', $this->mailer->getNextEmailStatus()['mail_subject']);
	}

	public function testAPageOfRecipientsComesWithTheNumberThereAreInAll()
	{
		$mail = $this->seedMail('with recipients');
		$other = $this->seedMail('another');

		foreach(array(array($mail, 'one@example.com'), array($mail, 'two@example.com'), array($other, 'three@example.com')) as $recipient)
		{
			e107::getDb()->createQueryBuilder()->insert('mail_recipients')->values(array(
				'mail_detail_id'       => $recipient[0],
				'mail_recipient_email' => $recipient[1],
				'mail_status'          => MAIL_STATUS_PENDING,
			))->execute();
		}

		self::assertSame(1, $this->mailer->selectTargetStatus($mail, 0, 1, '*', false, 'mail_target_id', 'desc'));
		self::assertSame(2, $this->mailer->getTargetCount());
		self::assertSame('two@example.com', $this->mailer->getNextTargetStatus()['mail_recipient_email']);

		self::assertSame(1, $this->mailer->selectTargetStatus($mail, 0, 1, 'mail_target_id, mail_recipient_email', false, 'not a column!', 'desc'),
			'an unknown order column falls back to the recipient id');
		self::assertSame('two@example.com', $this->mailer->getNextTargetStatus()['mail_recipient_email']);
	}

	public function testANamedFilterSelectsTheMailsInItsStatuses()
	{
		$this->seedMail('saved');
		$held = $this->seedMail('held');
		e107::getDb()->createQueryBuilder()->update('mail_content')->set('mail_content_status', MAIL_STATUS_HELD)->where('mail_source_id', $held)->execute();

		foreach(array('saved' => array(MAIL_STATUS_SAVED), 'pendingheld' => array(MAIL_STATUS_PENDING, MAIL_STATUS_HELD)) as $filter => $statuses)
		{
			$expected = e107::getDb()->createQueryBuilder()->from('mail_content')->whereIn('mail_content_status', $statuses)->count();

			$this->mailer->selectEmailStatus(0, 0, 'mail_source_id, mail_content_status', $filter);

			self::assertSame($expected, $this->mailer->getEmailCount(), $filter);

			while($mail = $this->mailer->getNextEmailStatus())
			{
				self::assertContains((int) $mail['mail_content_status'], $statuses, $filter);
			}
		}
	}

	/**
	 * @param string $subject
	 * @return int mail_source_id
	 */
	private function seedMail($subject)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('mail_content')->insertGetId(array(
			'mail_content_status' => MAIL_STATUS_SAVED,
			'mail_create_app'     => self::APP,
			'mail_subject'        => $subject,
			'mail_body'           => '',
		));

		self::assertGreaterThan(0, $id);
		$this->mailIds[] = $id;

		return $id;
	}
}
