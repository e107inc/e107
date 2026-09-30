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
 * The folder {@see e107::_init()} derives for a site whose e107_config.php carries no site_path.
 *
 * Boots e107_class.php in a subprocess through tests/_data/site_hash/boot.php with the
 * connection array class2.php builds on this branch, and reads back what it derived. The
 * suite's own instance is initialised once and cannot be re-initialised, so this is the
 * only way to observe a fresh derivation.
 *
 * @see https://github.com/e107inc/e107/issues/6498
 */
class e107SiteHashBootTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** @var string[] hashes whose media and system folders a boot created; nothing that was there before is touched */
	private $created = array();

	protected function _after()
	{
		foreach($this->created as $hash)
		{
			$this->removeSiteFolders($hash);
		}
	}

	public function testAFreshBootWithoutAPinnedSitePathDerivesTheFolderFromTheDatabaseNameAndPrefix()
	{
		list($answer, $lines) = $this->boot();

		self::assertSame($answer['expected'], $answer['site_path'],
			'the site path is the hash of the database name and the table prefix');
		self::assertSame('e107_media/'.$answer['expected'].'/', $answer['media'],
			'e_MEDIA lands under that hash');
		self::assertSame('e107_system/'.$answer['expected'].'/', $answer['system'],
			'e_SYSTEM lands under that hash');
		self::assertCount(1, $lines, "deriving the site path raised a diagnostic:\n".implode("\n", $lines));
	}

	public function testAFreshBootWithoutAPinnedSitePathCreatesNoFolderForEmptyCredentials()
	{
		list($answer) = $this->boot();

		self::assertNotSame($answer['known_bad'], $answer['site_path'],
			'the site path is not the hash of two empty values');
		self::assertSame($answer['known_bad_existed'], $answer['known_bad_exists'],
			'no media or system folder named by the hash of two empty values appears');
	}

	/**
	 * @return array the JSON answer boot.php printed, then every line it printed
	 */
	private function boot()
	{
		$expected = self::hashFor(e107::getMySQLConfig('defaultdb'), e107::getMySQLConfig('prefix'));
		if(!$this->hasSiteFolders($expected))
		{
			$this->created[] = $expected;
		}

		$php = "define('E107_BOOT_APP', ".var_export(APP_PATH, true)."); ";
		$php .= "require ".var_export(codecept_data_dir('site_hash/boot.php'), true).";";

		list($lines, $status) = $this->runInCli($php, '-d display_errors=1 -d log_errors=0 -d error_reporting='.(E_ALL & ~E_DEPRECATED));

		$answer = json_decode((string) end($lines), true);

		self::assertSame(0, $status, "boot.php exited with status $status:\n".implode("\n", $lines));
		self::assertTrue(is_array($answer), "boot.php printed no JSON answer:\n".implode("\n", $lines));

		if(!$answer['known_bad_existed'] && $answer['known_bad_exists'])
		{
			$this->created[] = $answer['known_bad'];
		}

		return array($answer, $lines);
	}

	/**
	 * @param string $db
	 * @param string $prefix
	 * @return string what {@see e107::makeSiteHash()} gives
	 */
	private static function hashFor($db, $prefix)
	{
		return e107::getInstance()->makeSiteHash($db, $prefix);
	}

	/**
	 * @param string $hash
	 * @return string[] the media and system folders that hash names
	 */
	private static function siteFolders($hash)
	{
		$root = rtrim(APP_PATH, '/').'/';

		return array($root.'e107_media/'.$hash, $root.'e107_system/'.$hash);
	}

	/**
	 * @param string $hash
	 * @return bool
	 */
	private function hasSiteFolders($hash)
	{
		foreach(self::siteFolders($hash) as $folder)
		{
			if(is_dir($folder))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $hash
	 * @return void
	 */
	private function removeSiteFolders($hash)
	{
		foreach(self::siteFolders($hash) as $folder)
		{
			self::removeTree($folder);
		}
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
