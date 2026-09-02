<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * sitelinks::getlinks() files a plugin-supplied submenu under sub_<parent
 * link_id>, but the rows inside carry ids the plugin minted, so a child's id
 * is not a key in the sitelink id space and must not be read as one.
 *
 * The stock install is such an arrangement: sitelink 6 "Content" runs
 * page::pagesFromChapter(2) and page 6 sits in chapter 2, so both renderers
 * that walk getLinkArray() called themselves with the id they were already
 * rendering, until PHP ran out of memory.
 *
 * Neither renderer can be driven in-process for that, because an unbounded
 * recursion takes the suite down with it. They run in a subprocess with a
 * small memory limit, the way eJslibCachePathTest and forumSubForumGuardTest
 * drive theirs, so the failure is a status code rather than a dead run.
 */
class sitelinksSubMenuTest extends \Codeception\Test\Unit
{

	const BEGIN = '@@e107help-markup-begin@@';
	const END   = '@@e107help-markup-end@@';

	/**
	 * @param int $id
	 * @param string $name
	 * @param int $parent the id this row hangs under, in whichever id space minted it
	 * @return array one row in the shape getLinkArray() hands out
	 */
	private static function row($id, $name, $parent)
	{
		return array(
			'link_id'     => $id,
			'link_name'   => $name,
			'link_url'    => 'p'.$id.'.php',
			'link_button' => '',
			'link_parent' => $parent,
			'link_open'   => '',
			'link_class'  => 0,
		);
	}

	/**
	 * The array getlinks() builds on a stock install, cut down to the two
	 * collisions a plugin bucket can produce. Bucket sub_6 belongs to sitelink
	 * 6 and holds pages from chapter 2, so every row in it carries the
	 * plugin's parentage and none of their ids mean anything in the sitelink
	 * id space:
	 *
	 *   page 6 collides with the bucket's own sitelink, which is the hang
	 *   page 5 collides with sitelink 5, whose own children live in sub_5
	 *
	 * Sitelink 5 has a real child here so the second collision has somewhere
	 * wrong to go. Without it the ancestor check alone answers both cases and
	 * the parentage check is never exercised.
	 *
	 * @return array
	 */
	private static function stockShape()
	{
		return array(
			'head_menu' => array(
				self::row(5, 'News', 0),
				self::row(6, 'Content', 0),
			),
			'sub_5' => array(
				self::row(51, 'Latest news', 5),
			),
			'sub_6' => array(
				self::row(5, 'Feature 1', 2),
				self::row(6, 'Feature 2', 2),
			),
		);
	}

	/**
	 * A navigation with no plugin bucket in it at all: sitelink 7 has a child
	 * in the links table, and that child has one of its own.
	 *
	 * @return array
	 */
	private static function nestedShape()
	{
		return array(
			'head_menu' => array(self::row(7, 'Top', 0)),
			'sub_7'     => array(self::row(8, 'Middle', 7)),
			'sub_8'     => array(self::row(9, 'Bottom', 8)),
		);
	}

	/**
	 * Rows that are their own parent. Every row here passes the parentage
	 * test, so the only thing that ends the walk is refusing to re-enter a
	 * menu that is already open.
	 *
	 * @return array
	 */
	private static function cyclicShape()
	{
		return array(
			'head_menu' => array(self::row(10, 'Loop', 0)),
			'sub_10'    => array(self::row(11, 'Eleven', 10)),
			'sub_11'    => array(self::row(10, 'Ten again', 11)),
		);
	}

