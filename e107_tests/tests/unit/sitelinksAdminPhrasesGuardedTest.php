<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The admin navigation names its categories and its news sub-links in phrases a
 * language pack supplies, and core ships English only: a pack older than core,
 * an incomplete one or a 1.x-era one carries none of them, and from PHP 8 the
 * first bare read of one is a fatal that takes the whole admin area down.
 *
 * Each case runs in a subprocess, because a constant cannot be undefined once
 * the process has defined it. adminCats() loads no language file of its own on
 * this branch, so a boot with no_lan leaves every phrase it names missing, and
 * the phrases adminLinks('sub') names live in admin/lan_admin.php, which
 * nothing outside the admin area loads at all.
 */
class sitelinksAdminPhrasesGuardedTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const BEGIN = '@@e107help-phrases-begin@@';
	const END = '@@e107help-phrases-end@@';

	/**
	 * Runs $php in a booted CLI process that reports everything, and returns what it printed between the markers.
	 *
	 * @param string $php statements to run once class2.php has booted
	 * @param bool $noLan boot without the global language files, as a pack missing the phrase leaves the read
	 * @return array the phrases the child printed
	 */
	private function phrases($php, $noLan = false)
	{
		$e107 = array('cli' => true);
		if($noLan)
		{
			$e107['no_lan'] = true;
		}

		list($output, $status) = $this->runInBootedCli('error_reporting(E_ALL); '.$php, '', $e107);

		$printed = implode("\n", $output);

		$this->assertSame(0, $status, "the admin navigation never returned:\n".$printed);
		$this->assertSame(0, preg_match('/undefined constant/i', $printed),
			"a phrase the language pack does not carry must not be read bare:\n".$printed);

		$matches = array();
		$this->assertSame(1, preg_match('/'.self::BEGIN.'(.*)'.self::END.'/s', $printed, $matches),
			"the navigation printed no phrases:\n".$printed);

		return explode('|', $matches[1]);
	}

	/**
	 * @param int $separatePlugins the pref that decides whether category six is the plugin menu or the misc one
	 * @return array the category titles in the order the method builds them
	 */
	private function categoryTitles($separatePlugins)
	{
		$php = "e107::getConfig('core')->setPref('admin_separate_plugins', $separatePlugins); "
			."\$cats = e107::getNav()->adminCats(); "
			."echo '".self::BEGIN."', implode('|', \$cats['title']), '".self::END."';";

		return $this->phrases($php, true);
	}

	public function testAdminCatsNamesItsCategoriesWithoutTheGlobalLanguageFiles()
	{
		$this->assertSame(array('Settings', 'Users', 'Content', 'Tools', 'Manage', 'Misc', 'About'),
			$this->categoryTitles(0),
			'every category title falls back to its English text rather than to the constant name');

		$this->assertSame(array('Settings', 'Users', 'Content', 'Tools', 'Manage', 'Plugins', 'About'),
			$this->categoryTitles(1),
			'the separate plugin menu falls back too, on the branch the default pref does not take');
	}

	public function testAdminSubLinksNameTheNewsRoutesWithoutTheAdminLanguageFile()
	{
		$php = "\$rows = e107::getNav()->adminLinks('sub'); \$phrases = array(); "
			."foreach(\$rows[17] as \$row) { \$phrases[] = \$row[1]; \$phrases[] = \$row[2]; } "
			."echo '".self::BEGIN."', implode('|', \$phrases), '".self::END."';";

		$phrases = $this->phrases($php);

		$this->assertSame(array('Manage', 'News items List', 'Create', 'Create news item', 'Preferences', 'Preferences'),
			$phrases, 'each news sub-link falls back to its English text rather than to the constant name');
	}
}
