<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {IMAGEPREVIEW=field} links to the image a form posted in that field.
 */
class imagepreviewShortcodeTest extends \Codeception\Test\Unit
{
	public function testThePreviewLinkRefusesAPostedScriptAddress()
	{
		$html = $this->render('javascript:alert(1)', 'x|0|0|');

		$this->assertStringContainsString('e-image-preview', $html);
		$this->assertStringNotContainsStringIgnoringCase("href='javascript:", $html);
	}

	public function testAPostedAddressCannotLeaveItsAttributes()
	{
		$html = $this->render("a.png' autofocus onfocus='alert(1)", 'x|0|0|1');

		$this->assertStringContainsString('e-image-preview', $html);
		$this->assertStringNotContainsString("' autofocus", $html);
	}

	public function testAThumbnailAddressIsEncodedOnce()
	{
		$html = $this->render('{e_IMAGE}generic/blank.gif', 'x|10|0|');

		$this->assertStringContainsString('&amp;w=10', $html);
		$this->assertStringNotContainsString('&amp;amp;', $html);
	}

	public function testAPostedImageStillPreviews()
	{
		$html = $this->render('{e_IMAGE}generic/blank.gif', 'x|0|0|1');

		$this->assertStringContainsString("e107_images/generic/blank.gif' rel='external shadowbox'", $html);
	}

	/**
	 * @param string $posted
	 * @param string $parameters
	 * @return string
	 */
	private function render($posted, $parameters)
	{
		$form = $_POST;
		$_POST = array('x' => $posted);

		try
		{
			return e107::getParser()->parseTemplate('{IMAGEPREVIEW='.$parameters.'}', true);
		}
		finally
		{
			$_POST = $form;
		}
	}
}
