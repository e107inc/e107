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
class uploadfileUploadClassTest extends \Test\Unit
{
	/** @var mixed */
	private $uploadClass;

	/** @var mixed */
	private $uploadEnabled;

	protected function _before()
	{
		$this->uploadClass = e107::getPref('upload_class');
		$this->uploadEnabled = e107::getPref('upload_enabled');
		e107::getConfig()->set('upload_enabled', 1);
	}

	protected function _after()
	{
		e107::getConfig()->set('upload_class', $this->uploadClass);
		e107::getConfig()->set('upload_enabled', $this->uploadEnabled);
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
