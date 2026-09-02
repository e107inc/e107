<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Failure reporting for e_file::unzipArchive(), issue #6119.
 *
 * Several unrelated faults shared one message describing only one of them, and
 * an archive whose detected root turned out not to be a folder said nothing at
 * all. Only the failure paths are driven here: a successful unzip renames its
 * folder into e_PLUGIN or e_THEME, which is not a unit test's business.
 *
 * _before() clears what a refused archive must not leave behind, so the
 * "was never written" assertions below mean what they say.
 *
 * COVERAGE GAPS, deliberate:
 *
 *  1. The missing-zip-extension path needs class_exists('ZipArchive') to be
 *     false, which no test can arrange without runkit.
 *  2. The $dir === '.' and $dir === '..' comparisons in unusableRootFolder().
 *     A fixture for either is destructive without the fix, which is the point
 *     of them: "." reaches removeDir(e_TEMP . '.') and empties e_TEMP under
 *     the rest of the suite, and ".." reaches removeDir(e_TEMP . '..') and
 *     deletes this install's e107_system folder. Driving them red would break
 *     the run that proves them. See issue #6216.
 */

class e_fileUnzipArchiveTest extends \Test\Unit
{
	/** @var e_file */
	private $fl;

	/** @var string[] absolute paths of the fixtures this test wrote */
	private $fixtures = array();

	protected function _before()
	{
		try
		{
			$this->fl = $this->make('e_file');
		}
		catch(Exception $e)
		{
			self::fail($e->getMessage());
		}

		e107::getMessage()->reset(false, false, true);

		@unlink(e_TEMP . 'plugin.php');
		@unlink(e_BACKUP . '.zip');
		e107::getFile()->removeDir(e_TEMP . 'outer');
	}

	protected function _after()
	{
		foreach($this->fixtures as $path)
		{
			@unlink($path);
		}

		$this->fixtures = array();

		e107::getMessage()->reset(false, false, true);
	}

	/**
	 * A file ZipArchive cannot open is not an archive with a missing root
	 * folder, and saying so sent people looking inside a file that was never read.
	 */
	public function testAnUnopenableFileIsNotReportedAsAMissingRootFolder()
	{
		$localfile = $this->seedFile('e6119-notazip.zip', 'This is not a zip archive.');

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin'));

		$reported = $this->reportedErrors();

		self::assertStringContainsString("Couldn't open the archive.", $reported,
			'A file ZipArchive::open() refused was not reported as unopenable.');
		self::assertStringNotContainsString('root folder', $reported,
			'A file that was never opened was blamed for the folders it contains.');
		self::assertFileDoesNotExist(e_TEMP . $localfile,
			'The rejected download was left behind in e_TEMP.');
	}

	/**
	 * The one case the old message did describe keeps its wording, because two
	 * acceptance Cests and a decade of search results are anchored to it.
	 */
	public function testAnArchiveWithNoRootFolderKeepsItsMessage()
	{
		$localfile = $this->seedZip('e6119-rootless.zip', array('deeper/still/readme.txt' => 'nothing usable here'));

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin'));

		self::assertStringContainsString("Couldn't detect the root folder in the zip.", $this->reportedErrors());
		self::assertFileDoesNotExist(e_TEMP . $localfile,
			'The rejected download was left behind in e_TEMP.');
	}

	/**
	 * A plugin zipped from inside its own folder puts plugin.php at the root, so
	 * the scan takes that file for the folder name and nothing is there to move.
	 */
	public function testAnArchiveZippedFromInsideItsFolderIsRefusedWithoutUnpacking()
	{
		$localfile = $this->seedZip('e6119-contents.zip', array('plugin.php' => '<?php // fixture'));

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin'));

		self::assertNotSame('', $this->reportedErrors(),
			'An archive whose root is a file failed silently.');
		self::assertFileDoesNotExist(e_TEMP . 'plugin.php',
			'An archive that cannot be installed was unpacked into e_TEMP anyway.');
		self::assertFileDoesNotExist(e_TEMP . $localfile,
			'The rejected download was left behind in e_TEMP.');
	}

	/**
	 * The detected root is pasted onto e_TEMP, e_BACKUP and the destination
	 * without ever being checked, so it has to be one plain folder name.
	 */
	public function testARootFolderThatIsNotAPlainNameIsRefused()
	{
		$localfile = $this->seedZip('e6119-nested.zip', array('outer/inner/plugin.php' => '<?php // fixture'));

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin'));

		self::assertStringContainsString('plain folder name', $this->reportedErrors(),
			'A root folder spanning more than one path segment was accepted.');
		self::assertFileDoesNotExist(e_TEMP . 'outer',
			'An archive that cannot be installed was unpacked into e_TEMP anyway.');
		self::assertFileDoesNotExist(e_TEMP . $localfile,
			'The rejected download was left behind in e_TEMP.');
	}

	/**
	 * The backup copy ran before the root folder was known, so every failure
	 * dropped a file named ".zip" into e_BACKUP.
	 */
	public function testAFailedUnzipLeavesNoAnonymousBackup()
	{
		$localfile = $this->seedZip('e6119-nobackup.zip', array('deeper/still/readme.txt' => 'nothing usable here'));

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin'));

		self::assertFileDoesNotExist(e_BACKUP . '.zip',
			'A failed unzip copied its download to e_BACKUP under an empty name.');
	}

	private function reportedErrors()
	{
		$errors = e107::getMessage()->get('error', 'default', true, true);

		return is_array($errors) ? implode("\n", $errors) : (string) $errors;
	}

	private function seedFile($localfile, $content)
	{
		file_put_contents(e_TEMP . $localfile, $content);
		$this->fixtures[] = e_TEMP . $localfile;

		return $localfile;
	}

	private function seedZip($localfile, array $entries)
	{
		@unlink(e_TEMP . $localfile);

		$zip = new ZipArchive;
		self::assertTrue($zip->open(e_TEMP . $localfile, ZipArchive::CREATE) === true,
			'The fixture archive could not be created in e_TEMP.');

		foreach($entries as $name => $content)
		{
			$zip->addFromString($name, $content);
		}

		$zip->close();
		$this->fixtures[] = e_TEMP . $localfile;

		return $localfile;
	}
}
