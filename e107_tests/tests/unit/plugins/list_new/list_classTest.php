<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The states a fresh install puts list_new in, none of which the admin page
 * ever reaches: preferences that have never been written, a section with
 * nothing new to list, a section whose plugin has been uninstalled, and the
 * day selector the 'new' page is built around.
 *
 * Four of the five run in a subprocess, because the plugin's files, its
 * template and its preferences are each loaded or written once per process:
 * in-process they would be measuring whatever the rest of a shuffled suite
 * had already defined, included and stored.
 */
class list_classTest extends \Test\Unit
{
	const DONE = '@@e107help-returned@@';

	/**
	 * Runs $php in a booted CLI process that reports everything, and returns what it wrote.
	 *
	 * @param string $php statements to run once class2.php has booted
	 * @return string stdout and stderr interleaved
	 */
	private function probe($php)
	{
		list($output, $status) = $this->runInBootedCli("error_reporting(E_ALL); ".$php." echo '".self::DONE."';");

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the call never returned:\n".$printed);
		self::assertStringContainsString(self::DONE, $printed, "the call never returned:\n".$printed);

		return $printed;
	}

	/**
	 * A front-end page is the first thing a fresh install renders, and it is the
	 * caller that builds and saves the defaults. Everything they are built from
	 * has to be in place by then: the section list, and the captions, which live
	 * in the language file only the admin page loads.
	 */
	public function testTheDefaultPreferencesAreBuiltBeforeTheAdminPageHasBeenOpened()
	{
		$php = "require_once(e_PLUGIN.'list_new/list_class.php'); \$rc = new listclass(); "
			."\$prf = \$rc->getDefaultPrefs(); "
			."echo '<<'.\$prf['new_menu_caption'].'|'.\$prf['recent_page_caption']"
			.".'|'.(isset(\$prf['news_new_page_display']) ? 'sections' : 'no sections')"
			.".'|'.\$prf['recent_page_welcometext'].'>>'; ";

		$printed = $this->probe($php);
		$matches = array();

		self::assertDoesNotMatchRegularExpression('/TypeError|count\(\)|Undefined variable|undefined constant/i', $printed,
			"the defaults are built from a section list and a language file nothing on this path has loaded:\n".$printed);

		self::assertSame(1, preg_match('/<<(.*)>>/s', $printed, $matches), "the probe printed no preferences:\n".$printed);

		$answers = explode('|', $matches[1]);

		self::assertSame('sections', $answers[2], 'the per-section preferences are what the sections are listed for');
		self::assertNotSame('LIST_ADMIN_15', $answers[0], 'an unloaded language file stores the constant name as the caption');
		self::assertNotSame('LIST_ADMIN_14', $answers[1], 'an unloaded language file stores the constant name as the caption');
		self::assertStringNotContainsString('SITENAME', $answers[3],
			'the welcome text saved into the site\'s preferences greets the visitor with the constant name');
	}

	/**
	 * "New since your last visit" is empty for a visitor who has no last visit,
	 * which is every guest, so the empty row is the common case rather than the
	 * odd one. The template reads the same fields there as it does for a record.
	 *
	 * In a subprocess as well: the section file and the template are included
	 * once per process, and their globals are whatever the rest of a shuffled
	 * suite has left behind by the time this runs.
	 */
	public function testASectionWithNothingToListRendersTheFieldsTheTemplateReads()
	{
		$php = "require_once(e_PLUGIN.'list_new/list_class.php'); "
			."\$rc = new listclass(); \$rc->mode = 'new_page'; "
			."\$rc->list_pref = array('new_page_showempty' => '1', 'new_page_icon_use' => '1', "
			."'new_page_icon_default' => '1', 'new_page_char_heading' => '', 'new_page_char_postfix' => '', "
			."'new_page_datestyle' => '%d %b', 'new_page_datestyletoday' => '%H:%M'); "
			."\$rc->shortcodes->list_pref = \$rc->list_pref; "
			."\$text = \$rc->displaySection(array('section' => 'comment', 'caption' => 'Comments', "
			."'open' => '1', 'icon' => '', 'amount' => '5', 'author' => '1', 'category' => '1', 'date' => '1')); "
			."echo '<<'.(strpos(\$text, LIST_COMMENT_2) === false ? 'nothing' : 'the section')"
			.".'|'.(\$rc->row === \$rc->shortcodes->row ? 'one row' : 'two rows').'>>'; ";

		$printed = $this->probe($php);
		$matches = array();

		self::assertSame(0, preg_match('/Undefined array key/i', $printed),
			"the template reads fields the empty section's row does not carry:\n".$printed);

		self::assertSame(1, preg_match('/<<(.*)>>/s', $printed, $matches), "the probe printed nothing:\n".$printed);

		$answers = explode('|', $matches[1]);

		self::assertSame('the section', $answers[0], 'a guest has no last visit, so the section has nothing new to show');
		self::assertSame('one row', $answers[1], 'the shortcodes render from the row the section prepared');
	}

