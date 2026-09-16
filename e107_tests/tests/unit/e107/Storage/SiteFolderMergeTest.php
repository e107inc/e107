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
 * Tests for {@see SiteFolderMerge} on scratch trees outside the app: what moves, what stays,
 * what a second run does, and which targets are refused.
 *
 * @see https://github.com/e107inc/e107/issues/6498
 */
class SiteFolderMergeTest extends \Codeception\Test\Unit
{
	const FROM = 'ffffffffff';

	const TO = '0123456789';

	/** @var string */
	private $base;

	protected function _before()
	{
		require_once(e_HANDLER."Storage/SiteFolder.php");
		require_once(e_HANDLER."Storage/SiteFolderMerge.php");

		$this->base = sys_get_temp_dir().'/e107_site_folder_merge_'.uniqid('', true);
		mkdir($this->base.'/media', 0777, true);
		mkdir($this->base.'/system', 0777, true);
	}

	protected function _after()
	{
		self::removeTree($this->base);
	}

	public function testTheKnownBadPairIsRefusedAsATarget()
	{
		$refused = null;

		try
		{
			new SiteFolderMerge($this->folder(self::FROM), $this->folder(\e107::getInstance()->makeSiteHash('', '')));
		}
		catch(\InvalidArgumentException $e)
		{
			$refused = $e;
		}

		self::assertNotNull($refused);
	}

	public function testMergingAPairIntoItselfIsRefused()
	{
		$refused = null;

		try
		{
			new SiteFolderMerge($this->folder(self::FROM), $this->folder(self::FROM));
		}
		catch(\InvalidArgumentException $e)
		{
			$refused = $e;
		}

		self::assertNotNull($refused);
	}

	public function testThePlanMovesEveryFileWithNoCounterpartInTheTargetAndListsTheRest()
	{
		$this->put('media/'.self::FROM.'/images/2026-07/a.jpg');
		$this->put('system/'.self::FROM.'/temp/pending.zip');
		$this->put('media/'.self::FROM.'/images/2026-07/c.jpg');
		$this->put('media/'.self::TO.'/images/2026-07/c.jpg');

		self::assertSame(
			array('moves' => array('media/images/2026-07/a.jpg', 'system/temp/pending.zip'), 'collisions' => array('media/images/2026-07/c.jpg')),
			$this->merge()->plan()
		);
	}

	public function testApplyingMovesFilesAndCreatesTheTargetDirectoriesTheyNeed()
	{
		$this->put('media/'.self::FROM.'/images/2026-07/a.jpg', 'photo');
		$this->put('system/'.self::FROM.'/temp/pending.zip', 'upload');
		$this->put('system/'.self::FROM.'/dkim_private.key', 'key');

		$result = $this->merge()->apply();

		self::assertSame(array('media/images/2026-07/a.jpg', 'system/dkim_private.key', 'system/temp/pending.zip'), $result['moved']);
		self::assertSame(array(), $result['collisions']);
		self::assertSame(array(), $result['failed']);
		self::assertSame('photo', file_get_contents($this->base.'/media/'.self::TO.'/images/2026-07/a.jpg'));
		self::assertSame('upload', file_get_contents($this->base.'/system/'.self::TO.'/temp/pending.zip'));
		self::assertSame('key', file_get_contents($this->base.'/system/'.self::TO.'/dkim_private.key'));
		self::assertFalse($this->folder(self::FROM)->exists(), 'nothing was left, so the source pair is gone');
	}

	public function testApplyingLeavesACollisionInPlaceWithTheTargetBytesUntouched()
	{
		$this->put('media/'.self::FROM.'/images/c.jpg', 'from the wrong folder');
		$this->put('media/'.self::TO.'/images/c.jpg', 'the original');

		$result = $this->merge()->apply();

		self::assertSame(array('media/images/c.jpg'), $result['collisions']);
		self::assertSame(array(), $result['moved']);
		self::assertSame('the original', file_get_contents($this->base.'/media/'.self::TO.'/images/c.jpg'));
		self::assertSame('from the wrong folder', file_get_contents($this->base.'/media/'.self::FROM.'/images/c.jpg'));
		self::assertTrue($this->folder(self::FROM)->holdsFiles(), 'the source keeps what it could not move');
	}

