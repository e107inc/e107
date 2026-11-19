<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

class StrcollTest extends \Test\Unit
{
	const LATIN = array('Zebra', 'Users', 'Émoticônes', 'eForum', 'Banlist', 'about');

	const TURKISH = array('Zebra', 'çay', 'cuma', 'dünya', 'Ödeme', 'ozan', 'ılık', 'iyi', 'Işık');

	const CAPTIONS = array('Users', 'Re-send', 'Administrators', 'User Classes', 'Read more', 'Admin password');

	const TURKISH_LOCALE = array('tr_TR.UTF-8', 'tr_TR.utf8');

	public function testStrcollDefault()
	{
		$this->runStrcollImplementation(array(InternalShims::class, 'strcoll'));
	}

	public function testStrcollDefaultLegacy()
	{
		$this->runStrcollImplementation(array(\eShims::class, 'strcoll'));
	}

	public function testStrcollAlt()
	{
		$this->runStrcollImplementation(array(InternalShims::class, 'strcoll_alt'));
	}

	public function testStrcollAltPutsStringsThatAreNotUtf8LastInOneOrder()
	{
		if(!class_exists('Collator'))
		{
			$this->markTestSkipped('intl is not loaded.');
		}

		$words = array('Zebra', "a\xFF", 'about', "\xFEb", 'Émoticônes');
		$restore = $this->withLocale(LC_COLLATE, self::TURKISH_LOCALE);
		$exceptions = ini_set('intl.use_exceptions', '1');

		try
		{
			foreach(array($words, array_reverse($words), array_merge(array_slice($words, 2), array_slice($words, 0, 2))) as $input)
			{
				$this->assertSame(array('about', 'Émoticônes', 'Zebra', "a\xFF", "\xFEb"), $this->sortedBy(array(InternalShims::class, 'strcoll_alt'), $input));
			}
		}
		finally
		{
			ini_set('intl.use_exceptions', $exceptions);
			$restore();
		}
	}

	/**
	 * @param callable $implementation
	 */
	private function runStrcollImplementation($implementation)
	{
		foreach(array(array('C'), array('POSIX'), array('C.UTF-8', 'C.utf8')) as $locales)
		{
			$restore = $this->withLocale(LC_COLLATE, $locales);

			try
			{
				foreach(array(self::LATIN, self::TURKISH, self::CAPTIONS) as $words)
				{
					$this->assertSame($this->sortedBy('strcmp', $words), $this->sortedBy($implementation, $words), $locales[0].' sorts by byte');
				}
			}
			finally
			{
				$restore();
			}
		}

		if(!class_exists('Collator'))
		{
			$this->markTestSkipped('intl is not loaded.');
		}

		$restore = $this->withLocale(LC_COLLATE, self::TURKISH_LOCALE);

		try
		{
			$this->assertSame(0, call_user_func($implementation, 'Users', 'Users'));
			$this->assertSame(array('about', 'Banlist', 'eForum', 'Émoticônes', 'Users', 'Zebra'), $this->sortedBy($implementation, self::LATIN));
			$this->assertSame(array('cuma', 'çay', 'dünya', 'ılık', 'Işık', 'iyi', 'ozan', 'Ödeme', 'Zebra'), $this->sortedBy($implementation, self::TURKISH));
			$this->assertSame(array('Administrators', 'Admin password', 'Read more', 'Re-send', 'User Classes', 'Users'), $this->sortedBy($implementation, self::CAPTIONS));
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * @param callable $comparison
	 * @param string[] $words
	 * @return string[]
	 */
	private function sortedBy($comparison, array $words)
	{
		usort($words, $comparison);

		return $words;
	}
}
