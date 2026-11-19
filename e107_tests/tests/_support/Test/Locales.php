<?php

namespace Test;

/**
 * Locales borrowed for the length of one test; keep this in PHP 5.6 syntax.
 */
trait Locales
{
	/**
	 * Switches one locale category for one test, skipping the test where none of $locales is installed; call what it returns in a finally block.
	 *
	 * @param int $category an LC_* constant
	 * @param string[] $locales the names to try, in order
	 * @return callable puts the category back as it was found
	 */
	protected function withLocale($category, array $locales)
	{
		$saved = setlocale($category, 0);

		if(false === setlocale($category, $locales))
		{
			$this->markTestSkipped('No '.implode(' or ', $locales).' locale is installed.');
		}

		return static function () use ($category, $saved)
		{
			setlocale($category, $saved);
		};
	}
}
