<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims\Internal;

/**
 * @param string $extension
 * @return bool
 */
function extension_loaded($extension)
{
	if (e_dateAlternateTest::$alternate_formatter) return false;

	return \extension_loaded($extension);
}

/**
 * @param string $constant_name
 * @return bool
 */
function defined($constant_name)
{
	if (e_dateAlternateTest::$alternate_locale) return false;

	return \defined($constant_name);
}

/**
 * @param int              $category
 * @param array|string|int $locales
 * @param string           ...$rest
 * @return false|string
 */
function setlocale($category, $locales, ...$rest)
{
	if (e_dateAlternateTest::$alternate_locale) return 'nl_NL';

	return \setlocale($category, $locales, ...$rest);
}


class e_dateAlternateTest extends \e_dateTest
{
	/**
	 * @var bool
	 */
	public static $alternate_formatter;
	/**
	 * @var bool
	 */
	public static $alternate_locale;

	public function _before()
	{
		self::$alternate_formatter = true;
		parent::_before();
	}

	public function _after()
	{
		parent::_after();
		self::$alternate_formatter = false;
	}

	public function testConvert_dateDutch()
	{
		self::$alternate_formatter = false;
		self::$alternate_locale = true;

		try
		{

			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'long');
			$expected = 'maandag 05 februari 2018 - 12:45:03';
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'inputtime');
			$expected = '12:45 P.M.';
			$this->assertEquals($expected, $actual);
		}
		finally
		{
			self::$alternate_locale = false;
		}
	}

	public function testStrftimeFollowsALocaleChange()
	{
		self::$alternate_formatter = false;
		$timestamp = gmmktime(12, 0, 0, 2, 5, 2018);

		$this->assertSame('February', \eShims::strftime('%B', $timestamp));

		self::$alternate_locale = true;

		try
		{
			$this->assertSame('februari', \eShims::strftime('%B', $timestamp));
		}
		finally
		{
			self::$alternate_locale = false;
		}
	}

	public function testStrftimeFollowsATimeZoneChange()
	{
		self::$alternate_formatter = false;
		$timestamp = gmmktime(12, 0, 0, 2, 5, 2018);

		$this->assertSame('12:00', \eShims::strftime('%H:%M', $timestamp));

		date_default_timezone_set('Asia/Kolkata');

		try
		{
			$this->assertSame('17:30', \eShims::strftime('%H:%M', $timestamp));
		}
		finally
		{
			date_default_timezone_set('UTC');
		}
	}

	/** America/Danmarkshavn reads +00:00 today, as UTC does, but was three hours behind UTC in 1990. */
	public function testStrftimeFollowsAChangeBetweenZonesWithTheSameOffset()
	{
		self::$alternate_formatter = false;
		$timestamp = gmmktime(12, 0, 0, 1, 15, 1990);

		\eShims::strftime('%H:%M', $timestamp);

		date_default_timezone_set('America/Danmarkshavn');

		try
		{
			$uncached = substr(\eShims::strftime('%H:%M #1990', $timestamp), 0, 5);
			$this->assertSame($uncached, \eShims::strftime('%H:%M', $timestamp));
		}
		finally
		{
			date_default_timezone_set('UTC');
		}
	}

	public function testStrftimeFollowsAPatternChange()
	{
		self::$alternate_formatter = false;
		$timestamp = gmmktime(12, 0, 0, 2, 5, 2018);

		$this->assertSame('2018', \eShims::strftime('%Y', $timestamp));
		$this->assertSame('02', \eShims::strftime('%m', $timestamp));
	}
}
