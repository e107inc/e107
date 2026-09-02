<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class private_messageAttachmentPathsTest extends \Codeception\Test\Unit
{
	/** @var private_message_attachment_double */
	private $pm;

	/** @var string */
	private $root;

	protected function _before()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');
		require_once(__DIR__ . '/private_message_attachment_double.php');

		$this->root = e_TEMP . 'pm_attachment_paths_' . uniqid() . '/';

		$this->pm = new private_message_attachment_double();
		$this->pm->root = $this->root;
	}

	protected function _after()
	{
		$this->removeTree(rtrim($this->root, '/'));
	}

	/**
	 * The member's own directory and the one holding every member's, both
	 * covered before anything is stored in either, and the caller told so.
	 */
	public function testProtectAttachmentPathsCoversTheMemberDirectoryAndItsParent()
	{
		self::assertTrue($this->pm->protectAttachmentPaths(12));

		self::assertFileExists($this->root . '.htaccess');
		self::assertFileExists($this->root . 'index.html');
		self::assertFileExists($this->root . 'user_000012/.htaccess');
		self::assertFileExists($this->root . 'user_000012/index.html');
	}

	/**
	 * A guest posts to the same directory e_file::getUserDir() gives a guest.
	 */
	public function testAGuestGetsTheAnonDirectory()
	{
		self::assertTrue($this->pm->protectAttachmentPaths(0));

		self::assertFileExists($this->root . 'anon/.htaccess');
	}

	/**
	 * The answer the whole package turns on. A host where the rules cannot be
	 * written is a host where an attachment must not be stored, and the only
	 * way the caller can know is this return value.
	 */
	public function testProtectAttachmentPathsRefusesWhenTheDirectoryCannotBeMade()
	{
		$file = rtrim($this->root, '/');

		file_put_contents($file, 'not a directory');

		self::assertFalse($this->pm->protectAttachmentPaths(12));
		self::assertFalse(is_file($file . '/user_000012/.htaccess'),
			'A guard file was written below a path that is a file, not a directory');
	}

	/**
	 * The one-time repair the plugin's install and upgrade hooks run. Every
	 * member directory already on disk is covered, with nobody sending
	 * anything and no member id passed in.
	 */
	public function testProtectStoredAttachmentsCoversEveryMemberDirectoryAlreadyThere()
	{
		mkdir($this->root . 'user_000012', 0755, true);
		mkdir($this->root . 'user_000034', 0755, true);
		mkdir($this->root . 'anon', 0755, true);

		self::assertTrue($this->pm->protectStoredAttachments());

		self::assertFileExists($this->root . '.htaccess');
		self::assertFileExists($this->root . 'user_000012/.htaccess');
		self::assertFileExists($this->root . 'user_000034/.htaccess');
		self::assertFileExists($this->root . 'anon/.htaccess');
	}

	/**
	 * A site that has never stored an attachment has nothing to repair, and
	 * saying so is not a failure.
	 */
	public function testProtectStoredAttachmentsIsHappyWithNothingToDo()
	{
		self::assertTrue($this->pm->protectStoredAttachments());
		self::assertFalse(is_dir($this->root), 'Nothing to protect must not mean a directory gets made');
	}

	/**
	 * The answer the install and upgrade hooks act on. A directory the rules
	 * cannot be written into is the one an administrator has to hear about, and
	 * PM_ADM_11 is written off this return.
	 */
	public function testProtectStoredAttachmentsRefusesADirectoryItCannotWriteInto()
	{
		if(function_exists('posix_geteuid') && posix_geteuid() === 0)
		{
			$this->markTestSkipped('root can write anywhere, so there is no unwritable directory to try');
		}

		mkdir($this->root, 0755, true);
		mkdir($this->root . 'user_000012', 0555);

		self::assertFalse($this->pm->protectStoredAttachments());
		self::assertFileExists($this->root . '.htaccess', 'The directories it could cover are still covered');
		self::assertFileDoesNotExist($this->root . 'user_000012/.htaccess');

		chmod($this->root . 'user_000012', 0755);
	}

	/**
	 * @param string $path
	 * @return void
	 */
	private function removeTree($path)
	{
		if (is_file($path))
		{
			unlink($path);

			return;
		}

		if (!is_dir($path))
		{
			return;
		}

		foreach (array_diff(scandir($path), array('.', '..')) as $entry)
		{
			$this->removeTree($path . '/' . $entry);
		}

		rmdir($path);
	}
}
