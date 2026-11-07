<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class error_pageTest extends \Test\Unit
{
	/** @var ReflectionProperty */
	private $responseProp;

	/** @var eResponse|null */
	private $originalResponse;

	protected function _before()
	{
		require_once(e_HANDLER . 'error_page_class.php');
		e107::getUrl();

		$this->responseProp = new \e107\Reflection\ReflectionProperty('eFront', '_response');
		$this->originalResponse = $this->responseProp->getValue(eFront::instance());
	}

	protected function _after()
	{
		$this->responseProp->setValue(eFront::instance(), $this->originalResponse);
	}

	/**
	 * Standalone error.php renders before eFront has a response object.
	 */
	public function testRenderWithoutFrontResponse()
	{
		$this->responseProp->setValue(eFront::instance(), null);

		foreach(array(400, 401, 403, 404, 500) as $code)
		{
			$body = @e107::getError()->render($code);
			$this->assertIsString($body);
			$this->assertNotEmpty($body, "render($code) returned empty output");
		}
	}

	/**
	 * SEF requests through index.php still get title, render mode and body.
	 */
	public function testRenderPopulatesFrontResponse()
	{
		$response = new eResponse();
		$this->responseProp->setValue(eFront::instance(), $response);

		$body = @e107::getError()->render(404);

		$this->assertSame(LAN_ERROR, $response->getTitle());
		$this->assertSame('error_page_404', $response->getRenderMod());
		$this->assertSame($body, $response->getBody());
	}
}
