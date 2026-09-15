<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Storage;

/**
 * Tests for {@see SiteFolder} on scratch trees outside the app: what a pair lists, what it
 * treats as regenerable, and what tidy() removes.
 *
 * @see https://github.com/e107inc/e107/issues/6498
 */
class SiteFolderTest extends \Codeception\Test\Unit
{
	const HASH = '0123456789';

	/** @var string */
	private $base;

	protected function _before()
	{
		require_once(e_HANDLER."Storage/SiteFolder.php");

		$this->base = sys_get_temp_dir().'/e107_site_folder_'.uniqid('', true);
		mkdir($this->base.'/media', 0777, true);
		mkdir($this->base.'/system', 0777, true);
	}

	protected function _after()
	{
		self::removeTree($this->base);
	}

	public function testAPairExistsWhenEitherRootIsADirectory()
	{
		$folder = $this->folder(self::HASH);

		self::assertFalse($folder->exists(), 'nothing on disk yet');

		mkdir($this->base.'/system/'.self::HASH);

		self::assertTrue($folder->exists(), 'a system root alone is enough');
	}

	public function testFilesListsBothRootsAsRootPrefixedRelativePathsInOrder()
	{
		$this->put('system/'.self::HASH.'/temp/pending.zip');
		$this->put('media/'.self::HASH.'/images/2026-07/a.jpg');
		$this->put('system/'.self::HASH.'/dkim_private.key');

		self::assertSame(
			array('media/images/2026-07/a.jpg', 'system/dkim_private.key', 'system/temp/pending.zip'),
			$this->folder(self::HASH)->files()
		);
	}

	public function testTheRegenerableCacheAndPlaceholdersAreNotListed()
	{
		$this->put('system/'.self::HASH.'/cache/content/S_Config_core.cache.php');
		$this->put('system/'.self::HASH.'/cache/banlist.php');
		$this->put('system/'.self::HASH.'/index.html');
		$this->put('system/'.self::HASH.'/logs/.htaccess');
		$this->put('media/'.self::HASH.'/images/index.html');
		$this->put('media/'.self::HASH.'/.htaccess');

		self::assertSame(array('media/.htaccess'), $this->folder(self::HASH)->files(),
			'the system cache, every index.html and the system .htaccess guards are the site\'s own to write again; a media .htaccess is not');
	}

	public function testHoldsFilesIsFalseWhenOnlyRegenerableFilesRemain()
	{
		$this->put('system/'.self::HASH.'/cache/content/S_Config_core.cache.php');
		$this->put('media/'.self::HASH.'/images/index.html');

		self::assertFalse($this->folder(self::HASH)->holdsFiles());

		$this->put('media/'.self::HASH.'/images/b.png');

		self::assertTrue($this->folder(self::HASH)->holdsFiles());
	}

	public function testSummaryCountsSizeAndNewestTimeOverTheListedFilesOnly()
	{
		$a = $this->put('media/'.self::HASH.'/images/a.jpg', 'aaaa');
		$b = $this->put('system/'.self::HASH.'/temp/b.zip', 'bb');
		$cache = $this->put('system/'.self::HASH.'/cache/content/c.cache.php', 'cccccccc');
		touch($a, 1700000000);
		touch($b, 1700000500);
		touch($cache, 1800000000);

		self::assertSame(array('files' => 2, 'bytes' => 6, 'newest' => 1700000500), $this->folder(self::HASH)->summary());
	}

	public function testAnAbsentPairSummarisesToNothing()
	{
		self::assertSame(array('files' => 0, 'bytes' => 0, 'newest' => 0), $this->folder(self::HASH)->summary());
	}

	public function testTheKnownBadPairIsTheHashOfTwoEmptyValues()
	{
		self::assertTrue($this->folder(\e107::getInstance()->makeSiteHash('', ''))->isKnownBad());
		self::assertFalse($this->folder(self::HASH)->isKnownBad());
	}

	public function testPathJoinsARootPrefixedRelativePathOntoItsRoot()
	{
		$folder = $this->folder(self::HASH);

		self::assertSame($this->base.'/media/'.self::HASH.'/images/a.jpg', $folder->path('media/images/a.jpg'));
		self::assertSame($this->base.'/system/'.self::HASH, $folder->path('system'));
	}

	public function testPathRefusesAPathUnderNeitherRoot()
	{
		$refused = null;

		try
		{
			$this->folder(self::HASH)->path('elsewhere/a.jpg');
		}
		catch(\InvalidArgumentException $e)
		{
			$refused = $e;
		}

		self::assertNotNull($refused, 'a relative path must name the media or the system root');
	}

	public function testASymbolicLinkToADirectoryIsListedButNotEntered()
	{
		$this->put('elsewhere/inside.txt');
		mkdir($this->base.'/media/'.self::HASH, 0777, true);

		if(!@symlink($this->base.'/elsewhere', $this->base.'/media/'.self::HASH.'/link'))
		{
			self::markTestSkipped('this filesystem takes no symbolic links');
		}

		self::assertSame(array('media/link'), $this->folder(self::HASH)->files());
	}

	public function testTidyDeletesWhatIsRegenerableAndEveryDirectoryLeftEmpty()
	{
		$this->put('system/'.self::HASH.'/cache/content/S_Config_core.cache.php');
		$this->put('system/'.self::HASH.'/index.html');
		$this->put('media/'.self::HASH.'/images/index.html');
		mkdir($this->base.'/media/'.self::HASH.'/avatars/upload', 0777, true);

		self::assertTrue($this->folder(self::HASH)->tidy(), 'nothing worth keeping, so both roots go');
		self::assertFalse($this->folder(self::HASH)->exists());
	}

	public function testTidyKeepsAPairThatStillHoldsFiles()
	{
		$kept = $this->put('media/'.self::HASH.'/images/a.jpg');
		$cache = $this->put('system/'.self::HASH.'/cache/db/news.php');

		self::assertFalse($this->folder(self::HASH)->tidy());
		self::assertTrue(is_file($kept), 'a file the site does not regenerate stays');
		self::assertFalse(is_file($cache), 'the cache file goes');
		self::assertFalse(is_dir($this->base.'/system/'.self::HASH), 'the system root was empty once its cache went');
	}

	/**
	 * @param string $hash
	 * @return SiteFolder
	 */
	private function folder($hash)
	{
		return new SiteFolder($hash, $this->base.'/media', $this->base.'/system');
	}

	/**
	 * @param string $relative relative to the scratch base
	 * @param string $content
	 * @return string the file's absolute path
	 */
	private function put($relative, $content = 'x')
	{
		$path = $this->base.'/'.$relative;

		if(!is_dir(dirname($path)))
		{
			mkdir(dirname($path), 0777, true);
		}

		file_put_contents($path, $content);

		return $path;
	}

	/**
	 * @param string $dir
	 * @return void
	 */
	private static function removeTree($dir)
	{
		if(!is_dir($dir))
		{
			return;
		}

		foreach(scandir($dir) as $entry)
		{
			if($entry === '.' || $entry === '..')
			{
				continue;
			}

			$path = $dir.'/'.$entry;
			if(is_dir($path) && !is_link($path))
			{
				chmod($path, 0777);
				self::removeTree($path);
				continue;
			}

			unlink($path);
		}

		rmdir($dir);
	}
}
