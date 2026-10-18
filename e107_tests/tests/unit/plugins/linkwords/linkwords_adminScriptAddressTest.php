<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Saves linkwords through the plugin's admin area, installing the plugin for its table when the site lacks it.
 */
class linkwords_adminScriptAddressTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ScriptClassPages;

	const WORD = 'linkwordsadminscriptaddresstest';

	/** @var bool whether this test installed the plugin */
	private $installed = false;

	protected function _before()
	{
		if(!e107::getDb()->gen("SHOW TABLES LIKE '".MPREFIX."linkwords'"))
		{
			e107::getPlugin()->install('linkwords');
			$this->installed = true;
		}
	}

	protected function _after()
	{
		e107::getDb()->delete('linkwords', "linkword_word LIKE '".self::WORD."%'");

		if($this->installed)
		{
			e107::getPlugin()->uninstall('linkwords', array('delete_tables' => true));
		}
	}

	public function testALinkThatRunsScriptIsRefusedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'javascript:void(0)');

		self::assertSame(array(), $this->storedLinks(), "the linkword was saved:\n".$page);
		self::assertStringContainsString("<div class='s-message-item'><strong>Link</strong> - ", $page, 'the refusal has to name the field');
	}

	public function testThePostScriptClassMaySaveALinkThatRunsScript()
	{
		$page = $this->create(e_UC_MAINADMIN, 'javascript:void(0)');

		self::assertSame(array('javascript:void(0)'), $this->storedLinks(), "the linkword was refused:\n".$page);
	}

	public function testALinkThatRunsNoScriptIsSavedOutsideThePostScriptClass()
	{
		$page = $this->create(e_UC_NOBODY, 'https://e107.org/');

		self::assertSame(array('https://e107.org/'), $this->storedLinks(), "the linkword was refused:\n".$page);
	}

	public function testAnInlineEditCannotGiveALinkwordALinkThatRunsScript()
	{
		$id = $this->seedLinkword('https://e107.org/');

		$this->editInline($id, 'javascript:void(0)');
		self::assertSame(array('https://e107.org/'), $this->storedLinks());

		$this->editInline($id, 'news.php');
		self::assertSame(array('news.php'), $this->storedLinks(), 'an inline edit of the link has to save it');
	}

	/**
	 * The edit form is opened first, and what it shows is posted back with a tooltip added.
	 *
	 * @dataProvider untouchedStoredAddresses
	 * @param string $address
	 */
	public function testAStoredLinkPassesUnchanged($address)
	{
		$id = $this->seedLinkword($address);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=edit&id='.$id));

		self::assertSame($address, $shown['linkword_link'], 'the form has to show the stored link');

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=edit&id='.$id,
			$this->linkword($shown['linkword_link'], 'Tip') + array('etrigger_submit' => 'update'));

		$row = e107::getDb()->retrieve('linkwords', '*', 'linkword_id='.(int) $id);

		self::assertSame('Tip', $row['linkword_tooltip'], "the unchanged link was refused:\n".$page);
		self::assertSame($address, $row['linkword_link']);
	}

	/**
	 * Saving the form again runs the link through toDB() once more, which would store one that runs script.
	 */
	public function testAStoredLinkThatSavingAgainWouldMakeRunScriptIsRefused()
	{
		$id = $this->seedLinkword(self::$inertScriptAddress);
		$shown = $this->inputValues($this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=edit&id='.$id));

		$page = $this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=edit&id='.$id,
			$this->linkword($shown['linkword_link'], 'Tip') + array('etrigger_submit' => 'update'));

		self::assertSame(array(self::$inertScriptAddress), $this->storedLinks(), "the linkword was saved:\n".$page);
	}

	/**
	 * @param int $scriptClass the class that may post script
	 * @param string $link
	 * @return string the page
	 */
	private function create($scriptClass, $link)
	{
		return $this->openAsScriptClass($scriptClass, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=create',
			$this->linkword($link, '') + array('etrigger_submit' => 'create'));
	}

	/**
	 * @param string $link
	 * @param string $tooltip
	 * @return array the form a linkword posts
	 */
	private function linkword($link, $tooltip)
	{
		return array(
			'linkword_word'      => self::WORD,
			'linkword_link'      => $link,
			'linkword_active'    => 1,
			'linkword_tooltip'   => $tooltip,
			'linkword_limit'     => 3,
			'linkword_tip_id'    => 0,
			'linkword_newwindow' => 0,
			'linkword_rel'       => '',
		);
	}

	/**
	 * @param int $id
	 * @param string $link
	 */
	private function editInline($id, $link)
	{
		$this->openAsScriptClass(e_UC_NOBODY, 'e107_plugins/linkwords/admin_config.php', 'mode=main&action=inline&id='.$id,
			array('name' => 'linkword_link', 'value' => $link, 'pk' => $id), '', true);
	}

	/**
	 * @param string $link
	 * @return int
	 */
	private function seedLinkword($link)
	{
		$id = (int) e107::getDb()->insert('linkwords', array(
			'linkword_active'  => 1,
			'linkword_word'    => self::WORD,
			'linkword_link'    => $link,
			'linkword_tooltip' => '',
		));

		self::assertGreaterThan(0, $id, 'the linkword was not seeded');

		return $id;
	}

	/**
	 * @return array the links of the linkwords this test saved
	 */
	private function storedLinks()
	{
		$rows = (e107::getDb()->retrieve('linkwords', 'linkword_link', "linkword_word LIKE '".self::WORD."%'", true) ?: array());

		return array_map(function ($row) { return $row['linkword_link']; }, $rows);
	}
}
