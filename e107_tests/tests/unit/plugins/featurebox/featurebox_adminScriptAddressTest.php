<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves feature box items through the plugin's admin area.
 */
class featurebox_adminScriptAddressTest extends \Test\Unit
{
	use \Test\ScriptClassPages;

	const TITLE = 'featurebox_adminScriptAddressTest';

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('featurebox')->whereLike('fb_title', self::TITLE.'%')->execute();
	}

	public function testAnImageLinkThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'javascript:void(0)');

		self::assertSame(array(), $this->storedLinks(), "the item was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Image Link</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveAnImageLinkThatRunsScript()
	{
		$page = $this->create(e_UC_MAINADMIN, 'javascript:void(0)');

		self::assertSame(array('javascript:void(0)'), $this->storedLinks(), "the item was refused:\n".$page);
	}

	public function testAnImageLinkThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'https://e107.org/');

		self::assertSame(array('https://e107.org/'), $this->storedLinks(), "the item was refused:\n".$page);
	}

	public function testAnEditCannotGiveAnItemAnImageLinkThatRunsScript()
	{
		$id = $this->seedItem('https://e107.org/');

		$page = $this->update($id, 'javascript:void(0)', 'Changed');

		self::assertSame(array('https://e107.org/'), $this->storedLinks(), "the image link was saved:\n".$page);
	}

	/**
	 * The edit form is opened first, and what it shows is posted back with the text changed.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredImageLinkPassesUnchanged($address)
	{
		$id = $this->seedItem($address);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id));

		self::assertSame($address, $shown['fb_imageurl'], 'the form has to show the stored image link');

		$page = $this->update($id, $shown['fb_imageurl'], 'Changed');
		$row = e107::getDb()->createQueryBuilder()->select('*')->from('featurebox')->where('fb_id', $id)->fetchRow();

		self::assertSame('Changed', $row['fb_text'], "the unchanged image link was refused:\n".$page);
		self::assertSame($address, $row['fb_imageurl']);
	}

	/**
	 * Saving the form again runs the image link through toDB() once more, which would store one that runs script.
	 */
	public function testAStoredImageLinkThatSavingAgainWouldMakeRunScriptIsRefused()
	{
		$id = $this->seedItem(self::$inertScriptAddress);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id));

		$page = $this->update($id, $shown['fb_imageurl'], 'Changed');

		self::assertSame(array(self::$inertScriptAddress), $this->storedLinks(), "the item was saved:\n".$page);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param string $link
	 * @return string the page
	 */
	private function create($scriptClass, $link)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=create',
			$this->item($link, 'Text') + array('etrigger_submit' => 'create'));
	}

	/**
	 * @param int $id
	 * @param string $link
	 * @param string $text
	 * @return string the page
	 */
	private function update($id, $link, $text)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/featurebox/admin_config.php', 'mode=main&action=edit&id='.$id,
			$this->item($link, $text) + array('etrigger_submit' => 'update'));
	}

	/**
	 * @param string $link
	 * @param string $text
	 * @return array the form an item posts
	 */
	private function item($link, $text)
	{
		return array(
			'fb_category' => 1,
			'fb_title'    => self::TITLE,
			'fb_image'    => '',
			'fb_text'     => $text,
			'fb_template' => 'default',
			'fb_imageurl' => $link,
			'fb_class'    => e_UC_PUBLIC,
			'fb_order'    => 1,
		);
	}

	/**
	 * @param string $link
	 * @return int
	 */
	private function seedItem($link)
	{
		$id = (int) e107::getDb()->createQueryBuilder()->insert('featurebox')->insertGetId(array(
			'fb_title'    => self::TITLE,
			'fb_text'     => 'Text',
			'fb_template' => 'default',
			'fb_imageurl' => $link,
			'fb_category' => 1,
		));

		self::assertGreaterThan(0, $id, 'the item was not seeded');

		return $id;
	}

	/**
	 * @return array the image links of the items this test saved
	 */
	private function storedLinks()
	{
		$rows = e107::getDb()->createQueryBuilder()->select('fb_imageurl')->from('featurebox')->whereLike('fb_title', self::TITLE.'%')->fetchAll();

		return array_map(function ($row) { return $row['fb_imageurl']; }, $rows);
	}
}
