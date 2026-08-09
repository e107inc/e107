<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class rss_importTest extends \Test\Unit
{

	/** 2x2 PNG, so getimagesize() has something real to report. */
	const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=';

	/** @var rss_import */
	private $rss;

	/** @var string feed URL the importer derives its directory from */
	private $feed = 'http://feed.example.net/unit.xml';

	protected function _before()
	{
		require_once(e_PLUGIN.'import/providers/rss_import_class.php');

		$_POST = array();
		$this->rss = new rss_import();
	}

	protected function _after()
	{
		$_POST = array();

		$dir = $this->importDir();

		if (is_dir($dir))
		{
			foreach (glob($dir.'/*') ?: array() as $file)
			{
				@unlink($file);
			}

			@rmdir($dir);
		}
	}

	/**
	 * The advertised opt-out. rss_import assigns $this->saveImages in init()
	 * and declares saveImages() as a method, so the assignment writes a
	 * property nothing reads and every import downloads whatever the feed
	 * carries.
	 *
	 * Read off the disk. The body is returned unchanged either way when the
	 * fetch fails, so a body assertion passes on vulnerable code.
	 */
	public function testTheImageOptOutIsHonoured()
	{
		$_POST['rss_feed'] = $this->feed;
		$_POST['rss_saveimages'] = '';

		$this->rss->init();

		$body = '<p><img src="http://127.0.0.1/logo.png" alt="x" /></p>';

		self::assertSame($body, $this->rss->saveImages($body, 'news'),
			'With image saving off the body must come back untouched');
		self::assertDirectoryDoesNotExist($this->importDir(),
			'With image saving off the importer must not create its directory');
	}

	/**
	 * The same answer through the entry point getNext() really calls.
	 */
	public function testTheNewsRouteHonoursTheImageOptOut()
	{
		$_POST['rss_feed'] = $this->feed;
		$_POST['rss_saveimages'] = '';

		$this->rss->init();

		$target = array();
		$source = array(
			'title'       => array('a headline'),
			'description' => array('<p><img src="http://127.0.0.1/logo.png" alt="x" /></p>'),
			'pubDate'     => array('Thu, 01 Jan 2026 00:00:00 +0000'),
		);

		$this->rss->copyNewsData($target, $source);

		self::assertSame('', $target['news_thumbnail'],
			'Nothing was downloaded, so nothing may be named as the thumbnail');
		self::assertDirectoryDoesNotExist($this->importDir(),
			'With image saving off the importer must not create its directory');
	}

	/**
	 * The positive control the two refusals above are worth nothing without.
	 *
	 * copyNewsData() is the one line joining getNext() to saveImages(), and
	 * news_thumbnail is filled from what saveImages() found. Delete either and
	 * the two tests above still pass while the news import quietly stops
	 * importing anything.
	 */
	public function testTheNewsRouteStillImportsWhenAskedTo()
	{
		require_once(__DIR__.'/import_remote_image_double.php');

		$_POST['rss_feed'] = $this->feed;
		$_POST['rss_saveimages'] = 1;

		$rss = new import_remote_image_double();
		$rss->init();
		$rss->bytes = base64_decode(self::PNG);

		$target = array();
		$source = array(
			'title'       => array('a headline'),
			'description' => array('<p><img src="http://127.0.0.1/logo.png" alt="x" /></p>'),
			'pubDate'     => array('Thu, 01 Jan 2026 00:00:00 +0000'),
		);

		$rss->copyNewsData($target, $source);

		$stored = glob($this->importDir().'/*');

		self::assertCount(1, $stored,
			'With image saving on the news route must still download what the feed carries');
		self::assertMatchesRegularExpression('/^logo_[0-9a-f]{10}\.png$/', basename($stored[0]));
		self::assertStringContainsString(basename($stored[0]), $target['news_thumbnail'],
			'news_thumbnail must name the image that was stored');
		self::assertStringNotContainsString('http://127.0.0.1/logo.png', $target['news_body'],
			'The body must be rewritten to the local copy');
	}

	/**
	 * @return string
	 */
	private function importDir()
	{
		return e_MEDIA.'images/'.substr(md5($this->feed), 0, 10);
	}
}
