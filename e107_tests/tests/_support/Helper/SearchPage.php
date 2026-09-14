<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace Helper;

/**
 * The front-end search page as a visitor is served it, for the tests that read its markup.
 */
trait SearchPage
{
	/** Marker proving the subprocess got past booting e107. */
	private static $searchPageBootedMarker = 'E107-BOOTED';

	/**
	 * Renders search.php with a search handler registered for every type asked for {@see \Test\Unit::runInBootedCli()}.
	 *
	 * @param array|string $requested what $_GET['t'] holds: the keys a checkbox site posts, or the one string a dropdown site posts
	 * @return string the page as it was sent
	 */
	private function renderSearchPage($requested)
	{
		$handler = "array('class' => e_UC_PUBLIC, 'chars' => '150', 'results' => '10', 'pre_title' => '1', 'pre_title_alt' => '', 'order' => '1')";
		$types = is_array($requested) ? array_keys($requested) : array($requested);

		$php = "echo '".self::$searchPageBootedMarker."'; ";
		$php .= "\$pref['search_restrict'] = e_UC_PUBLIC; ";
		$php .= "\$searchConfig = e107::getConfig('search'); ";
		$php .= "\$searchConfig->setPref('user_select', 1); ";
		$php .= "\$searchConfig->setPref('selector', ".(is_array($requested) ? 1 : 2)."); ";

		foreach($types as $type)
		{
			$php .= "\$searchConfig->setPref('plug_handlers/".$type."', ".$handler."); ";
			$php .= "e107::getConfig()->setPref('e_search_list/".$type."', ".var_export($type, true)."); ";
		}

		$php .= "\$_GET['t'] = ".var_export($requested, true)."; ";
		$php .= "chdir(".var_export(APP_PATH, true)."); ";
		$php .= "require(".var_export(APP_PATH.'/search.php', true)."); ";
		$php .= "while(ob_get_level() > 0) { @ob_end_flush(); } ";

		list($output) = $this->runInBootedCli($php);
		$html = implode("\n", $output);

		$this->assertStringContainsString(self::$searchPageBootedMarker, $html,
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n".$html);

		return $html;
	}

	/**
	 * @param string $html a rendered page, however malformed
	 * @return \DOMXPath
	 */
	private function searchPageXPath($html)
	{
		$previous = libxml_use_internal_errors(true);
		$dom = new \DOMDocument();
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		return new \DOMXPath($dom);
	}
}