	public function testApplyingDeletesTheSourceCacheAndPrunesWhatItEmptied()
	{
		$this->put('media/'.self::FROM.'/images/a.jpg');
		$this->put('media/'.self::FROM.'/images/index.html');
		$this->put('system/'.self::FROM.'/cache/content/S_Config_core.cache.php');
		$this->put('system/'.self::FROM.'/cache/banlist.php');
		$this->put('system/'.self::FROM.'/.htaccess');
		mkdir($this->base.'/system/'.self::FROM.'/backup', 0777, true);

		$result = $this->merge()->apply();

		self::assertSame(array('media/images/a.jpg'), $result['moved']);
		self::assertFalse($this->folder(self::FROM)->exists());
		self::assertFalse(is_file($this->base.'/media/'.self::TO.'/images/index.html'), 'placeholders are not carried across');
		self::assertFalse(is_file($this->base.'/system/'.self::TO.'/cache/banlist.php'), 'nor is the cache');
	}

	public function testApplyingTwiceMovesNothingTheSecondTime()
	{
		$this->put('media/'.self::FROM.'/images/a.jpg');

		$first = $this->merge()->apply();
		$second = $this->merge()->apply();

		self::assertSame(array('media/images/a.jpg'), $first['moved']);
		self::assertSame(array('moved' => array(), 'collisions' => array(), 'failed' => array()), $second);
	}

	public function testARunAfterTheOperatorClearedACollisionFinishesTheJob()
	{
		$this->put('media/'.self::FROM.'/images/c.jpg', 'newer');
		$this->put('media/'.self::TO.'/images/c.jpg', 'older');

		$this->merge()->apply();
		unlink($this->base.'/media/'.self::TO.'/images/c.jpg');
		$result = $this->merge()->apply();

		self::assertSame(array('media/images/c.jpg'), $result['moved']);
		self::assertSame('newer', file_get_contents($this->base.'/media/'.self::TO.'/images/c.jpg'));
		self::assertFalse($this->folder(self::FROM)->exists());
	}

	public function testThePluginMediaSubtreeMovesLikeAnyOther()
	{
		$this->put('media/'.self::FROM.'/plugins/forum/attachments/2026-07/post.png', 'attachment');

		$result = $this->merge()->apply();

		self::assertSame(array('media/plugins/forum/attachments/2026-07/post.png'), $result['moved']);
		self::assertSame('attachment', file_get_contents($this->base.'/media/'.self::TO.'/plugins/forum/attachments/2026-07/post.png'));
	}

	public function testAFileThatCannotBeMovedIsReportedWithTheReasonAndTheRestStillMoves()
	{
		if(function_exists('posix_geteuid') && posix_geteuid() === 0)
		{
			self::markTestSkipped('root can write into any directory, so nothing here can be made to fail');
		}

		$this->put('media/'.self::FROM.'/images/blocked.jpg');
		$this->put('media/'.self::FROM.'/images/index.html');
		$this->put('media/'.self::FROM.'/files/free.txt');
		mkdir($this->base.'/media/'.self::TO.'/images', 0555, true);

		$result = $this->merge()->apply();

		self::assertSame(array('media/files/free.txt'), $result['moved']);
		self::assertSame(array('media/images/blocked.jpg'), array_keys($result['failed']));
		self::assertNotSame('', $result['failed']['media/images/blocked.jpg'], 'the reason PHP gave is passed on');
		self::assertTrue(is_file($this->base.'/media/'.self::FROM.'/images/blocked.jpg'), 'the file stays where it was');
		self::assertTrue(is_file($this->base.'/media/'.self::FROM.'/images/index.html'),
			'a source that could not be emptied keeps its placeholders');
	}

	/**
	 * @return SiteFolderMerge from FROM into TO
	 */
	private function merge()
	{
		return new SiteFolderMerge($this->folder(self::FROM), $this->folder(self::TO));
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

		chmod($dir, 0777);

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
