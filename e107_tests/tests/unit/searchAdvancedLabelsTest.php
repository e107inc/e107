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
class searchAdvancedLabelsTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** Marker proving the subprocess got past booting e107. */
	const BOOTED = 'E107-BOOTED';

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
	 * Render search.php for one search type, the way a visitor asks for it.
	 *
	 * @param string $type what $_GET['t'] holds
	 * @return string the page as it was sent
	 */
	private function renderSearchPage($type)
	{
		$handler = "array('class' => e_UC_PUBLIC, 'chars' => '150', 'results' => '10', 'pre_title' => '1', 'pre_title_alt' => '', 'order' => '1')";

		$php = "echo '".self::BOOTED."'; ";
		$php .= "\$pref['search_restrict'] = e_UC_PUBLIC; ";
		$php .= "\$searchConfig = e107::getConfig('search'); ";
		$php .= "\$searchConfig->setPref('user_select', 1); ";
		$php .= "\$searchConfig->setPref('selector', 2); ";
		$php .= "\$searchConfig->setPref('plug_handlers/".$type."', ".$handler."); ";
		$php .= "e107::getConfig()->setPref('e_search_list/".$type."', ".var_export($type, true)."); ";
		$php .= "\$_GET['t'] = ".var_export($type, true)."; ";
		$php .= "chdir(".var_export(APP_PATH, true)."); ";
		$php .= "require(".var_export(APP_PATH.'/search.php', true)."); ";
		$php .= "while(ob_get_level() > 0) { @ob_end_flush(); } ";

		list($output, $status) = $this->runInBootedCli($php);
		$html = implode("\n", $output);

		$this->assertStringContainsString(self::BOOTED, $html,
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n".$html);

		return $html;
	}

	/**
	 * Every label of the advanced block, with what its `for` resolves to.
	 *
	 * @param string $html a rendered search page
	 * @return array of array(label text, for, ids in the same form group, elements with that id on the page)
	 */
	private function advancedLabels($html)
	{
		$previous = libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		$xpath = new DOMXPath($dom);
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

		$previous = libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		$xpath = new DOMXPath($dom);
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
