<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the front-end search page, whose results caption is assembled in
 * search.php itself rather than in any handler.
 */
class searchTest extends \Test\Unit
{
	/** Long enough to clear the minimum search length, and matched by nothing. */
	const NO_MATCH_QUERY = 'zzqqxxnomatchzz';

	/**
	 * Renders search.php for one query in a subprocess, with the news handler seeded in memory.
	 *
	 * @param string $query
	 * @return array {out: string, exit: int}
	 */
	private function renderSearchPage($query)
	{
		$php = "chdir('".addslashes(APP_PATH)."'); ";
		$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= "e107::getConfig()->setPref('e_search_list', array('news' => 'news')); ";
		$php .= "e107::getConfig('search')->setPref('plug_handlers/news', array('class' => '0', 'chars' => 150, 'results' => 10, 'pre_title' => 1, 'pre_title_alt' => '', 'order' => 1)); ";
		$php .= "\$_GET = array('q' => '".addslashes($query)."', 't' => 'news', 'r' => 0); ";
		$php .= "require_once('".addslashes(APP_PATH.'/search.php')."'); ";

		list($output, $status) = $this->runInBootedCli($php);

		return array('out' => implode("\n", $output), 'exit' => $status);
	}

	/**
	 * The range clause is empty whenever nothing matched, and its two fixed spaces used to survive it.
	 *
	 * @see https://github.com/e107inc/e107/issues/6298
	 */
	public function testResultsCaptionDropsTheEmptyRangeClause()
	{
		e107::coreLan('search');

		$result = $this->renderSearchPage(self::NO_MATCH_QUERY);

		self::assertStringContainsString(LAN_SEARCH_11.' '.LAN_SEARCH_13.' '.LAN_SEARCH_98, $result['out'],
			"A search that matched nothing must still caption its own section, with one space between each word.\n".$result['out']);
		self::assertStringNotContainsString(LAN_SEARCH_11.'  '.LAN_SEARCH_13, $result['out'],
			"The caption must not keep the spaces that belonged to the range clause.\n".$result['out']);
		self::assertSame(0, $result['exit'], $result['out']);
	}
}
