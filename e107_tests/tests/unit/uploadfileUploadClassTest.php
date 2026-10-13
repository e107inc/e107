<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {UPLOADFILE} outside the admin area against the site's upload settings.
 */
class uploadfileUploadClassTest extends \Codeception\Test\Unit
{
	/** @var mixed */
	private $uploadClass;

	/** @var mixed */
	private $uploadEnabled;

	/** @var mixed */
	private $phpunit;

	protected function _before()
	{
		global $_E107;

		$this->uploadClass = e107::getPref('upload_class');
		$this->uploadEnabled = e107::getPref('upload_enabled');
		e107::getConfig()->set('upload_enabled', 1);
		$this->phpunit = isset($_E107['phpunit']) ? $_E107['phpunit'] : null;
		$_E107['phpunit'] = true;
	}

	protected function _after()
	{
		global $_E107;

		e107::getConfig()->set('upload_class', $this->uploadClass);
		e107::getConfig()->set('upload_enabled', $this->uploadEnabled);
		$_E107['phpunit'] = $this->phpunit;
	}

	public function testTheUploadFormHonoursWhoMayUpload()
	{
		e107::getConfig()->set('upload_class', e_UC_NOBODY);

		$this->assertStringNotContainsString("type='file'", e107::getParser()->parseTemplate('{UPLOADFILE=|nowarn}', true));
	}

	public function testTheUploadFormHonoursTheUploadsSwitch()
	{
		e107::getConfig()->set('upload_class', e_UC_PUBLIC);
		e107::getConfig()->set('upload_enabled', 0);

		$this->assertStringNotContainsString("type='file'", e107::getParser()->parseTemplate('{UPLOADFILE=|nowarn}', true));
	}

	public function testTheUploadFormStillShowsForThoseWhoMayUpload()
	{
		e107::getConfig()->set('upload_class', e_UC_PUBLIC);

		$this->assertStringContainsString("type='file'", e107::getParser()->parseTemplate('{UPLOADFILE=|nowarn}', true));
	}
}
