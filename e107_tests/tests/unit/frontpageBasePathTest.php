<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Admin > Front Page keeps the slashes in a plugin page's path and in a page's title when e_HTTP is '/' (#6894).
 */
class frontpageBasePathTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\CorePrefs;

	const NAME = 'frontpageBasePathTest';

	const TITLE = 'frontpageBasePathTest/title';

	/** @var callable puts back the stored core preferences */
	private $restorePrefs;

	protected function _before()
	{
		$this->restorePrefs = $this->keepStoredCorePrefs();
	}

	protected function _after()
	{
		e107::getDb()->delete('page', "page_title = '".self::TITLE."'");

		call_user_func($this->restorePrefs);
	}

	public function testAPluginPageIsSavedWithTheSlashesInItsPath()
	{
		$page = $this->open(array(
			'fp_save_new'   => '1',
			'fp_order'      => '0',
			'class'         => (string) e_UC_PUBLIC,
			'frontpage'     => '_blank',
			'fp_force_page' => '_blank',
		));

		$stored = e107::unserialize(self::storedPrefsValue('SitePrefs'));

		self::assertSame(array(e_UC_PUBLIC => '{e_PLUGIN}_blank/_blank.php'), $stored['frontpage'], "the home page rule:\n".$page);
		self::assertSame(array(e_UC_PUBLIC => '{e_PLUGIN}_blank/_blank.php'), $stored['frontpage_force'], "the post-login page rule:\n".$page);
		self::assertStringNotContainsString('Fatal error', $page, "the page died after the save:\n".$page);
	}

	public function testACustomPageIsOfferedUnderItsTitleWithTheSlashesInIt()
	{
		$id = (int) e107::getDb()->insert('page', array(
			'page_title' => self::TITLE,
			'page_sef'   => 'frontpagebasepathtest',
			'page_text'  => 'A custom page the Front Page offers.',
			'page_class' => '0',
			'menu_name'  => '',
		));
		self::assertGreaterThan(0, $id, 'the page was not seeded');

		$page = $this->open(array(), array('id' => '1'));

		self::assertStringContainsString('>'.self::TITLE.'</option>', $page, 'the custom page has to be offered under its title');
	}

	/**
	 * @param array $post the fields the rule form posts, none to only open the page
	 * @param array $get the query, e.g. id=1 for the form that edits the first rule
	 * @return string the page
	 */
	private function open(array $post, array $get = array())
	{
		$seed = "define('SEP', ' &raquo; '); e107::getConfig()->setPref('e_frontpage_list/_blank', '_blank'); ";
		$seed .= "e107::plugLan('_blank', 'global', true); ";
		$seed .= '$_GET = '.var_export($get, true).'; ';
		$seed .= $post ? '$_POST = '.var_export($post, true)." + array('e-token' => defset('e_TOKEN')); " : '';

		return $this->renderInBootedCli('e107_admin/frontpage.php', $seed, self::buffered(), array('e_TOKEN' => self::NAME));
	}
}
