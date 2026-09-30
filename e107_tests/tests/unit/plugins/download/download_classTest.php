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
 * download_shortcodes::breadcrumb() picks which breadcrumb to build from the
 * query state and the row the page hands the shortcode batch, and that batch is
 * one cached instance for the whole request. Every page here renders with a row
 * already in it, because a menu that rendered before the page controller is how
 * a visitor meets this, and because that row is what makes a page taking the
 * wrong branch visible at all.
 *
 * Each case runs in its own subprocess: the plugin's handlers exit unless the
 * plugin is installed, its templates and language files load once per process,
 * and the batch would otherwise carry a previous case's row.
 *
 * The mirror page is covered in two halves, because rendering it needs the
 * plugin's tables and a row inside them, which a fresh install has neither of:
 * the arm of breadcrumb() that page asks for is driven directly, and the order
 * in which the page fills the batch is read from the source. Installing the
 * plugin per case would buy the page itself, and belongs with the change that
 * needs the row for its own sake, which is the unfiltered fetch in #6587.
 */
class download_classTest extends \Test\Unit
{
	/** Printed once e107 has booted, so the boot's own diagnostics are not read as the plugin's. */
	const BUILDING = '@@e107help-building@@';

	const OPEN = '@@e107help-breadcrumb@@';

	const CLOSE = '@@e107help-rendered@@';

	/** The row a menu leaves in the shared batch before the page controller runs. */
	private static $leftInTheBatch = array(
		'download_id'             => 1,
		'download_name'           => 'A file from a menu',
		'download_sef'            => 'a-file-from-a-menu',
		'download_category_id'    => 1,
		'download_category_name'  => 'A category from a menu',
		'download_category_sef'   => 'a-category-from-a-menu',
		'download_category_class' => 0,
	);

	/** A download joined to its category, which is the shape the mirror page's own query returns. */
	private static $theMirrorPagesRow = array(
		'download_id'             => 42,
		'download_name'           => 'The file on this page',
		'download_sef'            => 'the-file-on-this-page',
		'download_category_id'    => 7,
		'download_category_name'  => 'The category of this page',
		'download_category_sef'   => 'the-category-of-this-page',
		'download_category_class' => 0,
	);

	/**
	 * Renders one Downloads page in a subprocess whose batch already holds a row, and returns what the page left in the breadcrumb.
	 *
	 * @param array $get the query string the page is opened with
	 * @return array the breadcrumb's texts under 'crumbs', and the phrases the page builds them from under 'plugin' and 'mirror'
	 */
	private function renderDownloadPage(array $get)
	{
		$php = "e107::plugLan('download', 'global', true); ";
		$php .= "require_once(e_PLUGIN.'download/handlers/download_class.php'); ";
		$php .= "require_once(e_PLUGIN.'download/handlers/category_class.php'); ";
		$php .= "e107::getScBatch('download', true)->setVars(".var_export(self::$leftInTheBatch, true)."); ";
		$php .= "\$dl = new download(); \$dl->init(); \$dl->load(); \$dl->render(); ";

		return $this->breadcrumbReportedBy($get, $php);
	}

	/**
	 * Runs $php in a child that has the plugin installed, and reads back the breadcrumb it left.
	 *
	 * What the child printed while it was building that breadcrumb has to be the
	 * breadcrumb and nothing else, which the boot's own diagnostics are kept out
	 * of by the marker. The labels refused are the ones the interpreters that
	 * phrase these conditions as warnings print: PHP 5.6 and 7.x call an
	 * undefined key a notice and are not held to this, and a deprecation is
	 * printed by the legacy database path on every page either branch serves.
	 *
	 * @param array $get the query string the child is opened with
	 * @param string $php statements that build a breadcrumb
	 * @return array as {@see download_classTest::renderDownloadPage()}
	 */
	private function breadcrumbReportedBy(array $get, $php)
	{
		$php = "echo '".self::BUILDING."'; ".$php;
		$php .= "\$texts = array(); foreach((array) e107::breadcrumb() as \$crumb) { \$texts[] = \$crumb['text']; } ";
		$php .= "echo '".self::OPEN."'.json_encode(array("
			."'crumbs' => \$texts, 'plugin' => LAN_PLUGIN_DOWNLOAD_NAME, 'mirror' => LAN_dl_67)).'".self::CLOSE."'; ";

		list($output, $status) = $this->bootPluginInCli('download', '1.3', $get, $php);

		$printed = implode("\n", $output);
		$matches = array();
		$pattern = '/'.preg_quote(self::BUILDING, '/').'(.*)'
			.preg_quote(self::OPEN, '/').'(.*)'.preg_quote(self::CLOSE, '/').'/s';

		self::assertSame(0, $status, "the child did not return:\n".$printed);
		self::assertSame(1, preg_match($pattern, $printed, $matches),
			"the child never reported a breadcrumb:\n".$printed);
		self::assertDoesNotMatchRegularExpression('/(^|\n)(PHP )?(Warning|Fatal error|Parse error|Uncaught)\b/', $matches[1],
			"building the breadcrumb printed a diagnostic:\n".$printed);

		$answer = json_decode($matches[2], true);

		self::assertNotNull($answer, "the child reported nothing that could be read back:\n".$printed);

		return $answer;
	}

