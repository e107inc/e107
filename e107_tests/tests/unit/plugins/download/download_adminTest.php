<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The Local tab of the download editor takes a path relative to e_DOWNLOAD
 * beside the Media Manager picker, so a file that is on disk without a
 * core_media row of its own can still be attached. Nothing e_DOWNLOAD does not
 * hold may be stored.
 */
class download_adminTest extends \Test\Unit
{
	/** @var download_main_admin_ui */
	private $admin;

	/** @var string name of the file this test puts in the downloads directory */
	private $file;

	/** @var string|null working directory e_DOWNLOAD was relative to before the test */
	private $cwd;

	/** @var bool whether the downloads directory had to be created for the test */
	private $madeDirectory = false;

	protected function _before()
	{
		e107::plugLan('download', 'global', true);
		e107::plugLan('download', 'admin', true);

		require_once(e_HANDLER . 'admin_ui.php');
		require_once(e_PLUGIN . 'download/includes/admin.php');

		$class       = new ReflectionClass('download_main_admin_ui');
		$this->admin = $class->newInstanceWithoutConstructor();

		$this->cwd = getcwd();
		chdir(APP_PATH);

		$this->madeDirectory = !is_dir(e_DOWNLOAD) && mkdir(e_DOWNLOAD, 0755, true);

		$this->file = 'download_adminTest-' . uniqid() . '.txt';

		if(file_put_contents(e_DOWNLOAD . $this->file, 'download_adminTest') === false)
		{
			$this->markTestSkipped('the downloads directory is not writable in this environment');
		}
	}

	protected function _after()
	{
		if($this->file !== null && file_exists(e_DOWNLOAD . $this->file))
		{
			unlink(e_DOWNLOAD . $this->file);
		}

		if($this->madeDirectory)
		{
			rmdir(e_DOWNLOAD);
		}

		if($this->cwd !== null)
		{
			chdir($this->cwd);
		}
	}

	public function testAPathInTheDownloadsDirectoryResolvesToItsRelativeForm()
	{
		$this->assertSame($this->file, $this->localDownloadPath($this->file));
		$this->assertSame($this->file, $this->localDownloadPath('/' . $this->file), 'a leading separator is the admin naming the same file');
	}

	public function testAPathClimbingOutOfTheDownloadsDirectoryIsRefused()
	{
		$segments = array_diff(explode('/', trim(str_replace('\\', '/', e_DOWNLOAD), '/')), array('', '.'));
		$escape   = str_repeat('../', count($segments)) . 'class2.php';

		$this->assertFileExists(e_DOWNLOAD . $escape, 'the escape this refuses has to name something real, or it proves nothing');
		$this->assertFalse($this->localDownloadPath($escape));
	}

	public function testAPathNamingNothingIsRefused()
	{
		$this->assertFalse($this->localDownloadPath('no-such-file-' . uniqid() . '.zip'));
		$this->assertFalse($this->localDownloadPath('  '));
		$this->assertFalse($this->localDownloadPath(''));
	}

	public function testADirectoryIsNotADownload()
	{
		$this->assertFalse($this->localDownloadPath('.'));
	}

	public function testTheLocalTabOffersATypedPathBesideThePicker()
	{
		$field = $this->localFileField('');

		$this->assertNotNull($this->inputValue($field, 'download_url'), 'the Media Manager picker stays');
		$this->assertNotNull($this->inputValue($field, 'download_url_local'), 'a file on disk with no core_media row needs a field that can name it');
	}

	public function testAStoredLocalPathIsTypedRatherThanPicked()
	{
		$field = $this->localFileField($this->file);

		$this->assertSame($this->file, $this->inputValue($field, 'download_url_local'));
		$this->assertSame('', $this->inputValue($field, 'download_url'), 'a value the typed field carries would otherwise be submitted twice');
	}

