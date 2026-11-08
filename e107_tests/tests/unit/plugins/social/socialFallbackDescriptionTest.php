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
	use \Test\HtmlAttributes;

	const MARK = '@@e107help-social-meta@@';

	const DESCRIPTION = 'He said &quot;hi&quot; &amp; left. Write &amp;quot; for a quote.';

	public function testTheCopiesReadAsThePagesOwnDescriptionDoes()
	{
		$meta = $this->renderedMeta();
		$description = array('He said "hi" & left. Write &quot; for a quote.');

		$this->assertSame($description, $this->attributesOf($meta, "//meta[@name='description']/@content"), $meta);
		$this->assertSame($description, $this->attributesOf($meta, "//meta[@property='og:description']/@content"), $meta);
		$this->assertSame($description, $this->attributesOf($meta, "//meta[@name='twitter:description']/@content"), $meta);
	}

	/**
	 * @return string the tags a page prints after setting only its description through {@see e107::meta()}, with nothing printed between them
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
		$this->assertSame('', trim(strip_tags($match[1])), $match[1]);

		return $match[1];
	}
}
