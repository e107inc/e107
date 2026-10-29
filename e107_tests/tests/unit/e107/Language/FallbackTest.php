<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Language;

use e107\Cache\SourceDerived;

/**
 * A language file that lacks phrases its English counterpart has ends up with each of them defined, and keeps its own.
 */
class FallbackTest extends \Codeception\Test\Unit
{
	use \Test\ScratchFiles;

	/** @var string */
	private $dir;

	/** @var string unique to the test, since a defined constant outlives it */
	private $p;

	/** @var array */
	private $reports = array();

	protected function _before()
	{
		$this->dir = $this->makeScratchDir('fallback');
		mkdir($this->dir.'cache');
		$this->p = 'FB'.strtoupper(substr(md5(uniqid('', true)), 0, 10)).'_';
		$this->reports = array();
	}

	protected function _after()
	{
		$this->removeScratch();
	}

	public function testAPhraseThePackLacksIsDefinedInEnglish()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "define('{$p}A', 'Apple');\ndefine('{$p}B', 'Banana');\ndefine('{$p}C', 'Cherry');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_front.php', "define('{$p}A', 'Pomo');");

		require $pack;
		$filled = $this->fallback()->fill($pack, false, false);

		self::assertSame(array($p.'B', $p.'C'), $filled);
		self::assertSame('Pomo', constant($p.'A'));
		self::assertSame('Banana', constant($p.'B'));
		self::assertSame('Cherry', constant($p.'C'));
	}

	public function testWithNoLanguageSubsAPhraseIsItsOwnName()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "define('{$p}A', 'Apple');\ndefine('{$p}B', 'Banana');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_front.php', "define('{$p}A', 'Pomo');");

		require $pack;
		$this->fallback()->fill($pack, true, false);

		self::assertSame($p.'B', constant($p.'B'));
		self::assertStringContainsString('as their own names', $this->reports[0]);
	}

	public function testACompletePackFindsNothingToDefine()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English.php', "define('{$p}A', 'Apple');\nif(!defined('{$p}B')) { define('{$p}B', 'Banana'); }");
		$pack = $this->writeFile('languages/Esperanto/Esperanto.php', "define('{$p}A', 'Pomo');\nif(!defined('{$p}B')) define('{$p}B', 'Banano');");

		require $pack;

		self::assertSame(array(), $this->fallback()->fill($pack, false, false));
		self::assertSame(array(), $this->entry()['gaps'], 'nothing is left for later requests to check either');
		self::assertSame(array(), $this->reports);
	}

	public function testAConstantInAValueIsResolvedWhenThePhraseIsDefinedAndNeverKept()
	{
		$p = $this->p;
		define($p.'DIR', 'plugins/');
		$this->writeFile('languages/English/English_admin.php', "define('{$p}A', 'Upload to '.{$p}DIR.' first');\ndefine('{$p}B', {$p}NOWHERE);");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_admin.php', '');

		$this->fallback()->fill($pack, false, false);

		self::assertSame('Upload to plugins/ first', constant($p.'A'));
		self::assertSame($p.'NOWHERE', constant($p.'B'), 'an undefined constant reads as its name, as PHP 7 printed it');
		self::assertSame(array('Upload to ', array($p.'DIR'), ' first'), $this->entry()['gaps'][$p.'A']);
	}

	public function testTheEnglishFileIsNeverRun()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English.php', "\$GLOBALS['{$p}ran'] = true;\nsetlocale(LC_ALL, 'C');\ndefine('{$p}A', 'Apple');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto.php', '');

		$this->fallback()->fill($pack, false, false);

		self::assertSame('Apple', constant($p.'A'));
		self::assertArrayNotHasKey($p.'ran', $GLOBALS);
	}

	public function testAMissingTranslatedFileGetsEveryEnglishPhrase()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_global.php', "define('{$p}A', 'Apple');\ndefine('{$p}B', 'Banana');");

		$filled = $this->fallback()->fill($this->dir.'languages/Esperanto/Esperanto_global.php', false, false);

		self::assertSame(array($p.'A', $p.'B'), $filled);
	}

	public function testWithoutAnEnglishCounterpartNothingHappensAndNothingIsSaid()
	{
		$pack = $this->writeFile('languages/Esperanto_global.php', "define('{$this->p}A', 'Pomo');");

		self::assertSame(array(), $this->fallback()->fill($pack, false, false));
		self::assertSame(array(), $this->reports);
	}

	public function testEnglishFilesAndOverlaysAreLeftAlone()
	{
		$p = $this->p;
		$english = $this->writeFile('languages/English/English.php', "define('{$p}A', 'Apple');");
		$overlay = $this->writeFile('languages/Esperanto/Esperanto_custom.php', '');

		self::assertSame(array(), $this->fallback()->fill($english, false, false));
		self::assertSame(array(), $this->fallback()->fill($overlay, false, false));
		self::assertFalse(defined($p.'A'));
	}

	public function testAFileIsFilledAndReportedOncePerRequest()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "define('{$p}A', 'Apple');\ndefine('{$p}B', 'Banana');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_front.php', '');
		$fallback = $this->fallback();

		$fallback->fill($pack, false, false);

		self::assertSame(array(), $fallback->fill($pack, false, false));
		self::assertCount(1, $this->reports);
		self::assertSame('2 phrase(s) missing from '.$pack.' were defined in English: '.$p.'A, '.$p.'B', $this->reports[0]);
	}

	public function testALongListOfNamesIsCutShort()
	{
		$english = '';

		for($i = 0; $i < 100; $i++)
		{
			$english .= "define('{$this->p}PHRASE_{$i}', 'x');\n";
		}

		$this->writeFile('languages/English/English.php', $english);
		$pack = $this->writeFile('languages/Esperanto/Esperanto.php', '');

		$this->fallback()->fill($pack, false, false);

		self::assertLessThan(1024, strlen($this->reports[0]));
		self::assertStringEndsWith(', ...', $this->reports[0]);
	}

	public function testAnArrayEntryCountsOnlyWhereTheLoaderDefinedIt()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "return ['{$p}A' => 'Apple', '{$p}B' => 'Banana'];");
		$this->writeFile('languages/English/English_admin.php', "return ['{$p}C' => 'Cherry'];");
		$applied = $this->writeFile('languages/Esperanto/Esperanto_front.php', "return ['{$p}A' => 'Pomo'];");
		$discarded = $this->writeFile('languages/Esperanto/Esperanto_admin.php', "return ['{$p}C' => 'Cxerizo'];");

		define($p.'A', 'Pomo');
		$this->fallback()->fill($applied, false, true);
		$this->fallback()->fill($discarded, false, false);

		self::assertSame('Banana', constant($p.'B'));
		self::assertSame('Cherry', constant($p.'C'), 'a loader that drops the array has defined nothing from it');
	}

	public function testAConditionalDefineIsCheckedWhenTheFileHasRun()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "define('{$p}A', 'Apple');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_front.php', "if(false) { define('{$p}A', 'Pomo'); }");

		include $pack;
		$this->fallback()->fill($pack, false, false);

		self::assertSame('Apple', constant($p.'A'));
	}

	public function testAFileThatStopsWithAnErrorKeepsWhatItCanAndTheRestIsFilled()
	{
		if(PHP_VERSION_ID < 80000)
		{
			self::markTestSkipped('an undefined constant throws only from PHP 8');
		}

		$p = $this->p;
		$this->writeFile('languages/English/English_admin.php',
			"define('{$p}A', 'Apple');\ndefine('{$p}B', 'Banana');\ndefine('{$p}C', 'Cherry');\ndefine('{$p}D', 'Date');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_admin.php',
			"define('{$p}A', 'Pomo');\ndefine('{$p}B', 'Banano '.{$p}TYPO);\ndefine('{$p}C', 'Cxerizo');");
		$fallback = $this->fallback();

		try
		{
			include $pack;
			self::fail('the pack was meant to stop on the undefined constant');
		}
		catch(\Error $e)
		{
			$fallback->recover($pack, false, $e);
		}

		$filled = $fallback->fill($pack, false, false);

		self::assertSame('Pomo', constant($p.'A'), 'defined before the error');
		self::assertSame('Cxerizo', constant($p.'C'), 'read after the error');
		self::assertSame('Banana', constant($p.'B'), 'the phrase that stopped the file comes from English');
		self::assertSame(array($p.'B', $p.'D'), $filled);
		self::assertStringContainsString('stopped at line 3', $this->reports[0]);
	}

	public function testTheFilesAboutToLoadKeepTheirPhrasesAndCanReadTheFilledOnes()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English.php', "define('{$p}YES', 'Yes');\ndefine('{$p}NO', 'No');\ndefine('{$p}MAYBE', 'Maybe');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto.php', "define('{$p}NO', 'Ne');");
		$custom = $this->writeFile('languages/Esperanto/Esperanto_custom.php', "define('{$p}MORE', {$p}YES.' please');\ndefine('{$p}MAYBE', 'Eble');");
		$fallback = $this->fallback();

		$fallback->leaveTo(array($custom, $this->dir.'missing_custom.php'));
		require $pack;
		$fallback->fill($pack, false, false);
		require $custom;
		$fallback->leaveTo(array());

		self::assertSame('Yes please', constant($p.'MORE'), 'the custom file reads a phrase the pack lacks after it was filled');
		self::assertSame('Eble', constant($p.'MAYBE'), 'what the custom file defines was left to it');
	}

	public function testLeavingToNothingEndsIt()
	{
		$p = $this->p;
		$this->writeFile('languages/English/English_front.php', "define('{$p}A', 'Apple');");
		$pack = $this->writeFile('languages/Esperanto/Esperanto_front.php', '');
		$custom = $this->writeFile('languages/Esperanto/Esperanto_custom.php', "define('{$p}A', 'Pomo');");
		$fallback = $this->fallback();

		$fallback->leaveTo(array($custom));
		$fallback->leaveTo(array());
		$fallback->fill($pack, false, false);

		self::assertSame('Apple', constant($p.'A'));
	}

	public function testARenamedLanguagesFolderIsRecognised()
	{
		$fallback = $this->fallback();

		self::assertSame('../mylangs/English/admin/lan_admin.php', $fallback->englishPath('../mylangs/German/admin/lan_admin.php'));
		self::assertSame('../mylangs/French/admin/lan_admin.php', $fallback->translatePath('../mylangs/German/admin/lan_admin.php', 'German', 'French'));
	}

	public function testTheEnglishCounterpartIsReadFromThePath()
	{
		$fallback = $this->fallback();

		self::assertSame('e107_languages/English/admin/lan_admin.php', $fallback->englishPath('e107_languages/German/admin/lan_admin.php'));
		self::assertSame('e107_languages/English/English.php', $fallback->englishPath('e107_languages/German/German.php'));
		self::assertSame('e107_plugins/forum/languages/English/English_front.php', $fallback->englishPath('e107_plugins/forum/languages/German/German_front.php'));
		self::assertSame('e107_plugins/poll/languages/English_global.php', $fallback->englishPath('e107_plugins/poll/languages/German_global.php'));
		self::assertSame('e107_plugins/user/languages/English.php', $fallback->englishPath('e107_plugins/user/languages/German.php'));
		self::assertSame('e107_themes/bootstrap5/languages/English_admin.php', $fallback->englishPath('e107_themes/bootstrap5/languages/ChineseSimp_admin.php'));
		self::assertSame('/srv/German/site/e107_languages/English/lan_news.php', $fallback->englishPath('/srv/German/site/e107_languages/German/lan_news.php'));
		self::assertNull($fallback->englishPath('/tmp/lan_news.php'));
		self::assertSame('e107_languages/English/lan_news.php', $fallback->englishPath('e107_languages/English/lan_news.php'));
	}

	public function testTranslatingAPathTouchesOnlyItsLanguageParts()
	{
		$fallback = $this->fallback();

		self::assertSame('/German/e107_languages/French/French.php', $fallback->translatePath('/German/e107_languages/German/German.php', 'German', 'French'));
		self::assertSame('languages/French_admin.php', $fallback->translatePath('languages/German_admin.php', 'German', 'French'));
		self::assertSame('languages/German/German.php', $fallback->translatePath('languages/German/German.php', 'German', 'German'));
	}

	/**
	 * @return Fallback
	 */
	private function fallback()
	{
		$reports = &$this->reports;
		$report = function ($message) use (&$reports)
		{
			$reports[] = $message;
		};

		$write = array('e107', 'writeFileAtomic');

		$newReader = function ()
		{
			return new PhraseFile();
		};

		return new Fallback($newReader, new SourceDerived('unit', $this->dir.'cache/', $write), new SourceDerived('unitread', $this->dir.'cache/', $write), array('languages', 'mylangs'), $report);
	}

	/**
	 * @return array the one entry the cache holds
	 */
	private function entry()
	{
		$entries = glob($this->dir.'cache/unit_*.php');
		self::assertCount(1, $entries);
		$entry = include $entries[0];

		return $entry['value'];
	}

	/**
	 * @param string $relative
	 * @param string $body source after the opening tag
	 * @return string
	 */
	private function writeFile($relative, $body)
	{
		return $this->writeScratchFile($this->dir.$relative, "<?php\n".$body."\n");
	}
}