	/**
	 * @param string $render statements that echo the markup between the markers
	 * @param array $linklist the array to hand the renderer as $linklist
	 * @param string $prelude statements to run before class2.php, for constants a theme would otherwise define
	 * @return string the markup the subprocess produced
	 */
	private function render($render, $linklist = null, $prelude = '')
	{
		$php  = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$php .= $prelude;
		$php .= "\$_E107 = array('cli' => true); ";
		$php .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";
		$php .= "\$linklist = ".var_export($linklist === null ? self::stockShape() : $linklist, true)."; ";
		$php .= $render;

		$output = array();
		$status = 0;
		exec(sprintf('timeout 60 php -d memory_limit=64M -r %s 2>&1', escapeshellarg($php)), $output, $status);

		$out = implode("\n", $output);

		self::assertNotSame(124, $status, 'the subprocess wedged, so nothing was measured');
		self::assertSame(0, $status, "the renderer never returned:\n".self::diagnosis($out));

		$start = strpos($out, self::BEGIN);
		$end   = strpos($out, self::END);

		self::assertNotFalse($start, "the subprocess printed no markup:\n".self::diagnosis($out));
		self::assertNotFalse($end, "the subprocess printed no markup:\n".self::diagnosis($out));

		return (string) substr($out, $start + strlen(self::BEGIN), $end - $start - strlen(self::BEGIN));
	}

	/**
	 * Runaway recursion ends in a stack trace tens of thousands of frames
	 * long, so report what killed the child rather than the whole of it.
	 *
	 * @param string $out everything the subprocess wrote
	 * @return string
	 */
	private static function diagnosis($out)
	{
		$lines = explode("\n", $out);
		$fatal = array();

		foreach ($lines as $line)
		{
			if (preg_match('/^(PHP )?(Fatal error|Parse error):/', $line))
			{
				$fatal[] = trim($line);
			}
		}

		return implode("\n", $fatal ? $fatal : array_slice($lines, 0, 20));
	}

	/**
	 * @param string $statement the expression to echo between the markers
	 * @return string
	 */
	private function echoing($statement)
	{
		return "echo '".self::BEGIN."', ".$statement.", '".self::END."';";
	}

	/**
	 * @param array $linklist
	 * @return string
	 */
	private function renderAlt($linklist, $id)
	{
		return $this->render(
			"require_once('".addslashes(APP_PATH.'/e107_core/shortcodes/single/sitelinks_alt.php')."'); "
			.$this->echoing("sitelinks_alt::render_sub(\$linklist, ".$id.", array('no_icons', 'noclick'), '')"),
			$linklist
		);
	}

	/**
	 * @param array $linklist
	 * @return string
	 */
	private function renderSitelinks($linklist, $key)
	{
		return $this->render(
			"\$lnk = e107::getSitelinks(); \$lnk->eLinkList = \$linklist; "
			.$this->echoing("\$lnk->subLink('".$key."', array('sublinkclass' => '', 'linkdisplay' => 1), '')"),
			$linklist
		);
	}

	public function testTheAltMenuTreatsAPluginRowWhoseIdIsASitelinkIdAsALeaf()
	{
		$markup = $this->renderAlt(self::stockShape(), 6);

		self::assertStringContainsString('Feature 1', $markup,
			'precondition: every page in the chapter has to reach the menu');
		self::assertStringContainsString('Feature 2', $markup,
			'the page whose id collides with the bucket has to reach the menu too');

		self::assertSame(1, substr_count($markup, "id='l_6'"),
			"sub_6 is one menu, so it may only be opened once:\n".$markup);
		self::assertStringNotContainsString("menuItemMouseover(event, 'l_6')", $markup,
			"page 6 has no submenu of its own, so it must not open sitelink 6's:\n".$markup);

		self::assertStringNotContainsString('Latest news', $markup,
			"page 5 is not sitelink 5, so sitelink 5's children must not hang under it:\n".$markup);
		self::assertStringNotContainsString("id='l_5'", $markup,
			"page 5 has no submenu of its own, so it must not open sitelink 5's:\n".$markup);
	}

