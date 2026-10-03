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
 * The inbox and outbox list one page of a member's messages and the number of messages there are in all.
 */
class pm_mailboxTest extends \Test\Unit
{
	const SENDER = 990001;

	const RECIPIENT = 990002;

	/** @var int[] */
	private $pmIds = array();

	/** @var bool */
	private $createdTable = false;

	protected function _before()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');

		$db = e107::getDb();

		if(!$db->isTable('private_msg'))
		{
			$catalogue = new \e107\Database\Schema\Declared\SqlFileCatalogue();
			$declared = $catalogue->parse(file_get_contents(e_PLUGIN . 'pm/pm_sql.php'), 'pm');
			self::assertNotFalse($db->schema()->createDeclaredTable($declared['private_msg']), $db->getLastErrorText());
			$this->createdTable = true;
		}
	}

	protected function _after()
	{
		$db = e107::getDb();

		foreach($this->pmIds as $id)
		{
			$db->createQueryBuilder()->delete('private_msg')->where('pm_id', $id)->execute();
		}

		if($this->createdTable)
		{
			$db->schema()->dropTable('private_msg');
		}
	}

	public function testTheInboxListsAPageAndCountsEveryMessage()
	{
		$this->seed(3);
		$this->seed(1, array('pm_read_del' => 1));

		$inbox = (new private_message())->pm_get_inbox(self::RECIPIENT, 1, 2);

		self::assertSame(3, $inbox['total_messages'], 'the message the recipient deleted is not counted');
		self::assertCount(2, $inbox['messages']);
		self::assertSame(array('second', 'first'), array_column($inbox['messages'], 'pm_subject'), 'newest first, from the second');
	}

	public function testTheOutboxListsAPageAndCountsEveryMessage()
	{
		$this->seed(3);
		$this->seed(1, array('pm_sent_del' => 1));

		$outbox = (new private_message())->pm_get_outbox(self::SENDER, 0, 2);

		self::assertSame(3, $outbox['total_messages']);
		self::assertSame(array('third', 'second'), array_column($outbox['messages'], 'pm_subject'));
	}

	public function testAnEmptyMailboxHasNoMessages()
	{
		$inbox = (new private_message())->pm_get_inbox(self::RECIPIENT);

		self::assertSame(0, $inbox['total_messages']);
		self::assertArrayNotHasKey('messages', $inbox);
	}

	/**
	 * @param int $count messages to send, a second apart, subjects 'first', 'second', ...
	 * @param array $overrides
	 * @return void
	 */
	private function seed($count, $overrides = array())
	{
		static $sent = 1700000000;
		$names = array('first', 'second', 'third', 'fourth');

		for($i = 0; $i < $count; $i++)
		{
			$this->pmIds[] = (int) e107::getDb()->createQueryBuilder()->insert('private_msg')->insertGetId(array_merge(array(
				'pm_from'        => self::SENDER,
				'pm_to'          => (string) self::RECIPIENT,
				'pm_sent'        => $sent++,
				'pm_read'        => 0,
				'pm_subject'     => isset($overrides['pm_subject']) ? $overrides['pm_subject'] : $names[$i],
				'pm_text'        => 'mailbox fixture',
				'pm_sent_del'    => 0,
				'pm_read_del'    => 0,
				'pm_attachments' => '',
				'pm_option'      => '',
				'pm_size'        => 15
			), $overrides));
		}
	}
}
