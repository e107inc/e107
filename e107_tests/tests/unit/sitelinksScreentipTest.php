<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * With screentips on, {@see sitelinks::makeLink()} shows a link's description in the link's title, encoded as an attribute.
 */
class sitelinksScreentipTest extends \Codeception\Test\Unit
{
	use \Test\CorePrefs;
	use \Test\HtmlAttributes;
	use \Test\SitelinkRows;

	/**
	 * @return array
	 */
	public function descriptions()
	{
		return array(
			'plain words'              => array('A page about e107'),
			'a url bbcode'             => array('[url]http://e107.org/ autofocus onfocus=print() x[/url]'),
			'a url bbcode with a name' => array('[url=http://e107.org/autofocus/onfocus=print()//]e107[/url]'),
			'markup'                   => array('<b class="x">e107</b> "quoted"'),
			'a constant'               => array('See {e_PLUGIN}faqs'),
		);
	}

	/**
	 * @dataProvider descriptions
	 * @param string $description
	 * @return void
	 */
	public function testTheDescriptionIsTheTitleAndNothingElse($description)
	{
		$restore = $this->withCorePrefs(array('linkpage_screentip' => 1));

		try
		{
			$markup = self::makeLink(self::linkRow(array('link_description' => $description)));
		}
		finally
		{
			$restore();
		}

		self::assertSame(array('title', 'href'), $this->attributesOf($markup, '(//a)[1]/@*', true),
			"the description must not add attributes to the link:\n".$markup);
		self::assertSame(array($description), $this->attributesOf($markup, '(//a)[1]/@title'));
	}
}
