<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * What the Drupal provider writes into the avatar directory.
 *
 * e_AVATAR_UPLOAD is e107_media/<site>/avatars/upload/, inside the document
 * root, and the source site chooses both the name and the bytes. The provider
 * used to fopen() the remote URL and store basename() of it, so a Drupal row
 * naming a picture shell.php landed a file called shell.php there.
 */
class drupalAvatarTest extends \Test\Unit
{

	/** 2x2 PNG, so getimagesize() has something real to report. */
	const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=';

	/** @var drupal_avatar_double */
	private $drupal;

	/** @var string */
	private $uploadDir;

	protected function _before()
	{
		e107::lan('import', true);

		require_once(e_PLUGIN.'import/providers/drupal_import_class.php');
		require_once(__DIR__.'/drupal_avatar_double.php');

		$this->uploadDir = e_AVATAR_UPLOAD;

		if (!is_dir($this->uploadDir))
		{
			mkdir($this->uploadDir, 0755, true);
		}

		$_POST = array('version' => 6, 'baseUrl' => 'drupal.example.net');

		$this->drupal = new drupal_avatar_double();
		$this->drupal->init();
	}

	protected function _after()
	{
		$_POST = array();

		foreach (glob($this->uploadDir.'ap_*') ?: array() as $file)
		{
			@unlink($file);
		}

		foreach (glob(e_TEMP.'import_*.tmp') ?: array() as $file)
		{
			@unlink($file);
		}
	}

	/**
	 * The extension is the verifier's answer, and the member id still leads the
	 * name so two members with a picture.gif do not land on the same file.
	 */
	public function testTheStoredAvatarIsNamedForItsBytes()
	{
		$this->drupal->bytes = base64_decode(self::PNG);

		$path = $this->drupal->fileSaveAvatar(array(
			'uid'     => 12,
			'picture' => 'sites/default/files/picture.gif',
		));

		self::assertMatchesRegularExpression(
			'/^-upload-ap_0000012_picture_[0-9a-f]{10}\.png$/', $path);
		self::assertFileExists($this->uploadDir.substr($path, strlen('-upload-')));
		self::assertSame(array(), glob($this->uploadDir.'ap_*.gif'),
			'Nothing may be stored under the extension the source site asked for');
	}

	/**
	 * The address is built from the source site's own base URL, so the fetch has
	 * to be the one e_file::isUrlSafe() guards rather than a bare fopen().
	 */
	public function testTheFetchAsksForTheSourceSitesOwnUrl()
	{
		$this->drupal->bytes = base64_decode(self::PNG);

		$this->drupal->fileSaveAvatar(array('uid' => 12, 'picture' => 'files/picture.gif'));

		self::assertSame(array('http://drupal.example.net/files/picture.gif'),
			$this->drupal->requested);
	}

	/**
	 * A row whose picture is not an image stores nothing, whatever it is called.
	 */
	public function testAPayloadNamedPhpIsNotStored()
	{
		$this->drupal->bytes = '<?php echo "payload"; ?>';

		self::assertSame('', $this->drupal->fileSaveAvatar(array(
			'uid'     => 12,
			'picture' => 'sites/default/files/shell.php',
		)));
		self::assertSame(array(), glob($this->uploadDir.'ap_*'),
			'Nothing at all may be left for this member');
	}

	/**
	 * A refusal is said out loud. A migration usually reads from a site on the
	 * same host, which e_file::isUrlSafe() declines, and a run that reported
	 * success with every avatar missing would say nothing at all.
	 */
	public function testARefusedAvatarIsReported()
	{
		e107::getMessage()->reset();

		$this->drupal->bytes = '';

		self::assertSame('', $this->drupal->fileSaveAvatar(array(
			'uid'     => 12,
			'picture' => 'sites/default/files/picture.gif',
		)));

		$warnings = e107::getMessage()->get(E_MESSAGE_WARNING, 'default', true);

		self::assertNotEmpty($warnings, 'A refused avatar must leave a warning behind');
		self::assertStringContainsString('drupal.example.net/sites/default/files/picture.gif',
			implode(' ', (array) $warnings));
	}
}
