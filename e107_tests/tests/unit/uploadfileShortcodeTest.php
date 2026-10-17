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
class uploadfileShortcodeTest extends \Codeception\Test\Unit
{
	/** @var array the upload preferences as the test found them */
	private $prefs = array();

	protected function _before()
	{
		foreach(array('upload_enabled' => 1, 'upload_class' => e_UC_PUBLIC) as $key => $value)
		{
			$this->prefs[$key] = e107::getPref($key);
			e107::getConfig()->set($key, $value);
		}
	}

	protected function _after()
	{
		foreach($this->prefs as $key => $value)
		{
			e107::getConfig()->set($key, $value);
		}
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
