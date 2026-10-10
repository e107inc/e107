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
 * What private_message::pm_send_notify() reports about the email it sends, which the plugin's Test button shows the administrator.
 */
class private_messageNotifyTest extends \Codeception\Test\Unit
{
	const MAILER = 'core/e107/singleton/e107Email';

	/** @var object|null */
	private $mailer;

	protected function _before()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');
		e107::includeLan(e_PLUGIN . 'pm/languages/' . e_LANGUAGE . '.php');

		$this->mailer = e107::getRegistry(self::MAILER);
	}

	protected function _after()
	{
		e107::setRegistry(self::MAILER, $this->mailer);
	}

	/**
	 * e107Email::sendEmail() answers a failed send with the mailer's error message, never with false.
	 */
	public function testANotificationThatCouldNotBeSentIsReportedAsNotSent()
	{
		$this->haveMailerAnswering('Could not execute: /bin/false');

		$pm = new private_message();

		self::assertFalse($pm->pm_send_notify(null, $this->pmInfo(), 1),
			'A notification the mailer could not send must not be reported as sent.');
	}

	public function testANotificationThatWasSentIsReportedAsSent()
	{
		$this->haveMailerAnswering(true);

		$pm = new private_message();

		self::assertTrue($pm->pm_send_notify(null, $this->pmInfo(), 1));
	}

	/**
	 * @param bool|string $answer what sendEmail() returns
	 * @return void
	 */
	private function haveMailerAnswering($answer)
	{
		e107::setRegistry(self::MAILER, $this->make('e107Email', array('sendEmail' => $answer)));
	}

	/**
	 * @return array the message the plugin's Test button notifies about
	 */
	private function pmInfo()
	{
		return array(
			'pm_sent'    => time(),
			'pm_subject' => 'Notify test subject',
			'from_id'    => 1,
			'to_info'    => array(
				'user_id'    => 1,
				'user_name'  => 'notify-test',
				'user_email' => 'notify-test@example.invalid',
			),
		);
	}
}
