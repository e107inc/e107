<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves download mirrors through the download admin's mirror pages, and through the older mirror form that its main
 * pages still take, installing the plugin for its tables when the site lacks it.
 */
class download_mirrorScriptAddressTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const NAME = 'download_mirrorScriptAddressTest';

	/** @var bool whether this test installed the plugin */
	private $installed = false;

	protected function _before()
	{
		if(!e107::getDb()->execute("SHOW TABLES LIKE '".MPREFIX."download_mirror'"))
		{
			e107::getPlugin()->install('download');
			$this->installed = true;
		}
	}

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('download_mirror')->whereLike('mirror_name', self::NAME.'%')->execute();

		if($this->installed)
		{
			e107::getPlugin()->uninstall('download', array('delete_tables' => true));
		}
	}

	public function testAMirrorAddressThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'javascript:void(0)');

		self::assertSame(array(), $this->storedAddresses(), "the mirror was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>URL</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveAMirrorAddressThatRunsScript()
	{
		$page = $this->create(e_UC_MAINADMIN, 'javascript:void(0)');

		self::assertSame(array('javascript:void(0)'), $this->storedAddresses(), "the mirror was refused:\n".$page);
	}

	public function testAMirrorAddressThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'https://e107.org/');

		self::assertSame(array('https://e107.org/'), $this->storedAddresses(), "the mirror was refused:\n".$page);
	}

	public function testAnInlineEditCannotGiveAMirrorAnAddressThatRunsScript()
	{
		$id = $this->seedMirror('https://e107.org/');

		$this->editInline($id, 'javascript:void(0)');
		self::assertSame(array('https://e107.org/'), $this->storedAddresses());

		$this->editInline($id, 'https://e107.org/other');
		self::assertSame(array('https://e107.org/other'), $this->storedAddresses(), 'an inline edit of the address has to save it');
	}

	/**
	 * The edit form is opened first, and what it shows is posted back with a location added.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredMirrorAddressPassesUnchanged($address)
	{
		$id = $this->seedMirror($address);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=edit&id='.$id));

		self::assertSame($address, $shown['mirror_url'], 'the form has to show the stored address');

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=edit&id='.$id,
			$this->mirror($shown['mirror_url'], 'Here') + array('etrigger_submit' => 'update'));

		$row = e107::getDb()->createQueryBuilder()->select('*')->from('download_mirror')->where('mirror_id', $id)->fetchRow();

		self::assertSame('Here', $row['mirror_location'], "the unchanged address was refused:\n".$page);
		self::assertSame($address, $row['mirror_url']);
	}

	/**
	 * Saving the form again runs the address through toDB() once more, which would store one that runs script.
	 */
	public function testAStoredMirrorAddressThatSavingAgainWouldMakeRunScriptIsRefused()
	{
		$id = $this->seedMirror(self::$inertScriptAddress);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=edit&id='.$id));

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=edit&id='.$id,
			$this->mirror($shown['mirror_url'], 'Here') + array('etrigger_submit' => 'update'));

		self::assertSame(array(self::$inertScriptAddress), $this->storedAddresses(), "the mirror was saved:\n".$page);
	}

	public function testTheOlderMirrorFormRefusesAnAddressThatRunsScriptOutsideThePostScriptClass()
	{
		$page = $this->submitOlderForm('javascript:void(0)');

		self::assertSame(array(), $this->storedAddresses(), "the mirror was saved:\n".$page);

		$page = $this->submitOlderForm('https://e107.org/');

		self::assertSame(array('https://e107.org/'), $this->storedAddresses(), "the mirror was refused:\n".$page);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param string $address
	 * @return string the page
	 */
	private function create($scriptClass, $address)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=create',
			$this->mirror($address, '') + array('etrigger_submit' => 'create'));
	}

	/**
	 * @param string $address
	 * @return string the page
	 */
	private function submitOlderForm($address)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=main&action=mirror',
			array('submit_mirror' => 1, 'mirror_name' => self::NAME, 'mirror_url' => $address, 'mirror_image' => '', 'mirror_location' => '', 'mirror_description' => ''));
	}

	/**
	 * @param string $address
	 * @param string $location
	 * @return array the form a mirror posts
	 */
	private function mirror($address, $location)
	{
		return array(
			'mirror_name'        => self::NAME,
			'mirror_url'         => $address,
			'mirror_image'       => '',
			'mirror_location'    => $location,
			'mirror_description' => '',
		);
	}

	/**
	 * @param int $id
	 * @param string $address
	 */
	private function editInline($id, $address)
	{
		$this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/download/admin_download.php', 'mode=mirror&action=inline&id='.$id,
			array('name' => 'mirror_url', 'value' => $address, 'pk' => $id), '', true);
	}

	/**
	 * @param string $address
	 * @return int
	 */
	private function seedMirror($address)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('download_mirror')->insertGetId(array(
			'mirror_name'        => self::NAME,
			'mirror_url'         => $address,
			'mirror_description' => '',
		));

		self::assertGreaterThan(0, $id, 'the mirror was not seeded');

		return $id;
	}

	/**
	 * @return array the addresses of the mirrors this test saved
	 */
	private function storedAddresses()
	{
		$rows = e107::getDb()->createQueryBuilder()->select('mirror_url')->from('download_mirror')->whereLike('mirror_name', self::NAME.'%')->fetchAll();

		return array_map(function ($row) { return $row['mirror_url']; }, $rows);
	}
}