	/**
	 * Where the body of $function first writes $statement, with whitespace closed up and comments left out, so neither indentation nor a commented-out line can answer for it.
	 *
	 * @param string $function method of e107_plugins/download/handlers/download_class.php
	 * @param string $statement source with its spaces removed, e.g. '$sc->qry='
	 * @return int offset into that body
	 */
	private function whereTheBodySays($function, $statement)
	{
		$source = '';

		foreach($this->functionBodyTokens(e_PLUGIN.'download/handlers/download_class.php', $function) as $token)
		{
			if(is_array($token) && ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT))
			{
				continue;
			}

			$source .= preg_replace('/\s+/', '', is_array($token) ? $token[1] : $token);
		}

		$at = strpos($source, $statement);

		self::assertNotFalse($at, $function.'() does not contain '.$statement);

		return $at;
	}

	/**
	 * The index asks for the 'maincats' breadcrumb, which is the plugin name on
	 * its own and reads no row at all. Anything else means it took the branch
	 * that reads whatever row the batch is carrying.
	 */
	public function testTheIndexBreadcrumbNamesTheDownloadsPageAndNothingElse()
	{
		$answer = $this->renderDownloadPage(array());

		self::assertSame(array($answer['plugin']), $answer['crumbs'],
			'the index breadcrumb is the plugin name, whatever another render left in the batch');
	}

	/**
	 * A mirror page for a download nobody can see renders nothing, so it has no
	 * file of its own to name and must not borrow one.
	 */
	public function testTheMirrorPageBreadcrumbDoesNotNameAnotherPagesFile()
	{
		$answer = $this->renderDownloadPage(array('action' => 'mirror', 'id' => '0'));

		self::assertNotContains(self::$leftInTheBatch['download_name'], $answer['crumbs'],
			'the mirror page named a file it never rendered');
		self::assertNotContains(self::$leftInTheBatch['download_category_name'], $answer['crumbs'],
			'the mirror page named a category it never rendered');
	}

	/**
	 * The same for an item page: when the row is missing the page says so, and
	 * the breadcrumb has to say the same.
	 */
	public function testTheItemPageBreadcrumbDoesNotNameAnotherPagesFile()
	{
		$answer = $this->renderDownloadPage(array('action' => 'view', 'id' => '0'));

		self::assertNotContains(self::$leftInTheBatch['download_name'], $answer['crumbs'],
			'the item page named a file it never rendered');
		self::assertNotContains(self::$leftInTheBatch['download_category_name'], $answer['crumbs'],
			'the item page named a category it never rendered');
	}

	/**
	 * The arm the mirror page asks for, driven with the row that page fetches. It
	 * reads that row where the fallback arm guards the same reads, so a field the
	 * row does not carry would print a diagnostic, which every case here refuses.
	 */
	public function testTheMirrorArmNamesTheCategoryTheFileAndTheMirrorChoice()
	{
		$php = "e107::plugLan('download', 'global', true); ";
		$php .= "\$sc = e107::getScBatch('download', true); ";
		$php .= "\$sc->qry = array('action' => 'mirror'); ";
		$php .= "\$sc->setVars(".var_export(self::$theMirrorPagesRow, true)."); ";
		$php .= "\$sc->breadcrumb(); ";

		$answer = $this->breadcrumbReportedBy(array('action' => 'mirror', 'id' => '42'), $php);

		self::assertSame(array(
			$answer['plugin'],
			self::$theMirrorPagesRow['download_category_name'],
			self::$theMirrorPagesRow['download_name'],
			$answer['mirror'],
		), $answer['crumbs'], 'the mirror breadcrumb walks from the index down to the mirror choice');
	}

	/**
	 * Which leaves the order the page asks in. Rendering that page needs the
	 * plugin's tables and a row in them, so the obligation is held against the
	 * source: the arm reads both the query state and the row, so the batch has
	 * to have both before the page asks.
	 */
	public function testTheMirrorPageFillsTheBatchBeforeItAsksForTheBreadcrumb()
	{
		$asks = $this->whereTheBodySays('renderMirror', '$sc->breadcrumb();');

		self::assertLessThan($asks, $this->whereTheBodySays('renderMirror', '$sc->qry=$this->qry;'),
			'the mirror arm is chosen by the query state, so the batch has to be given it first');
		self::assertLessThan($asks, $this->whereTheBodySays('renderMirror', '$sc->setVars($dlrow);'),
			'the mirror arm reads the row, so the batch has to be carrying it first');
	}
}
