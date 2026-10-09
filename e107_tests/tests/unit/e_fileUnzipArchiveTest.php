<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Failure reporting for e_file::unzipArchive(), issue #6119, and its moves
 * between file systems.
 *
 * Several unrelated faults shared one message describing only one of them, and
 * an archive whose detected root turned out not to be a folder said nothing at
 * all. A successful unzip moves its folder into e_PLUGIN, so only the updates
 * across file systems drive one, over a plugin written through the deployer,
 * which takes the folder back out when the test ends.
 *
 * _before() clears what a refused archive must not leave behind, so the
 * "was never written" assertions below mean what they say.
 *
 * COVERAGE GAPS, deliberate:
 *
 *  1. The PclZip fallback, which is what class_exists('ZipArchive') === false
 *     reaches on this branch, needs a host with no zip extension. No image in
 *     e107_tests/docker is one and no test can arrange it without runkit, so
 *     the whole legacy arm is undriven, including its own root-folder refusal.
 *  2. The $dir === '.' and $dir === '..' comparisons in unusableRootFolder().
 *     A fixture for either is destructive without the fix, which is the point
 *     of them: "." reaches removeDir(e_TEMP . '.') and empties e_TEMP under
 *     the rest of the suite, and ".." reaches removeDir(e_TEMP . '..') and
 *     deletes this install's e107_system folder. Driving them red would break
 *     the run that proves them. See issue #6216.
 */

class e_fileUnzipArchiveTest extends \Codeception\Test\Unit
{
	/** @var e_file */
	private $fl;

	/** @var string[] absolute paths of the fixtures this test wrote */
	private $fixtures = array();

	/** @var string[][] each folder linked to another file system, and where the real one was put aside */
	private $relocated = array();

	/** @var string[] the folders this test made on another file system */
	private $elsewhere = array();

