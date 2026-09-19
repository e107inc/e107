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
 * The fixtures every attachment test needs: disposable attachment trees, the
 * private_msg table the unit install does not carry, a stored attachment, a
 * message naming it, and a bulk send queued for the cron task.
 *
 * Everything seeded here is tracked and taken away again, because the unit
 * suite shuffles and runs against a live install.
 */
abstract class private_message_attachment_fixture extends \Test\Unit
{
	/** @var string the media tree attachments store into */
	protected $root;

	/** @var string the directory releases before the media tree wrote to */
	protected $legacy;

	/** @var string stored name, as pm_attachments carries it */
	protected $name;

	/** @var array */
	private $pmIds = array();

	/** @var array */
	private $genIds = array();

	/** @var array */
	private $files = array();

	/** @var array */
	private $dirs = array();

	/** @var bool */
	private $createdTable = false;

	protected function _before()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');
		require_once(__DIR__ . '/private_message_attachment_double.php');

		e107::includeLan(e_PLUGIN . 'pm/languages/' . e_LANGUAGE . '.php');

		$this->requirePrivateMsgTable();

		$this->root = e_TEMP . 'pm_attachment_root_' . uniqid() . '/';
		$this->legacy = e_TEMP . 'pm_attachment_legacy_' . uniqid() . '/';

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

		if($this->createdTable)
		{
			$db->schema()->dropTable('private_msg');
			$this->createdTable = false;
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
	 * @param private_message_attachment_double $pm
	 * @return private_message_attachment_double the double, reading and writing this test's disposable trees
	 */
	protected function pointAtTestTrees($pm)
	{
		$pm->root = $this->root;
		$pm->legacy = $this->legacy;

		return $pm;
	}

	/**
	 * @param int $owner
	 * @return string the name upload_handler builds: time_userid_random_original
	 */
	protected function storedName($owner)
	{
		return time() . '_' . $owner . '_pmtest_report.pdf';
	}

	/**
	 * @return string where the sending member's uploads go
	 */
	protected function mediaDir()
	{
		return $this->root . (((int) USERID > 0) ? sprintf('user_%06d/', (int) USERID) : 'anon/');
	}

	/**
	 * @param string $dir
	 * @param string $name
	 * @return string the file written
	 */
	protected function storeAttachment($dir, $name)
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
	protected function seedMessage($attachments)
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
	 * The gen_type is the literal the table holds rather than the class constant, so reverting the fix reds an assertion instead of breaking the seed.
	 *
	 * @param string $attachments chr(0)-separated list, as add() serialises it
	 * @return int gen_id of the queued row
	 */
	protected function seedQueuedBulkSend($attachments)
	{
		$pmInfo = array(
			'pm_from'        => (int) USERID,
			'pm_attachments' => $attachments,
			'to_array'       => array(array('user_id' => (int) USERID + 2, 'user_name' => 'Queued'))
		);

		return $this->seedGenericRow('pm_bulk', e107::serialize($pmInfo, TRUE));
	}


	/**
	 * @param string $type gen_type to file the row under
	 * @param string $chardata gen_chardata, serialised by the caller
	 * @return int gen_id of the row
	 */
	protected function seedGenericRow($type, $chardata = '')
	{
		$id = e107::getDb()->createQueryBuilder()->insert('generic')->insertGetId(array(
			'gen_type'      => $type,
			'gen_datestamp' => time(),
			'gen_user_id'   => (int) USERID,
			'gen_ip'        => '',
			'gen_intdata'   => 1,
			'gen_chardata'  => $chardata
		));

		self::assertNotEmpty($id, 'Could not seed a generic row.');

		$this->genIds[] = $id;

		return (int) $id;
	}

	/**
	 * @return int rows the bulk queue holds, the ones a run has claimed included
	 */
	protected function queuedSendCount()
	{
		return (int) e107::getDb()->createQueryBuilder()->select('gen_id')->from('generic')
			->whereIn('gen_type', array('pm_bulk', 'pm_bulk_running'))->execute();
	}


	/**
	 * What the cron task does at the end of a chunk: the messages it owed are in
	 * the table and the row that stood for them is gone.
	 *
	 * @param int $genId gen_id of the queued row the run claimed
	 */
	protected function finishQueuedBulkSend($genId)
	{
		$this->seedMessage($this->name);

		e107::getDb()->createQueryBuilder()->delete('generic')->where('gen_id', (int) $genId)->execute();
	}


	/**
	 * @param int $pmid
	 * @return array|bool
	 */
	protected function fetchMessage($pmid)
	{
		return e107::getDb()->createQueryBuilder()->select('pm_id')->from('private_msg')
			->where('pm_id', (int) $pmid)->fetchRow();
	}

	/**
	 * @param int $genId
	 * @return array|bool the queued row as it stands, or FALSE once it is gone
	 */
	protected function fetchQueuedSend($genId)
	{
		return e107::getDb()->createQueryBuilder()->select('gen_id', 'gen_type')->from('generic')
			->where('gen_id', (int) $genId)->fetchRow();
	}

	/**
	 * The unit install carries no plugin tables, so the table the pm plugin declares is built from its own schema file and dropped again.
	 */
	private function requirePrivateMsgTable()
	{
		$db = e107::getDb();

		if($db->isTable('private_msg'))
		{
			return;
		}

		$catalogue = new \e107\Database\Schema\Declared\SqlFileCatalogue();
		$declared = $catalogue->parse(file_get_contents(e_PLUGIN . 'pm/pm_sql.php'), 'pm');

		self::assertArrayHasKey('private_msg', $declared, 'pm_sql.php no longer declares private_msg');

		$created = $db->schema()->createTableRaw('private_msg',
			$db->createQueryBuilder()->raw($declared['private_msg']->getBody()));

		self::assertNotEmpty($created, 'Could not create the private_msg table pm_sql.php declares');

		$this->createdTable = true;
	}
}
