<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {@see e_file::currentUserMayUsePublicUploads()} against the uploads switch and the class allowed to upload.
 */
class e_fileCurrentUserMayUsePublicUploadsTest extends \Codeception\Test\Unit
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

	/**
	 * @return array
	 */
	public function settings()
	{
		return array(
			'switched on, open to everyone'  => array(1, 0, true),
			'switched off, open to everyone' => array(0, 0, false),
			'switched on, open to nobody'    => array(1, 255, false),
		);
	}

	/**
	 * @dataProvider settings
	 * @param int  $enabled
	 * @param int  $class
	 * @param bool $expected
	 */
	public function testItDecidesFromTheSwitchAndTheClass($enabled, $class, $expected)
	{
		e107::getConfig()->set('upload_enabled', $enabled);
		e107::getConfig()->set('upload_class', $class);

		self::assertSame($expected, e107::getFile()->currentUserMayUsePublicUploads());
	}
}
