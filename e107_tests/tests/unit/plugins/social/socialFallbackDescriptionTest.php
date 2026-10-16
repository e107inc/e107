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
 * The description the social plugin copies into og:description and twitter:description for a page that set only its own.
 */
class socialFallbackDescriptionTest extends \Test\Unit
{
	const MARK = '@@e107help-social-meta@@';

	const DESCRIPTION = 'He said &quot;hi&quot; &amp; left. Write &amp;quot; for a quote.';

	public function testTheCopiesReadAsThePagesOwnDescriptionDoes()
	{
		$xpath = $this->renderedMeta();
		$description = $this->contentOf($xpath, "//meta[@name='description']");

		$this->assertSame('He said "hi" & left. Write &quot; for a quote.', $description);
		$this->assertSame($description, $this->contentOf($xpath, "//meta[@property='og:description']"));
		$this->assertSame($description, $this->contentOf($xpath, "//meta[@name='twitter:description']"));
	}

	/**
	 * @return DOMXPath the meta tags a page prints after setting only its description through e107::meta()
	 */
	private function renderedMeta()
	{
		list($output) = $this->runInBootedCli(
			"e107::meta('description', '".self::DESCRIPTION."'); "
			. "echo '".self::MARK."', e107::getSingleton('eResponse')->renderMeta(), '".self::MARK."'; "
		);
		$printed = implode("\n", $output);
		$mark = preg_quote(self::MARK, '/');

		$this->assertSame(1, preg_match('/'.$mark.'(.*?)'.$mark.'/s', $printed, $match), $printed);

		$dom = new DOMDocument();
		$dom->loadHTML('<html><head>'.$match[1].'</head></html>');

		return new DOMXPath($dom);
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $query
	 * @return string
	 */
	private function contentOf(DOMXPath $xpath, $query)
	{
		$tags = $xpath->query($query);

		$this->assertSame(1, $tags->length, 'expected exactly one tag for '.$query);

		return $tags->item(0)->getAttribute('content');
	}
}
