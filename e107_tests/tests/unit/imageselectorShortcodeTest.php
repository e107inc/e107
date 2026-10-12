<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {IMAGESELECTOR} parameters as the markup it prints carries them.
 */
class imageselectorShortcodeTest extends \Codeception\Test\Unit
{
	/**
	 * @return array
	 */
	public function scriptParameters()
	{
		return array(
			'width'         => array("class=c&width=1');alert(1)//", "');alert(1)"),
			'height'        => array("class=c&height=1');alert(1)//", "');alert(1)"),
			'click prefix'  => array("click_target=t&click_prefix=\");alert(1)//", "\");alert(1)"),
			'click postfix' => array("click_target=t&click_postfix=\");alert(1)//", "\");alert(1)"),
		);
	}

	/**
	 * @dataProvider scriptParameters
	 * @param string $parameters
	 * @param string $breakout
	 */
	public function testAParameterCannotEndTheStringItIsPrintedIntoInAHandler($parameters, $breakout)
	{
		$handlers = $this->handlers($this->render($parameters));

		$this->assertNotEmpty($handlers);
		$this->assertStringNotContainsString($breakout, $handlers);
	}

	public function testOrdinaryParametersReachTheHandlersUnchanged()
	{
		$tp = e107::getParser();
		$handlers = $this->handlers($this->render('width=40&click_target=t&click_prefix=[img]&click_postfix=[/img]'));

		$this->assertStringContainsString('replaceSC('.$tp->toJsString('imagepreview=x|40|0').',this.form,'.$tp->toJsString('x_prev').');', $handlers);
		$this->assertStringContainsString('addtext('.$tp->toJsString('[img]').'+document.getElementById('.$tp->toJsString('x').').value+'.$tp->toJsString('[/img]').', true);', $handlers);
	}

	/**
	 * @param string $parameters overriding a value for every parameter the shortcode reads
	 * @return string
	 */
	private function render($parameters)
	{
		$every = 'name=x&path=&default=%7Be_IMAGE%7Dgeneric/blank.gif&width=&height=&multiple=&label=&subdirs=&filter=&fullpath=&tabindex=&class=&click_target=';

		return e107::getParser()->parseTemplate('{IMAGESELECTOR='.$every.'&'.$parameters.'}', true);
	}

	/**
	 * @param string $html
	 * @return string every inline handler in the markup, decoded as the browser decodes it before compiling it
	 */
	private function handlers($html)
	{
		preg_match_all('#\son[a-z]+=([\'"])(.*?)\1#s', $html, $matches);

		return html_entity_decode(implode("\n", $matches[2]), ENT_QUOTES, 'UTF-8');
	}
}
