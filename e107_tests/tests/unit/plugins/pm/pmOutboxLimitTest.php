<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once(__DIR__.'/pm_limit_fixture.php');

/**
 * @group plugins
 *
 * What the outbox limits stop, with the limit mode and the class limits as the Limits page stores them: messages sent through pm.php's send form, the form itself, and whether reading the outbox again queries the limits again.
 */
class pmOutboxLimitTest extends pm_limit_fixture
{
	const SUBJECT = 'pmOutboxLimitTest';

	const MODE_COUNTS = 1;

	const MODE_SIZES = 2;

	protected function _before()
	{
		parent::_before();

		$this->requireTable('private_msg');
		$this->requireTable('private_msg_block');
	}

	protected function _after()
	{
		e107::getDb()->db_Query(array(
			'PREPARE' => 'DELETE FROM `'.MPREFIX.'private_msg` WHERE `pm_subject` = :subject',
			'BIND'    => array('subject' => array('value' => self::SUBJECT, 'type' => PDO::PARAM_STR)),
		), null, 'db_Delete');

		parent::_after();
	}

	public function testASenderWhoseClassesHaveNoSizeLimitMaySend()
	{
		$this->assertSent($this->send(self::MODE_SIZES, 'A short message.'));
	}

	public function testASenderWhoseClassesHaveNoCountLimitMaySend()
	{
		$this->assertSent($this->send(self::MODE_COUNTS, 'A short message.'));
	}

	public function testTheSizeLimitIsReadInKilobytes()
	{
		$this->addLimit(e_UC_MEMBER, 0, 1);

		$this->assertSent($this->send(self::MODE_SIZES, str_repeat('x', 600)));
	}

	public function testAMessageOverTheSizeLimitIsRefused()
	{
		$this->addLimit(e_UC_MEMBER, 0, 1);

		$this->assertRefused(LAN_PM_21, $this->send(self::MODE_SIZES, str_repeat('x', 2000)));
	}

	public function testTheNumericallyLargestSizeLimitOfTheSendersClassesApplies()
	{
		$this->addLimit(e_UC_MEMBER, 0, 100);
		$this->addLimit(e_UC_ADMIN, 0, 40);

		$this->assertSent($this->send(self::MODE_SIZES, str_repeat('x', 50 * 1024)));
	}

	public function testASenderAlreadyPastTheCountLimitIsRefused()
	{
		$this->addLimit(e_UC_MEMBER, 1, 0);

		$this->assertRefused(LAN_PM_19, $this->send(self::MODE_COUNTS, 'A short message.', 2));
	}

	public function testTheNumericallyLargestCountLimitOfTheSendersClassesApplies()
	{
		$this->addLimit(e_UC_MEMBER, 10, 0);
		$this->addLimit(e_UC_ADMIN, 9, 0);

		$this->assertSent($this->send(self::MODE_COUNTS, 'A short message.', 9));
	}

	public function testTheSendFormSaysAnOutboxTenTimesOverItsLimitIsFull()
	{
		$this->addLimit(e_UC_MEMBER, 1, 0);
		$this->storeMode(self::MODE_COUNTS);

		$page = $this->renderPage('e107_plugins/pm/pm.php', 'send', $this->outbox(10));

		self::assertStringContainsString(str_replace('{PERCENT}', '1000.00', LAN_PM_13), $page, "The send form does not say the outbox is full:\n".$page);
	}

	public function testASecondReadOfTheOutboxDoesNotQueryTheLimitsAgain()
	{
		$this->storeMode(self::MODE_SIZES);

		$php = $this->installed();
		$php .= "require_once(e_PLUGIN.'pm/pm_func.php'); ";
		$php .= "\$manager = new pmbox_manager(); \$manager->pm_getInfo('outbox'); ";
		$php .= "\$before = e107::getDb()->queryCount(); \$manager->pm_getInfo('outbox'); ";
		$php .= "echo \"\\nQUERIES:\".(e107::getDb()->queryCount() - \$before).\"\\n\"; ";

		list($output) = $this->runInBootedCli($php);
		$out = implode("\n", $output);

		self::assertSame(1, preg_match('/^QUERIES:(\d+)$/m', $out, $match), "The child did not finish:\n".$out);
		self::assertSame('0', $match[1], "A second read of the outbox queried the database again:\n".$out);
	}

	/**
	 * Stores the plugin's preferences with this limit mode, as a site where members may send to one member at a time.
	 *
	 * @param int $mode
	 * @return void
	 */
	private function storeMode($mode)
	{
		$this->storePrefs(array(
			'pm_class' => e_UC_MEMBER, 'pm_limits' => $mode, 'perpage' => 10, 'pm_max_send' => 100, 'notify_class' => e_UC_NOBODY, 'receipt_class' => e_UC_NOBODY,
			'attach_class' => e_UC_NOBODY, 'multi_class' => e_UC_NOBODY, 'opt_userclass' => e_UC_NOBODY, 'sendall_class' => e_UC_NOBODY,
		));
	}

	/**
	 * @param int $sent messages to put in the sender's outbox
	 * @return string PHP that puts them there
	 */
	private function outbox($sent)
	{
		$php = "for(\$i = 0; \$i < ".(int) $sent."; \$i++) { e107::getDb()->insert('private_msg', array(";
		$php .= "'pm_from' => USERID, 'pm_to' => USERID, 'pm_sent' => time(), 'pm_subject' => '".self::SUBJECT."', 'pm_text' => '', 'pm_attachments' => '', 'pm_option' => '', 'pm_size' => 0";
		$php .= ")); } ";

		return $php;
	}

	/**
	 * Sends one message to the sender's own account from pm.php's send form.
	 *
	 * @param int $mode the limit mode the plugin's preferences hold
	 * @param string $message
	 * @param int $sent messages already in the sender's outbox
	 * @return string everything the page wrote
	 */
	private function send($mode, $message, $sent = 0)
	{
		$this->storeMode($mode);

		$post = array('postpm' => '1', 'pm_to' => '1', 'pm_subject' => self::SUBJECT, 'pm_message' => $message, 'numsent' => (string) $sent);

		return $this->renderPage('e107_plugins/pm/pm.php', '', $this->outbox($sent).self::posting($post));
	}

	/**
	 * @param string $page
	 * @return void
	 */
	private function assertSent($page)
	{
		self::assertStringNotContainsString(LAN_PM_19, $page, "The send was refused:\n".$page);
		self::assertStringNotContainsString(LAN_PM_21, $page, "The send was refused:\n".$page);
		self::assertSame(1, $this->stored(), "The message was not stored:\n".$page);
	}

	/**
	 * @param string $refusal
	 * @param string $page
	 * @return void
	 */
	private function assertRefused($refusal, $page)
	{
		self::assertStringContainsString($refusal, $page, "The send was not refused:\n".$page);
		self::assertSame(0, $this->stored(), "The message was stored:\n".$page);
	}

	/**
	 * @return int messages this test's send stored, the outbox it seeded aside
	 */
	private function stored()
	{
		return (int) e107::getDb()->select('private_msg', 'pm_id', 'pm_subject = :subject AND pm_size > 0', array('subject' => self::SUBJECT));
	}
}
