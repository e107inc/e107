<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers how many advanced search blocks the front-end search page renders.
 * It used to render one per selected type, and the controls of different
 * types share their form field names, so two blocks overwrote each other.
 *
 * @see https://github.com/e107inc/e107/issues/6321
 */
class searchAdvancedMultiTypeTest extends \Test\Unit
{
	use \Helper\SearchPage;

	/**
	 * The form field names of the advanced block, in the order they render.
	 *
	 * @param string $html a rendered search page
	 * @return array
	 */
	private function advancedFieldNames($html)
	{
		$names = array();

		foreach($this->searchPageXPath($html)->query("//*[@id='search-advanced']//*[self::input or self::select or self::textarea]") as $field)
		{
			$names[] = $field->getAttribute('name');
		}

		return $names;
	}

	/**
	 * @param string $html a rendered search page
	 * @return string what the advanced block says, whitespace trimmed
	 */
	private function advancedBlockText($html)
	{
		$xpath = $this->searchPageXPath($html);
		$block = $xpath->query("//*[@id='search-advanced']")->item(0);

		$this->assertNotNull($block,
			"The advanced block is not on the page at all, so the assertions below would pass on nothing.\n".$html);

		return trim(preg_replace('/\s+/', ' ', $block->textContent));
	}

	/**
	 * News and Members both declare a date filter, and the pair of controls
	 * behind one is named 'on' and 'time' whichever handler asked for it, so
	 * two blocks in one form submit both names twice and each handler reads
	 * whichever value the browser sent last (#6321).
	 */
	public function testAskingForTwoTypesRendersNoAdvancedFields()
	{
		$html = $this->renderSearchPage(array('news' => 1, 'user' => 1));
		$names = $this->advancedFieldNames($html);

		$this->assertSame(array(), $names,
			"Advanced search covers one type at a time, so two ticked types get no fields rather than two blocks "
			."whose values collide (#6321). These rendered: ".implode(', ', $names));

		$this->assertNotSame('', $this->advancedBlockText($html),
			"A visitor who asked for two types is told that advanced search covers one, rather than being shown "
			."an empty panel.\n".$html);
	}

	/**
	 * One ticked checkbox is still an array, so the fix must not narrow the
	 * advanced block to requests that name their type as a plain string.
	 */
	public function testOneTickedCheckboxRendersWhatTheDropdownRenders()
	{
		$ticked = $this->advancedFieldNames($this->renderSearchPage(array('news' => 1)));

		$this->assertNotSame(array(), $ticked,
			"A single ticked type renders its own handler's advanced fields.");

		$this->assertSame($this->advancedFieldNames($this->renderSearchPage('news')), $ticked,
			"One ticked checkbox asks for the same type as the dropdown does, so it gets the same fields.");
	}
}
