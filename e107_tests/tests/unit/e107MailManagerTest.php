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
 * What e107MailManager::sendEmails() answers for a send it makes at once, rather than through the queue.
 */
class e107MailManagerTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER . 'mail_manager_class.php');
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
