<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {CPAGETHUMBNAIL} from the Custom Pages shortcode batch.
 */
class page_shortcodesTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const IMAGE = 'images/e107help-test.jpg';

	const TAG = '<img class="cpage-image" src="%s" alt="" %s />';

	/**
	 * @param string $parm
	 * @param array  $sizes the thumbnail sizes $parm asks for
	 * @param string $setup PHP run first, with the parser in $tp
	 * @return array what the shortcode returned, the thumbnail address and image address it should use, and the thumbnail address with no sizes
	 */
	private function renderThumbnail($parm, array $sizes = array(), $setup = '')
	{
		list($json) = $this->marksFromBootedCli("\$tp = e107::getParser(); ".$setup." \$sc = e107::getScBatch('page', null, 'cpage'); "
			."echo '@@'.json_encode(array("
			."'html' => \$sc->sc_cpagethumbnail(".var_export($parm, true)."), "
			."'thumb' => \$tp->thumbUrl('{e_MEDIA}".self::IMAGE."', ".var_export($sizes, true)."), "
			."'unsized' => \$tp->thumbUrl('{e_MEDIA}".self::IMAGE."'), "
			."'image' => \$tp->replaceConstants('{e_MEDIA}".self::IMAGE."', 'abs'))).'@@';", 1);

		return json_decode($json, true);
	}

	/**
	 * @return array
	 */
	public function tagTypes()
	{
		return array(
			'default' => array(''),
			'tag'     => array('&type=tag'),
		);
	}

	/**
	 * @dataProvider tagTypes
	 * @param string $type
	 */
	public function testTheTagCarriesTheAltParameter($type)
	{
		$render = $this->renderThumbnail(self::IMAGE.'|alt=Say+%22hi%22'.$type);

		self::assertStringStartsWith('<img class="cpage-image" src="'.$render['thumb'].'" alt="Say &quot;hi&quot;" ', $render['html']);
	}

	public function testTheLinkCarriesTheAltParameter()
	{
		$render = $this->renderThumbnail(self::IMAGE.'|type=link&alt=A+lighthouse');

		self::assertStringStartsWith('<a href="'.$render['image'].'" class="cpage-image" rel="external image">'
			.'<img class="cpage-image" src="'.$render['thumb'].'" alt="A lighthouse" ', $render['html']);
		self::assertStringEndsWith('</a>', $render['html']);
	}

	public function testTheSourceTypeStillReturnsTheBareUrl()
	{
		$render = $this->renderThumbnail(self::IMAGE.'|type=src&alt=Unused');

		self::assertSame($render['thumb'], $render['html']);
	}

	/**
	 * @return array
	 */
	public function documentedSizes()
	{
		return array(
			'maximum width, tag, e_MEDIA path' => array('e_MEDIA/'.self::IMAGE.'|type=tag&w=200', array('w' => 200), self::TAG),
			'maximum width, default type'      => array(self::IMAGE.'|w=200', array('w' => 200), self::TAG),
			'forced size, address only'        => array(self::IMAGE.'|type=src&aw=200&ah=200', array('aw' => 200, 'ah' => 200), '%s'),
		);
	}

	/**
	 * The examples in the shortcode's own docblock.
	 *
	 * @dataProvider documentedSizes
	 * @param string $parm
	 * @param array  $sizes
	 * @param string $expected what the shortcode returns, with %s for the thumbnail address and then the dimensions
	 * @see https://github.com/e107inc/e107/issues/6945
	 */
	public function testTheDocumentedSizesReachTheThumbnail($parm, array $sizes, $expected)
	{
		$render = $this->renderThumbnail($parm, $sizes);

		self::assertNotSame($render['unsized'], $render['thumb'], 'The sizes have to change the thumbnail address, or this test proves nothing.');
		self::assertSame(sprintf($expected, $render['thumb'], ''), $render['html']);
	}

	/**
	 * @return array
	 */
	public function sizesUnderTheParserCrop()
	{
		return array(
			'a maximum width'        => array('|w=200', array('w' => 200), ''),
			'no size'                => array('', array(), 'width="400" height="300"'),
			'a blank width'          => array('|w=', array(), 'width="400" height="300"'),
			'a negative width'       => array('|w=-200', array(), 'width="400" height="300"'),
			'a forced height alone'  => array('|ah=200', array(), 'width="400" height="300"'),
		);
	}

	/**
	 * With a crop size set on the parser, as {SETIMAGE} sets one.
	 *
	 * @dataProvider sizesUnderTheParserCrop
	 * @param string $size
	 * @param array  $sizes what the thumbnail should be built with
	 * @param string $dimensions the width and height the tag should carry
	 * @see https://github.com/e107inc/e107/issues/6945
	 */
	public function testOnlyASizeTheThumbnailUsesReplacesTheParserDimensions($size, array $sizes, $dimensions)
	{
		$render = $this->renderThumbnail(self::IMAGE.$size, $sizes, '$tp->setThumbSize(400, 300, 1);');

		self::assertSame(sprintf(self::TAG, $render['thumb'], $dimensions), $render['html']);
	}
}
