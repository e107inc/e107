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
 * What base_import_class does with bytes a feed sent it.
 *
 * The acceptance suite drives the same code over real HTTP; this says what the
 * answer is for payloads it would be awkward to serve, and it is what the
 * release/v2.3.x backport can be checked against on PHP 5.6.
 */
class importRemoteImageTest extends \Codeception\Test\Unit
{
	/** 2x2 images, so getimagesize() has something real to report. */
	const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=';

	const GIF = 'R0lGODdhAgACAIAAAAQCBAAAACwAAAAAAgACAAACAoRRADs=';

	const BMP = 'Qk1GAAAAAAAAADYAAAAoAAAAAgAAAAIAAAABABgAAAAAABAAAAATCwAAEwsAAAAAAAAAAAAA////////AAAA////////AAA=';

	/** @var import_remote_image_double */
	private $import;

	/** @var string directory the importer stores into */
	private $dir;

	protected function _before()
	{
		require_once(e_PLUGIN.'import/providers/rss_import_class.php');
		require_once(__DIR__.'/import_remote_image_double.php');

		$this->dir = e_TEMP.'import_target_'.uniqid().'/';
		mkdir($this->dir);

		$this->import = new import_remote_image_double();
	}

	protected function _after()
	{
		foreach (glob($this->dir.'*') ?: array() as $file)
		{
			@unlink($file);
		}

		@rmdir($this->dir);

		foreach (glob(e_TEMP.'import_*.tmp') ?: array() as $file)
		{
			@unlink($file);
		}
	}

	/**
	 * The extension is the verifier's answer, never the feed's.
	 */
	public function testTheStoredExtensionComesFromTheBytes()
	{
		$this->import->bytes = base64_decode(self::PNG);

		$name = $this->import->storeRemoteImage('http://feed.example.net/logo.jpg', $this->dir);

		self::assertMatchesRegularExpression('/^logo_[0-9a-f]{10}\.png$/', $name);
		self::assertFileExists($this->dir.$name);
		self::assertSame(array($this->dir.$name), glob($this->dir.'*'),
			'Nothing under the extension the URL asked for may be left behind');
	}

	/**
	 * The same for a format whose extension the feed got right.
	 */
	public function testAnImageWhoseUrlWasHonestKeepsItsName()
	{
		$this->import->bytes = base64_decode(self::GIF);

		self::assertMatchesRegularExpression('/^banner_[0-9a-f]{10}\.gif$/',
			$this->import->storeRemoteImage('http://feed.example.net/banner.gif', $this->dir));
	}

	/**
	 * A format e107's own media tables carry and getimagesize() reports is a
	 * format this imports, so the hardening does not quietly narrow the feature.
	 */
	public function testABitmapIsStoredUnderItsOwnExtension()
	{
		$this->import->bytes = base64_decode(self::BMP);

		self::assertMatchesRegularExpression('/^logo_[0-9a-f]{10}\.bmp$/',
			$this->import->storeRemoteImage('http://feed.example.net/logo.png', $this->dir));
	}

	/**
	 * The defect, one layer down from the acceptance suite: a payload that is
	 * not an image is not written whatever the URL called it.
	 */
	public function testAPayloadThatIsNotAnImageIsNotStored()
	{
		$this->import->bytes = '<?php echo "payload"; ?>';

		self::assertFalse($this->import->storeRemoteImage('http://feed.example.net/shell.php', $this->dir));
		self::assertSame(array(), glob($this->dir.'*'),
			'Nothing at all may be left in the directory');
	}

	/**
	 * The same payload with the URL dressed up as an image.
	 */
	public function testAPayloadNamedAsAnImageIsNotStoredEither()
	{
		$this->import->bytes = '<?php echo "payload"; ?>';

		self::assertFalse($this->import->storeRemoteImage('http://feed.example.net/x.jpg', $this->dir));
		self::assertSame(array(), glob($this->dir.'*'));
	}

	/**
	 * getimagesize() cannot read SVG, and an SVG carries script. A format that
	 * cannot be verified is a format this does not import.
	 */
	public function testAnSvgIsNotStored()
	{
		$this->import->bytes = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

		self::assertFalse($this->import->storeRemoteImage('http://feed.example.net/icon.svg', $this->dir));
	}

	/**
	 * An empty answer is not an image either, and the staged file goes with it.
	 */
	public function testAnEmptyAnswerLeavesNothingStaged()
	{
		$this->import->bytes = '';

		self::assertFalse($this->import->storeRemoteImage('http://feed.example.net/empty.png', $this->dir));
		self::assertSame(array(), glob(e_TEMP.'import_*.tmp'),
			'The staging file must not be left in the temporary directory');
	}

	/**
	 * A remote host that never answers writes nothing and leaves nothing.
	 */
	public function testAnUnreachableRemoteStoresNothing()
	{
		$this->import->reachable = false;

		self::assertFalse($this->import->storeRemoteImage('http://feed.example.net/logo.png', $this->dir));
		self::assertSame(array(), glob($this->dir.'*'));
		self::assertSame(array(), glob(e_TEMP.'import_*.tmp'));
	}

