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

	public function testThePreviewLinkRefusesAScriptAddress()
	{
		$html = $this->render('default=javascript:alert(1)');

		$this->assertStringContainsString('e-image-preview', $html);
		$this->assertStringNotContainsStringIgnoringCase("href='javascript:", $html);
	}

	public function testAParameterCannotReplaceThePreviewAddress()
	{
		$html = $this->render('default=a.png&pvw_default=/elsewhere.png');

		$this->assertStringContainsString('e-image-preview', $html);
		$this->assertStringNotContainsString("href='/elsewhere.png'", $html);
	}

	public function testTheCommaFormStillReadsEachParameterInItsPlace()
	{
		$html = e107::getParser()->parseTemplate('{IMAGESELECTOR=x,,a.png,40,,,,,,,,,,,c}', true);

		$this->assertStringContainsString("class='c' name='x' id='x'", $html);
		$this->assertStringContainsString(e107::getParser()->toJsString('imagepreview=x|40|0'), html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
	}

	/**
	 * @return array
	 */
	public function hostileVariables()
	{
		return array(
			'the parser'          => array('tp=x'),
			'a value as an array' => array('path%5B%5D=a'),
		);
	}

	/**
	 * @dataProvider hostileVariables
	 * @param string $parameters
	 */
	public function testAParameterCannotStopThePage($parameters)
	{
		$this->assertStringContainsString('e-image-preview', $this->render($parameters));
	}

	/**
	 * @param string $parameters overriding a baseline that names every variable the shortcode has read from its parameters
	 * @return string
	 */
	private function render($parameters)
	{
		$every = 'name=x&path=&default=%7Be_IMAGE%7Dgeneric/blank.gif&width=&height=&multiple=&label=&subdirs=&filter=&fullpath=&tabindex=&class=&click_target=&pvw_default=';

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
