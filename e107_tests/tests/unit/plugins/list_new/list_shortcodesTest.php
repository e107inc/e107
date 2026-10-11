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
 * The headings of the list_new page and menus, each built by its section from what the section's plugin stored.
 */
class list_shortcodesTest extends \Codeception\Test\Unit
{
	/**
	 * Headings as the forum and members sections build them, from names stored as their forms store what a member typed.
	 *
	 * @return array
	 */
	public function builtHeadings()
	{
		return array(
			'a thread name calling a shortcode' => array('<a href="/forum/chat/1/">{PRINT_ITEM=x&quot; autofocus onfocus=&quot;alert(1)^y} named thread</a>'),
			'a thread name holding bbcode in a table' => array('<a href="/forum/chat/2/">[table][img]x.png&#039; autofocus onfocus=&#039;alert(1)[/img][/table] notes</a>'),
			'a display name naming a constant' => array('e_SYSTEM'),
		);
	}

	/**
	 * @dataProvider builtHeadings
	 * @param string $heading
	 */
	public function testAHeadingIsPrintedAsItsSectionBuiltIt($heading)
	{
		self::assertSame($heading, $this->printedHeading($heading));
	}

	/**
	 * A page comment's subject, which the comment section takes already rendered.
	 */
	public function testARenderedHeadingIsNotRenderedAgain()
	{
		$subject = e107::getParser()->toHTML('[link=//x/&#039;/autofocus/onfocus=&#039;alert(1)&#039;/]y[/link]', true);
		$heading = "<a href='/page.php?id=1'>".$subject.'</a>';

		self::assertSame($heading, $this->printedHeading($heading));
	}

	/**
	 * @param string $heading
	 * @return string
	 */
	private function printedHeading($heading)
	{
		require_once(e_PLUGIN.'list_new/list_shortcodes.php');

		$sc = new list_shortcodes();
		$sc->row = array('heading' => $heading);

		return $sc->sc_list_heading();
	}
}
