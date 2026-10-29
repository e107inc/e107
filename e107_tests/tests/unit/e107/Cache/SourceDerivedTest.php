<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Cache;

/**
 * A derived value is worked out again exactly when its source changes.
 */
class SourceDerivedTest extends \Codeception\Test\Unit
{
	use \Test\ScratchFiles;

	/** @var string */
	private $dir;

	/** @var string */
	private $source;

	/** @var int */
	private $derived = 0;

	protected function _before()
	{
		$this->dir = $this->makeScratchDir('source_derived');
		mkdir($this->dir.'cache');
		$this->source = $this->dir.'source.php';
		$this->derived = 0;
	}

	protected function _after()
	{
		$this->removeScratch();
	}

	public function testASourceThatStaysPutIsWorkedOutOnce()
	{
		$this->writeSource('alpha', time() - 10);

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')));
		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')), 'a later request reads the entry');
		self::assertSame(1, $this->derived);
	}

	public function testAChangedSourceIsWorkedOutAgain()
	{
		$this->writeSource('alpha', time() - 10);
		$this->store()->get($this->source, array($this, 'derive'));

		$this->writeSource('omega', time() - 5);

		self::assertSame('OMEGA', $this->store()->get($this->source, array($this, 'derive')));
		self::assertSame(2, $this->derived);
	}

	public function testAChangeInTheSecondTheEntryWasRecordedIsCaughtByTheHash()
	{
		$racy = time() + 60;
		$this->writeSource('alpha', $racy);
		$this->store()->get($this->source, array($this, 'derive'));

		$this->writeSource('gamma', $racy);

		self::assertSame('GAMMA', $this->store()->get($this->source, array($this, 'derive')), 'same size, same mtime, same inode');
	}

	public function testTheSameInstanceSeesASameSecondChange()
	{
		$racy = time() + 60;
		$store = $this->store();
		$this->writeSource('alpha', $racy);
		$store->get($this->source, array($this, 'derive'));

		$this->writeSource('gamma', $racy);

		self::assertSame('GAMMA', $store->get($this->source, array($this, 'derive')));
	}

	public function testAnUnchangedSourceIsNotWorkedOutAgainWhenItsEntryIsConfirmed()
	{
		$this->writeSource('alpha', time() + 60);
		$this->store()->get($this->source, array($this, 'derive'));

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')));
		self::assertSame(1, $this->derived, 'the hash matched, so the old value stands');
	}

	public function testOneEntryFilePerSource()
	{
		foreach(array('alpha', 'beta', 'gamma') as $i => $word)
		{
			$this->writeSource($word, time() - 30 + $i);
			$this->store()->get($this->source, array($this, 'derive'));
		}

		self::assertCount(1, glob($this->dir.'cache/unit_*.php'));
	}

	public function testATornEntryFileIsReplaced()
	{
		if(PHP_VERSION_ID < 70000)
		{
			self::markTestSkipped('PHP 5 cannot recover from a parse error in an included file');
		}

		$this->writeSource('alpha', time() - 10);
		$this->store()->get($this->source, array($this, 'derive'));
		$entries = glob($this->dir.'cache/unit_*.php');
		file_put_contents($entries[0], "<?php\nreturn array('format' => ");

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')));
		self::assertTrue(is_array(include $entries[0]));
	}

	public function testAnEntryMadeForAnotherPathIsIgnored()
	{
		$this->writeSource('alpha', time() - 10);
		$this->store()->get($this->source, array($this, 'derive'));
		$entries = glob($this->dir.'cache/unit_*.php');
		$entry = include $entries[0];
		$entry['paths'][0] = '/elsewhere/source.php';
		$entry['value'] = 'FOREIGN';
		file_put_contents($entries[0], "<?php\nreturn ".var_export($entry, true).";\n");

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')));
	}

	public function testAChangedDependencyIsWorkedOutAgain()
	{
		$dependency = $this->dir.'dependency.php';
		file_put_contents($dependency, 'one');
		touch($dependency, time() - 10);
		$this->writeSource('alpha', time() - 10);
		$this->store()->get($this->source, array($this, 'derive'), array($dependency));

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive'), array($dependency)));
		self::assertSame(1, $this->derived);

		file_put_contents($dependency, 'two');
		touch($dependency, time() - 5);
		clearstatcache();

		$this->store()->get($this->source, array($this, 'derive'), array($dependency));
		self::assertSame(2, $this->derived);
		self::assertNull($this->store()->get($this->source, array($this, 'derive'), array($this->dir.'gone.php')));
	}

	public function testEntriesInAFormatNoLongerReadAreDeletedOnTheFirstWrite()
	{
		$legacy = $this->dir.'cache/unit_'.str_repeat('a', 64).'_'.str_repeat('b', 64).'.php';
		$other = $this->dir.'cache/unitother_v1_x.php';
		file_put_contents($legacy, "<?php\nreturn array();\n");
		file_put_contents($other, "<?php\nreturn array();\n");
		$this->writeSource('alpha', time() - 10);

		$this->store()->get($this->source, array($this, 'derive'));

		self::assertFalse(file_exists($legacy));
		self::assertTrue(file_exists($other), 'another prefix is left alone');
		self::assertCount(1, glob($this->dir.'cache/unit_v1_*.php'));
	}

	public function testAnEntryThatCannotSettleIsNotRewrittenOnEveryRequest()
	{
		$this->writeSource('alpha', time() + 3600);
		$this->store()->get($this->source, array($this, 'derive'));
		$entries = glob($this->dir.'cache/unit_*.php');
		$entry = include $entries[0];
		$entry['recorded'] -= 5;
		$written = "<?php\nreturn ".var_export($entry, true).";\n";
		file_put_contents($entries[0], $written);

		self::assertSame('ALPHA', $this->store()->get($this->source, array($this, 'derive')));
		self::assertSame($written, file_get_contents($entries[0]), 'a source dated ahead of the clock is hashed, not rewritten');
	}

	public function testAnUnreadableSourceIsNull()
	{
		self::assertNull($this->store()->get($this->dir.'missing.php', array($this, 'derive')));
		self::assertSame(0, $this->derived);
	}

	public function testWithoutADirectoryEntriesLiveForTheRequest()
	{
		$this->writeSource('alpha', time() - 10);
		$store = new SourceDerived('unit', null, array('e107', 'writeFileAtomic'));

		self::assertSame('ALPHA', $store->get($this->source, array($this, 'derive')));
		self::assertSame('ALPHA', $store->get($this->source, array($this, 'derive')));
		self::assertSame(1, $this->derived);
		self::assertCount(0, glob($this->dir.'cache/*'));
	}

	/**
	 * @param string $source
	 * @return string
	 */
	public function derive($source)
	{
		$this->derived++;

		return strtoupper(trim($source));
	}

	/**
	 * @return SourceDerived
	 */
	private function store()
	{
		return new SourceDerived('unit', $this->dir.'cache/', array('e107', 'writeFileAtomic'));
	}

	/**
	 * @param string $text
	 * @param int    $mtime
	 * @return void
	 */
	private function writeSource($text, $mtime)
	{
		$handle = fopen($this->source, 'c');
		ftruncate($handle, 0);
		fwrite($handle, $text);
		fclose($handle);
		touch($this->source, $mtime);
		clearstatcache();
	}
}
