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
class page_shortcodesTest extends \Test\Unit
{
	const IMAGE = 'images/e107help-test.jpg';

	/**
	 * @param string $parm
	 * @return array what the shortcode returned, and the thumbnail address and image address it should use
	 */
	private function renderThumbnail($parm)
	{
		list($json) = $this->marksFromBootedCli("\$tp = e107::getParser(); \$sc = e107::getScBatch('page', null, 'cpage'); "
			."echo '@@'.json_encode(array("
			."'html' => \$sc->sc_cpagethumbnail(".var_export($parm, true)."), "
			."'thumb' => \$tp->thumbUrl('{e_MEDIA}".self::IMAGE."'), "
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
}