	public function testTheSitelinksMenuTreatsAPluginRowWhoseIdIsASitelinkIdAsALeaf()
	{
		$markup = $this->renderSitelinks(self::stockShape(), 'sub_6');

		self::assertStringContainsString('Feature 1', $markup,
			'precondition: every page in the chapter has to reach the menu');
		self::assertStringContainsString('Feature 2', $markup,
			'the page whose id collides with the bucket has to reach the menu too');

		self::assertSame(1, substr_count($markup, "id='sub_6'"),
			"sub_6 is one menu, so it may only be rendered once:\n".$markup);
		self::assertSame(0, substr_count($markup, 'sublink-level-2'),
			"the chapter's pages are all at the same depth:\n".$markup);

		self::assertStringNotContainsString('Latest news', $markup,
			"page 5 is not sitelink 5, so sitelink 5's children must not hang under it:\n".$markup);
		self::assertStringNotContainsString("id='sub_5'", $markup,
			"sitelink 5's menu belongs to sitelink 5, not to a page that shares its id:\n".$markup);
	}

	/**
	 * The other half of the contract, and the one a guard is most likely to
	 * break: a submenu that is genuinely a submenu still nests.
	 */
	public function testTheAltMenuStillNestsRealSublinks()
	{
		$markup = $this->renderAlt(self::nestedShape(), 7);

		self::assertStringContainsString("menuItemMouseover(event, 'l_8')", $markup,
			"the sublink with children of its own has to open them:\n".$markup);
		self::assertStringContainsString("id='l_8'", $markup,
			"sub_8 has to be rendered as a menu:\n".$markup);
		self::assertStringContainsString('Bottom', $markup,
			"the third level has to reach the markup:\n".$markup);
	}

	public function testTheSitelinksMenuStillNestsRealSublinks()
	{
		$markup = $this->renderSitelinks(self::nestedShape(), 'sub_7');

		self::assertStringContainsString("id='sub_8'", $markup,
			"sub_8 has to be rendered as a menu:\n".$markup);
		self::assertStringContainsString('sublink-level-2', $markup,
			"the third level has to be rendered one deeper than the second:\n".$markup);
		self::assertStringContainsString('Bottom', $markup,
			"the third level has to reach the markup:\n".$markup);
	}

	public function testTheAltMenuReturnsOnALinkParentLoop()
	{
		$markup = $this->renderAlt(self::cyclicShape(), 10);

		self::assertSame(1, substr_count($markup, "id='l_10'"),
			"the loop must not reopen the menu it started in:\n".$markup);
		self::assertSame(1, substr_count($markup, "id='l_11'"),
			"and must not reopen the one below it either:\n".$markup);
	}

	public function testTheSitelinksMenuReturnsOnALinkParentLoop()
	{
		$markup = $this->renderSitelinks(self::cyclicShape(), 'sub_10');

		self::assertSame(1, substr_count($markup, "id='sub_10'"),
			"the loop must not reopen the menu it started in:\n".$markup);
		self::assertSame(1, substr_count($markup, "id='sub_11'"),
			"and must not reopen the one below it either:\n".$markup);
	}

	/**
	 * get() refills eLinkList from the database, so unlike its neighbours this
	 * runs on the deployed navigation, whose first head link 'Home' has nothing
	 * under it. LINKCLASS_HILITE takes get() off both sides of its cache, and
	 * E_ALL goes back after class2.php masks E_NOTICE for the CLI, because
	 * below PHP 7.2 count(null) is a silent zero and the undefined-index notice
	 * is all that separates the fixed source from the unfixed one.
	 */
	public function testDisplayModeThreeRendersAHeadLinkWithNoSubmenu()
	{
		$markup = $this->render(
			"error_reporting(E_ALL); ".$this->echoing("e107::getSitelinks()->get(1)"),
			array(),
			"define('LINKDISPLAY', 3); define('LINKCLASS_HILITE', 'active'); "
		);

		self::assertStringContainsString('Home', $markup,
			"the head link with no submenu is the one this mode renders:\n".$markup);
		self::assertStringNotContainsString('sub_1', $markup,
			"a bucket getlinks() never built must not be read:\n".$markup);
	}
}
