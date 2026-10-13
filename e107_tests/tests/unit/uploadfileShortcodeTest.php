<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {UPLOADFILE} parameters as its "add another file" handler prints them.
 */
class uploadfileShortcodeTest extends \Test\Unit
{
	/** @var mixed */
	private $uploadClass;

	protected function _before()
	{
		$this->uploadClass = e107::getPref('upload_class');
		e107::getConfig()->set('upload_class', e_UC_PUBLIC);
	}

	protected function _after()
	{
		e107::getConfig()->set('upload_class', $this->uploadClass);
	}

	/**
	 * @return array
	 */
	public function handlerParameters()
	{
		return array(
			'row'       => array("up_row=x');alert(1)//"),
			'container' => array("up_container=x');alert(1)//"),
		);
	}

	/**
	 * @dataProvider handlerParameters
	 * @param string $parameters
	 */
	public function testAParameterCannotEndTheStringItIsPrintedIntoInTheHandler($parameters)
	{
		$handler = $this->duplicateHandler($this->render($parameters));

		$this->assertNotSame('', $handler);
		$this->assertStringNotContainsString("');alert(1)", $handler);
	}

	public function testTheDefaultRowsStillDuplicate()
	{
		$tp = e107::getParser();

		$this->assertSame('duplicateHTML('.$tp->toJsString('upline').','.$tp->toJsString('up_container').');', $this->duplicateHandler($this->render('up_row=upline')));
	}

	/**
	 * @param string $parameters
	 * @return string
	 */
	private function render($parameters)
	{
		return e107::getParser()->parseTemplate('{UPLOADFILE=|nowarn&'.$parameters.'}', true);
	}

	/**
	 * @param string $html
	 * @return string the "add another file" button's handler, decoded as the browser decodes it before compiling it
	 */
	private function duplicateHandler($html)
	{
		preg_match('#class=\'action duplicate\'[^>]*?\sonclick=([\'"])(.*?)\1#s', $html, $match);

		return isset($match[2]) ? html_entity_decode($match[2], ENT_QUOTES, 'UTF-8') : '';
	}
}
