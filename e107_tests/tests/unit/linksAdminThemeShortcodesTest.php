<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Opens e107_admin/links.php in an admin-area child whose site theme is a fixture, so only the fixture can declare theme_shortcodes.
 */
class linksAdminThemeShortcodesTest extends \Test\Unit
{
	/** @var callable puts back the stored core preferences, which the admin page the child opens may save with its site theme */
	private $restorePrefs;

	/** @var string the fixture theme's folder under e107_themes */
	private $theme;

	protected function _before()
	{
		$this->restorePrefs = $this->keepStoredCorePrefs();
		$this->theme = 'e107help_'.uniqid();
	}

	protected function _after()
	{
		call_user_func($this->restorePrefs);
	}

	public function testAThemeShortcodesFileWithoutItsClassAddsNoLinkFunctions()
	{
		$page = $this->createFormWithThemeShortcodes("<?php\n");

		self::assertSame(0, preg_match("/(Fatal error|Warning)[^\n]*e107_admin\/links\.php/", $page), "links.php failed:\n".$page);
		self::assertSame(1, preg_match("/<select[^>]* name=['\"]link_function['\"]/", $page), "the form has no link function list:\n".$page);
		self::assertSame(0, preg_match("/value=['\"]theme::/", $page), "a theme link function was listed:\n".$page);
	}

	public function testTheThemeShortcodesClassGivesALinkFunctionPerShortcodeInNameOrder()
	{
		$page = $this->createFormWithThemeShortcodes("<?php\nclass theme_shortcodes\n{\n\tfunction sc_e107help_zulu() {}\n\tfunction e107help_helper() {}\n\tfunction sc_e107help_alpha() {}\n}\n");

		$inOrder = '/'.self::linkFunctionOption('sc_e107help_alpha', 'e107help_alpha').'\s*'.self::linkFunctionOption('sc_e107help_zulu', 'e107help_zulu').'/';
		self::assertSame(1, preg_match($inOrder, $page), "the theme's shortcodes are not listed as link functions, in name order:\n".$page);
		self::assertStringNotContainsString('e107help_helper', $page);
	}

	public function testTheOwnerFilterListsTheLinkOwnersWhenTheThemeHasNoShortcodesFile()
	{
		$this->writeThemeFile('theme.php', "<?php\n");

		$page = $this->render('mode=main&action=list');

		self::assertSame(1, preg_match("/<option value=['\"]link_owner__news['\"]/", $page), "the owner filter does not list the owner of the sample site's News link:\n".$page);
	}

	/**
	 * @param string $source the fixture theme's theme_shortcodes.php
	 * @return string the create form
	 */
	private function createFormWithThemeShortcodes($source)
	{
		$this->writeThemeFile('theme_shortcodes.php', $source);

		return $this->render('mode=main&action=create');
	}

	/**
	 * @param string $name
	 * @param string $contents
	 * @return void
	 */
	private function writeThemeFile($name, $contents)
	{
		$this->writeAppFile('e107_themes/'.$this->theme.'/'.$name, $contents);
	}

	/**
	 * @param string $method
	 * @param string $label
	 * @return string a pattern, without delimiters, for the link function option a theme shortcode method gives
	 */
	private static function linkFunctionOption($method, $label)
	{
		return "<option value=['\"]theme::".$method."['\"]>".$label.'<\/option>';
	}

	/**
	 * @param string $query
	 * @return string the page
	 */
	private function render($query)
	{
		$seed = 'e107::getConfig()->set('."'sitetheme', ".var_export($this->theme, true).'); ';

		return $this->renderInBootedCli('e107_admin/links.php', $seed, self::BUFFERED, array('e_ADMIN_AREA' => true, 'e_QUERY' => $query));
	}
}