	/**
	 * Everything the remote host chose about the name is dropped except a stem
	 * an administrator can recognise. A query string, a directory separator and
	 * a second extension are all the remote host's to choose.
	 */
	public function testTheLocalNameKeepsNothingTheRemoteChose()
	{
		$this->import->bytes = base64_decode(self::PNG);

		self::assertMatchesRegularExpression('/^holiday_snap_[0-9a-f]{10}\.png$/',
			$this->import->storeRemoteImage(
				'http://feed.example.net/albums/2026/holiday snap.php?as=.jpg', $this->dir));
	}

	/**
	 * A URL with no usable path still gets a name, and it is this side's.
	 */
	public function testAUrlWithNoNameStillGetsOne()
	{
		$this->import->bytes = base64_decode(self::PNG);

		$name = $this->import->storeRemoteImage('http://feed.example.net/', $this->dir);

		self::assertMatchesRegularExpression('/^image_[0-9a-f]{10}\.png$/', $name);
	}

	/**
	 * A caller's own leader is kept, and is no looser than the stem beside it.
	 * The Drupal provider stores avatars under one so two members importing a
	 * picture of the same name do not land on the same file.
	 */
	public function testACallersPrefixLeadsTheStoredName()
	{
		$this->import->bytes = base64_decode(self::PNG);

		self::assertMatchesRegularExpression('/^ap_0000012_picture_[0-9a-f]{10}\.png$/',
			$this->import->storeRemoteImage(
				'http://drupal.example.net/sites/default/files/picture.gif', $this->dir, 'ap_0000012_'));

		self::assertMatchesRegularExpression('/^evil_x_[0-9a-f]{10}\.png$/',
			$this->import->storeRemoteImage('http://drupal.example.net/x.gif', $this->dir, '../evil_'));
	}

	/**
	 * The same address read twice is the same file, and is not fetched twice.
	 *
	 * A feed re-read every hour would otherwise download every image it already
	 * holds, and could overwrite a stored copy with whatever answers now.
	 */
	public function testAStoredFileIsNotFetchedOrWrittenAgain()
	{
		$this->import->bytes = base64_decode(self::PNG);

		$first = $this->import->storeRemoteImage('http://feed.example.net/logo.png', $this->dir);

		$this->import->bytes = base64_decode(self::GIF);

		self::assertSame($first,
			$this->import->storeRemoteImage('http://feed.example.net/logo.png', $this->dir));
		self::assertSame(base64_decode(self::PNG), file_get_contents($this->dir.$first),
			'The stored copy must be the one already there');
		self::assertCount(1, $this->import->requested,
			'The second run must not go back to the remote host at all');
	}

	/**
	 * Two images from one feed sharing a basename are two images.
	 *
	 * A WordPress feed carrying 2020/01/header.jpg and 2021/05/header.jpg is
	 * the ordinary case, and storing one file for both would rewrite the second
	 * article's body to the first article's picture.
	 */
	public function testTwoUrlsSharingABasenameDoNotCollide()
	{
		$this->import->bytes = base64_decode(self::PNG);

		$first = $this->import->storeRemoteImage('http://feed.example.net/2020/01/header.jpg', $this->dir);
		$second = $this->import->storeRemoteImage('http://feed.example.net/2021/05/header.jpg', $this->dir);

		self::assertNotSame($first, $second, 'Two addresses must give two files');
		self::assertCount(2, glob($this->dir.'*'));
		self::assertCount(2, $this->import->requested,
			'Neither address may be answered with the other one already stored');
	}

	/**
	 * The extension is not part of what makes an address distinct either, so
	 * logo.jpg and logo.jpeg from the same feed stay two files.
	 */
	public function testTwoUrlsDifferingOnlyInExtensionDoNotCollide()
	{
		$this->import->bytes = base64_decode(self::PNG);

		self::assertNotSame(
			$this->import->storeRemoteImage('http://feed.example.net/logo.jpg', $this->dir),
			$this->import->storeRemoteImage('http://feed.example.net/logo.jpeg', $this->dir));
	}

	/**
	 * The whole method, with the preference on: the body comes back pointing at
	 * the local copy and the local copy is named for what it is.
	 */
	public function testSaveImagesRewritesTheBodyToTheVerifiedLocalCopy()
	{
		$_POST = array('rss_feed' => 'http://feed.example.net/unit.xml', 'rss_saveimages' => 1);

		$this->import->init();
		$this->import->bytes = base64_decode(self::PNG);

		$body = $this->import->saveImages(
			'<p><img src="http://feed.example.net/logo.jpg" alt="x" /></p>', 'news');

		$dir = e_MEDIA.'images/'.substr(md5('http://feed.example.net/unit.xml'), 0, 10).'/';

		try
		{
			$stored = glob($dir.'*');

			self::assertStringNotContainsString('http://feed.example.net/logo.jpg', $body);
			self::assertCount(1, $stored, 'One image in, one file out');
			self::assertMatchesRegularExpression('/^logo_[0-9a-f]{10}\.png$/', basename($stored[0]));
			self::assertStringContainsString(basename($stored[0]), $body,
				'The body must point at the file that was actually stored');
		}
		finally
		{
			foreach(glob($dir.'*') ?: array() as $file)
			{
				@unlink($file);
			}

			@rmdir($dir);
			$_POST = array();
		}
	}
}
