<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * The advanced author field of the front-end search page, as served to whoever wrote the query string.
 */
class searchAdvancedAuthorFieldTest extends \Test\Unit
{
	use \Helper\SearchPage;

	/**
	 * Author values a visitor can send, each carrying alert(1), that would reach the page if the field took them as anything but a value.
	 *
	 * @return array
	 */
	public function authorQueryValues()
	{
		return array(
			'attribute through other' => array('other=autofocus onfocus=alert(1)'),
			'element after the input' => array('other=><img src=x onerror=alert(1)>'),
			'attribute out of tabindex' => array("tabindex=1' autofocus onfocus='alert(1)"),
			'attributes in a data- name' => array('data-a%09autofocus%09onfocus%3Dalert(1)%09=1'),
			'options as an array' => array(array('other' => 'autofocus onfocus=alert(1)')),
			'markup for the picker script' => array('selectize[dropdownParent]=<img src=x onerror=alert(1)>'),
			'another origin for the picker' => array('loadPath=https://example.invalid/alert(1)'),
		);
	}

	/**
	 * @dataProvider authorQueryValues
	 * @param string|array $author
	 */
	public function testAuthorQueryValueReachesNeitherTheAdvancedBlockNorItsScript($author)
	{
		$html = $this->renderSearchPage('_blank', "\$_GET['author'] = \$_REQUEST['author'] = ".var_export($author, true)."; ");
		$xpath = $this->searchPageXPath($html);

		$this->assertGreaterThan(0, $xpath->query("//*[@id='search-advanced']//input[@type='text']")->length,
			"The author field did not render, so there is nothing to test.\n".$html);

		$added = array();

		foreach($xpath->query("//*[@id='search-advanced']//img | //*[@id='search-advanced']//@*[starts-with(name(), 'on') or name() = 'autofocus']") as $node)
		{
			$added[] = $node->nodeName;
		}

		$this->assertSame(array(), $added,
			"The author value became markup in the advanced block.\n".$html);

		$settings = '';

		foreach($xpath->query("//script[contains(., 'e107.settings')]") as $script)
		{
			$settings .= $script->textContent;
		}

		$this->assertStringContainsString('"selectize"', $settings,
			"The page carries no picker settings, so the script half of this test would pass on nothing.\n".$html);

		$this->assertStringNotContainsString('alert(1)', $settings,
			"The author value reached the settings the page hands to its scripts.\n".$settings);
	}
}
