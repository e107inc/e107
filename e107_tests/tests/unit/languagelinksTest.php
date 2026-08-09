<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {LANGUAGELINKS} takes per-call options after a pipe:
 *
 *   {LANGUAGELINKS=English,Bulgarian}          links keep the query string
 *   {LANGUAGELINKS=English,Bulgarian|noquery}  links drop it
 *   {LANGUAGELINKS=|home}                      links point at the site index
 *
 * They were read once and frozen into constants, so the first
 * {LANGUAGELINKS} rendered in a request decided the shape of every later
 * one. A theme with the shortcode in both a menu and the footer got one of
 * them wrong, and which one depended on render order.
 */
class languagelinksTest extends \Test\Unit
{

	public function _before()
	{
		require_once(e_CORE.'shortcodes/single/languagelinks.php');
	}

	/**
	 * Two calls, different options, in one process. They have to differ.
	 */
	public function testHomeOptionAppliesToTheCallThatAsksForIt()
	{
		$plain = languagelinks_shortcode('English,Bulgarian');
		$home = languagelinks_shortcode('English,Bulgarian|home=1');

		$this->assertNotEmpty($plain, 'Precondition: two languages render two links.');
		$this->assertNotEquals($plain, $home,
			'The second call asked for home links and must get them, whatever the first call asked for.');
		$this->assertStringContainsString(SITEURL, $home,
			'Home links point at the site index.');
	}

	/**
	 * And the reverse order, so neither call can be the one that happens to
	 * win by going first.
	 */
	public function testTheOptionsOfAnEarlierCallDoNotSurviveIntoALaterOne()
	{
		$home = languagelinks_shortcode('English,Bulgarian|home=1');
		$plain = languagelinks_shortcode('English,Bulgarian');
		$homeAgain = languagelinks_shortcode('English,Bulgarian|home=1');

		$this->assertNotEquals($home, $plain,
			'A call with no options must not inherit the previous call options.');
		$this->assertEquals($home, $homeAgain,
			'The same options must give the same links every time.');
	}
}
