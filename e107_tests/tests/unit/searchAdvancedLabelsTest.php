<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the labels of the advanced search block on the front-end search
 * page. Every one of them used to carry the category selector's id, so a
 * screen reader announced the category label for an advanced field and
 * clicking a label moved focus to the wrong control.
 *
 * @see https://github.com/e107inc/e107/issues/6300
 */
class searchAdvancedLabelsTest extends \Test\Unit
{
	use \Helper\SearchPage;

	/**
	 * The search types the tests render. news covers the dropdown and date
	 * field types, _blank the author one, and no bundled handler the sample
	 * database ships has an author field.
	 *
	 * @return array
	 */
	public function advancedSearchTypes()
	{
		return array(
			'news' => array('news'),
			'_blank' => array('_blank'),
		);
	}

	/**
	 * Every label of the advanced block, with what its `for` resolves to.
	 *
	 * @param string $html a rendered search page
	 * @return array of array(label text, for, ids in the same form group, elements with that id on the page)
	 */
	private function advancedLabels($html)
	{
		$xpath = $this->searchPageXPath($html);
		$found = array();

		foreach($xpath->query("//*[@id='search-advanced']//label[@for]") as $label)
		{
			$for = $label->getAttribute('for');
			$ids = array();

			foreach($xpath->query(".//*[@id]", $label->parentNode) as $control)
			{
				$ids[] = $control->getAttribute('id');
			}

			$named = $for === '' ? 0 : $xpath->query("//*[@id='".$for."']")->length;

			$found[] = array(trim($label->textContent), $for, $ids, $named);
		}

		return $found;
	}

	/**
	 * A label belongs to the control beside it. The category selector is
	 * elsewhere on the page, so an advanced label naming it is the defect.
	 *
	 * @dataProvider advancedSearchTypes
	 * @param string $type
	 */
	public function testAdvancedLabelNamesAControlInItsOwnGroup($type)
	{
		$labels = $this->advancedLabels($this->renderSearchPage($type));

		$this->assertNotEmpty($labels,
			"The advanced block of '".$type."' rendered no labels, so the assertions below would pass on an empty page.");

		foreach($labels as $label)
		{
			list($text, $for, $ids, $named) = $label;

			$this->assertContains($for, $ids,
				"The label '".$text."' points at '".$for."', which is not the id of anything in its own form group (#6300). "
				."That group holds: ".(empty($ids) ? '(nothing with an id)' : implode(', ', $ids)));

			$this->assertSame(1, $named,
				"Exactly one element on the page may answer to '".$for."', or the label '".$text."' resolves to whichever came first.");
		}
	}

	/**
	 * The category selector keeps its own label and the advanced fields
	 * stop borrowing it.
	 *
	 * @dataProvider advancedSearchTypes
	 * @param string $type
	 */
	public function testAdvancedLabelDoesNotNameTheCategorySelector($type)
	{
		foreach($this->advancedLabels($this->renderSearchPage($type)) as $label)
		{
			list($text, $for, $ids, $named) = $label;

			$this->assertNotSame('t', $for,
				"The advanced label '".$text."' is bound to the category selector rather than to its own field (#6300).");
		}
	}

	/**
	 * The author field is drawn by the form handler, which names the input
	 * itself, so the label has to follow that name rather than one core
	 * picked.
	 */
	public function testAuthorLabelNamesTheFieldTheFormHandlerDrew()
	{
		$html = $this->renderSearchPage('_blank');
		$xpath = $this->searchPageXPath($html);
		$fields = $xpath->query("//*[@id='search-advanced']//input[@name='author_name']");

		$this->assertSame(1, $fields->length,
			"The author field did not render, so there is nothing to bind a label to.\n".$html);

		$field = $fields->item(0);
		$labels = $xpath->query("ancestor::div[contains(@class, 'form-group')]//label[@for]", $field);

		$this->assertSame(1, $labels->length,
			"The author field's form group has no label of its own.\n".$html);

		$this->assertSame($field->getAttribute('id'), $labels->item(0)->getAttribute('for'),
			"The author label must name the id the form handler gave the input (#6300).");
	}
}
