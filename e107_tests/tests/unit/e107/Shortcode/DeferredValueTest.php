<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shortcode;

/**
 * A value in the array given to {@see \e_parse::parseTemplate()} that implements {@see DeferredValue} is rendered where the template reaches its tag, with that tag's parameters.
 */
class DeferredValueTest extends \Test\Unit
{
	const TAGS = '[{DEFERRED_VALUE}][{DEFERRED_VALUE: a=1&b=2}][{DEFERRED_VALUE=text}][{DEFERRED_VALUE|mode}]';

	protected function _before()
	{
		require_once(__DIR__.'/recording_deferred_value.php');
	}

	public function testEachTagIsRenderedWithItsOwnParametersAndMode()
	{
		$value = new RecordingDeferredValue('out', \e107::getParser());

		$html = \e107::getParser()->parseTemplate(self::TAGS, false, array('DEFERRED_VALUE' => $value));

		self::assertSame('[out][out][out][out]', $html);
		self::assertSame(array(
			array(array(), ''),
			array(array('a' => '1', 'b' => '2'), ''),
			array('text', ''),
			array(array(), 'mode'),
		), $value->arguments);
	}

	public function testATemplateWithoutTheTagNeverRendersIt()
	{
		$value = new RecordingDeferredValue('out', \e107::getParser());

		\e107::getParser()->parseTemplate('[{SOME_OTHER_VALUE}]', false, array('DEFERRED_VALUE' => $value, 'SOME_OTHER_VALUE' => 'plain'));

		self::assertSame(array(), $value->arguments);
	}

	public function testEachTagSeesTheThumbSizeSetBeforeIt()
	{
		$found = $this->parserState();

		try
		{
			$parser = \e107::getParser();
			$value = new RecordingDeferredValue('out', $parser);

			$parser->parseTemplate('{SETIMAGE: w=320}{DEFERRED_VALUE}{SETIMAGE: w=240}{DEFERRED_VALUE}', true, array('DEFERRED_VALUE' => $value));

			self::assertSame(array(320, 240), $value->thumbWidths);
		}
		finally
		{
			$this->restoreParserState($found);
		}
	}

	public function testARenderedValueTakesTheWrapperOfItsCode()
	{
		global $sc_style;
		$found = $sc_style;
		$sc_style['DEFERRED_VALUE'] = '<b>{---}</b>';

		try
		{
			$value = new RecordingDeferredValue('out', \e107::getParser());

			self::assertSame('[<b>out</b>]', \e107::getParser()->parseTemplate('[{DEFERRED_VALUE}]', false, array('DEFERRED_VALUE' => $value)));
		}
		finally
		{
			$sc_style = $found;
		}
	}

	public function testARenderThatReturnsNullPrintsNothing()
	{
		$value = new RecordingDeferredValue(null, \e107::getParser());

		self::assertSame('[]', \e107::getParser()->parseTemplate('[{DEFERRED_VALUE}]', false, array('DEFERRED_VALUE' => $value)));
	}

	public function testAPlainValuePrintsAsItIsWhateverTheFormOfItsTag()
	{
		$html = \e107::getParser()->parseTemplate(self::TAGS, false, array('DEFERRED_VALUE' => 'plain'));

		self::assertSame('[plain][plain][plain][plain]', $html);
	}
}
