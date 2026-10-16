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
 * What e107MailManager::sendEmails() answers for a send it makes at once, rather than through the queue, and what
 * the queue reads back of an email it saved.
 */
class e107MailManagerTest extends \Test\Unit
{
	/** @var int[] */
	private $saved = array();

	protected function _before()
	{
		require_once(e_HANDLER . 'mail_manager_class.php');
	}

	protected function _after()
	{
		foreach($this->saved as $id)
		{
			e107::getDb()->createQueryBuilder()->delete('mail_content')->where('mail_source_id', $id)->execute();
		}
	}

	/**
	 * e107Email::sendEmail() answers a failed send with the mailer's error message, never with false.
	 */
	public function testAnEmailTheMailerCouldNotSendIsReportedAsNotSent()
	{
		self::assertFalse($this->sendThrough('Could not execute: /bin/false'),
			'An email the mailer could not send must not be reported as sent.');
	}

	public function testAnEmailTheMailerSentIsReportedAsSent()
	{
		self::assertTrue($this->sendThrough(true));
	}

	/**
	 * @dataProvider senderNames
	 * @param string $name
	 */
	public function testAQueuedEmailReadsBackTheSenderNameItWasSavedWith($name)
	{
		foreach($this->readBack(array('mail_sender_name' => $name)) as $reader => $email)
		{
			self::assertSame($name, varset($email['mail_sender_name']), $reader);
			self::assertSame('', varset($email['mail_bcopy_to']), $reader);
		}
	}

	/**
	 * @return array[]
	 */
	public function senderNames()
	{
		return array(
			'an apostrophe' => array("O'Brien Ltd"),
			'a quoted key'  => array("Acme', 'mail_bcopy_to' => 'copy@example.invalid"),
		);
	}

	/**
	 * @param array $other the email's sender, copies and other details
	 * @return array[] the saved email, as each reader of the queue returns it
	 */
	private function readBack(array $other)
	{
		$manager = new e107MailManager();
		$id = $manager->saveEmail(array_merge(array(
			'mail_create_app' => 'core',
			'mail_title'      => 'Mail manager test',
			'mail_subject'    => 'Mail manager test',
			'mail_body'       => 'Mail manager test body',
		), $other), true);
		$this->saved[] = (int) $id;

		$row = e107::getDb()->createQueryBuilder()->select('*')->from('mail_content')
			->where('mail_source_id', (int) $id)->fetchRow();

		return array(
			'retrieveEmail()' => $manager->retrieveEmail($id),
			'dbToBoth()'      => $manager->dbToBoth($row),
		);
	}

	/**
	 * @param bool|string $answer what the mailer's sendEmail() returns
	 * @return bool what sendEmails() answers for one recipient
	 */
	private function sendThrough($answer)
	{
		$manager = new e107MailManager();

		$mailer = new \e107\Reflection\ReflectionProperty('e107MailManager', 'mailer');
		$mailer->setValue($manager, $this->make('e107Email', array('sendEmail' => $answer)));

		$email = array(
			'mail_subject'    => 'Mail manager test',
			'mail_body'       => 'Mail manager test body',
			'mail_send_style' => 'textonly',
		);

		$recipient = array(
			'mail_recipient_email' => 'mail-manager-test@example.invalid',
			'mail_recipient_name'  => 'Mail manager test',
			'mail_target_info'     => array(),
		);

		return $manager->sendEmails('textonly', $email, array($recipient));
	}
}
