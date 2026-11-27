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
	use \Test\FeatureboxItems;
	use \Test\FeatureboxColumns;

	const TITLE = 'featurebox_adminScriptAddressTest';

	protected function _before()
	{
		$this->addFeatureboxColumns();
	}

	protected function _after()
	{
		e107::getDb()->createQueryBuilder()->delete('featurebox')->whereLike('fb_title', self::TITLE.'%')->execute();
		$this->restoreFeatureboxColumns();
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
		return $this->saveFeatureboxItem($scriptClass, 0, self::featureboxItemForm(array('fb_imageurl' => $link)));
	}

	/**
	 * @param int $id
	 * @param string $link
	 * @param string $text
	 * @return string the page
	 */
	private function update($id, $link, $text)
	{
		return $this->saveFeatureboxItem(e_UC_NOBODY, $id, self::featureboxItemForm(array('fb_imageurl' => $link, 'fb_text' => $text)));
	}

	/**
	 * @param string $link
	 * @return int
	 */
	private function seedItem($link)
	{
		return self::seedFeatureboxItem(array('fb_imageurl' => $link));
	}

	/**
	 * @return array the image links of the items this test saved
	 */
	private function storedLinks()
	{
		return self::featureboxItemsColumn('fb_imageurl');
	}
}
