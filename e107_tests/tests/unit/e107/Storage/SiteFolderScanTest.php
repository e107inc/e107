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
 * Tests for {@see SiteFolderScan} on scratch trees outside the app: which directory names count
 * as site folders, which are left out, and the order they come back in.
 *
 * @see https://github.com/e107inc/e107/issues/6498
 */
class SiteFolderScanTest extends \Codeception\Test\Unit
{
	const ACTIVE = 'aaaaaaaaaa';

	/** @var string */
	private $base;

	protected function _before()
	{
		require_once(e_HANDLER."Storage/SiteFolder.php");
		require_once(e_HANDLER."Storage/SiteFolderScan.php");

		$this->base = sys_get_temp_dir().'/e107_site_folder_scan_'.uniqid('', true);
		mkdir($this->base.'/media', 0777, true);
		mkdir($this->base.'/system', 0777, true);
	}

	protected function _after()
	{
		self::removeTree($this->base);
	}

	public function testTheActiveHashIsNeverACandidate()
	{
		mkdir($this->base.'/media/'.self::ACTIVE);
		mkdir($this->base.'/system/'.self::ACTIVE);
		mkdir($this->base.'/media/0123456789');

		self::assertSame(array('0123456789'), array_keys($this->scan()->candidates()));
	}

	public function testOnlyLowerCaseTenHexNamesAreCandidates()
	{
		foreach(array('000000test', 'abc', 'abcdef01234', 'ABCDEF0123', '0123456789', 'index.html') as $entry)
		{
			mkdir($this->base.'/media/'.$entry);
		}

		self::assertSame(array('0123456789'), array_keys($this->scan()->candidates()));
	}

	public function testAFileNamedLikeAHashIsNotACandidate()
	{
		file_put_contents($this->base.'/media/0123456789', '');

		self::assertSame(array(), array_keys($this->scan()->candidates()));
	}

	public function testASymbolicLinkNamedLikeAHashIsNotACandidate()
	{
		mkdir($this->base.'/elsewhere');

		if(!@symlink($this->base.'/elsewhere', $this->base.'/media/0123456789'))
		{
			self::markTestSkipped('this filesystem takes no symbolic links');
		}

		self::assertSame(array(), array_keys($this->scan()->candidates()),
			'a merge must never reach outside the two bases through a link');
	}

	public function testACandidateUnderOnlyOneBaseIsStillListed()
	{
		mkdir($this->base.'/system/0123456789');

		$candidates = $this->scan()->candidates();

		self::assertSame(array('0123456789'), array_keys($candidates));
		self::assertTrue($candidates['0123456789']->exists());
	}

	public function testTheKnownBadPairComesFirstAndThenTheNewestFirst()
	{
		$knownBad = \e107::getInstance()->makeSiteHash('', '');
		$old = $this->put('media/2222222222/images/old.jpg');
		$new = $this->put('media/1111111111/images/new.jpg');
		$older = $this->put('media/'.$knownBad.'/images/older.jpg');
		touch($old, 1700000000);
		touch($new, 1700009999);
		touch($older, 1600000000);

		self::assertSame(array($knownBad, '1111111111', '2222222222'), self::hashes($this->scan()->candidates()));
	}

	public function testANumericHashIsStillHandedBackAsAString()
	{
		mkdir($this->base.'/media/1111111111');

		self::assertSame(array('1111111111'), self::hashes($this->scan()->candidates()));
	}

	/**
	 * @param SiteFolder[] $folders
	 * @return string[] their hashes in order; PHP turns a purely numeric hash into an integer key, so the keys are not used
	 */
	private static function hashes(array $folders)
	{
		$hashes = array();

		foreach($folders as $folder)
		{
			$hashes[] = $folder->hash();
		}

		return $hashes;
	}

	public function testMissingBaseDirectoriesGiveAnEmptyScan()
	{
		$scan = new SiteFolderScan($this->base.'/nowhere', $this->base.'/nowhere-either', self::ACTIVE);

		self::assertSame(array(), $scan->candidates());
	}

	public function testActiveAndKnownBadNameTheirPairsWhetherOrNotTheyExist()
	{
		$scan = $this->scan();

		self::assertSame(self::ACTIVE, $scan->active()->hash());
		self::assertFalse($scan->active()->exists());
		self::assertSame(\e107::getInstance()->makeSiteHash('', ''), $scan->knownBad()->hash());
		self::assertTrue($scan->knownBad()->isKnownBad());
		self::assertFalse($scan->knownBad()->exists());
	}

	public function testTheRunningSiteScansItsOwnBasesUnderItsOwnHash()
	{
		$scan = SiteFolderScan::ofThisSite();

		self::assertNotNull($scan, 'the suite\'s site keeps its media under its hash');
		self::assertSame(\e107::getInstance()->getSitePath(), $scan->active()->hash());
		self::assertSame(realpath(e_MEDIA), realpath($scan->active()->path('media')),
			'the media base is the parent of the folder the site resolves, overrides included');
		self::assertSame(realpath(e_SYSTEM), realpath($scan->active()->path('system')));
	}

	/**
	 * @return SiteFolderScan
	 */
	private function scan()
	{
		return new SiteFolderScan($this->base.'/media', $this->base.'/system', self::ACTIVE);
	}

	/**
	 * @param string $relative relative to the scratch base
	 * @return string the file's absolute path
	 */
	private function put($relative)
	{
		$path = $this->base.'/'.$relative;
		mkdir(dirname($path), 0777, true);
		file_put_contents($path, 'x');

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
				self::removeTree($path);
				continue;
			}

			unlink($path);
		}

		rmdir($dir);
	}
}