	/** the plugin folder the update across file systems installs */
	const CROSS_DEVICE_PLUGIN = 'e107xdevfixture';

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
		$this->removeCrossDevicePlugin();
	}

	protected function _after()
	{
		foreach($this->relocated as $relocation)
		{
			list($folder, $aside) = $relocation;

			if(is_link($folder))
			{
				unlink($folder);
			}

			rename($aside, $folder);
		}

		foreach($this->elsewhere as $folder)
		{
			e107::getFile()->removeDir($folder);
		}

		$this->relocated = array();
		$this->elsewhere = array();
		$this->removeCrossDevicePlugin();

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
		self::assertFalse(file_exists(e_TEMP . $localfile),
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
		self::assertFalse(file_exists(e_TEMP . $localfile),
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
		self::assertFalse(file_exists(e_TEMP . 'plugin.php'),
			'An archive that cannot be installed was unpacked into e_TEMP anyway.');
		self::assertFalse(file_exists(e_TEMP . $localfile),
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
		self::assertFalse(file_exists(e_TEMP . 'outer'),
			'An archive that cannot be installed was unpacked into e_TEMP anyway.');
		self::assertFalse(file_exists(e_TEMP . $localfile),
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

		self::assertFalse(file_exists(e_BACKUP . '.zip'),
			'A failed unzip copied its download to e_BACKUP under an empty name.');
	}

	public function crossDeviceFolderProvider()
	{
		return array(
			'unpacked on another file system'  => array('e_TEMP'),
			'backed up to another file system' => array('e_BACKUP'),
		);
	}

	/**
	 * rename() cannot take a folder to another file system, as when e107_system
	 * is a container volume of its own, and the update stopped at "Couldn't Move".
	 *
	 * @dataProvider crossDeviceFolderProvider
	 */
	public function testAnUpdateMovesItsFoldersBetweenFileSystems($constant)
	{
		$this->relocate(constant($constant));

		$plugin = self::CROSS_DEVICE_PLUGIN;
		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/' . $plugin . '/plugin.php', '<?php // installed');
		$localfile = $this->seedZip('xdev-update.zip', array($plugin . '/plugin.php' => '<?php // update'));

		self::assertSame($plugin, $this->fl->unzipArchive($localfile, 'plugin', true), $this->reportedErrors());

		self::assertStringEqualsFile(e_PLUGIN . $plugin . '/plugin.php', '<?php // update');
		self::assertFalse(file_exists(e_TEMP . $plugin), 'The unpacked folder was left behind in e_TEMP.');

		$backups = glob(e_BACKUP . $plugin . '_*/plugin.php');
		self::assertCount(1, $backups, 'The installed plugin was not backed up.');
		self::assertStringEqualsFile($backups[0], '<?php // installed');
	}

	/**
	 * Copying a linked folder and then removing it would empty the folder the link points at, such as a plugin's working copy.
	 */
	public function testAMoveBetweenFileSystemsLeavesWhatALinkedFolderPointsAtAlone()
	{
		$elsewhere = $this->otherFileSystem();
		$link = e_TEMP . self::CROSS_DEVICE_PLUGIN . '-link';
		$target = realpath(e_TEMP) . '/' . self::CROSS_DEVICE_PLUGIN . '-target';
		mkdir($target);
		file_put_contents($target . '/plugin.php', '<?php // installed');
		symlink($target, $link);

		$moveDir = new \e107\Reflection\ReflectionMethod('e_file', 'moveDir');

		self::assertFalse($moveDir->invoke($this->fl, $link, $elsewhere . '/' . self::CROSS_DEVICE_PLUGIN));
		self::assertStringEqualsFile($target . '/plugin.php', '<?php // installed', 'The move emptied the folder the link points at.');
	}

	/**
	 * A link to nothing, deep in the unpacked folder, stands in for any entry the copy cannot take, as on a full disk.
	 */
	public function testAnUpdateThatCannotMoveItsFolderPutsTheInstalledPluginBack()
	{
		$this->relocate(e_TEMP);

		$plugin = self::CROSS_DEVICE_PLUGIN;
		$this->getModule('\Helper\Unit')->writeAppFile('e107_plugins/' . $plugin . '/plugin.php', '<?php // installed');
		mkdir(e_TEMP . $plugin . '/sub', 0755, true);
		symlink(e_TEMP . $plugin . '/sub/missing', e_TEMP . $plugin . '/sub/broken');
		$localfile = $this->seedZip('xdev-unmovable.zip', array($plugin . '/plugin.php' => '<?php // update'));

		self::assertFalse($this->fl->unzipArchive($localfile, 'plugin', true));

		self::assertStringEqualsFile(e_PLUGIN . $plugin . '/plugin.php', '<?php // installed',
			'The installed plugin was left in e_BACKUP.');
		self::assertSame(array(), glob(e_BACKUP . $plugin . '_*'), 'A copy of the installed plugin was left in e_BACKUP.');
	}

	/**
	 * Puts $folder aside and links a folder on another file system in its place.
	 *
	 * @param string $folder
	 * @return void
	 */
	private function relocate($folder)
	{
		$folder = rtrim($folder, '/');
		$elsewhere = $this->otherFileSystem();
		$aside = $folder . '-aside-' . uniqid();
		rename($folder, $aside);
		$this->relocated[] = array($folder, $aside);
		symlink($elsewhere, $folder);
	}

	/**
	 * Makes a folder on a file system other than e_PLUGIN's, or skips the test where the temporary folder offers none.
	 *
	 * @return string
	 */
	private function otherFileSystem()
	{
		$elsewhere = sys_get_temp_dir() . '/e107-xdev-' . uniqid();
		mkdir($elsewhere, 0755, true);
		$this->elsewhere[] = $elsewhere;

		if(stat($elsewhere)['dev'] === stat(e_PLUGIN)['dev'])
		{
			self::markTestSkipped('The temporary folder shares a file system with e_PLUGIN, so no move here crosses one.');
		}

		return $elsewhere;
	}

	private function removeCrossDevicePlugin()
	{
		$fl = e107::getFile();
		$link = e_TEMP . self::CROSS_DEVICE_PLUGIN . '-link';

		if(is_link($link))
		{
			unlink($link);
		}

		$fl->removeDir(e_TEMP . self::CROSS_DEVICE_PLUGIN);
		$fl->removeDir(e_TEMP . self::CROSS_DEVICE_PLUGIN . '-target');

		foreach((array) glob(e_BACKUP . self::CROSS_DEVICE_PLUGIN . '_*') as $backup)
		{
			$fl->removeDir($backup);
		}

		@unlink(e_BACKUP . self::CROSS_DEVICE_PLUGIN . '.zip');
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