	/**
	 * A section stays in the stored preferences after the plugin behind it is
	 * uninstalled, and the page still has to render for everything else.
	 */
	public function testASectionWhoseProviderIsGoneLeavesTheRestOfThePageStanding()
	{
		require_once(e_PLUGIN.'list_new/list_class.php');

		$rc = new listclass();
		$rc->mode = 'new_page';
		$rc->list_pref = array('new_page_showempty' => '1');
		$rc->shortcodes->list_pref = $rc->list_pref;

		$text = $rc->displaySection(array(
			'section'  => 'e107help_uninstalled_section',
			'caption'  => 'Gone',
			'open'     => '1',
			'icon'     => '',
			'amount'   => '5',
			'author'   => '1',
			'category' => '1',
			'date'     => '1',
		));

		self::assertIsString($text, 'a section with no plugin behind it stops the page instead of rendering nothing');
	}

	/**
	 * The selector is assembled into the list class's own row, and the only
	 * shortcode that prints it reads the row on the shortcode object, so the
	 * control the 'new' page is built around renders as an empty div unless
	 * the row is handed over.
	 */
	public function testTheTimelapseSelectorReachesTheShortcodeThatPrintsIt()
	{
		$php = "require_once(e_PLUGIN.'list_new/list_class.php'); require_once(e_HANDLER.'form_handler.php'); "
			."\$rs = new form(); \$rc = new listclass(); "
			."\$rc->list_pref = array('new_page_timelapse' => '1', 'new_page_timelapse_days' => '3'); "
			."\$text = \$rc->displayTimelapse(); "
			."echo '<<'.(strpos(\$text, LIST_MENU_6) === false ? 'nothing' : 'the label')"
			.".'|'.(strpos(\$text, '<select') === false ? 'no days' : 'the days').'>>'; ";

		$printed = $this->probe($php);
		$matches = array();

		self::assertSame(1, preg_match('/<<(.*)>>/s', $printed, $matches), "the probe printed nothing:\n".$printed);

		$answers = explode('|', $matches[1]);

		self::assertSame('the label', $answers[0], 'the selector renders as an empty div');
		self::assertSame('the days', $answers[1], 'the selector renders without the days to choose from');
	}

	/**
	 * The defaults the front end builds have to survive the request that built
	 * them; until they are stored, every request rebuilds them from scratch.
	 */
	public function testTheDefaultPreferencesAreStoredOnceTheFrontEndHasBuiltThem()
	{
		$php = "e107::getPlugConfig('list_new')->reset()->save(false, true, false); "
			."require_once(e_PLUGIN.'list_new/list_class.php'); \$rc = new listclass(); \$rc->getListPrefs(); "
			."\$stored = e107::getDb()->createQueryBuilder()->select('e107_value')->from('core')"
			."->where('e107_name', 'plugin_list_new')->fetchOne(); "
			."echo '<<'.(strpos((string) \$stored, 'recent_page_caption') === false ? 'nothing stored' : 'stored').'>>'; ";

		$printed = $this->probe($php);
		$matches = array();

		self::assertSame(1, preg_match('/<<(.*)>>/s', $printed, $matches), "the probe printed nothing:\n".$printed);
		self::assertSame('stored', $matches[1],
			"the preferences the front end built are gone again by the next request:\n".$printed);
	}
}