	public function testAMediaManagerValueStaysWithThePicker()
	{
		$field = $this->localFileField('{e_MEDIA_FILE}2026-05/document.pdf');

		$this->assertSame('{e_MEDIA_FILE}2026-05/document.pdf', $this->inputValue($field, 'download_url'));
		$this->assertSame('', $this->inputValue($field, 'download_url_local'));
	}

	public function testATypedPathIsUsedWhileTheMediaPickerIsUntouched()
	{
		self::assertSame($this->file, $this->submittedLocalPath(array(
			'download_url_local'  => $this->file,
			'download_url'        => '',
			'download_url_picked' => '',
		)));
	}

	/**
	 * The stored path of the very download this issue is about does not resolve,
	 * so the render leaves it with the picker; a path typed beside it is the
	 * admin repairing that record and has to win.
	 */
	public function testATypedPathIsUsedOverAStoredPathThatDoesNotResolve()
	{
		self::assertSame($this->file, $this->submittedLocalPath(array(
			'download_url_local'  => $this->file,
			'download_url'        => 'gone/missing.zip',
			'download_url_picked' => 'gone/missing.zip',
		)));
	}

	public function testAFreshMediaManagerSelectionWinsOverATypedPath()
	{
		self::assertNull($this->submittedLocalPath(array(
			'download_url_local'  => $this->file,
			'download_url'        => '{e_MEDIA_FILE}2026-05/document.pdf',
			'download_url_picked' => '',
		)));
	}

	/**
	 * Emptying the picker is not choosing a file, so the path typed beside it is
	 * still the only thing the admin said. Reading the cleared picker as a fresh
	 * selection threw the typed path away and stored nothing at all, which is the
	 * repair this field exists for.
	 */
	public function testAClearedMediaPickerLeavesTheTypedPathStanding()
	{
		self::assertSame($this->file, $this->submittedLocalPath(array(
			'download_url_local'  => $this->file,
			'download_url'        => '',
			'download_url_picked' => 'gone/missing.zip',
		)));
	}

	public function testAPostedFieldThatIsNotAPathLeavesTheSubmissionAlone()
	{
		self::assertNull($this->submittedLocalPath(array(
			'download_url_local'  => array($this->file),
			'download_url'        => '',
			'download_url_picked' => '',
		)));
	}

	public function testNothingTypedLeavesTheSubmissionAlone()
	{
		self::assertNull($this->submittedLocalPath(array('download_url_local' => '   ')));
		self::assertNull($this->submittedLocalPath(array()));
	}

	public function testATypedPathOutsideTheDownloadsDirectoryIsRefusedOnSubmit()
	{
		self::assertFalse($this->submittedLocalPath(array(
			'download_url_local'  => '../../../class2.php',
			'download_url'        => '',
			'download_url_picked' => '',
		)));
	}

	private function submittedLocalPath(array $posted)
	{
		$method = new ReflectionMethod('download_main_admin_ui', 'submittedLocalPath');
		$method->setAccessible(true);

		return $method->invoke($this->admin, $posted);
	}

	private function localDownloadPath($path)
	{
		$method = new ReflectionMethod('download_main_admin_ui', 'localDownloadPath');
		$method->setAccessible(true);

		return $method->invoke($this->admin, $path);
	}

	private function localFileField($downloadUrl)
	{
		$method = new ReflectionMethod('download_main_admin_ui', 'localFileField');
		$method->setAccessible(true);

		return $method->invoke($this->admin, $downloadUrl);
	}

	/**
	 * The value the input named $name carries, or null when the markup has no such input.
	 *
	 * @param string $html
	 * @param string $name
	 * @return string|null
	 */
	private function inputValue($html, $name)
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><div>' . $html . '</div>', LIBXML_NOWARNING | LIBXML_NOERROR);

		$xpath = new DOMXPath($dom);
		$input = $xpath->query("//input[@name='" . $name . "']")->item(0);

		return $input === null ? null : $input->getAttribute('value');
	}
}
