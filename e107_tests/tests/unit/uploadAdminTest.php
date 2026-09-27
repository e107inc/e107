<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Drives e107_admin/upload.php in a booted child as the CLI main admin; e_QUERY is fixed at boot, so the query string goes in through the environment.
 *
 * @see https://github.com/e107inc/e107/issues/6385
 */
class uploadAdminTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** An owner no plugin registers an e_upload handler under. */
	const ORPHAN_OWNER = 'uploadAdminTestGone';

	/** An owner whose e_upload handler takes an upload into the upload table, as download's takes one into its own. */
	const HANDLED_OWNER = 'uploadAdminTestHandled';

	/** The name the handled owner gives the record it makes of an upload. */
	const TAKEN = 'uploadAdminTestTaken';

	const FILE = 'uploadAdminTest.zip';

	/** @var int */
	private $uploadId = 0;

	protected function _before()
	{
		$uploads = str_replace(realpath(APP_PATH).'/', '', realpath(e_UPLOAD).'/');
		$this->getModule('\Helper\Unit')->writeAppFile($uploads.self::FILE, 'uploadAdminTest');

		$this->uploadId = (int) e107::getDb()->insert('upload', array(
			'upload_datestamp'   => time(),
			'upload_name'        => 'uploadAdminTest',
			'upload_file'        => self::FILE,
			'upload_description' => '',
			'upload_active'      => 0,
			'upload_category'    => 1,
			'upload_owner'       => self::ORPHAN_OWNER,
		));

		self::assertGreaterThan(0, $this->uploadId, 'the upload row was not seeded');
	}

	protected function _after()
	{
		putenv('QUERY_STRING');
		putenv('HTTP_X_REQUESTED_WITH');

		$db = e107::getDb();
		$db->delete('upload', 'upload_id = '.(int) $this->uploadId);
		$db->delete('upload', "upload_name = '".self::TAKEN."'");

		foreach($db->retrieve('core_media', 'media_url', "media_caption = 'uploadAdminTest'", true) as $imported)
		{
			unlink(e107::getParser()->replaceConstants($imported['media_url']));
		}
		$db->delete('core_media', "media_caption = 'uploadAdminTest'");
	}

	public function testTheListShowsAnUploadWhoseOwnerIsGoneWithoutWarnings()
	{
		$page = $this->runPage('mode=main&action=list', '');

		self::assertStringContainsString('uploadAdminTest', $page, "the list never reached the seeded row:\n".$page);
		self::assertSame(0, preg_match('/(Undefined (array key|index)|foreach\(\))[^\n]*upload\.php/i', $page),
			"a category whose owner registers no categories has nothing to look up:\n".$page);
	}

	public function testBatchAcceptLeavesAnUploadWhoseOwnerIsGonePending()
	{
		$page = $this->acceptFromTheList('');

		$this->assertStillPending(self::ORPHAN_OWNER);
		$this->assertTheOwnerIsNamed($page, self::ORPHAN_OWNER);
	}

	/**
	 * Uploads from before owners were recorded are download's, so with no download handler registered the refusal names it.
	 */
	public function testBatchAcceptTakesAnUploadWithNoOwnerAsDownloads()
	{
		e107::getDb()->update('upload', array('data' => array('upload_owner' => ''), 'WHERE' => 'upload_id = '.(int) $this->uploadId));

		$page = $this->acceptFromTheList("e107::getConfig()->set('e_upload_list', array()); ");

		$this->assertStillPending('');
		$this->assertTheOwnerIsNamed($page, 'download');
	}

	/**
	 * The handled owner's table is the upload table itself, so the record it makes of the upload is a second upload row.
	 */
	public function testBatchAcceptHandsAnUploadToAnOwnerWithAHandler()
	{
		$this->writeOwner(self::HANDLED_OWNER, array(
			'config' => array('table' => 'upload', 'url' => self::TAKEN.'-{ID}'),
			'insert' => array('upload_name' => self::TAKEN, 'upload_file' => self::FILE, 'upload_description' => ''),
		));
		e107::getDb()->update('upload', array('data' => array('upload_owner' => self::HANDLED_OWNER), 'WHERE' => 'upload_id = '.(int) $this->uploadId));

		$page = $this->acceptFromTheList("e107::getConfig()->set('e_upload_list', array('".self::HANDLED_OWNER."' => '".self::HANDLED_OWNER."')); ");

		$taken = e107::getDb()->retrieve('upload', 'upload_id', "upload_name = '".self::TAKEN."'");

		self::assertSame(1, (int) $this->storedRow()['upload_active'], "an upload its owner's handler takes in is accepted:\n".$page);
		self::assertNotEmpty($taken, "the owner's handler was never given the upload:\n".$page);
		self::assertStringContainsString(self::TAKEN.'-'.$taken, $page, 'the administrator is linked to the record the upload became');
	}

	/**
	 * Posts what an edit form rendered before the owner's plugin was uninstalled posts, on a site that has download installed.
	 */
	public function testSavingTheEditFormAsAcceptedLeavesAnUploadWhoseOwnerIsGonePending()
	{
		$page = $this->runPage('mode=main&action=edit&id='.$this->uploadId,
			"\$installed = (array) e107::getConfig()->get('plug_installed'); \$installed['download'] = '1.0'; e107::getConfig()->set('plug_installed', \$installed); "
			."\$_POST = array('etrigger_submit' => 'update', '__after_submit_action' => 'edit', 'upload_name' => 'uploadAdminTest', "
			."'upload_file' => '".self::FILE."', 'upload_category' => '".self::ORPHAN_OWNER."__1', 'upload_active' => 1); ");

		$this->assertStillPending(self::ORPHAN_OWNER);
		$this->assertTheOwnerIsNamed($page, self::ORPHAN_OWNER);
	}

	/**
	 * An inline edit posts the one field it changed.
	 */
	public function testRenamingAnUploadInlineKeepsItsCategoryAndOwner()
	{
		putenv('HTTP_X_REQUESTED_WITH=XMLHttpRequest');

		$page = $this->runPage('mode=main&action=inline&id='.$this->uploadId,
			"\$_POST = array('name' => 'upload_name', 'value' => 'uploadAdminTestRenamed', 'pk' => ".$this->uploadId.", 'token' => password_hash(session_id(), PASSWORD_DEFAULT)); ");

		$row = $this->storedRow();

		self::assertSame('uploadAdminTestRenamed', $row['upload_name'], "the inline edit never saved:\n".$page);
		self::assertSame(1, (int) $row['upload_category'], 'renaming an upload must not change its category');
		self::assertSame(self::ORPHAN_OWNER, $row['upload_owner'], 'renaming an upload must not change its owner');
	}

	/**
	 * Writes an e_upload handler for $owner into the app, one method per key of $returns returning that key's value.
	 *
	 * @param string $owner
	 * @param array $returns
	 */
	private function writeOwner($owner, $returns)
	{
		$class = "<?php\nclass ".$owner."_upload\n{\n";
		foreach($returns as $method => $return)
		{
			$class .= "\tfunction ".$method."()\n\t{\n\t\treturn ".var_export($return, true).";\n\t}\n";
		}

		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/'.$owner.'/e_upload.php', $class."}\n");
	}

	/**
	 * Loads the admin upload page as the main admin and returns what it wrote.
	 *
	 * @param string $query the page's query string
	 * @param string $seed PHP run after boot and before the page is loaded
	 * @return string
	 */
	private function runPage($query, $seed)
	{
		putenv('QUERY_STRING='.$query);

		$php = "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= $seed;
		$php .= "require '".addslashes(APP_PATH)."/e107_admin/upload.php'; ";

		list($output, $status) = $this->runInBootedCli($php);
		$page = implode("\n", $output);

		self::assertSame(0, $status, "the page died:\n".$page);
		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);

		return $page;
	}

	/**
	 * Ticks the seeded upload in the list and applies the batch Accept.
	 *
	 * @param string $seed PHP run after boot and before the page is loaded
	 * @return string
	 */
	private function acceptFromTheList($seed)
	{
		return $this->runPage('mode=main&action=list',
			$seed."\$_POST = array('e__execute_batch' => 1, 'etrigger_batch' => 'upload_active', 'e-multiselect' => array(".$this->uploadId.")); ");
	}

	/**
	 * @param string $page
	 * @param string $owner
	 */
	private function assertTheOwnerIsNamed($page, $owner)
	{
		self::assertSame(1, preg_match("#<div class='s-message-item'>[^<]*\\b".$owner."\\b[^<]*</div>#", $page),
			"the administrator has to be told which owner stopped the upload:\n".$page);
	}

	/**
	 * @param string $owner
	 */
	private function assertStillPending($owner)
	{
		$row = $this->storedRow();

		self::assertSame(0, (int) $row['upload_active'], 'an upload no handler can take in must stay pending');
		self::assertSame($owner, $row['upload_owner'], 'the upload must keep its owner');
		self::assertFileExists(e_UPLOAD.self::FILE, 'the upload must not have been moved anywhere');
	}

	/**
	 * @return array
	 */
	private function storedRow()
	{
		return e107::getDb()->retrieve('upload', 'upload_name, upload_active, upload_category, upload_owner', 'upload_id = '.(int) $this->uploadId);
	}
}
