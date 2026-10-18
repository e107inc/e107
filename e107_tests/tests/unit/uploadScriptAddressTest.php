<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Submits uploads through upload.php as the CLI user, with uploads turned on and no file chosen, and edits them through e107_admin/upload.php.
 */
class uploadScriptAddressTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ScriptClassPages;

	const NAME = 'uploadScriptAddressTest';

	/** How upload.php thanks the uploader for an upload it took. */
	const TAKEN = 'Your upload will be reviewed by an administrator';

	protected function _after()
	{
		e107::getDb()->delete('upload', "upload_name LIKE '".self::NAME."%'");
	}

	/**
	 * @return array the fields an upload's addresses are posted in
	 */
	public function addressFields()
	{
		return array(
			'the website' => array('file_website'),
			'the demo'    => array('file_demo'),
		);
	}

	/**
	 * @dataProvider addressFields
	 * @param string $field
	 */
	public function testAnUploadWithAnAddressThatRunsScriptIsRefusedOutsideThePostScriptClass($field)
	{
		$page = $this->submit(e_UC_NOBODY, array($field => 'javascript:void(0)'));

		self::assertSame(array(), $this->storedUploads(), "the upload was saved:\n".$page);
		self::assertStringNotContainsString(self::TAKEN, $page, 'the upload was taken');
		self::assertStringContainsString(LAN_LINK_SCRIPT_SCHEME_REFUSED, $page, 'the refusal has to be shown');
	}

	/**
	 * @dataProvider addressFields
	 * @param string $field
	 */
	public function testThePostScriptClassMayUploadWithAnAddressThatRunsScript($field)
	{
		$page = $this->submit(e_UC_MAINADMIN, array($field => 'javascript:void(0)'));

		self::assertStringContainsString(self::TAKEN, $page, "the upload was refused:\n".$page);
	}

	public function testAnUploadWithAddressesThatRunNoScriptIsTakenOutsideThePostScriptClass()
	{
		$page = $this->submit(e_UC_NOBODY, array('file_website' => 'https://e107.org/', 'file_demo' => 'https://e107.org/demo'));

		self::assertStringContainsString(self::TAKEN, $page, "the upload was refused:\n".$page);
	}

	public function testAnAdministratorsCreateRefusesAnAddressThatRunsScript()
	{
		$page = $this->createAsAdministrator('javascript:void(0)');

		self::assertSame(array(), $this->storedUploads(), "the upload was saved:\n".$page);

		$page = $this->createAsAdministrator('https://e107.org/');

		self::assertCount(1, $this->storedUploads(), "the upload was refused:\n".$page);
	}

	public function testAnAdministratorsEditCannotGiveAnUploadAnAddressThatRunsScript()
	{
		$id = $this->seedUpload('https://e107.org/');

		$page = $this->edit($id, array('upload_name' => self::NAME.' renamed', 'upload_website' => 'javascript:void(0)'));

		self::assertSame(array(self::NAME), array_keys($this->storedUploads()), "the address was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>URL</strong> - ", $page, 'the refusal has to name the field');
	}

	/**
	 * The edit form is opened first, and what it shows is posted back under a new name.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredAddressPassesUnchangedThroughAnAdministratorsEdit($address)
	{
		$id = $this->seedUpload($address);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/upload.php', 'mode=main&action=edit&id='.$id));

		self::assertSame($address, $shown['upload_website'], 'the form has to show the stored address');

		$page = $this->edit($id, array('upload_name' => self::NAME.' renamed', 'upload_website' => $shown['upload_website'], 'upload_demo' => $shown['upload_demo']));
		$stored = $this->storedUploads();

		self::assertSame(array(self::NAME.' renamed'), array_keys($stored), "the unchanged address was refused:\n".$page);
		self::assertSame($address, $stored[self::NAME.' renamed']['upload_website']);
	}

	/**
	 * Saving the form again runs the address through toDB() once more, which would store one that runs script.
	 */
	public function testAStoredAddressThatSavingAgainWouldMakeRunScriptIsRefusedOnAnAdministratorsEdit()
	{
		$id = $this->seedUpload(self::$inertScriptAddress);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/upload.php', 'mode=main&action=edit&id='.$id));

		$page = $this->edit($id, array('upload_name' => self::NAME.' renamed', 'upload_website' => $shown['upload_website'], 'upload_demo' => $shown['upload_demo']));
		$stored = $this->storedUploads();

		self::assertSame(array(self::NAME), array_keys($stored), "the upload was saved:\n".$page);
		self::assertSame(self::$inertScriptAddress, $stored[self::NAME]['upload_website']);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param array $addresses the address fields posted
	 * @return string the page
	 */
	private function submit($scriptClass, array $addresses)
	{
		$seed = "\$pref['upload_enabled'] = 1; \$pref['upload_class'] = e_UC_PUBLIC; e107::getConfig()->set('upload_enabled', 1)->set('upload_class', e_UC_PUBLIC); e107::getUser()->set('user_hideemail', 1); "
			."\$_FILES = array('file_userfile' => array('name' => array(''), 'type' => array(''), 'tmp_name' => array(''), 'error' => array(UPLOAD_ERR_NO_FILE), 'size' => array(0))); ";

		return $this->openAsScriptClass($scriptClass, 'upload.php', '', $addresses + array(
			'upload'           => 1,
			'file_name'        => self::NAME,
			'file_description' => 'Description',
			'category'         => 'download__1',
			'file_email'       => 'upload@example.com',
			'file_version'     => '1',
			'file_website'     => '',
			'file_demo'        => '',
		), $seed);
	}

	/**
	 * Posts every column of the upload, since the model writes null for one left out.
	 *
	 * @param string $demo
	 * @return string the page
	 */
	private function createAsAdministrator($demo)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/upload.php', 'mode=main&action=create', array(
			'etrigger_submit'    => 'create',
			'upload_datestamp'   => time(),
			'upload_name'        => self::NAME,
			'upload_poster'      => 1,
			'upload_email'       => '',
			'upload_website'     => '',
			'upload_version'     => '',
			'upload_file'        => self::NAME.'.zip',
			'upload_ss'          => '',
			'upload_description' => '',
			'upload_demo'        => $demo,
			'upload_filesize'    => 0,
			'upload_active'      => 0,
			'upload_category'    => 1,
			'upload_owner'       => 'download',
		));
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return string the page
	 */
	private function edit($id, array $fields)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_admin/upload.php', 'mode=main&action=edit&id='.$id, $fields + array(
			'etrigger_submit'       => 'update',
			'__after_submit_action' => 'edit',
			'upload_file'           => self::NAME.'.zip',
			'upload_category'       => 'download__1',
			'upload_active'         => 0,
		));
	}

	/**
	 * @param string $website
	 * @return int
	 */
	private function seedUpload($website)
	{
		$id = (int) e107::getDb()->insert('upload', array(
			'upload_datestamp'   => time(),
			'upload_name'        => self::NAME,
			'upload_website'     => $website,
			'upload_file'        => self::NAME.'.zip',
			'upload_description' => '',
			'upload_active'      => 0,
			'upload_category'    => 1,
			'upload_owner'       => 'download',
		));

		self::assertGreaterThan(0, $id, 'the upload was not seeded');

		return $id;
	}

	/**
	 * @return array the uploads this test saved, by name
	 */
	private function storedUploads()
	{
		$uploads = array();

		foreach((e107::getDb()->retrieve('upload', '*', "upload_name LIKE '".self::NAME."%'", true) ?: array()) as $row)
		{
			$uploads[$row['upload_name']] = $row;
		}

		return $uploads;
	}
}
